<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DemoPlot;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('report_type');

        $currentUser = Auth::user();
        $users = [];
        if ($currentUser->role == User::Role_Agronomist) {
            $users = User::query()->where('role', '=', User::Role_BS)
                ->where('parent_id', '=', $currentUser->id)
                ->orWhere('id', '=', $currentUser->id)
                ->orderBy('name', 'asc')
                ->get();
        } else if ($currentUser->role == User::Role_Admin) {
            $users = User::query()->orderBy('name', 'asc')->get();
        }

        return inertia('admin/report/Index', [
            'report_type' => $type,
            'users' => $users,
            'products' => Product::where('active', true)
                ->orderBy('name')
                ->select('id', 'name')
                ->get(),
        ]);
    }

    public function demoPlotDetail(Request $request)
    {
        $user_id = $request->get('user_id');

        if (isset($user_id)) {
            $current_user = Auth::user();

            $q = DemoPlot::select('demo_plots.*')
                ->leftJoin('users', 'users.id', '=', 'demo_plots.user_id')
                ->leftJoin('products', 'products.id', '=', 'demo_plots.product_id')
                ->with([
                    'user:id,username,name',
                    'product:id,name',
                ]);

            if ($current_user->role == User::Role_Agronomist) {
                if ($user_id == 'all') {
                    $q->whereHas('user', function ($query) use ($current_user) {
                        $query->where('parent_id', $current_user->id);
                    });
                } else {
                    $q->where('demo_plots.user_id', $user_id);
                }
            } else if ($current_user->role == User::Role_Admin) {
                if ($user_id != 'all') {
                    $q->where('demo_plots.user_id', $user_id);
                }
            }

            $items = $q->where('demo_plots.active', true)
                ->orderBy('users.name', 'asc')
                ->orderBy('products.name', 'asc')
                ->get();

            [$title, $user] = $this->resolveTitle('Laporan Demo Plot', $user_id);

            return $this->generatePdfReport('report.demo-plot-detail', 'landscape', compact(
                'items',
                'title',
                'user'
            ));
        }
    }

    public function demoPlotWithPhoto(Request $request)
    {
        $user_id = $request->get('user_id');

        if (isset($user_id)) {
            $current_user = Auth::user();

            $q = DemoPlot::select('demo_plots.*')
                ->leftJoin('users', 'users.id', '=', 'demo_plots.user_id')
                ->leftJoin('products', 'products.id', '=', 'demo_plots.product_id')
                ->leftJoin(
                    DB::raw('
                        (
                            SELECT dpv1.demo_plot_id, dpv1.image_path
                            FROM demo_plot_visits dpv1
                            INNER JOIN (
                                SELECT demo_plot_id, MAX(created_datetime) AS max_created_datetime
                                FROM demo_plot_visits
                                GROUP BY demo_plot_id
                            ) dpv2 ON dpv1.demo_plot_id = dpv2.demo_plot_id AND dpv1.created_datetime = dpv2.max_created_datetime
                        ) AS latest_visits
                    '),
                    'latest_visits.demo_plot_id',
                    '=',
                    'demo_plots.id'
                )
                ->with([
                    'user:id,username,name',
                    'product:id,name',
                ]);

            if ($current_user->role == User::Role_Agronomist) {
                if ($user_id == 'all') {
                    $q->whereHas('user', function ($query) use ($current_user) {
                        $query->where('parent_id', $current_user->id);
                    });
                } else {
                    $q->where('demo_plots.user_id', $user_id);
                }
            } else if ($current_user->role == User::Role_Admin) {
                if ($user_id != 'all') {
                    $q->where('demo_plots.user_id', $user_id);
                }
            }

            $items = $q->where('demo_plots.active', true)
                ->orderBy('users.name', 'asc')
                ->orderBy('products.name', 'asc')
                ->get();

            [$title, $user] = $this->resolveTitle('Laporan Foto Demo Plot', $user_id);

            return $this->generatePdfReport('report.demo-plot-with-photo', 'landscape', compact(
                'items',
                'title',
                'user'
            ));
        }
    }

    public function newDemoPlotDetail(Request $request)
    {
        [$start_date, $end_date] = resolve_period(
            $request->get('period'),
            $request->get('start_date'),
            $request->get('end_date')
        );
        $user_id = $request->get('user_id');

        if (isset($user_id)) {
            $current_user = Auth::user();

            $q = DemoPlot::select('demo_plots.*')
                ->leftJoin('users', 'users.id', '=', 'demo_plots.user_id')
                ->leftJoin('products', 'products.id', '=', 'demo_plots.product_id')
                ->with([
                    'user:id,username,name',
                    'product:id,name',
                ]);

            if ($current_user->role == User::Role_Agronomist) {
                if ($user_id == 'all') {
                    $q->whereHas('user', function ($query) use ($current_user) {
                        $query->where('parent_id', $current_user->id);
                    });
                } else {
                    $q->where('demo_plots.user_id', $user_id);
                }
            }

            $items = $q->where('demo_plots.active', true)
                ->whereBetween('plant_date', [$start_date, $end_date])
                ->orderBy('users.name', 'asc')
                ->orderBy('products.name', 'asc')
                ->get();

            [$title, $user] = $this->resolveTitle('Laporan Demo Plot Baru', $user_id);

            return $this->generatePdfReport('report.new-demo-plot-detail', 'landscape', compact(
                'items',
                'title',
                'user',
                'start_date',
                'end_date',
            ));
        }
    }

    public function clientActualInventory(Request $request)
    {
        $userId = $request->get('user_id');
        $productId = $request->get('product_id');
        $currentUser = Auth::user();

        // 1. Buat subquery untuk mendapatkan tanggal pemeriksaan (check_date) terbaru untuk setiap grup
        $latestCheckDateSubQuery = InventoryLog::select(
            'product_id',
            'customer_id',
            'lot_package',
            DB::raw('MAX(check_date) as latest_date')
        )
            // Terapkan filter user_id dan product_id secara opsional di sini
            ->when($userId && $userId !== 'all', function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->when($productId && $productId !== 'all', function ($query) use ($productId) {
                return $query->where('product_id', $productId);
            })
            ->groupBy('product_id', 'customer_id', 'lot_package');

        // Logika tambahan untuk peran Agronomist
        if ($currentUser->role == User::Role_Agronomist) {
            // Ambil semua user_id bawahan dari parent_id yang sesuai
            $childUserIds = User::where('parent_id', $currentUser->id)->pluck('id');

            // Tambahkan user_id BS itu sendiri ke dalam daftar
            $childUserIds->push($currentUser->id);

            // Tambahkan klausa 'whereIn' untuk memfilter berdasarkan user_id bawahan
            $latestCheckDateSubQuery->whereIn('user_id', $childUserIds);
        }

        // 2. Buat subquery utama untuk mendapatkan ID terakhir dari entri dengan check_date terbaru
        $latestIdSubQuery = InventoryLog::select(DB::raw('MAX(id) as max_id'))
            ->joinSub($latestCheckDateSubQuery, 't_latest_date', function ($join) {
                $join->on('inventory_logs.product_id', '=', 't_latest_date.product_id')
                    ->on('inventory_logs.customer_id', '=', 't_latest_date.customer_id')
                    ->on('inventory_logs.lot_package', '=', 't_latest_date.lot_package')
                    ->on('inventory_logs.check_date', '=', 't_latest_date.latest_date');
            })
            ->groupBy('inventory_logs.product_id', 'inventory_logs.customer_id', 'inventory_logs.lot_package');

        // 3. Jalankan query utama untuk mendapatkan data final
        $items = InventoryLog::from('inventory_logs as t1')
            ->joinSub($latestIdSubQuery, 't_latest_id', function ($join) {
                $join->on('t1.id', '=', 't_latest_id.max_id');
            })
            ->where('t1.quantity', '>', 0)
            ->orderBy('t1.user_id')
            ->orderBy('t1.customer_id')
            ->orderBy('t1.product_id')
            ->get();

        [$title, $user, $product] = $this->resolveTitle('Laporan Inventori Aktual', $userId, $productId);

        return $this->generatePdfReport('report.client-actual-inventory', 'landscape', compact(
            'items',
            'title',
            'user',
            'product',
        ));
    }

    protected function resolveTitle(string $baseTitle, $user_id, $product_id = 'all'): array
    {
        $user = null;
        if ($user_id !== 'all') {
            $user = User::find($user_id);
            $title = "$baseTitle - $user->name ($user->username)";
        } else {
            $title = "$baseTitle - All BS";
        }

        $product = null;
        if ($product_id !== 'all') {
            $product = Product::find($product_id);
            $title .= ' - ' . $product->name;
        } else {
            $title .= ' - All Varietas';
        }

        return [$title, $user, $product];
    }


    protected function generatePdfReport($view, $orientation, $data, $response = 'pdf')
    {
        $filename = env('APP_NAME') . ' - ' . $data['title'];

        if (isset($data['start_date']) || isset($data['end_date'])) {
            if (empty($data['subtitles'])) {
                $data['subtitles'] = [];
            }
            $data['subtitles'][] = 'Periode ' . format_date($data['start_date']) . ' s/d ' . format_date($data['end_date']);
        }

        if ($response == 'pdf') {
            return Pdf::loadView($view, $data)
                ->setPaper('a4', $orientation)
                ->download($filename . '.pdf');
        }

        if ($response == 'html') {
            return view($view, $data);
        }

        throw new Exception('Unknown response type!');
    }

    public function generateExcelReport($header, $data)
    {
        throw new Exception('Unknown response type!');
    }
}
