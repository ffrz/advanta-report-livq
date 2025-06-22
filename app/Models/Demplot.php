<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Demplot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'variety_id',
        'owner_name',
        'date',
        'location',
        'latlong',
        'image_path',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function variety()
    {
        return $this->belongsTo(Variety::class, 'variety_id');
    }
}
