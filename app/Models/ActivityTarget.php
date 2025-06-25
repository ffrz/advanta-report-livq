<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_id',
        'user_id',
        'period_type',
        'year',
        'month',
        'quarter',
        'qty',
        'period_start_date',
        'period_end_date',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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

    protected static function booted()
    {
        static::saving(function (self $target) {
            if ($target->period_type === 'month' && $target->year && $target->month) {
                $target->period_start_date = Carbon::create($target->year, $target->month, 1)->startOfMonth();
                $target->period_end_date = Carbon::create($target->year, $target->month, 1)->endOfMonth();
            } elseif ($target->period_type === 'quarter' && $target->year && $target->quarter) {
                // Mapping quarter to month
                $startMonth = ($target->quarter - 1) * 3 + 1;
                $start = Carbon::create($target->year, $startMonth, 1);
                $target->period_start_date = $start->copy()->startOfMonth();
                $target->period_end_date = $start->copy()->addMonths(2)->endOfMonth();
            } else {
                // Jika tidak valid, kosongkan saja
                $target->period_start_date = null;
                $target->period_end_date = null;
            }
        });
    }
}
