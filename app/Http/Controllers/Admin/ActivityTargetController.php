<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityTarget;
use App\Models\ActivityType;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Nette\NotImplementedException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

// TODO: Controller dan view pada modul ini belum disesuaikan dengan perubahan database ActivityTarget,
// perlu diperbaiki!!

class ActivityTargetController extends Controller
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
        }

        return inertia('admin/activity-target/Index', [
            'users' => $users,
            'types' => ActivityType::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function detail($id = 0)
    {
        return inertia('admin/activity-target/Detail', [
            'data' => ActivityTarget::with([
                'user',
                'type:id,name',
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
        $item = ActivityTarget::findOrFail($id);
        $item->id = 0;
        $item->user_id = $user->role == User::Role_BS ? $user->id : $item->user->id;
        return inertia('admin/activity-target/Editor', [
            'data' => $item,
            'types' => ActivityType::where('active', true)
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
        $item = $id ? ActivityTarget::findOrFail($id) : new ActivityTarget([
            'year' => intval(date('Y')),
            'month' => intval(date('m')),
            'period_type' => 'month',
            'qty' => 1,
        ]);

        return inertia('admin/activity-target/Editor', [
            'data' => $item,
            'types' => ActivityType::where('active', true)
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
            'quarter'     => ['required', 'regex:/^\d{4}-q[1-4]$/i'],
            'targets'     => 'required|array',
            'targets.*.q'  => 'required|numeric',
            'targets.*.m1' => 'required|numeric',
            'targets.*.m2' => 'required|numeric',
            'targets.*.m3' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->targets as $typeId => $target) {
                // Optional: validasi penjumlahan q == m1+m2+m3
                if (intval($target['m1'] + $target['m2'] + $target['m3']) != intval($target['q'])) {
                    DB::rollBack();
                    return back()->withInput()->withErrors([
                        'message.' => "Jumlah bulan tidak sama dengan target kuartal untuk tipe $typeId"
                    ]);
                }

                $quarterTextArr = explode('-', $validated['quarter']);
                $year = intval($quarterTextArr[0]);
                $quarter = intval($quarterTextArr[1][1]);

                // Cek apakah record sudah ada
                $existing = ActivityTarget::where('user_id', $request->user_id)
                    ->where('type_id', $typeId)
                    ->where('year', $year)
                    ->where('quarter', $quarter)
                    ->when($request->id, fn($q) => $q->where('id', '!=', $request->id)) // abaikan jika sedang update
                    ->exists();

                if ($existing) {
                    DB::rollBack();
                    return back()->withInput()->withErrors([
                        "message" => "Target untuk jenis kegiatan ini pada tahun $year kuartal $quarter sudah ada.",
                    ]);
                }

                $item = ActivityTarget::updateOrCreate(
                    [
                        'user_id' => $request->user_id,
                        'type_id' => $typeId,
                        'year'    => $year,
                        'quarter' => $quarter,
                    ],
                    [
                        'quarter_qty'    => $target['q'],
                        'month1_qty'     => $target['m1'],
                        'month2_qty'     => $target['m2'],
                        'month3_qty'     => $target['m3'],
                    ]
                );

                $item->save();
            }

            DB::commit();
            return redirect()->route('admin.activity-target.index')
                ->with('success', 'Seluruh target berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    public function delete($id)
    {
        $item = ActivityTarget::findOrFail($id);
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
        $items = $this->createQuery($request)->orderBy('id', 'desc')->get();

        $title = 'Laporan Kegiatan';
        $filename = $title . ' - ' . env('APP_NAME') . Carbon::now()->format('dmY_His');

        if ($request->get('format') == 'pdf') {
            $pdf = Pdf::loadView('export.activity-target-list-pdf', compact('items', 'title'))
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
            //     $sheet->setCellValue('C' . $row, ActivityTarget::Types[$item->type]);
            //     $sheet->setCellValue('D' . $row, ActivityTarget::Statuses[$item->status]);
            //     $sheet->setCellValue('E' . $row, $item->user->name .  ' (' . $item->user->username . ')');
            //     $sheet->setCellValue('F' . $row, $item->customer->name . ' - ' . $item->customer->company . ' - ' . $item->customer->address);
            //     $sheet->setCellValue('I' . $row, $item->service->name);
            //     $sheet->setCellValue('G' . $row, ActivityTarget::EngagementLevels[$item->engagement_level]);
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

        $q = ActivityTarget::with([
            'user:id,username,name',
            'type:id,name',
        ]);

        if (!empty($filter['user_id']) && ($filter['user_id'] != 'all')) {
            $q->where('user_id', '=', $filter['user_id']);
        }

        if (!empty($filter['type_id']) && ($filter['type_id'] != 'all')) {
            $q->where('type_id', '=', $filter['type_id']);
        }

        if (!empty($filter['year']) && ($filter['year'] != 'all')) {
            $q->where('year', $filter['year']);
        }

        if (!empty($filter['quarter']) && ($filter['quarter'] != 'all')) {
            $q->where('quarter', $filter['quarter']);
        }

        return $q;
    }
}
