<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Interaction;
use App\Models\Service;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function index()
    {
        return inertia('admin/customer/Index');
    }

    public function detail($id = 0)
    {
        return inertia('admin/customer/Detail', [
            'data' => Customer::with([
                'assigned_user:id,username,name',
                'created_by_user:id,username,name',
                'updated_by_user:id,username,name',
                'services',
                'services.service:id,name',
            ])->findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = Customer::with([
            'services',
            'services.service:id,name',
        ]);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('phone', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('address', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('email', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('company', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('source', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('business_type', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && (in_array($filter['status'], ['active', 'inactive']))) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        $item = Customer::findOrFail($id);
        $item->id = null;
        $item->created_at = null;
        return inertia('admin/customer/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        $item = $id ? Customer::findOrFail($id) : new Customer(['active' => true]);
        return inertia('admin/customer/Editor', [
            'data' => $item,
            'users' => User::where('active', true)->orderBy('username', 'asc')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $validated =  $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:500',
            'company'        => 'nullable|string|max:255',
            'business_type'  => 'nullable|string|max:255',
            'active'         => 'required|boolean',
            'source'         => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
            'assigned_user_id' => 'nullable|exists:users,id',
        ]);

        $item = !$request->id ? new Customer() : Customer::findOrFail($request->post('id', 0));
        $item->fill($validated);
        $item->save();

        return redirect(route('admin.customer.index'))->with('success', "Pelanggan $item->name telah disimpan.");
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = Customer::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Client $item->name telah dihapus."
        ]);
    }

    /**
     * Mengekspor daftar client ke dalam format PDF atau Excel.
     */
    public function export(Request $request)
    {
        $items = Customer::orderBy('id', 'asc')->get();

        $title = 'Daftar Client';
        $filename = $title . ' - ' . env('APP_NAME') . Carbon::now()->format('dmY_His');

        if ($request->get('format') == 'pdf') {
            $pdf = Pdf::loadView('export.customer-list-pdf', compact('items', 'title'))
                ->setPaper('a4', 'landscape');
            return $pdf->download($filename . '.pdf');
        }

        if ($request->get('format') == 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Tambahkan header
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'Nama');
            $sheet->setCellValue('C1', 'Telepon');
            $sheet->setCellValue('D1', 'Email');
            $sheet->setCellValue('E1', 'Alamat');
            $sheet->setCellValue('F1', 'Nama Perusahaan');
            $sheet->setCellValue('G1', 'Jenis Usaha');
            $sheet->setCellValue('H1', 'Source');
            $sheet->setCellValue('I1', 'Status');
            $sheet->setCellValue('J1', 'Catatan');

            // Tambahkan data ke Excel
            $row = 2;
            foreach ($items as $item) {
                $sheet->setCellValue('A' . $row, $item->id);
                $sheet->setCellValue('B' . $row, $item->name);
                $sheet->setCellValue('C' . $row, $item->phone);
                $sheet->setCellValue('D' . $row, $item->email);
                $sheet->setCellValue('E' . $row, $item->address);
                $sheet->setCellValue('F' . $row, $item->company);
                $sheet->setCellValue('G' . $row, $item->business_type);
                $sheet->setCellValue('H' . $row, $item->source);
                $sheet->setCellValue('I' . $row, $item->active ? 'Aktif' : 'Tidak Aktif');
                $sheet->setCellValue('J' . $row, $item->notes);
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
}
