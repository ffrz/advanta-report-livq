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

    protected $casts = [
        'parent_id' => 'integer',
        'type_id' => 'integer',
        'quarter_qty' => 'integer',
        'month1_qty' => 'integer',
        'month2_qty' => 'integer',
        'month3_qty' => 'integer',
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
