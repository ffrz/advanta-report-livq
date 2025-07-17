<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityPlan;
use App\Models\ActivityPlanDetail;
use App\Models\ActivityType;
use App\Models\Product;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManager;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityPlanController extends Controller
{
    public function index()
    {
        $users = [];
        if (Auth::user()->role == User::Role_BS) {
            $users = User::query()->where('role', User::Role_BS)->orderBy('name')->get();
        } else if (Auth::user()->role == User::Role_Agronomist) {
            $users = User::query()
                ->where('role', User::Role_BS)
                ->where('parent_id', Auth::user()->id)
                ->orderBy('name')->get();
        } else {
            $users = User::query()
                ->where('role', User::Role_BS)
                ->orderBy('name')->get();
        }

        return inertia('admin/activity-plan/Index', [
            'users' => $users
        ]);
    }

    public function detail($id = 0)
    {
        return inertia('admin/activity-plan/Detail', [
            'data' => ActivityPlan::with([
                'user',
                'responded_by:id,username,name',
                'created_by_user:id,username,name',
                'updated_by_user:id,username,name',
            ])->findOrFail($id)->toArray(),
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

    public function duplicate(Request $request, $id)
    {
        $user = Auth::user();
        $item = ActivityPlan::findOrFail($id);
        $item->id = 0;
        $item->user_id = $user->role == User::Role_BS ? $user->id : $item->user->id;
        $item->image_path = null;

        return inertia('admin/activity-plan/Editor', [
            'data' => $item,
            'types' => ActivityType::where('active', true)
                ->orderBy('name', 'asc')
                ->get(),
            'products' => Product::where('active', true)
                ->orderBy('name', 'asc')
                ->get(),
            'users' => User::where('active', true)
                ->where('role', User::Role_BS)
                ->orderBy('username', 'asc')->get(),
        ]);
    }

    public function editor(Request $request, $id = 0)
    {
        $user = Auth::user();
        $item = $id ? ActivityPlan::findOrFail($id) : new ActivityPlan([
            'user_id' => $user->role == User::Role_BS ? $user->id : null,
        ]);

        if ($user->role == User::Role_BS && $id && $item->status == ActivityPlan::Status_Approved) {
            abort(403, 'Rekaman yang sudah disetujui tidak bisa diubah!');
        }

        return inertia('admin/activity-plan/Editor', [
            'data' => $item,
            'types' => ActivityType::where('active', true)
                ->orderBy('name', 'asc')
                ->get(),
            'products' => Product::where('active', true)
                ->orderBy('name', 'asc')
                ->get(),
            'users' => User::where('active', true)
                ->where('role', User::Role_BS)
                ->orderBy('username', 'asc')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'year'        => 'required|integer|min:2000|max:2100',
            'month'       => 'required|integer|min:1|max:12',
            'notes'       => 'nullable|string|max:500',
        ]);

        $item = !$request->id
            ? new ActivityPlan()
            : ActivityPlan::findOrFail($request->post('id', 0));

        $date = sprintf('%04d-%02d-01', $validated['year'], $validated['month']);

        // Cek jika ada plan untuk user & bulan/tahun yang sama
        $existingApproved = ActivityPlan::where('user_id', $validated['user_id'])
            ->where('date', $date);

        // Jika edit (ada request id), abaikan record itu sendiri dari validasi
        if ($request->id) {
            $existingApproved->where('id', '!=', $request->id);
        }

        if ($existingApproved->exists()) {
            return back()->withErrors([
                'month' => 'Plan sudah ada untuk bulan tersebut.',
            ])->withInput();
        }

        $validated['date'] = $date;
        unset($validated['year'], $validated['month']);

        $item->fill($validated);
        $item->save();

        return redirect(route('admin.activity-plan.detail', ['id' => $item->id]))
            ->with('success', "Rencana Kegiatan #$item->id telah disimpan.");
    }

    public function respond(Request $request, $id)
    {
        $current_user = Auth::user();
        $item = ActivityPlan::findOrFail($id);
        $supervisor_account = $item->user->parent;

        if (!($current_user->role == User::Role_Admin || $current_user->role == User::Role_Agronomist)) {
            abort(403, 'Akses ditolak, hanya supervisor yang bisa menyetujui.');
        }

        $action = $request->get('action');
        if ($action == 'approve') {
            $item->status = 'approved';
        } else if ($action == 'reject') {
            $item->status = 'rejected';
        } else if ($action == 'reset') {
            $item->status = 'not_responded';
        }

        $item->responded_datetime = $action == 'reset' ? null : Carbon::now();
        $item->responded_by_id = $action == 'reset' ? null : $current_user->id;
        $item->save();

        return response()->json([
            'message' => "Kegiatan #$item->id telah direspon.",
            'data' => $item
        ]);
    }

    public function delete($id)
    {
        $item = ActivityPlan::findOrFail($id);


        $item->delete();

        return response()->json([
            'message' => "Rencana Kegiatan #$item->id telah dihapus."
        ]);
    }

    public function exportOne(Request $request, $id)
    {
        $item = ActivityPlan::findOrFail($id);

        // TODO: Verifikasi dan otorisasi rekaman sebelum digunakan

        // persiapan title
        $title = 'Rencana Kegiatan ' + $item->user->name + ' ' + Carbon::parse($item->date)->translatedFormat("F Y");
        $filename = $title . ' - ' . env('APP_NAME') . Carbon::now()->format('dmY_His');

        // expor html dulu supaya hasil bisa kelihatan
        if ($request->get('format') == 'html') {
            return view('export.activity-plan-pdf', compact('item', 'title'));
        }

        if ($request->get('format') == 'pdf') {
            $pdf = Pdf::loadView('export.activity-plan-pdf', compact('item', 'title'))
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
            $sheet->setCellValue('D1', 'BS');
            $sheet->setCellValue('E1', 'Lokasi');
            $sheet->setCellValue('F1', 'Biaya (Rp)');
            $sheet->setCellValue('G1', 'Status');
            $sheet->setCellValue('H1', 'Catatan');

            // Tambahkan data ke Excel
            $row = 2;
            foreach ($item->details as $detail) {
                $sheet->setCellValue('A' . $row, $detail->id);
                $sheet->setCellValue('B' . $row, $detail->date);
                $sheet->setCellValue('C' . $row, $detail->type->name);
                $sheet->setCellValue('D' . $row, $detail->user->name);
                $sheet->setCellValue('E' . $row, $detail->location);
                $sheet->setCellValue('F' . $row, $detail->cost);
                $sheet->setCellValue('G' . $row, ActivityPlan::Statuses[$detail->status]);
                $sheet->setCellValue('H' . $row, $detail->notes);
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

    /**
     * Mengekspor daftar interaksi ke dalam format PDF atau Excel.
     */
    public function export(Request $request)
    {
        $items = $this->createQueryForExport($request)
            ->orderBy('users.name', 'asc')
            ->orderBy('activity_types.name', 'asc')
            ->get();

        $title = 'Rencana Kegiatan';
        $filename = $title . ' - ' . env('APP_NAME') . Carbon::now()->format('dmY_His');

        if ($request->get('format') == 'pdf') {
            $pdf = Pdf::loadView('export.activity-plan-list-pdf', compact('items', 'title'))
                ->setPaper('A4', 'landscape');
            return $pdf->download($filename . '.pdf');
        }

        if ($request->get('format') == 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header kolom
            $sheet->setCellValue('A1', 'Bulan');
            $sheet->setCellValue('B1', 'BS');
            $sheet->setCellValue('C1', 'Kegiatan');
            $sheet->setCellValue('D1', 'Varietas');
            $sheet->setCellValue('E1', 'Lokasi');
            $sheet->setCellValue('F1', 'Biaya (Rp)');
            $sheet->setCellValue('G1', 'Status');
            $sheet->setCellValue('H1', 'Catatan');

            // Data isi
            $row = 2;
            foreach ($items as $item) {
                $sheet->setCellValue('A' . $row, \Carbon\Carbon::parse($item->date)->translatedFormat('F Y'));
                $sheet->setCellValue('B' . $row, $item->bs_name);
                $sheet->setCellValue('C' . $row, $item->activity_type);
                $sheet->setCellValue('D' . $row, $item->product_name);
                $sheet->setCellValue('E' . $row, $item->location);
                $sheet->setCellValue('F' . $row, $item->cost);
                $sheet->setCellValue('G' . $row, \App\Models\ActivityPlan::Statuses[$item->status] ?? '-');
                $sheet->setCellValue('H' . $row, $item->notes);
                $row++;
            }

            // Kirim file ke browser
            $response = new StreamedResponse(function () use ($spreadsheet) {
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save('php://output');
            });

            $filename = 'export_rencana_kegiatan_' . date('Ymd_His') . '.xlsx';

            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
            $response->headers->set('Cache-Control', 'max-age=0');

            return $response;
        }

        return abort(400, 'Format tidak didukung');
    }

    protected function createQuery(Request $request)
    {
        $current_user = Auth::user();

        $filter = $request->get('filter', []);

        $q = ActivityPlan::select('activity_plans.*')
            ->join('users', 'users.id', '=', 'activity_plans.user_id')
            ->with([
                'user:id,username,name',
                'responded_by:id,username,name',
            ]);

        if ($current_user->role == User::Role_Agronomist) {
            $q->whereHas('user', function ($query) use ($current_user) {
                $query->where('parent_id', $current_user->id);
            });
        } else if ($current_user->role == User::Role_BS) {
            $q->where('activity_plans.user_id', $current_user->id);
        }

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('activity_plans.notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['user_id']) && ($filter['user_id'] != 'all')) {
            $q->where('activity_plans.user_id', '=', $filter['user_id']);
        }

        if (!empty($filter['status']) && ($filter['status'] != 'all')) {
            $q->where('activity_plans.status', '=', $filter['status']);
        }

        if (!empty($filter['year']) && $filter['year'] != 'all') {
            $q->whereYear('activity_plans.date', '=', $filter['year']);
        }

        if (!empty($filter['month']) && $filter['month'] != 'all') {
            $q->whereMonth('activity_plans.date', '=', $filter['month']);
        }

        return $q;
    }

    protected function createQueryForExport(Request $request)
    {
        $current_user = Auth::user();
        $filter = $request->get('filter', []);

        $q = ActivityPlanDetail::select([
            'activity_plans.date',
            'activity_plans.status',
            'users.name as bs_name',
            'activity_types.name as activity_type',
            'products.name as product_name',
            'activity_plan_details.location',
            'activity_plan_details.cost',
            'activity_plan_details.notes',
        ])
            ->join('activity_plans', 'activity_plans.id', '=', 'activity_plan_details.parent_id')
            ->join('users', 'users.id', '=', 'activity_plans.user_id')
            ->leftJoin('activity_types', 'activity_types.id', '=', 'activity_plan_details.type_id')
            ->leftJoin('products', 'products.id', '=', 'activity_plan_details.product_id');

        // Filter berdasarkan role
        if ($current_user->role == User::Role_Agronomist) {
            $q->where('users.parent_id', $current_user->id);
        } elseif ($current_user->role == User::Role_BS) {
            $q->where('users.id', $current_user->id);
        }

        // Filter pencarian
        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('activity_plan_details.notes', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('activity_plan_details.location', 'like', '%' . $filter['search'] . '%');
            });
        }

        // Filter user
        if (!empty($filter['user_id']) && $filter['user_id'] !== 'all') {
            $q->where('users.id', '=', $filter['user_id']);
        }

        // Filter status rencana (jika diperlukan)
        if (!empty($filter['status']) && $filter['status'] !== 'all') {
            $q->where('activity_plans.status', '=', $filter['status']);
        }

        // Filter tahun
        if (!empty($filter['year']) && $filter['year'] !== 'all') {
            $q->whereYear('activity_plans.date', '=', $filter['year']);
        }

        // Filter bulan
        if (!empty($filter['month']) && $filter['month'] !== 'all') {
            $q->whereMonth('activity_plans.date', '=', $filter['month']);
        }

        return $q;
    }
}
