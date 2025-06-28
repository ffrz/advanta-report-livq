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
        'default_quarter_target',
        'default_month1_target',
        'default_month2_target',
        'default_month3_target',
        'weight',
        'require_product'
    ];
}
