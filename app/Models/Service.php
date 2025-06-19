<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Service extends Model
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

    public static function activeServiceCount()
    {
        return DB::select(
            'select count(0) as count from services where active = 1'
        )[0]->count;
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_services')
            ->withPivot('status') // jika kolom active ada di pivot
            ->withTimestamps();   // opsional, jika kamu pakai timestamps di pivot
    }

    public function activeCustomers()
    {
        return $this->belongsToMany(Customer::class, 'customer_services')
            ->wherePivot('status', CustomerService::Status_Active);
    }
}
