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
            'suppliers' => Supplier::all(['id', 'name', 'phone']),
        ]);
    }

    public function detail($id = 0)
    {
        $item = Product::with(['category', 'supplier', 'createdBy', 'updatedBy'])->findOrFail($id);
        return inertia('admin/product/Detail', [
            'data' => $item,
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'date');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = Product::with(['supplier', 'category']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('description', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['type']) && $filter['type'] != 'all') {
            $q->where('type', '=', $filter['type']);
            if ($filter['type'] == Product::Type_Stocked) {
                if (!empty($filter['stock_status']) && $filter['stock_status'] != 'all') {
                    if ($filter['stock_status'] == 'low') {
                        $q->whereColumn('stock', '<', 'min_stock');
                        $q->where('stock', '!=', 0);
                    } elseif ($filter['stock_status'] == 'out') {
                        $q->where('stock', '=', 0);
                    } elseif ($filter['stock_status'] == 'over') {
                        $q->whereColumn('stock', '>', 'max_stock');
                    } elseif ($filter['stock_status'] == 'ready') {
                        $q->where('stock', '>', 0);
                    }
                }
            }
        }

        if (!empty($filter['category_id']) && $filter['category_id'] != 'all') {
            $q->where('category_id', '=', $filter['category_id']);
        }

        if (!empty($filter['supplier_id']) && $filter['supplier_id'] != 'all') {
            $q->where('supplier_id', '=', $filter['supplier_id']);
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
            'suppliers' => Supplier::all(['id', 'name', 'phone']),
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? Product::findOrFail($id) : new Product(
            ['active' => 1, 'type' => Product::Type_Stocked]
        );
        return inertia('admin/product/Editor', [
            'data' => $item,
            'categories' => ProductCategory::all(['id', 'name']),
            'suppliers' => Supplier::all(['id', 'name', 'phone']),
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'category_id' => [
                'nullable',
                Rule::exists('product_categories', 'id'),
            ],
            'supplier_id' => [
                'nullable',
                Rule::exists('suppliers', 'id'),
            ],
            'type' => [
                'nullable',
                Rule::in(array_keys(Product::Types)),
            ],
            'name' => [
                'required',
                'max:255',
                Rule::unique('products', 'name')->ignore($request->id), // agar saat update tidak dianggap duplikat sendiri
            ],
            'description' => 'nullable|max:1000',
            'barcode' => 'nullable|max:255',
            'uom' => 'nullable|max:255',
            'stock' => 'nullable|numeric',
            'min_stock' => 'nullable|numeric',
            'max_stock' => 'nullable|numeric',
            'cost' => 'nullable|numeric',
            'price' => 'nullable|numeric',
            'active' => 'nullable|boolean',
            'notes' => 'nullable|max:1000',
        ]);

        $item = $request->id ? Product::findOrFail($request->id) : new Product();

        $item->fill([
            'category_id' => $data['category_id'] ?? null,
            'supplier_id' => $data['supplier_id'] ?? null,
            'type' => $data['type'] ?? Product::Type_Stocked,
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'barcode' => $data['barcode'] ?? '',
            'uom' => $data['uom'] ?? '',
            'stock' => $data['stock'] ?? 0,
            'min_stock' => $data['min_stock'] ?? 0,
            'max_stock' => $data['max_stock'] ?? 0,
            'cost' => $data['cost'] ?? 0,
            'price' => $data['price'] ?? 0,
            'active' => $data['active'] ?? 0,
            'notes' => $data['notes'] ?? '',
        ]);

        DB::beginTransaction();

        $item->save();

        if ($request->id) {
            $oldStock = $item->getOriginal('stock');
            $newStock = $item->stock;
            $diff = $newStock - $oldStock;

            if ($oldStock != $newStock) {
                StockMovement::create([
                    'ref_type' => StockMovement::RefType_ManualAdjustment,
                    'product_id' => $item->id,
                    'quantity' => $diff,
                ]);
            }
        }
        else {
            StockMovement::create([
                'ref_type' => StockMovement::RefType_InitialStock,
                'product_id' => $item->id,
                'quantity' => $item->stock,
            ]);
        }
        
        DB::commit();

        $messageKey = $request->id ? 'product-updated' : 'product-created';

        return redirect(route('admin.product.index'))
            ->with('success', __("messages.$messageKey", ['name' => $item->name]));
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = Product::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => __('messages.product-deleted', ['name' => $item->name])
        ]);
    }
}
