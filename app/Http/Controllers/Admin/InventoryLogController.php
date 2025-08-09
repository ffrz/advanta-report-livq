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
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryLogController extends Controller
{
    public function index()
    {
        return inertia('admin/inventory-log/Index', [
            // 'categories' => ProductCategory::all(['id', 'name']),
            'products' => Product::all(['id', 'name']),
            'customers' => Customer::all(['id', 'name']),
            'users' => User::all(['id', 'name']),
        ]);
    }

    public function detail($id = 0)
    {
        $item = InventoryLog::with(['product', 'product.category', 'user', 'customer'])->findOrFail($id);
        return inertia('admin/inventory-log/Detail', [
            'data' => $item,
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'date');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = InventoryLog::with(['product', 'product.category', 'user', 'customer']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('area', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        $item = InventoryLog::findOrFail($id);
        $item->id = null;
        return inertia('admin/inventory-log/Editor', [
            'data' => $item,
            'categories' => ProductCategory::all(['id', 'name']),
        ]);
    }

    public function editor($id = 0)
    {
        $item = $id ? InventoryLog::findOrFail($id) : new InventoryLog(
            [
                'check_date' => current_date(),
                'user_id' => Auth::user()->id,
            ]
        );
        return inertia('admin/inventory-log/Editor', [
            'data' => $item,
            'products' => Product::all(['id', 'name']),
            'customers' => Customer::all(['id', 'name']),
            'users' => User::all(['id', 'name']),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'product_id'       => ['nullable', 'integer', 'exists:products,id'],
            'customer_id'      => ['nullable', 'integer', 'exists:customers,id'],
            'user_id'          => ['nullable', 'integer', 'exists:users,id'],
            'check_date'       => ['required', 'date'],
            'area'             => ['required', 'string', 'max:255'],
            'lot_package'      => ['required', 'string', 'max:255'],
            'quantity'         => ['required', 'numeric', 'between:0,999999.999'],
            'notes'            => ['nullable', 'string'],
        ], [
            'product_id.exists'     => 'Produk yang dipilih tidak ditemukan.',
            'customer_id.exists'    => 'Client yang dipilih tidak ditemukan.',
            'user_id.exists'        => 'Karyawan yang dipilih tidak ditemukan.',
            'check_date.required'   => 'Tanggal pemeriksaan wajib diisi.',
            'quantity.between'      => 'Jumlah harus antara 0 hingga 999999.999.',
            'area.required'         => 'Area harus diisi.',
            'lot_package.required'  => 'Lot package harus diisi.',
        ]);

        $item = $request->id ? InventoryLog::findOrFail($request->id) : new InventoryLog();
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

    // /**
    //  * Mengekspor daftar client ke dalam format PDF atau Excel.
    //  */
    // public function export(Request $request)
    // {
    //     $items = InventoryLog::orderBy('name', 'asc')->get();

    //     $title = 'Daftar Varietas';
    //     $filename = $title . ' - ' . env('APP_NAME') . Carbon::now()->format('dmY_His');

    //     if ($request->get('format') == 'pdf') {
    //         $pdf = Pdf::loadView('export.inventory-log-list-pdf', compact('items', 'title'))
    //             ->setPaper('a4', 'landscape');
    //         return $pdf->download($filename . '.pdf');
    //     }

    //     if ($request->get('format') == 'excel') {
    //         $spreadsheet = new Spreadsheet();
    //         $sheet = $spreadsheet->getActiveSheet();

    //         // Tambahkan header
    //         $sheet->setCellValue('A1', 'No');
    //         $sheet->setCellValue('B1', 'Kategori');
    //         $sheet->setCellValue('C1', 'Nama Varietas');
    //         $sheet->setCellValue('D1', 'Harga Distributor (Rp / sat)');
    //         $sheet->setCellValue('E1', 'Harga (Rp / sat)');
    //         $sheet->setCellValue('F1', 'Status');
    //         $sheet->setCellValue('G1', 'Catatan');

    //         // Tambahkan data ke Excel
    //         $row = 2;
    //         foreach ($items as $num => $item) {
    //             $sheet->setCellValue('A' . $row, $num + 1);
    //             $sheet->setCellValue('B' . $row, $item->category ? $item->category->name : '');
    //             $sheet->setCellValue('C' . $row, $item->name);
    //             $sheet->setCellValue('D' . $row, "$item->price_1 / $item->uom_1");
    //             $sheet->setCellValue('E' . $row, "$item->price_2 / $item->uom_2");
    //             $sheet->setCellValue('F' . $row, $item->active ? 'Aktif' : 'Tidak Aktif');
    //             $sheet->setCellValue('G' . $row, $item->notes);
    //             $row++;
    //         }

    //         // Kirim ke memori tanpa menyimpan file
    //         $response = new StreamedResponse(function () use ($spreadsheet) {
    //             $writer = new Xlsx($spreadsheet);
    //             $writer->save('php://output');
    //         });

    //         // Atur header response untuk download
    //         $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //         $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '.xlsx"');

    //         return $response;
    //     }

    //     return abort(400, 'Format tidak didukung');
    // }
}
