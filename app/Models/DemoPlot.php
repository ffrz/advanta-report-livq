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

    const PlantStatus_NotYetPlanted   = 'not_yet_planted';
    // const PlantStatus_Planted         = 'planted';
    const PlantStatus_NotYetEvaluated = 'not_yet_evaluated';
    const PlantStatus_Satisfactoy     = 'satisfactory';
    const PlantStatus_Unsatisfactory  = 'unsatisfactory';
    const PlantStatus_Completed       = 'completed';
    const PlantStatus_Failed          = 'failed';

    const PlantStatuses = [
        self::PlantStatus_NotYetPlanted   => 'Belum Ditanam',
        // self::PlantStatus_Planted         => 'Baru Ditanam',
        self::PlantStatus_NotYetEvaluated => 'Belum Dievaluasi',
        self::PlantStatus_Satisfactoy     => 'Memuaskan',
        self::PlantStatus_Unsatisfactory  => 'Kurang Memuaskan',
        self::PlantStatus_Completed       => 'Selesai',
        self::PlantStatus_Failed          => 'Gagal',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    public function updated_by_user()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
    }

    public function visits()
    {
        return $this->hasMany(DemoPlotVisit::class);
    }
}
