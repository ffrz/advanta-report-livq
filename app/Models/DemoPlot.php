<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class DemoPlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'plant_date',
        'latlong',
        'image_path',
        'owner_name',
        'owner_phone',
        'field_location',
        'active',
        'notes',

        'plant_status',
        'last_visit',
    ];

    const PlantStatus_Planted        = 'planted';
    const PlantStatus_Completed      = 'completed';
    const PlantStatus_Failed         = 'failed';
    const PlantStatus_Satisfactoy    = 'satisfactory';
    const PlantStatus_Unsatisfactory = 'unsatisfactory';

    const PlantStatuses = [
        self::PlantStatus_Planted        => 'Baru Ditanam',
        self::PlantStatus_Completed      => 'Selesai',
        self::PlantStatus_Failed         => 'Gagal',
        self::PlantStatus_Satisfactoy    => 'Memuaskan',
        self::PlantStatus_Unsatisfactory => 'Kurang Memuaskan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
