<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityPlan;
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

        $validated['date'] = sprintf('%04d-%02d-01', $validated['year'], $validated['month']);
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

    /**
     * Mengekspor daftar interaksi ke dalam format PDF atau Excel.
     */
    public function export(Request $request)
    {
        $items = $this->createQuery($request)->orderBy('id', 'desc')->get();

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
            foreach ($items as $item) {
                $sheet->setCellValue('A' . $row, $item->id);
                $sheet->setCellValue('B' . $row, $item->date);
                $sheet->setCellValue('C' . $row, $item->type->name);
                $sheet->setCellValue('D' . $row, $item->user->name);
                $sheet->setCellValue('E' . $row, $item->location);
                $sheet->setCellValue('F' . $row, $item->cost);
                $sheet->setCellValue('G' . $row, ActivityPlan::Statuses[$item->status]);
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
        $current_user = Auth::user();

        $filter = $request->get('filter', []);

        $q = ActivityPlan::with([
            'user:id,username,name',
            'responded_by:id,username,name',
        ]);

        if ($current_user->role == User::Role_Agronomist) {
            $q->whereHas('user', function ($query) use ($current_user) {
                $query->where('parent_id', $current_user->id);
            });
        } else if ($current_user->role == User::Role_BS) {
            $q->where('user_id', $current_user->id);
        }

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['user_id']) && ($filter['user_id'] != 'all')) {
            $q->where('user_id', '=', $filter['user_id']);
        }

        if (!empty($filter['status']) && ($filter['status'] != 'all')) {
            $q->where('status', '=', $filter['status']);
        }

        if (!empty($filter['year']) && $filter['year'] != 'all') {
            $q->whereYear('date', '=', $filter['year']);
        }

        if (!empty($filter['month']) && $filter['month'] != 'all') {
            $q->whereMonth('date', '=', $filter['month']);
        }

        return $q;
    }
}
