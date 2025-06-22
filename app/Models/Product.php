<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

/**
 * Product Model
 */
class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'category_id',
        'name',
        'active',
        'price_1',
        'uom_1',
        'price_2',
        'uom_2',
        'notes',
    ];

    /**
     * Get the category for the product.
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * Get the author of the product.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    /**
     * Get the updater of the product.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
    }

    /**
     * Get the number of active products.
     */
    public static function activeProductCount()
    {
        return DB::select(
            'select count(0) as count from products where active = 1'
        )[0]->count;
    }
}
