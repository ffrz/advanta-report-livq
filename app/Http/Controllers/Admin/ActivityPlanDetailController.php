<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityPlan;
use App\Models\ActivityPlanDetail;
use App\Models\ActivityType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityPlanDetailController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        return inertia('admin/activity-plan-detail/Index');
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
        $item = ActivityPlanDetail::findOrFail($id);
        $item->id = 0;
        $item->parent_id = $request->get('parent_id');

        $this->authorize('view', $item);

        return inertia('admin/activity-plan-detail/Editor', [
            'data' => $item,
            'products' => Product::orderBy('name', 'asc')->get(),
            'types' => ActivityType::orderBy('name', 'asc')->get(),
        ]);
    }

    public function editor(Request $request, $id = 0)
    {
        $item = $id ? ActivityPlanDetail::findOrFail($id) : new ActivityPlanDetail([
            'parent_id' => $request->get('parent_id')
        ]);

        $user = Auth::user();
        if ($user->role == User::Role_BS && $id && $item->parent->status == ActivityPlan::Status_Approved) {
            abort(403, 'Rekaman yang sudah disetujui tidak bisa diedit.');
        }

        $this->authorize('update', $item);

        return inertia('admin/activity-plan-detail/Editor', [
            'data' => $item,
            'products' => Product::orderBy('name', 'asc')->get(),
            'types' => ActivityType::orderBy('name', 'asc')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $validated =  $request->validate([
            'parent_id'  => 'required|exists:activity_plans,id',
            'type_id'    => 'required|exists:activity_types,id',
            'product_id' => 'nullable|exists:products,id',
            'cost'       => 'nullable|numeric',
            'location'   => 'nullable|string|max:100',
            'notes'      => 'nullable|string|max:500',
        ]);

        $item = !$request->id
            ? new ActivityPlanDetail()
            : ActivityPlanDetail::findOrFail($request->post('id', 0));

        $this->authorize('update', $item);

        DB::beginTransaction();
        $validated['cost'] = !empty($validated['cost']) ? $validated['cost'] : 0;
        $validated['location'] = !empty($validated['location']) ? $validated['location'] : '';
        $item->fill($validated);
        $item->save();

        $parent = $item->parent;
        $parent->total_cost = $parent->details()->sum('cost');
        $parent->save();
        DB::commit();

        return redirect(route('admin.activity-plan.detail', ['id' => $item->parent_id, 'tab' => 'detail']))
            ->with('success', "Detail plan #$item->id telah disimpan.");
    }

    public function delete($id)
    {
        $item = ActivityPlanDetail::findOrFail($id);
        $parent = $item->parent;

        $user = Auth::user();
        if ($user->role == User::Role_BS && $item->parent->status == ActivityPlan::Status_Approved) {
            abort(403, 'Rekaman yang sudah disetujui tidak bisa dihapus');
        }

        DB::beginTransaction();
        $item->delete();

        $parent->total_cost = $parent->details()->sum('cost');
        $parent->save();

        DB::commit();

        return response()->json([
            'message' => "Detail #$item->id telah dihapus.",
            'new_total' => $parent->total_cost,
        ]);
    }

    protected function createQuery(Request $request)
    {
        $q = ActivityPlanDetail::with([
            'type:id,name',
            'product:id,name',
        ]);

        $q->where('parent_id', $request->get('parent_id'));

        return $q;
    }
}
