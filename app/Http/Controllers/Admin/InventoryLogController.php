<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryLogController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $current_user = Auth::user();
        $q = User::query();

        if ($current_user->role == User::Role_BS) {
            // BS hanya melihat dirinya sendiri
            $q->where('id', $current_user->id);
        } elseif ($current_user->role == User::Role_Agronomist) {
            // Agronomist melihat dirinya sendiri + semua BS di bawahnya
            $q->where(function ($query) use ($current_user) {
                $query->where('parent_id', $current_user->id)
                    ->orWhere('id', $current_user->id);
            });
        }

        $users = $q->where('active', true)
            ->orderBy('name')
            ->get();

        return inertia('admin/inventory-log/Index', [
            'products' => Product::orderBy('name', 'asc')->get(['id', 'name']),
            'customers' => $this->getCustomers(),
            'users' => $users,
        ]);
    }

    public function detail($id = 0)
    {
        $item = InventoryLog::with(['product', 'product.category', 'user', 'customer', 'created_by', 'updated_by'])->findOrFail($id);
        // $this->authorize('view', $item);
        return inertia('admin/inventory-log/Detail', [
            'data' => $item,
        ]);
    }

    public function data(Request $request)
    {
        $current_user = Auth::user();

        $orderBy = $request->get('order_by', 'date');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = InventoryLog::with(['product', 'product.category', 'user', 'customer']);

        if ($current_user->role == User::Role_Agronomist) {
            $q->where(function ($query) use ($current_user) {
                $query->whereHas('user', function ($sub) use ($current_user) {
                    $sub->where('parent_id', $current_user->id);
                })
                    ->orWhere('user_id', $current_user->id);
            });
        } else if ($current_user->role == User::Role_BS) {
            $q->where('user_id', $current_user->id);
        }

        if (!empty($filter['search'])) {
            $search = '%' . $filter['search'] . '%';

            $q->where(function ($q) use ($search) {
                // Kolom langsung di tabel inventory_log
                $q->where('area', 'like', $search)
                    ->orWhere('notes', 'like', $search)
                    ->orWhere('lot_package', 'like', $search)

                    // Relasi: nama produk
                    ->orWhereHas('product', function ($sub) use ($search) {
                        $sub->where('name', 'like', $search);
                    })

                    // Relasi: nama kategori produk
                    ->orWhereHas('product.category', function ($sub) use ($search) {
                        $sub->where('name', 'like', $search);
                    })

                    // Relasi: nama pelanggan
                    ->orWhereHas('customer', function ($sub) use ($search) {
                        $sub->where('name', 'like', $search);
                    });
            });
        }


        if (!empty($filter['user_id']) && ($filter['user_id'] != 'all')) {
            $q->where('user_id', '=', $filter['user_id']);
        }

        if (!empty($filter['customer_id']) && ($filter['customer_id'] != 'all')) {
            $q->where('customer_id', '=', $filter['customer_id']);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        $item = InventoryLog::findOrFail($id);
        $item->id = null;

        $this->authorize('update', $item);

        return inertia('admin/inventory-log/Editor', [
            'data' => $item,
            'categories' => ProductCategory::all(['id', 'name']),
        ]);
    }

    public function editor($id = 0)
    {
        $currentUserId = Auth::user()->id;
        $item = $id ? InventoryLog::findOrFail($id) : new InventoryLog(
            [
                'check_date' => current_date(),
                'user_id' => $currentUserId,
            ]
        );

        $this->authorize('update', $item);

        return inertia('admin/inventory-log/Editor', [
            'data' => $item,
            'products' => Product::orderBy('name', 'asc')->get(['id', 'name', 'weight']),
            'customers' => $this->getCustomers(),
            'users' => User::orderBy('name', 'asc')->get(['id', 'name']),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'product_id'       => ['required', 'integer', 'exists:products,id'],
            'customer_id'      => ['required', 'integer', 'exists:customers,id'],
            'user_id'          => ['required', 'integer', 'exists:users,id'],
            'check_date'       => ['required', 'date'],
            'area'             => ['required', 'string', 'max:255'],
            'lot_package'      => ['required', 'string', 'max:255'],
            'base_quantity'    => ['required', 'numeric', 'between:0,999999'],
            'quantity'         => ['required', 'numeric', 'between:0,999999.999'],
            'notes'            => ['nullable', 'string'],
        ], [
            'product_id.exists'     => 'Produk yang dipilih tidak ditemukan.',
            'customer_id.exists'    => 'Client yang dipilih tidak ditemukan.',
            'user_id.exists'        => 'Karyawan yang dipilih tidak ditemukan.',
            'check_date.required'   => 'Tanggal pemeriksaan wajib diisi.',
            'base_quantity.between' => 'Jumlah harus bilangan bulat.',
            'base_quantity.required' => 'Jumlah harus diisi.',
            'quantity.between'      => 'Jumlah harus antara 0 hingga 999999.999.',
            'quantity.required'     => 'Jumlah harus diisi.',
            'area.required'         => 'Area harus diisi.',
            'lot_package.required'  => 'Lot package harus diisi.',
        ]);

        $item = $request->id ? InventoryLog::findOrFail($request->id) : new InventoryLog();

        $this->authorize('update', $item);

        $item->fill($validated);
        $item->save();

        return redirect(route('admin.inventory-log.index'))
            ->with('success', "Log inventori #$item->id telah disimpan.");
    }

    public function delete($id)
    {
        $item = InventoryLog::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Log inventori #$item->id telah dihapus."
        ]);
    }

    /**
     * Mengekspor daftar client ke dalam format PDF atau Excel.
     */
    public function export(Request $request)
    {
        $items = InventoryLog::orderBy('name', 'asc')->get();

        $title = 'Daftar Log Inventory';
        $filename = $title . ' - ' . env('APP_NAME') . Carbon::now()->format('dmY_His');

        if ($request->get('format') == 'pdf') {
            $pdf = Pdf::loadView('export.inventory-log-list-pdf', compact('items', 'title'))
                ->setPaper('a4', 'landscape');
            return $pdf->download($filename . '.pdf');
        }

        if ($request->get('format') == 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Tambahkan header
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Kategori');
            $sheet->setCellValue('C1', 'Nama Varietas');
            $sheet->setCellValue('D1', 'Harga Distributor (Rp / sat)');
            $sheet->setCellValue('E1', 'Harga (Rp / sat)');
            $sheet->setCellValue('F1', 'Status');
            $sheet->setCellValue('G1', 'Catatan');

            // Tambahkan data ke Excel
            $row = 2;
            foreach ($items as $num => $item) {
                $sheet->setCellValue('A' . $row, $num + 1);
                $sheet->setCellValue('B' . $row, $item->category ? $item->category->name : '');
                $sheet->setCellValue('C' . $row, $item->name);
                $sheet->setCellValue('D' . $row, "$item->price_1 / $item->uom_1");
                $sheet->setCellValue('E' . $row, "$item->price_2 / $item->uom_2");
                $sheet->setCellValue('F' . $row, $item->active ? 'Aktif' : 'Tidak Aktif');
                $sheet->setCellValue('G' . $row, $item->notes);
                $row++;
            }

            // Kirim ke memori tanpa menyimpan file
            $response = new StreamedResponse(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            });

            // Atur header response untuk download
            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '.xlsx"');

            return $response;
        }

        return abort(400, 'Format tidak didukung');
    }

    private function getCustomers()
    {
        $currentUser = Auth::user();
        $customersQuery = Customer::query();
        if ($currentUser->role !== User::Role_Admin) {
            $customersQuery->where('assigned_user_id', $currentUser->id);
        }
        return $customersQuery->orderBy('name', 'asc')->get(['id', 'name']);
    }
}
