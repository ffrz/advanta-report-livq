<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class Model extends \Illuminate\Database\Eloquent\Model
{
    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'created_datetime')) {
                if (Auth::id()) {
                    $model->created_datetime = current_datetime();
                    $model->created_by_uid = Auth::id();
                }
            }
            return true;
        });

        static::updating(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'updated_datetime')) {
                $model->updated_datetime = current_datetime();
                $model->updated_by_uid = Auth::id();
            }
            return true;
        });
    }
}
