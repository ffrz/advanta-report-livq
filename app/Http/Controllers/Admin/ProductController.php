<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        return inertia('admin/product/Index', [
            'categories' => ProductCategory::all(['id', 'name']),
        ]);
    }

    public function detail($id = 0)
    {
        $item = Product::with(['category'])->findOrFail($id);
        return inertia('admin/product/Detail', [
            'data' => $item,
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'date');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = Product::with(['category']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['category_id']) && $filter['category_id'] != 'all') {
            $q->where('category_id', '=', $filter['category_id']);
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        $items->getCollection()->transform(function ($item) {
            $item->description = strlen($item->description) > 50 ? substr($item->description, 0, 50) . '...' : $item->description;
            return $item;
        });

        return response()->json($items);
    }

    public function duplicate($id)
    {
        allowed_roles([User::Role_Admin]);
        $item = Product::findOrFail($id);
        $item->id = null;
        return inertia('admin/product/Editor', [
            'data' => $item,
            'categories' => ProductCategory::all(['id', 'name']),
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? Product::findOrFail($id) : new Product(
            ['active' => 1]
        );
        return inertia('admin/product/Editor', [
            'data' => $item,
            'categories' => ProductCategory::all(['id', 'name']),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'category_id' => [
                'nullable',
                Rule::exists('product_categories', 'id'),
            ],
            'name' => [
                'required',
                'max:255',
                Rule::unique('products', 'name')->ignore($request->id), // agar saat update tidak dianggap duplikat sendiri
            ],
            'description' => 'nullable|max:1000',
            'uom_1' => 'required|max:255',
            'uom_2' => 'nullable|max:255',
            'price_1' => 'required|numeric',
            'price_2' => 'nullable|numeric',
            'active' => 'nullable|boolean',
            'notes' => 'nullable|max:1000',
        ]);

        $item = $request->id ? Product::findOrFail($request->id) : new Product();
        $item->fill($validated);
        $item->save();

        return redirect(route('admin.product.index'))
            ->with('success', "Varietas $item->name telah disimpan.");
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = Product::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Varietas $item->name telah dihapus."
        ]);
    }
}
