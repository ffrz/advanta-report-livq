<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityPlan;
use App\Models\ActivityTarget;
use App\Models\ActivityType;
use App\Models\Closing;
use App\Models\Customer;
use App\Models\CustomerService;
use App\Models\Interaction;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function bsDashboard(Request $request)
    {
        // TODO: refactor karena kode dupikat dengan ActivityTargetController@index()
        $user = auth()->user(); // atau Auth::user()
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $quarter = null;

        if (!$quarter) {
            $month = now()->month;
            if (in_array($month, [4, 5, 6])) $quarter = 1;
            elseif (in_array($month, [7, 8, 9])) $quarter = 2;
            elseif (in_array($month, [10, 11, 12])) $quarter = 3;
            else $quarter = 4;
        }

        $fiscalQuarterMonths = [
            1 => [4, 5, 6],
            2 => [7, 8, 9],
            3 => [10, 11, 12],
            4 => [1, 2, 3],
        ];

        $months = $fiscalQuarterMonths[$quarter];
        $startYear = ($quarter == 4) ? $year + 1 : $year;

        $start = Carbon::createFromDate($startYear, $months[0], 1)->startOfDay();
        $end = Carbon::createFromDate($startYear, $months[2], 1)->endOfMonth()->endOfDay();

        $targets = ActivityTarget::with([
            'user:id,username,name',
            'details',
            'details.type:id,name',
        ])->where('user_id', $user->id)
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->get();

        // Ambil data rencana kegiatan (plans)
        $plans = ActivityPlan::with('details')
            ->where('user_id', $user->id)
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

        // Ambil data realisasi kegiatan (activities)
        $activities = Activity::query()
            ->where('user_id', $user->id)
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

        return inertia('admin/dashboard/Index', [
            'data' => [
                'types' => ActivityType::where('active', true)
                    ->select(['id', 'name', 'weight'])
                    ->orderBy('name', 'asc')
                    ->get(),
                'targets' => $targets ? $targets[0]->details : [],
                'plans' => $plan_details_by_type_ids,
                'activities' => $actvities_by_type_ids,
            ],
            ''
        ]);
    }

    public function index(Request $request)
    {
        // Redirect to BS dashboard if user is BS
        if ($request->user()->role === User::Role_BS) {
            return $this->bsDashboard($request);
        }

        // For other roles, show the default dashboard
        return inertia('admin/dashboard/Index', [
            'data' => [],
            'period' => [
                'label' => 'This Month',
                'start_date' => '',
                'end_date' => '',
            ],
        ]);
    }

    /// TIDAK DIGUNAKAN
    public function _index(Request $request)
    {
        $period = $request->get('period', 'this_month');
        [$start_date, $end_date] = resolve_period($period);

        $start_date = $start_date ? Carbon::parse($start_date)->startOfMonth() : Carbon::createFromDate(2000, 1, 1);
        $end_date = $start_date->copy()->endOfMonth();

        $user = $request->user();

        if ($user->role === User::Role_BS) {
            $fiscalInfo = getFiscalQuarterInfo($start_date);

            $year = $fiscalInfo['fiscal_year'];
            $quarter = $fiscalInfo['quarter'];
            $month_position = $fiscalInfo['month_position'];

            $targets = ActivityTarget::with(['details.type'])
                ->where('user_id', $user->id)
                ->where('year', $year)
                ->where('quarter', $quarter)
                ->get();

            $summary = [];
            $month_column = 'month' . $month_position . '_qty';

            $total_target = 0;
            $total_completed = 0;
            $total_planned = 0;
            $total_weight = 0;
            $weighted_progress = 0;

            foreach ($targets as $target) {
                foreach ($target->details as $detail) {
                    $type_id = $detail->type_id;
                    $weight = (int) $detail->type->weight ?? 0;

                    if (!isset($summary[$type_id])) {
                        $summary[$type_id] = [
                            'type_id' => $type_id,
                            'type_name' => $detail->type->code ?? $detail->type->name,
                            'target_qty' => 0,
                            'plan_qty' => 0,
                            'real_qty' => 0,
                            'weight' => $weight,
                        ];
                        $total_weight += $weight;
                    }

                    $qty = (int) $detail->{$month_column};

                    $summary[$type_id]['target_qty'] += $qty;
                    $total_target += $qty;
                }
            }

            $start = Carbon::createFromDate($fiscalInfo['start_year'], $fiscalInfo['start_month'], 1)->startOfDay();
            $end = Carbon::createFromDate($fiscalInfo['end_year'], $fiscalInfo['end_month'], 1)->endOfMonth()->endOfDay();

            $plans = ActivityPlan::with('details')
                ->where('user_id', $user->id)
                ->where('status', ActivityPlan::Status_Approved)
                ->whereBetween('date', [$start_date, $end_date])
                ->get();

            foreach ($plans as $plan) {
                foreach ($plan->details as $detail) {
                    $type_id = $detail->type_id;

                    if (!isset($summary[$type_id])) {
                        $summary[$type_id] = [
                            'type_id' => $type_id,
                            'type_name' => $detail->type->name,
                            'target_qty' => 0,
                            'plan_qty' => 0,
                            'real_qty' => 0,
                            'weight' => (int) $detail->type->weight ?? 0,
                        ];
                        $total_weight += $summary[$type_id]['weight'];
                    }

                    $summary[$type_id]['plan_qty'] += 1;
                    $total_planned += 1;
                }
            }

            $activities = Activity::with(['type'])
                ->where('user_id', $user->id)
                ->where('status', Activity::Status_Approved)
                ->whereBetween('date', [$start_date, $end_date])
                ->get();

            foreach ($activities as $activity) {
                $type_id = $activity->type_id;
                $weight = (int) $activity->type->weight ?? 0;

                if (!isset($summary[$type_id])) {
                    $summary[$type_id] = [
                        'type_id' => $type_id,
                        'type_name' => $activity->type->name,
                        'target_qty' => 0,
                        'plan_qty' => 0,
                        'real_qty' => 0,
                        'weight' => $weight,
                    ];
                    $total_weight += $weight;
                }

                $summary[$type_id]['real_qty'] += 1;
                $total_completed += 1;
            }

            if ($total_weight > 0) {
                foreach ($summary as $row) {
                    $target = $row['target_qty'];
                    $real = $row['real_qty'];
                    $weight = $row['weight'];

                    if ($target > 0) {
                        $progress = min($real / $target, 1);
                        $normalized_weight = $weight / $total_weight;
                        $weighted_progress += $progress * $normalized_weight;
                    }
                }
            }

            // === HITUNG PROGRES KUARTALAN ===

            $quarter_summary = [];
            $total_quarter_target = 0;
            $total_quarter_completed = 0;
            $total_quarter_weight = 0;
            $quarter_weighted_progress = 0;

            foreach ($targets as $target) {
                foreach ($target->details as $detail) {
                    $type_id = $detail->type_id;
                    $weight = (int) $detail->type->weight ?? 0;

                    if (!isset($quarter_summary[$type_id])) {
                        $quarter_summary[$type_id] = [
                            'type_id' => $type_id,
                            'type_name' => $detail->type->code ?? $detail->type->name,
                            'target_qty' => 0,
                            'real_qty' => 0,
                            'weight' => $weight,
                        ];
                        $total_quarter_weight += $weight;
                    }

                    $qty =
                        (int) $detail->month1_qty +
                        (int) $detail->month2_qty +
                        (int) $detail->month3_qty;

                    $quarter_summary[$type_id]['target_qty'] += $qty;
                    $total_quarter_target += $qty;
                }
            }

            foreach ($activities as $activity) {
                $type_id = $activity->type_id;
                $weight = (int) $activity->type->weight ?? 0;

                if (!isset($quarter_summary[$type_id])) {
                    $quarter_summary[$type_id] = [
                        'type_id' => $type_id,
                        'type_name' => $activity->type->name,
                        'target_qty' => 0,
                        'real_qty' => 0,
                        'weight' => $weight,
                    ];
                    $total_quarter_weight += $weight;
                }

                $quarter_summary[$type_id]['real_qty'] += 1;
                $total_quarter_completed += 1;
            }

            if ($total_quarter_weight > 0) {
                foreach ($quarter_summary as $row) {
                    $target = $row['target_qty'];
                    $real = $row['real_qty'];
                    $weight = $row['weight'];

                    if ($target > 0) {
                        $progress = min($real / $target, 1);
                        $normalized_weight = $weight / $total_quarter_weight;
                        $quarter_weighted_progress += $progress * $normalized_weight;
                    }
                }
            }

            return inertia('admin/dashboard/Index', [
                'data' => [
                    'targets' => array_values($summary),
                    'total_target' => $total_target,
                    'total_completed' => $total_completed,
                    'total_progress' => round($weighted_progress * 100), // Bulanan
                    'total_quarter_progress' => round($quarter_weighted_progress * 100),
                ],
                'period' => [
                    'label' => \Illuminate\Support\Str::headline(str_replace('_', ' ', $period)),
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                ],
            ]);
        }




        // $labels = [];
        // $count_interactions = [];
        // $count_closings = [];
        // $count_new_customers = [];
        // $total_closings = [];

        // $start = $start_date ? Carbon::parse($start_date) : Carbon::createFromDate(2000, 1, 1);
        // $end = $end_date ? Carbon::parse($end_date) : Carbon::now();

        // if (in_array($period, ['all_time', 'this_year', 'last_year'])) {
        //     // BULANAN
        //     $current = $start->copy();

        //     while ($current->lessThanOrEqualTo($end)) {
        //         $labels[] = $current->format('F Y'); // e.g., January 2024

        //         $monthStart = $current->copy()->startOfMonth();
        //         $monthEnd = $current->copy()->endOfMonth();

        //         $countInteraction = Interaction::where('status', 'done')
        //             ->whereBetween('date', [$monthStart, $monthEnd])
        //             ->count();

        //         $countClosing = Closing::whereBetween('date', [$monthStart, $monthEnd])
        //             ->count();

        //         $countNewCustomer = Customer::whereBetween('created_datetime', [$monthStart, $monthEnd])
        //             ->count();

        //         $sum_closing = Closing::whereBetween('date', [$monthStart, $monthEnd])
        //             ->sum('amount');

        //         $count_interactions[]  = $countInteraction;
        //         $count_closings[]      = $countClosing;
        //         $count_new_customers[] = $countNewCustomer;
        //         $total_closings[]      = $sum_closing;

        //         $current->addMonth();
        //     }
        // } else {
        //     // HARIAN
        //     $current = $start->copy();

        //     while ($current->lessThanOrEqualTo($end)) {
        //         $labels[] = $current->format('d'); // e.g., 01, 02, ..., 31

        //         $countInteraction = Interaction::where('status', 'done')
        //             ->whereDate('date', $current->format('Y-m-d'))
        //             ->count();

        //         $countClosing = Closing::whereDate('date', $current->format('Y-m-d'))
        //             ->count();

        //         $countNewCustomer = Customer::whereDate('created_datetime', $current->format('Y-m-d'))
        //             ->count();

        //         $sum_closing = Closing::whereDate('date', $current->format('Y-m-d'))
        //             ->sum('amount');

        //         $count_interactions[]  = $countInteraction;
        //         $count_closings[]      = $countClosing;
        //         $count_new_customers[] = $countNewCustomer;
        //         $total_closings[]      = $sum_closing;

        //         $current->addDay();
        //     }
        // }

        return inertia('admin/dashboard/Index', [
            'chart_data' => [
                // 'labels' => $labels,
                // 'count_interactions' => $count_interactions,
                // 'count_closings' => $count_closings,
                // 'count_new_customers' => $count_new_customers,
                // 'total_closings' => $total_closings,
                // 'interactions' => Interaction::interactionCountByStatus($start_date, $end_date),
                // 'top_interactions'  => Interaction::getTopInteractions($start_date, $end_date, 5),
                // 'top_sales_closings'  => Closing::getTop5SalesClosings($start_date, $end_date, 5),
            ],
            'data' => [
                // 'recent_interactions' => Interaction::recentInteractions(5),
                // 'recent_closings' => Closing::recentClosings(5),
                // 'recent_customers' => Customer::recentCustomers(5),
                // 'active_interaction_plan_count' => Interaction::activePlanCount(),
                // 'active_customer_service_count' => CustomerService::activeCustomerServiceCount(),
                // 'active_customer_count' => Customer::activeCustomerCount(),
                // 'active_sales_count' => User::activeSalesCount(),
                // 'active_user_count' => User::activeUserCount(),
                // 'active_service_count' => Service::activeServiceCount(),

                // 'interaction_count' => Interaction::interactionCount($start_date, $end_date),
                // 'new_customer_count' => Customer::newCustomerCount($start_date, $end_date),
                // 'closing_count' => Closing::closingCount($start_date, $end_date),
                // 'closing_amount' => Closing::closingAmount($start_date, $end_date),
            ]
        ]);
    }

    /**
     * This method exists here for testing purpose only.
     */
    public function test()
    {
        return inertia('admin/dashboard/Test');
    }
}
