<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
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

    public const Status_NotResponded = 'not_responded';
    public const Status_Approved = 'approved';
    public const Status_Rejected = 'rejected';

    public const Statuses = [
        self::Status_NotResponded => 'Belum Direspon',
        self::Status_Approved => 'Disetujui',
        self::Status_Rejected => 'Ditolak',
    ];

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
