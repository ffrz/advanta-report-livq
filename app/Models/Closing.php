<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Closing extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'customer_id',
        'service_id',
        'description',
        'date',
        'amount',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function customerService()
    {
        return $this->hasOne(CustomerService::class, 'closing_id');
    }

    public static function booted()
    {
        static::saved(function ($item) {
            if ($item->customerService) {
                $item->customerService->closing_id = $item->id;
                $item->customerService->save();
            } else {
                $customerService = new CustomerService([
                    'customer_id' => $item->customer_id,
                    'service_id' => $item->service_id,
                    'closing_id' => $item->id,
                    'status' => CustomerService::Status_Active,
                    'description' => $item->description,
                    'start_date' => $item->date,
                    'notes' => $item->notes,
                ]);
                $customerService->save();
            }
        });

        static::deleted(function ($item) {
            if ($item->customerService) {
                $item->customerService->delete();
            }
        });
    }

    public static function closingCount($start_date = null, $end_date = null)
    {
        $q = self::query();

        if ($start_date) {
            $q->where('date', '>=', $start_date);
        }
        if ($end_date) {
            $q->where('date', '<=', $end_date);
        }

        return $q->count();
    }

    public static function closingAmount($start_date = null, $end_date = null)
    {
        $q = self::query();

        if ($start_date) {
            $q->where('date', '>=', $start_date);
        }
        if ($end_date) {
            $q->where('date', '<=', $end_date);
        }

        return $q->sum('amount');
    }

    public static function getTop5SalesClosings($start_date, $end_date, $limit = 5)
    {
        $items = DB::table('closings as c')
            ->join('users as u', 'c.user_id', '=', 'u.id')
            ->select('u.name', DB::raw('SUM(c.amount) as total_amount'))
            ->whereBetween('c.date', [$start_date, $end_date])
            ->groupBy('c.user_id', 'u.name')
            ->orderByDesc('total_amount')
            ->limit($limit)
            ->get();

        return [
            'labels' => $items->pluck('name')->toArray(),
            'data' => $items->pluck('total_amount')->toArray(),
        ];
    }

    public static function recentClosings($limit)
    {
        $q = self::with([
            'customer',
            'service',
            'user'
        ]);

        return $q->limit($limit)
            ->orderByDesc('date')
            ->get();
    }
}
