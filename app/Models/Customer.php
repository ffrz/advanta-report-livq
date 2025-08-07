<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'assigned_user_id',
        'name',
        'phone',
        'type',
        'address',
        'shipping_address',
        'notes',
        'active',
    ];

    protected $casts = [
        'assigned_user_id' => 'integer',
        'active' => 'boolean',
        'created_by_uid' => 'integer',
        'updated_by_uid' => 'integer',
    ];

    const Type_R1 = 'R1';
    const Type_Distributor = 'Distributor';
    const Type_R2 = 'R2';

    const Types = [
        self::Type_R1 => 'R1',
        self::Type_R2 => 'R2',
        self::Type_Distributor => 'Distributor',
    ];

    public static function activeCustomerCount()
    {
        return DB::select(
            "select count(0) as count from customers where active=1"
        )[0]->count;
    }

    public function assigned_user()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    public function updated_by_user()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
    }

    public static function newCustomerCount($start_date, $end_date)
    {
        return DB::select(
            "select count(0) as count from customers where created_datetime >= ? and created_datetime <= ? and active=1",
            [$start_date, $end_date]
        )[0]->count;
    }

    public static function recentCustomers($limit = 5)
    {
        return self::query()
            ->where('active', '=', 1)
            ->limit($limit)
            ->orderByDesc('created_datetime')
            ->get();
    }
}
