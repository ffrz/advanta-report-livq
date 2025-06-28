<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type_id',
        'product_id',
        'date',
        'cost',
        'location',
        'latlong',
        'image_path',
        'responded_by_id',
        'responded_datetime',
        'status',
        'notes',
    ];

    public function getFormattedIdAttribute(): string
    {
        return '#RK-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function responded_by()
    {
        return $this->belongsTo(User::class, 'responded_by_id');
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
