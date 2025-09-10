<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityPlanDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'type_id',
        'product_id',
        'cost',
        'date',
        'location',
        'notes',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'type_id' => 'integer',
        'product_id' => 'integer',
        'cost' => 'float',
        'date' => 'date',
        'created_by_uid' => 'integer',
        'updated_by_uid' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(ActivityPlan::class, 'parent_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function type()
    {
        return $this->belongsTo(ActivityType::class, 'type_id');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    public function updated_by_user()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
    }
}
