<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Target;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TargetController extends Controller
{
    public function index()
    {
        Auth::user()->setLastActivity('Melihat daftar target');

        return inertia('admin/target/Index', [
            'users' => User::where('active', true)
                ->where('role', User::Role_BS)
                ->orderBy('username', 'asc')->get(),
        ]);
    }

    public function detail($id = 0)
    {
        Auth::user()->setLastActivity('Melihat rincian target #' . $id);

        return inertia('admin/target/Detail', [
            'data' => Target::with([
                'user',
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

    public function editor(Request $request, $id = 0)
    {
        allowed_roles([User::Role_Admin, User::Role_Agronomist]);

        $item = $id ? Target::findOrFail($id) : new Target([
            'user_id' => null,
            'fm' => 3,
            'odp' => 1,
            'ft' => 2,
            'fdd' => 1,
        ]);

        return inertia('admin/target/Editor', [
            'data' => $item,
            'users' => User::where('active', true)
                ->where('role', User::Role_BS)
                ->orderBy('username', 'asc')
                ->select(['id', 'name', 'username'])
                ->get(),
        ]);
    }

    public function save(Request $request)
    {
        allowed_roles([User::Role_Admin, User::Role_Agronomist]);

        $item = !$request->id
            ? new Target()
            : Target::findOrFail($request->post('id', 0));

        $validated =  $request->validate([
            'user_id'          => 'required|exists:users,id',
            'date'             => 'required|date',
            'fm'               => 'required|numeric',
            'odp'              => 'required|numeric',
            'ft'               => 'required|numeric',
            'fdd'              => 'required|numeric',
            'notes'            => 'nullable|string|max:500',
        ], [
            'date.unique' => 'Target pada bulan tersebut sudah ada.',
            'user_id.required' => 'Kolom BS wajib diisi.',
            'fm.required' => 'Jumlah FM wajib diisi.',
            'odp.required' => 'Jumlah ODP wajib diisi.',
            'ft.required' => 'Jumlah FT wajib diisi.',
            'fdd.required' => 'Jumlah FDD wajib diisi.',
        ]);

        $incomingDate = Carbon::parse($validated['date']);
        $incomingYear = $incomingDate->year;
        $incomingMonth = $incomingDate->month;

        $duplicateCheckQuery = Target::where('user_id', $validated['user_id'])
            ->whereYear('date', $incomingYear)
            ->whereMonth('date', $incomingMonth);

        if ($item->exists) {
            $duplicateCheckQuery->where('id', '!=', $item->id);
        }

        $existingDuplicate = $duplicateCheckQuery->first();

        if ($existingDuplicate) {
            throw ValidationException::withMessages([
                'date' => ['Target pada bulan tersebut sudah ada.'],
            ]);
        }

        $item->fill($validated);
        $item->total_target = $validated['fm'] + $validated['odp'] + $validated['ft'] + $validated['fdd'];
        $item->save();

        if (!$request->id) {
            Auth::user()->setLastActivity('Menambahkan target #' . $item->id);
        } else {
            Auth::user()->setLastActivity('Memperbarui target #' . $item->id);
        }

        return redirect(route('admin.target.index'))->with('success', "Target #$item->id telah disimpan.");
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin, User::Role_Agronomist]);

        $item = Target::findOrFail($id);
        $item->delete();

        Auth::user()->setLastActivity('Menghapus target #' . $item->id);

        return response()->json([
            'message' => "Target $item->name telah dihapus."
        ]);
    }

    /**
     * Mengekspor daftar interaksi ke dalam format PDF atau Excel.
     */
    public function export(Request $request)
    {
        $items = $this->createQuery($request)->orderBy('id', 'desc')->get();

        $title = 'Daftar Interaksi';
        $filename = $title . ' - ' . env('APP_NAME') . Carbon::now()->format('dmY_His');

        if ($request->get('format') == 'pdf') {
            $pdf = Pdf::loadView('export.target-list-pdf', compact('items', 'title'))
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
            $sheet->setCellValue('D1', 'Status');
            $sheet->setCellValue('E1', 'Sales');
            $sheet->setCellValue('F1', 'Client');
            $sheet->setCellValue('G1', 'Layanan');
            $sheet->setCellValue('H1', 'Engagement');
            $sheet->setCellValue('I1', 'Subjek');
            $sheet->setCellValue('J1', 'Summary');
            $sheet->setCellValue('K1', 'Catatan');

            // Tambahkan data ke Excel
            $row = 2;
            foreach ($items as $item) {
                $sheet->setCellValue('A' . $row, $item->id);
                $sheet->setCellValue('B' . $row, $item->date);

                $sheet->setCellValue('E' . $row, $item->user->name .  ' (' . $item->user->username . ')');
                $sheet->setCellValue('F' . $row, $item->customer->name . ' - ' . $item->customer->company . ' - ' . $item->customer->address);
                $sheet->setCellValue('I' . $row, $item->service->name);

                $sheet->setCellValue('H' . $row, $item->subject);
                $sheet->setCellValue('J' . $row, $item->summary);
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
        $user = Auth::user();
        $filter = $request->get('filter', []);

        $q = Target::with([
            'user:id,username,name',
        ]);

        if ($user->role == User::Role_BS) {
            $q->where('user_id', '=', $user->id);
        } else if (!empty($filter['user_id']) && ($filter['user_id'] != 'all')) {
            $q->where('user_id', '=', $filter['user_id']);
        }

        // if (!empty($filter['search'])) {
        //     $q->where(function ($q) use ($filter) {
        //         $q->where('notes', 'like', '%' . $filter['search'] . '%')
        //             ->orWhereHas('user', function ($q) use ($filter) {
        //                 $q->where('name', 'like', '%' . $filter['search'] . '%');
        //                 $q->orWhere('username', 'like', '%' . $filter['search'] . '%');
        //             });
        //     });
        // }

        if (!empty($filter['period']) && ($filter['period'] != 'all')) {
            if ($filter['period'] == 'this_month') {
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                $q->whereBetween('date', [$start, $end]);
            } elseif ($filter['period'] == 'last_month') {
                $start = Carbon::now()->subMonthNoOverflow()->startOfMonth();
                $end = Carbon::now()->subMonthNoOverflow()->endOfMonth();
                $q->whereBetween('date', [$start, $end]);
            } elseif ($filter['period'] == 'this_year') {
                $start = Carbon::now()->startOfYear();
                $end = Carbon::now()->endOfYear();
                $q->whereBetween('date', [$start, $end]);
            } elseif ($filter['period'] == 'last_year') {
                $start = Carbon::now()->subYear()->startOfYear();
                $end = Carbon::now()->subYear()->endOfYear();
                $q->whereBetween('date', [$start, $end]);
            } else {
                // Asumsikan filter['period'] dalam format YYYY-MM-DD
                try {
                    $date = Carbon::parse($filter['period']);
                    $q->whereDate('date', $date);
                } catch (\Exception $e) {
                    // Handle kesalahan parsing tanggal jika perlu
                }
            }
        }

        return $q;
    }
}
