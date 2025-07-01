<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoPlot;
use App\Models\User;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManager;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DemoPlotController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $current_user = Auth::user();
        $q = User::query();
        if ($current_user->role == User::Role_BS) {
            $q->where('id', $current_user->id);
        } else if ($current_user->role == User::Role_Agronomist) {
            $q->where('parent_id', $current_user->id);
        }

        $users = $q->where('role', User::Role_BS)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return inertia('admin/demo-plot/Index', [
            'products' => Product::query()->orderBy('name')->get(),
            'users' => $users,
        ]);
    }

    public function detail($id = 0)
    {
        $data = DemoPlot::with([
            'user',
            'product',
            'created_by_user:id,username',
            'updated_by_user:id,username',
        ])->findOrFail($id);

        $this->authorize('view', $data);

        return inertia('admin/demo-plot/Detail', [
            'data' => $data,
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
        $item = DemoPlot::findOrFail($id);
        $item->id = 0;
        $item->user_id = $user->role == User::Role_BS ? $user->id : $item->user->id;
        $item->image_path = null;

        $this->authorize('update', $item);

        return inertia('admin/demo-plot/Editor', [
            'data' => $item,
            'users' => User::where('active', true)
                ->where('role', User::Role_BS)
                ->orderBy('username', 'asc')->get(),
            'products' => Product::orderBy('name', 'asc')->get(),
        ]);
    }

    public function editor(Request $request, $id = 0)
    {
        $user = Auth::user();
        $item = $id ? DemoPlot::findOrFail($id) : new DemoPlot([
            'user_id' => $user->role == User::Role_BS ? $user->id : null,
            'plant_date' => Carbon::now(),
            'active' => true,
            'plant_status' => DemoPlot::PlantStatus_NotYetPlanted,
        ]);

        if ($id) {
            $this->authorize('update', $item);
        }

        return inertia('admin/demo-plot/Editor', [
            'data' => $item,
            'users' => User::where('active', true)
                ->where('role', User::Role_BS)
                ->orderBy('username', 'asc')->get(),
            'products' => Product::orderBy('name', 'asc')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $validated =  $request->validate([
            'user_id'          => 'required|exists:users,id',
            'product_id'       => 'required|exists:products,id',
            'plant_date'       => 'required|date',
            'owner_name'       => 'required|string|max:100',
            'owner_phone'      => 'nullable|string|max:30',
            'notes'            => 'nullable|string|max:500',
            'field_location'   => 'nullable|string|max:100',
            'latlong'          => 'nullable|string|max:100',
            'image'            => 'nullable|image|max:5120',
            'image_path'       => 'nullable|string',
        ]);

        $item = !$request->id
            ? new DemoPlot([
                'plant_status' => DemoPlot::PlantStatus_NotYetPlanted,
            ])
            : DemoPlot::findOrFail($request->post('id', 0));

        if ($request->id) {
            $this->authorize('update', $item);
        }

        // Force current user id
        $current_user = Auth::user();
        if ($current_user->role == User::Role_BS) {
            $validated['user_id'] = $current_user->id;
        }

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

        $item->fill($validated);
        $item->save();

        return redirect(route('admin.demo-plot.detail', ['id' => $item->id]))
            ->with('success', "DemoPlot #$item->id telah disimpan.");
    }

    public function delete($id)
    {
        $item = DemoPlot::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Demo Plot #$item->id telah dihapus."
        ]);
    }

    /**
     * Mengekspor daftar interaksi ke dalam format PDF atau Excel.
     */
    public function export(Request $request)
    {
        $items = $this->createQuery($request)->orderBy('id', 'desc')->get();

        $title = 'Daftar Demo Plot';
        $filename = $title . ' - ' . env('APP_NAME') . Carbon::now()->format('dmY_His');

        if ($request->get('format') == 'html') {
            return view('export.demo-plot-list-pdf', compact('items', 'title'));
        }

        if ($request->get('format') == 'pdf') {
            $pdf = Pdf::loadView('export.demo-plot-list-pdf', compact('items', 'title'))
                ->setPaper('A4', 'landscape');
            return $pdf->download($filename . '.pdf');
        }

        if ($request->get('format') == 'excel') {

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Tambahkan header
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'BS');
            $sheet->setCellValue('C1', 'Pemilik');
            $sheet->setCellValue('D1', 'No HP');
            $sheet->setCellValue('E1', 'Lokasi');
            $sheet->setCellValue('F1', 'Tgl Tanam');
            $sheet->setCellValue('G1', 'Umur');
            $sheet->setCellValue('H1', 'Last Visit');
            $sheet->setCellValue('I1', 'Status Tanaman');
            $sheet->setCellValue('J1', 'Status Demplot');
            $sheet->setCellValue('K1', 'Catatan');

            // Tambahkan data ke Excel
            $row = 2;
            foreach ($items as $i => $item) {
                $sheet->setCellValue('A' . $row, $i + 1);
                $sheet->setCellValue('B' . $row, optional($item->user)->name);
                $sheet->setCellValue('C' . $row, $item->owner_name);
                $sheet->setCellValue('D' . $row, $item->owner_phone);
                $sheet->setCellValue('E' . $row, $item->field_location);
                $sheet->setCellValue('F' . $row, Carbon::parse($item->plant_date)->format('d-m-Y'));
                $sheet->setCellValue('G' . $row, $item->plant_date && $item->active ? (int) \Carbon\Carbon::parse($item->plant_date)->diffInDays(\Carbon\Carbon::now()) : '');
                $sheet->setCellValue('H' . $row, $item->last_visit ? Carbon::parse($item->last_visit)->format('d-m-Y') : '');
                $sheet->setCellValue('I' . $row, DemoPlot::PlantStatuses[$item->plant_status]);
                $sheet->setCellValue('J' . $row, $item->activ ? 'Aktif' : 'Tidak Aktif');
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

        $q = DemoPlot::with([
            'user:id,username,name',
            'product:id,name',
        ]);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('owner_name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('owner_phone', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('field_location', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['user_id']) && ($filter['user_id'] != 'all')) {
            $q->where('user_id', '=', $filter['user_id']);
        }

        if (!empty($filter['product_id']) && ($filter['product_id'] != 'all')) {
            $q->where('product_id', '=', $filter['product_id']);
        }

        if (!empty($filter['plant_status']) && ($filter['plant_status'] != 'all')) {
            $q->where('plant_status', '=', $filter['plant_status']);
        }

        if (!empty($filter['status']) && ($filter['status'] != 'all')) {
            $q->where('active', '=', $filter['status'] == 'active');
        }

        // if (!empty($filter['period']) && ($filter['period'] != 'all')) {
        //     if ($filter['period'] == 'this_month') {
        //         $start = Carbon::now()->startOfMonth();
        //         $end = Carbon::now()->endOfMonth();
        //         $q->whereBetween('date', [$start, $end]);
        //     } elseif ($filter['period'] == 'last_month') {
        //         $start = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        //         $end = Carbon::now()->subMonthNoOverflow()->endOfMonth();
        //         $q->whereBetween('date', [$start, $end]);
        //     } elseif ($filter['period'] == 'this_year') {
        //         $start = Carbon::now()->startOfYear();
        //         $end = Carbon::now()->endOfYear();
        //         $q->whereBetween('date', [$start, $end]);
        //     } elseif ($filter['period'] == 'last_year') {
        //         $start = Carbon::now()->subYear()->startOfYear();
        //         $end = Carbon::now()->subYear()->endOfYear();
        //         $q->whereBetween('date', [$start, $end]);
        //     } else {
        //         // Asumsikan filter['period'] dalam format YYYY-MM-DD
        //         try {
        //             $date = Carbon::parse($filter['period']);
        //             $q->whereDate('date', $date);
        //         } catch (\Exception $e) {
        //             // Handle kesalahan parsing tanggal jika perlu
        //         }
        //     }
        // }

        return $q;
    }
}
