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
        'email',
        'address',
        'company',
        'business_type',
        'source',
        'notes',
        'active',
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

    public function services()
    {
        return $this->hasMany(CustomerService::class);
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
