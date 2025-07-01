<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'total_cost',
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

    public function getFormattedIdAttribute(): string
    {
        return '#RK-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function responded_by()
    {
        return $this->belongsTo(User::class, 'responded_by_id');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    public function updated_by_user()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
    }

    public function details()
    {
        return $this->hasMany(ActivityPlanDetail::class, 'parent_id');
    }
}
