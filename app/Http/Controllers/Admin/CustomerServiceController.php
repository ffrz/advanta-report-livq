<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerService;
use App\Models\Service;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerServiceController extends Controller
{
    public function index()
    {
        return inertia('admin/customer-service/Index', [
            'customers' => Customer::query()->orderBy('name', 'asc')->get(),
            'services' => Service::query()->orderBy('name', 'asc')->get(),
        ]);
    }

    public function detail($id = 0)
    {
        return inertia('admin/customer-service/Detail', [
            'data' => CustomerService::with([
                'customer',
                'service',
                'created_by_user:id,username',
                'updated_by_user:id,username',
            ])->findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'id');
        $orderType = $request->get('order_type', 'asc');

        $items = $this->createQuery($request)
            ->orderBy($orderBy, $orderType)
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return response()->json($items);
    }

    public function editor($id = 0)
    {
        $item = $id ? CustomerService::findOrFail($id) : new CustomerService([
            'status' => CustomerService::Status_Active,
        ]);
        return inertia('admin/customer-service/Editor', [
            'data' => $item,
            'customers' => Customer::orderBy('name', 'asc')->get(),
            'services' => Service::orderBy('name', 'asc')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $validated =  $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'service_id'       => 'required|exists:services,id',
            'status'           => 'required|in:' . implode(',', array_keys(CustomerService::Statuses)),
            'description'      => 'nullable|string',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date',
            'notes'            => 'nullable|string',
        ]);

        $item = !$request->id ? new CustomerService() : CustomerService::findOrFail($request->post('id', 0));
        $item->fill($validated);
        $item->save();

        return redirect(route('admin.customer-service.index'))->with('success', "Intearksi #$item->id telah disimpan.");
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = CustomerService::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "CustomerService #$item->id telah dihapus."
        ]);
    }

    /**
     * Mengekspor daftar layanan client ke dalam format PDF atau Excel.
     */
    public function export(Request $request)
    {
        $items = $this->createQuery($request)->orderBy('id', 'desc')->get();

        $title = 'Daftar Layanan Client';
        $filename = $title . ' - ' . env('APP_NAME') . Carbon::now()->format('dmY_His');

        if ($request->get('format') == 'pdf') {
            $pdf = Pdf::loadView('export.customer-service-list-pdf', compact('items', 'title'))
                ->setPaper('A4', 'landscape');
            return $pdf->download($filename . '.pdf');
        }

        if ($request->get('format') == 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Tambahkan header
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'Client');
            $sheet->setCellValue('C1', 'Layanan');
            $sheet->setCellValue('D1', 'Deskripsi');
            $sheet->setCellValue('E1', 'Tanggal Mulai');
            $sheet->setCellValue('F1', 'Tanggal Berhenti');
            $sheet->setCellValue('G1', 'Catatan');

            // Tambahkan data ke Excel
            $row = 2;
            foreach ($items as $item) {
                $sheet->setCellValue('A' . $row, $item->id);
                $sheet->setCellValue('B' . $row, $item->customer->name . ' - ' . $item->customer->company . ' - ' . $item->customer->address);
                $sheet->setCellValue('C' . $row, $item->service->name);
                $sheet->setCellValue('D' . $row, $item->description);
                $sheet->setCellValue('E' . $row, $item->start_date);
                $sheet->setCellValue('F' . $row, $item->end_date);
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

    protected function createQuery(Request $request)
    {
        $filter = $request->get('filter', []);

        $q = CustomerService::with(['customer', 'service']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('subject', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('summary', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('notes', 'like', '%' . $filter['search'] . '%')
                    ->orWhereHas('customer', function ($q) use ($filter) {
                        $q->where('name', 'like', '%' . $filter['search'] . '%')
                            ->orWhere('company', 'like', '%' . $filter['search'] . '%');
                    })
                    ->orWhereHas('service', function ($q) use ($filter) {
                        $q->where('name', 'like', '%' . $filter['search'] . '%');
                    });
            });
        }

        if (!empty($filter['status']) && ($filter['status'] != 'all')) {
            $q->where('status', '=', $filter['status']);
        }

        return $q;
    }
}
