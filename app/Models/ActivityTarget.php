<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'quarter',
        'notes',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'year' => 'integer',
        'quarter' => 'integer',
        'created_by_uid' => 'integer',
        'updated_by_uid' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
        return $this->hasMany(ActivityTargetDetail::class, 'parent_id');
    }
}
