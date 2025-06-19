<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerService;
use App\Models\Service;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServiceController extends Controller
{
    public function index()
    {
        return inertia('admin/service/Index');
    }

    public function detail($id = 0)
    {
        return inertia('admin/service/Detail', [
            'data' => Service::with([
                'created_by_user:id,username',
                'updated_by_user:id,username',
            ])
            ->withCount('activeCustomers')
            ->findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = Service::query()->withCount('activeCustomers');

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && ($filter['status'] != 'all')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        $item = Service::findOrFail($id);
        $item->id = null;
        $item->created_at = null;
        return inertia('admin/service/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        $item = $id ? Service::findOrFail($id) : new Service([
            'active' => true,
        ]);
        return inertia('admin/service/Editor', [
            'data' => $item,
            'users' => User::where('active', true)->orderBy('username', 'asc')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $validated =  $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('services', 'name')->ignore($request->id),
            ],
            'active' => 'required|boolean',
            'notes'  => 'nullable|string|max:255',
        ]);

        $item = !$request->id ? new Service() : Service::findOrFail($request->post('id', 0));
        $item->fill($validated);
        $item->save();

        return redirect(route('admin.service.index'))->with('success', "Layanan $item->name telah disimpan.");
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = Service::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Layanan $item->name telah dihapus."
        ]);
    }

    /**
     * Mengekspor daftar layanan ke dalam format PDF atau Excel.
     */
    public function export(Request $request)
    {
        $items = Service::orderBy('id', 'asc')->get();
        $title = 'Daftar Layanan';
        $filename = $title . ' - ' . env('APP_NAME') . Carbon::now()->format('dmY_His');

        if ($request->get('format') == 'pdf') {
            $pdf = Pdf::loadView('export.service-list-pdf', compact('items', 'title'));
            return $pdf->download($filename . '.pdf');
        }

        if ($request->get('format') == 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Tambahkan header
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'Nama');
            $sheet->setCellValue('C1', 'Status');
            $sheet->setCellValue('D1', 'Catatan');

            // Tambahkan data ke Excel
            $row = 2;
            foreach ($items as $item) {
                $sheet->setCellValue('A' . $row, $item->id);
                $sheet->setCellValue('B' . $row, $item->name);
                $sheet->setCellValue('C' . $row, $item->active ? 'Aktif' : 'Tidak Aktif');
                $sheet->setCellValue('D' . $row, $item->notes);
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
