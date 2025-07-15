<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
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

class ActivityController extends Controller
{
    public function index()
    {
        $users = [];
        if (Auth::user()->role == User::Role_BS) {
            $users = User::query()->where('role', User::Role_BS)->orderBy('name')->get();
        } else if (Auth::user()->role == User::Role_Agronomist) {
            $users = User::query()
                ->where('role', User::Role_BS)
                ->where('parent_id', Auth::user()->role == User::Role_Agronomist ? Auth::user()->id : null)
                ->orderBy('name')->get();
        } else {
            $users = User::query()
                ->where('role', User::Role_BS)
                ->orderBy('name')->get();
        }

        return inertia('admin/activity/Index', [
            'users' => $users,
            'types' => ActivityType::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function detail($id = 0)
    {
        return inertia('admin/activity/Detail', [
            'data' => Activity::with([
                'user',
                'type:id,name',
                'product:id,name',
                'responded_by:id,username,name',
                'created_by_user:id,username,name',
                'updated_by_user:id,username,name',
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

    public function duplicate(Request $request, $id)
    {
        $user = Auth::user();
        $item = Activity::findOrFail($id);
        $item->id = 0;
        $item->user_id = $user->role == User::Role_BS ? $user->id : $item->user->id;
        $item->image_path = null;

        return inertia('admin/activity/Editor', [
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
        $item = $id ? Activity::findOrFail($id) : new Activity([
            'user_id' => $user->role == User::Role_BS ? $user->id : null,
        ]);

        return inertia('admin/activity/Editor', [
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
        $validated =  $request->validate([
            'user_id'          => 'required|exists:users,id',
            'type_id'          => 'required|exists:activity_types,id',
            'product_id'       => 'nullable|exists:products,id',
            'date'             => 'required|date',
            'cost'             => 'nullable|numeric',
            'location'         => 'nullable|string|max:500',
            'notes'            => 'nullable|string|max:500',
            'latlong'          => 'nullable|string|max:100',
            'image'            => 'nullable|image|max:5120',
            'image_path'       => 'nullable|string',
        ]);

        $item = !$request->id
            ? new Activity()
            : Activity::findOrFail($request->post('id', 0));

        // Handle image upload jika ada
        if ($request->hasFile('image')) {
            // Hapus file lama jika ada
            if ($item->image_path && file_exists(public_path($item->image_path))) {
                @unlink(public_path($item->image_path)); // pakai @ untuk suppress error jika file tidak ada
            }

            // Simpan file baru
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $validated['image_path'] = 'uploads/' . $filename; // timpah dengan path yang digenerate

            // Resize dan simpan dengan Intervention Image v3
            $manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->read($file);

            // Hitung sisi panjang
            $width = $image->width();
            $height = $image->height();

            // Hitung rasio
            $ratio = max($width / 1024, $height / 1024);

            if ($ratio > 1) {
                // Jika lebih besar dari batas, resize berdasarkan rasio terbesar
                $newWidth = (int) round($width / $ratio);
                $newHeight = (int) round($height / $ratio);

                $image->resize($newWidth, $newHeight, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $image->save(public_path($validated['image_path']));
        } else if (empty($validated['image_path'])) {
            // Hapus file lama jika ada
            if ($item->image_path && file_exists(public_path($item->image_path))) {
                @unlink(public_path($item->image_path)); // pakai @ untuk suppress error jika file tidak ada
            }
        }

        $validated['cost'] = $validated['cost'] ? $validated['cost'] : 0;
        $validated['location'] = $validated['location'] ? $validated['location'] : '';
        $validated['latlong'] = $validated['latlong'] ? $validated['latlong'] : '';
        $validated['notes'] = $validated['notes'] ? $validated['notes'] : '';

        $item->fill($validated);
        $item->save();

        return redirect(route('admin.activity.detail', ['id' => $item->id]))
            ->with('success', "Kegiatan #$item->id telah disimpan.");
    }

    public function respond(Request $request, $id)
    {
        $current_user = Auth::user();
        $item = Activity::findOrFail($id);
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
        $item = Activity::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Kegiatan #$item->id telah dihapus."
        ]);
    }

    /**
     * Mengekspor daftar interaksi ke dalam format PDF atau Excel.
     */
    public function export(Request $request)
    {
        $q = $this->createQuery($request)
            ->orderBy('users.name', 'asc')
            ->orderBy('date', 'asc');

        $items = $q->get();

        $title = 'Realisasi Kegiatan';
        $filename = $title . ' - ' . env('APP_NAME') . Carbon::now()->format('dmY_His');

        if ($request->get('format') == 'pdf') {
            $pdf = Pdf::loadView('export.activity-list-pdf', compact('items', 'title'))
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
                $sheet->setCellValue('G' . $row, Activity::Statuses[$item->status]);
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

        $q = Activity::select('activities.*')
            ->join('users', 'users.id', '=', 'activities.user_id')
            ->with([
                'user:id,username,name',
                'responded_by:id,username,name',
                'type:id,name',
            ]);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('location', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        $current_user = Auth::user();
        if ($current_user->role == User::Role_Agronomist) {
            if (!empty($filter['user_id']) && ($filter['user_id'] != 'all')) {
                $q->where('user_id', '=', $filter['user_id']);
            } else {
                $q->whereHas('user', function ($query) use ($current_user) {
                    $query->where('parent_id', $current_user->id);
                });
            }
        } else if ($current_user->role == User::Role_BS) {
            $q->where('user_id', $current_user->id);
        } else if ($current_user->role == User::Role_Admin) {
            if (!empty($filter['user_id']) && ($filter['user_id'] != 'all')) {
                $q->where('user_id', '=', $filter['user_id']);
            }
        }

        if (!empty($filter['type_id']) && ($filter['type_id'] != 'all')) {
            $q->where('type_id', '=', $filter['type_id']);
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
