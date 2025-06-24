<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    public function index()
    {
        allowed_roles([User::Role_Admin]);
        return inertia('admin/product-category/Index');
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'date');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = ProductCategory::query();

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
            });
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        allowed_roles([User::Role_Admin]);
        $item = ProductCategory::findOrFail($id);
        $item->id = null;
        return inertia('admin/product-category/Editor', [
            'data' => $item
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? ProductCategory::findOrFail($id) : new ProductCategory();
        return inertia('admin/product-category/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $item = $request->id ? ProductCategory::findOrFail($request->id) : new ProductCategory();

        $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('product_categories', 'name')->ignore($item->id),
            ],
            'description' => 'nullable|max:1000',
        ]);

        $item->fill([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
        ]);

        $item->save();

        $messageKey = $request->id ? 'product-category-updated' : 'product-category-created';

        return redirect()
            ->route('admin.product-category.index')
            ->with('success', __("messages.$messageKey", ['name' => $item->name]));
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = ProductCategory::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => __('messages.product-category-deleted', ['name' => $item->name])
        ]);
    }
}
