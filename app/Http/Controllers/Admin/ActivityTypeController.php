<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityTypeController extends Controller
{
    public function index()
    {
        return inertia('admin/activity-type/Index');
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'date');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = ActivityType::query();

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        allowed_roles([User::Role_Admin]);
        $item = ActivityType::findOrFail($id);
        $item->id = null;
        return inertia('admin/activity-type/Editor', [
            'data' => $item
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? ActivityType::findOrFail($id) : new ActivityType([
            'default_target' => 0,
            'weight' => 0,
            'active' => true,
        ]);
        return inertia('admin/activity-type/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $item = $request->id ? ActivityType::findOrFail($request->id) : new ActivityType();

        $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('activity_types', 'name')->ignore($item->id),
            ],
            'target_period' => 'required|in:' . implode(',', array_keys(ActivityType::TargetPeriods)),
            'default_target' => 'required|numeric',
            'weight' => 'required|numeric',
            'active' => 'nullable|boolean',
            'description' => 'nullable|max:1000',
        ]);

        $item->fill($validated);
        $item->save();

        return redirect()
            ->route('admin.activity-type.index')
            ->with('success', "Jenis kegiatan $item->name telah disimpan.");
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = ActivityType::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Jenis kegiatan $item->name telah dihapus."
        ]);
    }
}
