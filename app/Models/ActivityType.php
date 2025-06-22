<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'active',
        'target_period',
        'default_target',
        'weight'
    ];

    const TargetPeriod_Yearly = 'tahunan';
    const TargetPeriod_Quarterly = 'quarterly';
    const TargetPeriod_Monthly = 'monthly';

    const TargetPeriods = [
        self::TargetPeriod_Monthly => 'Bulanan',
        self::TargetPeriod_Quarterly => 'Kwartal',
        self::TargetPeriod_Yearly => 'Tahunan',
    ];
}
