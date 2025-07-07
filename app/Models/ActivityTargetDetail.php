<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityTargetDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'type_id',
        'quarter_qty',
        'month1_qty',
        'month2_qty',
        'month3_qty',
    ];

    public function parent()
    {
        return $this->belongsTo(ActivityTarget::class, 'parent_id');
    }

    public function type()
    {
        return $this->belongsTo(ActivityType::class, 'type_id');
    }
}
