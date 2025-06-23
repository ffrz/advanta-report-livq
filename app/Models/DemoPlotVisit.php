<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class DemoPlotVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'demo_plot_id',
        'visit_date',
        'plant_status',
        'latlong',
        'image_path',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function demo_plot()
    {
        return $this->belongsTo(DemoPlot::class, 'demo_plot_id');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    public function updated_by_user()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
    }
}
