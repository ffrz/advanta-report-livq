<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityPlan;
use App\Models\ActivityPlanDetail;
use App\Models\ActivityTarget;
use App\Models\ActivityTargetDetail;
use App\Models\ActivityType;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Nette\NotImplementedException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityTargetController extends Controller
{
    public function index()
    {
        $q = User::query()
            ->where('role', User::Role_BS);

        if (Auth::user()->role == User::Role_Agronomist) {
            $q->where('parent_id', Auth::user()->id)
                ->orWhere('id', Auth::user()->id);
        }

        $users = $q->select(['id', 'username', 'name'])
            ->orderBy('name', 'asc')
            ->get();

        return inertia('admin/activity-target/Index', [
            'users' => $users,
            'types' => ActivityType::where('active', true)
                ->select(['id', 'name', 'weight'])
                ->orderBy('name', 'asc')
                ->get(),
        ]);
    }

    public function detail($id = 0)
    {
        return inertia('admin/activity-target/Detail', [
            'data' => ActivityTarget::with([
                'user:id,username,name',
                'details',
                'details.type:id,name',
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
            ->withQueryString()
            ->toArray();

        $items = $this->_processItems($items, true);

        return response()->json($items);
    }

    public function duplicate(Request $request, $id)
    {
        $user = Auth::user();
        $item = ActivityTarget::findOrFail($id);
        $item->id = 0;
        $item->user_id = $user->role == User::Role_BS ? $user->id : $item->user->id;
        return $this->_editor($item);
    }

    public function editor(Request $request, $id = 0)
    {
        $item = $id ? ActivityTarget::findOrFail($id) : new ActivityTarget([
            'year' => intval(date('Y')),
            'quarter' => 1,
        ]);

        return $this->_editor($item);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'quarter'      => ['required', 'regex:/^\d{4}-q[1-4]$/i'],
            'targets'      => 'required|array',
            'targets.*.q'  => 'required|numeric',
            'targets.*.m1' => 'required|numeric',
            'targets.*.m2' => 'required|numeric',
            'targets.*.m3' => 'required|numeric',
            'notes'        => 'nullable|string|max:250',
        ]);

        DB::beginTransaction();
        try {
            $quarterTextArr = explode('-', $validated['quarter']);
            $year = intval($quarterTextArr[0]);
            $quarter = intval($quarterTextArr[1][1]);

            if ($request->id) {
                $activityTarget = ActivityTarget::findOrFail($request->id);
            } else {
                $activityTarget = new ActivityTarget();
            }

            // Cek apakah target untuk user, tahun dan kuartal ini sudah ada
            $existingTarget = ActivityTarget::where('user_id', $validated['user_id'])
                ->where('year', $year)
                ->where('quarter', $quarter)
                ->first();

            if ($existingTarget && $existingTarget->id != $activityTarget->id) {
                $user = $existingTarget->user;
                DB::rollBack();
                return back()->withInput()->withErrors([
                    'message' => "Target $year-Q$quarter $user->username sudah ada!",
                ]);
            }

            $activityTarget->user_id = $validated['user_id'];
            $activityTarget->year = $year;
            $activityTarget->quarter = $quarter;
            $activityTarget->notes = $validated['notes'] ?? null;
            $activityTarget->save();

            $existingDetails = $activityTarget->details()->pluck('id', 'type_id')->toArray();
            $typeIdsInRequest = [];

            foreach ($request->targets as $typeId => $target) {
                $typeIdsInRequest[] = $typeId;

                if (intval($target['m1'] + $target['m2'] + $target['m3']) != intval($target['q'])) {
                    DB::rollBack();
                    return back()->withInput()->withErrors([
                        'message' => "Jumlah bulan tidak sama dengan target kuartal untuk keigiatan $typeId",
                    ]);
                }

                // Jika type_id sudah ada, update
                if (isset($existingDetails[$typeId])) {
                    ActivityTargetDetail::where('id', $existingDetails[$typeId])->update([
                        'quarter_qty' => $target['q'],
                        'month1_qty' => $target['m1'],
                        'month2_qty' => $target['m2'],
                        'month3_qty' => $target['m3'],
                    ]);
                } else {
                    // Jika belum, insert
                    ActivityTargetDetail::create([
                        'parent_id' => $activityTarget->id,
                        'type_id' => $typeId,
                        'quarter_qty' => $target['q'],
                        'month1_qty' => $target['m1'],
                        'month2_qty' => $target['m2'],
                        'month3_qty' => $target['m3'],
                    ]);
                }
            }

            // Opsional: hapus detail yang tidak dikirim (jika perlu sinkron penuh)
            ActivityTargetDetail::where('parent_id', $activityTarget->id)
                ->whereNotIn('type_id', $typeIdsInRequest)
                ->delete();

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
            'message' => "Target kegiatan #$item->id telah dihapus."
        ]);
    }

    /**
     * Mengekspor daftar interaksi ke dalam format PDF atau Excel.
     */
    public function export(Request $request)
    {
        $items = $this->createQuery($request)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        $items = $this->_processItems($items);
        $types = ActivityType::where('active', true)
            ->select(['id', 'name', 'weight'])
            ->orderBy('name', 'asc')
            ->get();
        $title = 'Laporan Target Kegiatan';
        $filename = $title . ' - ' . env('APP_NAME') . Carbon::now()->format('dmY_His');

        if ($request->get('format') == 'pdf') {
            return view('export.activity-target-list-pdf', compact('items', 'title', 'types'));
            $pdf = Pdf::loadView('export.activity-target-list-pdf', compact('items', 'title', 'types'))
                ->setPaper('A4', 'portrait');
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
            'details',
            'details.type:id,name',
        ]);

        if (!empty($filter['user_id']) && ($filter['user_id'] != 'all')) {
            $q->where('user_id', '=', $filter['user_id']);
        }

        if (!empty($filter['year']) && ($filter['year'] != 'all')) {
            $q->where('year', $filter['year']);
        }

        if (!empty($filter['quarter']) && ($filter['quarter'] != 'all')) {
            $q->where('quarter', $filter['quarter']);
        }

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        return $q;
    }

    private function _editor(ActivityTarget $item)
    {
        return inertia('admin/activity-target/Editor', [
            'data' => $item,
            'types' => ActivityType::where('active', true)
                ->select(['id', 'name', 'default_quarter_target', 'default_month1_target', 'default_month2_target', 'default_month3_target'])
                ->orderBy('name', 'asc')
                ->get(),
            'users' => User::where('active', true)
                ->select(['id', 'username', 'name'])
                ->where('role', User::Role_BS)
                ->orderBy('username', 'asc')
                ->get(),
        ]);
    }

    private function _processItems($items, $paginated = false)
    {
        // untuk acuan weight
        $types_by_ids = ActivityType::where('active', true)
            ->select(['id', 'weight'])
            ->orderBy('name', 'asc')
            ->get()
            ->keyBy('id');

        $fiscalQuarterMonths = [
            1 => [4, 5, 6],    // Q1: Apr–Jun
            2 => [7, 8, 9],    // Q2: Jul–Sep
            3 => [10, 11, 12], // Q3: Oct–Dec
            4 => [1, 2, 3],    // Q4: Jan–Mar (tahun berikutnya)
        ];

        if (!$paginated) {
            $temp = $items;
            unset($items);
            $items['data'] = $temp;
        }

        foreach ($items['data'] as $index => $item) {

            $year = $item['year'];         // fiscal year
            $quarter = $item['quarter'];   // 1-4
            $months = $fiscalQuarterMonths[$quarter];

            // Jika Q4, berarti tahun awalnya naik 1 (karena Jan–Mar tahun berikutnya)
            $startYear = ($quarter == 4) ? $year + 1 : $year;

            $start = Carbon::createFromDate($startYear, $months[0], 1)->startOfDay();
            $end = Carbon::createFromDate($startYear, $months[2], 1)->endOfMonth()->endOfDay();

            $targets_by_type_ids = [];

            foreach ($item['details'] as $detail) {
                $typeId = $detail['type_id'];

                if (!isset($targets_by_type_ids[$typeId])) {
                    $targets_by_type_ids[$typeId] = [
                        'quarter_qty' => $detail['quarter_qty'],
                        'month1_qty' => $detail['month1_qty'],
                        'month2_qty' => $detail['month2_qty'],
                        'month3_qty' => $detail['month3_qty'],
                    ];
                }
            }

            $plans = ActivityPlan::with('details')
                ->where('user_id', $item['user_id'])
                ->where('status', ActivityPlan::Status_Approved)
                ->whereBetween('date', [$start, $end])
                ->get();

            $plan_details_by_type_ids = [];

            foreach ($plans as $plan) {
                $planMonth = Carbon::parse($plan->date)->month;
                $monthIndex = array_search($planMonth, $months);

                if ($monthIndex === false) continue;

                foreach ($plan->details as $detail) {
                    $typeId = $detail->type_id;

                    if (!isset($plan_details_by_type_ids[$typeId])) {
                        $plan_details_by_type_ids[$typeId] = [
                            'quarter_qty' => 0,
                            'month1_qty' => 0,
                            'month2_qty' => 0,
                            'month3_qty' => 0,
                        ];
                    }

                    $plan_details_by_type_ids[$typeId]['quarter_qty'] += 1;
                    $monthKey = 'month' . ($monthIndex + 1) . '_qty';
                    $plan_details_by_type_ids[$typeId][$monthKey] += 1;
                }
            }

            $activities = Activity::query()
                ->where('user_id', $item['user_id'])
                ->where('status', Activity::Status_Approved)
                ->whereBetween('date', [$start, $end])
                ->get();

            $actvities_by_type_ids = [];

            foreach ($activities as $activity) {
                $activityMonth = Carbon::parse($activity->date)->month;
                $monthIndex = array_search($activityMonth, $months);

                if ($monthIndex === false) continue;

                $typeId = $activity->type_id;

                if (!isset($actvities_by_type_ids[$typeId])) {
                    $actvities_by_type_ids[$typeId] = [
                        'quarter_qty' => 0,
                        'month1_qty' => 0,
                        'month2_qty' => 0,
                        'month3_qty' => 0,
                    ];
                }

                $actvities_by_type_ids[$typeId]['quarter_qty'] += 1;
                $monthKey = 'month' . ($monthIndex + 1) . '_qty';
                $actvities_by_type_ids[$typeId][$monthKey] += 1;
            }

            $items['data'][$index]['plans'] = $plan_details_by_type_ids;
            $items['data'][$index]['activities'] = $actvities_by_type_ids;
            $items['data'][$index]['targets'] = $targets_by_type_ids;

            // hithung progress
            $totalQuarterProgress = 0;
            $totalMonth1Progress = 0;
            $totalMonth2Progress = 0;
            $totalMonth3Progress = 0;

            foreach ($targets_by_type_ids as $typeId => $target) {
                $weight = $types_by_ids[$typeId]->weight ?? 0;
                $actual = $actvities_by_type_ids[$typeId] ?? [
                    'quarter_qty' => 0,
                    'month1_qty' => 0,
                    'month2_qty' => 0,
                    'month3_qty' => 0,
                ];

                // Hitung per periode
                $qProgress = $target['quarter_qty'] > 0
                    ? ($actual['quarter_qty'] / $target['quarter_qty']) * $weight
                    : 0;

                $m1Progress = $target['month1_qty'] > 0
                    ? ($actual['month1_qty'] / $target['month1_qty']) * $weight
                    : 0;

                $m2Progress = $target['month2_qty'] > 0
                    ? ($actual['month2_qty'] / $target['month2_qty']) * $weight
                    : 0;

                $m3Progress = $target['month3_qty'] > 0
                    ? ($actual['month3_qty'] / $target['month3_qty']) * $weight
                    : 0;

                $totalQuarterProgress += $qProgress;
                $totalMonth1Progress += $m1Progress;
                $totalMonth2Progress += $m2Progress;
                $totalMonth3Progress += $m3Progress;
            }

            // Set hasil ke item
            $items['data'][$index]['total_quarter_progress'] = round($totalQuarterProgress, 2);
            $items['data'][$index]['total_month1_progress'] = round($totalMonth1Progress, 2);
            $items['data'][$index]['total_month2_progress'] = round($totalMonth2Progress, 2);
            $items['data'][$index]['total_month3_progress'] = round($totalMonth3Progress, 2);
        }

        if (!$paginated) {
            $items = $items['data'];
        }

        return $items;
    }
}
