<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Variety extends Model
{
    protected $fillable = [
        'name',
        'active',
        'notes',
    ];

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    public function updated_by_user()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
    }
}
