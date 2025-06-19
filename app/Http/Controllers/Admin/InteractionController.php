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
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InteractionController extends Controller
{
    public function index()
    {
        return inertia('admin/interaction/Index');
    }

    public function detail($id = 0)
    {
        return inertia('admin/interaction/Detail', [
            'data' => Interaction::with([
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

    public function editor(Request $request, $id = 0)
    {
        $item = $id ? Interaction::findOrFail($id) : new Interaction([
            'status' => Interaction::Status_Done,
            'type' => Interaction::Type_Visit,
            'user_id' => Auth::user()->id,
            'date' => Carbon::now(),
            'customer_id' => $request->get('customer_id', null)
        ]);
        
        return inertia('admin/interaction/Editor', [
            'data' => $item,
            'users' => User::where('active', true)->orderBy('username', 'asc')->get(),
            'customers' => Customer::orderBy('name', 'asc')->get(),
            'services' => Service::orderBy('name', 'asc')->get(),
            'statuses' => Interaction::Statuses,
        ]);
    }

    public function save(Request $request)
    {
        $validated =  $request->validate([
            'user_id'          => 'required|exists:users,id',
            'customer_id'      => 'required|exists:customers,id',
            'service_id'       => 'required|exists:services,id',
            'date'             => 'required|date',
            'type'             => 'required|in:' . implode(',', array_keys(Interaction::Types)),
            'engagement_level' => 'required|in:' . implode(',', array_keys(Interaction::EngagementLevels)),
            'status'           => 'required|in:' . implode(',', array_keys(Interaction::Statuses)),
            'subject'          => 'required|string|max:255',
            'summary'          => 'nullable|string|max:500',
            'notes'            => 'nullable|string|max:500',
            'location'         => 'nullable|string|max:100',
            'image'            => 'nullable|image|max:5120',
            'image_path'       => 'nullable|string',
        ]);

        $item = !$request->id
            ? new Interaction()
            : Interaction::findOrFail($request->post('id', 0));

        // Handle image upload jika ada
        if ($request->hasFile('image')) {
            // Hapus file lama jika ada
            if ($item->image_path && file_exists(public_path($item->image_path))) {
                @unlink(public_path($item->image_path)); // pakai @ untuk suppress error jika file tidak ada
            }

            // Simpan file baru
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
            $validated['image_path'] = 'uploads/' . $filename; // timpah dengan path yang digenerate
        } else if (empty($validated['image_path'])) {
            // Hapus file lama jika ada
            if ($item->image_path && file_exists(public_path($item->image_path))) {
                @unlink(public_path($item->image_path)); // pakai @ untuk suppress error jika file tidak ada
            }
        }

        $item->fill($validated);
        $item->save();

        return redirect(route('admin.interaction.index'))->with('success', "Intearksi #$item->id telah disimpan.");
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = Interaction::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Interaction $item->name telah dihapus."
        ]);
    }

    /**
     * Mengekspor daftar interaksi ke dalam format PDF atau Excel.
     */
    public function export(Request $request)
    {
        $items = $this->createQuery($request)->orderBy('id', 'desc')->get();

        $title = 'Daftar Interaksi';
        $filename = $title . ' - ' . env('APP_NAME') . Carbon::now()->format('dmY_His');

        if ($request->get('format') == 'pdf') {
            $pdf = Pdf::loadView('export.interaction-list-pdf', compact('items', 'title'))
                ->setPaper('A4', 'landscape');
            return $pdf->download($filename . '.pdf');
        }

        if ($request->get('format') == 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Tambahkan header
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'Tanggal');
            $sheet->setCellValue('C1', 'Jenis');
            $sheet->setCellValue('D1', 'Status');
            $sheet->setCellValue('E1', 'Sales');
            $sheet->setCellValue('F1', 'Client');
            $sheet->setCellValue('G1', 'Layanan');
            $sheet->setCellValue('H1', 'Engagement');
            $sheet->setCellValue('I1', 'Subjek');
            $sheet->setCellValue('J1', 'Summary');
            $sheet->setCellValue('K1', 'Catatan');

            // Tambahkan data ke Excel
            $row = 2;
            foreach ($items as $item) {
                $sheet->setCellValue('A' . $row, $item->id);
                $sheet->setCellValue('B' . $row, $item->date);
                $sheet->setCellValue('C' . $row, Interaction::Types[$item->type]);
                $sheet->setCellValue('D' . $row, Interaction::Statuses[$item->status]);
                $sheet->setCellValue('E' . $row, $item->user->name .  ' (' . $item->user->username . ')');
                $sheet->setCellValue('F' . $row, $item->customer->name . ' - ' . $item->customer->company . ' - ' . $item->customer->address);
                $sheet->setCellValue('I' . $row, $item->service->name);
                $sheet->setCellValue('G' . $row, Interaction::EngagementLevels[$item->engagement_level]);
                $sheet->setCellValue('H' . $row, $item->subject);
                $sheet->setCellValue('J' . $row, $item->summary);
                $sheet->setCellValue('K' . $row, $item->notes);
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

        $q = Interaction::with([
            'user:id,username,name',
            'customer:id,name,company,address,business_type',
            'service:id,name'
        ]);

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

        if (!empty($filter['type']) && ($filter['type'] != 'all')) {
            $q->where('type', '=', $filter['type']);
        }

        if (!empty($filter['customer_id']) && ($filter['customer_id'] != 'all')) {
            $q->where('customer_id', '=', $filter['customer_id']);
        }

        if (!empty($filter['engagement_level']) && ($filter['engagement_level'] != 'all')) {
            $q->where('engagement_level', '=', $filter['engagement_level']);
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
