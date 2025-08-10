<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

/**
 * InventoryLog Model
 */
class InventoryLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'customer_id',
        'user_id',
        'check_date',
        'area',
        'lot_package',
        'quantity',
        'notes',
        'base_quantity',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'customer_id' => 'integer',
        'user_id' => 'integer',
        'quantity' => 'float',
        'base_quantity' => 'integer',
        'check_date' => 'date',
    ];

    /**
     * Get the product for the product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the customer for the product.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user for the product.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the author of the product.
     */
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    /**
     * Get the updater of the product.
     */
    public function updated_by()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
    }
}
