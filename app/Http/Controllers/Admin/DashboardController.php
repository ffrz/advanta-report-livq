<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Closing;
use App\Models\Customer;
use App\Models\CustomerService;
use App\Models\Interaction;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'this_month');
        [$start_date, $end_date] = resolve_period($period);

        $labels = [];
        $count_interactions = [];
        $count_closings = [];
        $count_new_customers = [];
        $total_closings = [];

        $start = $start_date ? Carbon::parse($start_date) : Carbon::createFromDate(2000, 1, 1);
        $end = $end_date ? Carbon::parse($end_date) : Carbon::now();

        if (in_array($period, ['all_time', 'this_year', 'last_year'])) {
            // BULANAN
            $current = $start->copy();

            while ($current->lessThanOrEqualTo($end)) {
                $labels[] = $current->format('F Y'); // e.g., January 2024

                $monthStart = $current->copy()->startOfMonth();
                $monthEnd = $current->copy()->endOfMonth();

                $countInteraction = Interaction::where('status', 'done')
                    ->whereBetween('date', [$monthStart, $monthEnd])
                    ->count();

                $countClosing = Closing::whereBetween('date', [$monthStart, $monthEnd])
                    ->count();

                $countNewCustomer = Customer::whereBetween('created_datetime', [$monthStart, $monthEnd])
                    ->count();

                $sum_closing = Closing::whereBetween('date', [$monthStart, $monthEnd])
                    ->sum('amount');

                $count_interactions[]  = $countInteraction;
                $count_closings[]      = $countClosing;
                $count_new_customers[] = $countNewCustomer;
                $total_closings[]      = $sum_closing;

                $current->addMonth();
            }
        } else {
            // HARIAN
            $current = $start->copy();

            while ($current->lessThanOrEqualTo($end)) {
                $labels[] = $current->format('d'); // e.g., 01, 02, ..., 31

                $countInteraction = Interaction::where('status', 'done')
                    ->whereDate('date', $current->format('Y-m-d'))
                    ->count();

                $countClosing = Closing::whereDate('date', $current->format('Y-m-d'))
                    ->count();

                $countNewCustomer = Customer::whereDate('created_datetime', $current->format('Y-m-d'))
                    ->count();

                $sum_closing = Closing::whereDate('date', $current->format('Y-m-d'))
                    ->sum('amount');

                $count_interactions[]  = $countInteraction;
                $count_closings[]      = $countClosing;
                $count_new_customers[] = $countNewCustomer;
                $total_closings[]      = $sum_closing;

                $current->addDay();
            }
        }

        return inertia('admin/dashboard/Index', [
            'chart_data' => [
                'labels' => $labels,
                'count_interactions' => $count_interactions,
                'count_closings' => $count_closings,
                'count_new_customers' => $count_new_customers,
                'total_closings' => $total_closings,
                'interactions' => Interaction::interactionCountByStatus($start_date, $end_date),
                'top_interactions'  => Interaction::getTopInteractions($start_date, $end_date, 5),
                'top_sales_closings'  => Closing::getTop5SalesClosings($start_date, $end_date, 5),
            ],
            'data' => [
                'recent_interactions' => Interaction::recentInteractions(5),
                'recent_closings' => Closing::recentClosings(5),
                'recent_customers' => Customer::recentCustomers(5),
                'active_interaction_plan_count' => Interaction::activePlanCount(),
                'active_customer_service_count' => CustomerService::activeCustomerServiceCount(),
                'active_customer_count' => Customer::activeCustomerCount(),
                'active_sales_count' => User::activeSalesCount(),
                'active_user_count' => User::activeUserCount(),
                'active_service_count' => Service::activeServiceCount(),

                'interaction_count' => Interaction::interactionCount($start_date, $end_date),
                'new_customer_count' => Customer::newCustomerCount($start_date, $end_date),
                'closing_count' => Closing::closingCount($start_date, $end_date),
                'closing_amount' => Closing::closingAmount($start_date, $end_date),
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
