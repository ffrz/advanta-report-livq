<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Closing;
use App\Models\Service;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClosingController extends Controller
{
    public function index()
    {
        return inertia('admin/closing/Index', [
            'users' => User::where('active', true)->orderBy('username', 'asc')->get(),
            'services' => Service::orderBy('name', 'asc')->get(),
        ]);
    }

    public function detail($id = 0)
    {
        return inertia('admin/closing/Detail', [
            'data' => Closing::with([
                'user',
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
        $item = $id ? Closing::findOrFail($id) : new Closing([
            'user_id' => Auth::user()->id,
            'date' => Carbon::today(),
        ]);
        return inertia('admin/closing/Editor', [
            'data' => $item,
            'users' => User::where('active', true)->orderBy('username', 'asc')->get(),
            'customers' => Customer::orderBy('name', 'asc')->get(),
            'services' => Service::orderBy('name', 'asc')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $validated =  $request->validate([
            'user_id'          => 'required|exists:users,id',
            'customer_id'      => 'required|exists:customers,id',
            'service_id'       => 'required|exists:services,id',
            'date'             => 'required|date',
            'description'      => 'required|string|max:255',
            'amount'           => 'required|numeric|gt:0',
            'notes'            => 'nullable|string|max:500',
        ]);

        $item = !$request->id ? new Closing() : Closing::findOrFail($request->post('id', 0));
        $item->fill($validated);
        $item->save();

        return redirect(route('admin.closing.index'))->with('success', "Intearksi #$item->id telah disimpan.");
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = Closing::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Closing $item->name telah dihapus."
        ]);
    }

    /**
     * Mengekspor daftar closing ke dalam format PDF atau Excel.
     */
    public function export(Request $request)
    {
        $items = $this->createQuery($request)->orderBy('id', 'desc')->get();

        $title = 'Daftar Closing';
        $filename = $title . ' - ' . env('APP_NAME') . Carbon::now()->format('dmY_His');

        if ($request->get('format') == 'pdf') {
            $pdf = Pdf::loadView('export.closing-list-pdf', compact('items', 'title'))
                ->setPaper('A4', 'landscape');
            return $pdf->download($filename . '.pdf');
        }

        if ($request->get('format') == 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Tambahkan header
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'Tanggal');
            $sheet->setCellValue('C1', 'Sales');
            $sheet->setCellValue('D1', 'Client');
            $sheet->setCellValue('E1', 'Layanan');
            $sheet->setCellValue('F1', 'Deskripsi');
            $sheet->setCellValue('G1', 'Jumlah');
            $sheet->setCellValue('H1', 'Catatan');

            // Tambahkan data ke Excel
            $row = 2;
            foreach ($items as $item) {
                $sheet->setCellValue('A' . $row, $item->id);
                $sheet->setCellValue('B' . $row, $item->date);
                $sheet->setCellValue('C' . $row, $item->user->name .  ' (' . $item->user->username . ')');
                $sheet->setCellValue('D' . $row, $item->customer->name . ' - ' . $item->customer->company . ' - ' . $item->customer->address);
                $sheet->setCellValue('E' . $row, $item->service->name);
                $sheet->setCellValue('F' . $row, $item->description);
                $sheet->setCellValue('G' . $row, $item->amount);
                $sheet->setCellValue('H' . $row, $item->notes);
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

        $q = Closing::with(['user', 'customer', 'service']);

        if (!empty($filter['search'])) {
            $q->where('description', 'like', '%' . $filter['search'] . '%')
                ->orWhere('notes', 'like', '%' . $filter['search'] . '%')
                ->orWhereHas('customer', function ($q) use ($filter) {
                    $q->where('name', 'like', '%' . $filter['search'] . '%')
                        ->orWhere('company', 'like', '%' . $filter['search'] . '%');
                })
                ->orWhereHas('service', function ($q) use ($filter) {
                    $q->where('name', 'like', '%' . $filter['search'] . '%');
                });
        }

        if (!empty($filter['user_id']) && ($filter['user_id'] != 'all')) {
            $q->where('user_id', '=', $filter['user_id']);
        }

        if (!empty($filter['service_id']) && ($filter['service_id'] != 'all')) {
            $q->where('service_id', '=', $filter['service_id']);
        }

        if (!empty($filter['period']) && ($filter['period'] != 'all')) {
            if ($filter['period'] == 'this_month') {
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                $q->whereBetween('date', [$start, $end]);
            } elseif ($filter['period'] == 'last_month') {
                $start = Carbon::now()->subMonthNoOverflow()->startOfMonth();
                $end = Carbon::now()->subMonthNoOverflow()->endOfMonth();
                $q->whereBetween('date', [$start, $end]);
            } elseif ($filter['period'] == 'this_year') {
                $start = Carbon::now()->startOfYear();
                $end = Carbon::now()->endOfYear();
                $q->whereBetween('date', [$start, $end]);
            } elseif ($filter['period'] == 'last_year') {
                $start = Carbon::now()->subYear()->startOfYear();
                $end = Carbon::now()->subYear()->endOfYear();
                $q->whereBetween('date', [$start, $end]);
            } else {
                // Asumsikan filter['period'] dalam format YYYY-MM-DD
                try {
                    $date = Carbon::parse($filter['period']);
                    $q->whereDate('date', $date);
                } catch (\Exception $e) {
                    // Handle kesalahan parsing tanggal jika perlu
                }
            }
        }

        return $q;
    }
}
