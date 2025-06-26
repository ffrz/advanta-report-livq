<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoPlot;
use App\Models\DemoPlotVisit;
use App\Models\User;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManager;
use Nette\NotImplementedException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DemoPlotVisitController extends Controller
{
    public function index()
    {
        return inertia('admin/demo-plot-visit/Index', [
            'products' => Product::query()->orderBy('name')->get(),
            'users' => User::query()->where('role', User::Role_BS)->orderBy('name')->get(),
        ]);
    }

    public function detail($id = 0)
    {
        $visit = DemoPlotVisit::with([
            'user',
            'demo_plot',
            'demo_plot.product',
            'created_by_user:id,username',
            'updated_by_user:id,username',
        ])->findOrFail($id);

        $demoPlotId = $visit->demo_plot_id;
        $currentDate = $visit->visit_date;

        $prevVisit = DemoPlotVisit::where('demo_plot_id', $demoPlotId)
            ->where('visit_date', '<', $currentDate)
            ->orderBy('visit_date', 'desc')
            ->first();

        $nextVisit = DemoPlotVisit::where('demo_plot_id', $demoPlotId)
            ->where('visit_date', '>', $currentDate)
            ->orderBy('visit_date', 'asc')
            ->first();

        return inertia('admin/demo-plot-visit/Detail', [
            'data' => $visit,
            'prev_visit_id' => $prevVisit ? $prevVisit->id : null,
            'next_visit_id' => $nextVisit ? $nextVisit->id : null,
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'visit_date');
        $orderType = $request->get('order_type', 'desc');
        $items = $this->createQuery($request)
            ->orderBy($orderBy, $orderType)
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return response()->json($items);
    }

    public function duplicate(Request $request, $id)
    {
        $user = Auth::user();
        $item = DemoPlotVisit::findOrFail($id);
        $item->id = 0;
        $item->user_id = $user->role == User::Role_BS ? $user->id : $item->user->id;
        $item->image_path = null;

        return inertia('admin/demo-plot-visit/Editor', [
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
        $item = $id ? DemoPlotVisit::findOrFail($id) : new DemoPlotVisit([
            'user_id' => $user->role == User::Role_BS ? $user->id : null,
            'visit_date' => Carbon::now(),
            'plant_status' => DemoPlot::PlantStatus_Satisfactoy,
            'demo_plot_id' => $request->get('demo_plot_id')
        ]);

        return inertia('admin/demo-plot-visit/Editor', [
            'data' => $item,
            'users' => User::where('active', true)
                ->where('role', User::Role_BS)
                ->orderBy('username', 'asc')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $validated =  $request->validate([
            'user_id'          => 'required|exists:users,id',
            'demo_plot_id'     => 'required|exists:demo_plots,id',
            'visit_date'       => 'required|date',
            'plant_status'     => 'required|in:' . implode(',', array_keys(DemoPlot::PlantStatuses)),
            'notes'            => 'nullable|string|max:500',
            'latlong'          => 'nullable|string|max:100',
            'image'            => 'nullable|image|max:5120',
            'image_path'       => 'nullable|string',
        ]);

        $item = !$request->id
            ? new DemoPlotVisit()
            : DemoPlotVisit::findOrFail($request->post('id', 0));

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

        // Update kolom last_visit dan plant_status di demo_plots
        $latestVisit = DemoPlotVisit::where('demo_plot_id', $item->demo_plot_id)
            ->orderByDesc('visit_date')
            ->first();

        if ($latestVisit) {
            $item->demo_plot->update([
                'last_visit' => $latestVisit->visit_date,
                'plant_status' => $latestVisit->plant_status,
            ]);
        }

        return redirect(route('admin.demo-plot-visit.detail', ['id' => $item->id]))
            ->with('success', "Kunjungan Demo Plot #$item->id - $item->visit_date telah disimpan.");
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = DemoPlotVisit::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Kunjungan Demo Plot #$item->id telah dihapus."
        ]);
    }

    /**
     * Mengekspor daftar interaksi ke dalam format PDF atau Excel.
     */
    public function export(Request $request)
    {
        $items = $this->createQuery($request)->orderBy('id', 'desc')->get();

        $title = 'Laporan Demo Plot';
        $filename = $title . ' - ' . env('APP_NAME') . Carbon::now()->format('dmY_His');

        if ($request->get('format') == 'pdf') {
            $pdf = Pdf::loadView('export.demo-plot-visit-list-pdf', compact('items', 'title'))
                ->setPaper('A4', 'landscape');
            return $pdf->download($filename . '.pdf');
        }

        if ($request->get('format') == 'excel') {
            throw new NotImplementedException('Belum diimplementasikan');

            // $spreadsheet = new Spreadsheet();
            // $sheet = $spreadsheet->getActiveSheet();

            // // Tambahkan header
            // $sheet->setCellValue('A1', 'ID');
            // $sheet->setCellValue('B1', 'Tanggal');
            // $sheet->setCellValue('C1', 'Jenis');
            // $sheet->setCellValue('D1', 'Status');
            // $sheet->setCellValue('E1', 'Sales');
            // $sheet->setCellValue('F1', 'Client');
            // $sheet->setCellValue('G1', 'Layanan');
            // $sheet->setCellValue('H1', 'Engagement');
            // $sheet->setCellValue('I1', 'Subjek');
            // $sheet->setCellValue('J1', 'Summary');
            // $sheet->setCellValue('K1', 'Catatan');

            // // Tambahkan data ke Excel
            // $row = 2;
            // foreach ($items as $item) {
            //     $sheet->setCellValue('A' . $row, $item->id);
            //     $sheet->setCellValue('B' . $row, $item->date);
            //     $sheet->setCellValue('C' . $row, DemoPlotVisit::Types[$item->type]);
            //     $sheet->setCellValue('D' . $row, DemoPlotVisit::Statuses[$item->status]);
            //     $sheet->setCellValue('E' . $row, $item->user->name .  ' (' . $item->user->username . ')');
            //     $sheet->setCellValue('F' . $row, $item->customer->name . ' - ' . $item->customer->company . ' - ' . $item->customer->address);
            //     $sheet->setCellValue('I' . $row, $item->service->name);
            //     $sheet->setCellValue('G' . $row, DemoPlotVisit::EngagementLevels[$item->engagement_level]);
            //     $sheet->setCellValue('H' . $row, $item->subject);
            //     $sheet->setCellValue('J' . $row, $item->summary);
            //     $sheet->setCellValue('K' . $row, $item->notes);
            //     $row++;
            // }

            // // Kirim ke memori tanpa menyimpan file
            // $response = new StreamedResponse(function () use ($spreadsheet) {
            //     $writer = new Xlsx($spreadsheet);
            //     $writer->save('php://output');
            // });

            // // Atur header response untuk download
            // $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            // $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '.xlsx"');

            // return $response;
        }

        return abort(400, 'Format tidak didukung');
    }

    protected function createQuery(Request $request)
    {
        $filter = $request->get('filter', []);

        $q = DemoPlotVisit::with([
            'user:id,username,name',
        ]);

        $q->where('demo_plot_id', $request->get('demo_plot_id'));

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('notes', 'like', '%' . $filter['search'] . '%');
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
