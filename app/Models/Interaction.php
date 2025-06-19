<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Interaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'service_id',
        'date',
        'type',
        'status',
        'engagement_level',
        'subject',
        'summary',
        'location',
        'image_path',
        'notes',
    ];

    const Status_Planned = 'planned';
    const Status_Done = 'done';
    const Status_Cancelled = 'cancelled';

    const Statuses = [
        self::Status_Planned => 'Planned',
        self::Status_Done => 'Done',
        self::Status_Cancelled => 'Cancelled',
    ];

    const Type_Visit = 'visit';
    const Type_Call = 'call';
    const Type_Chat = 'chat';
    const Type_Email = 'email';
    const Type_Other = 'other';

    const Types = [
        self::Type_Visit => 'Visit',
        self::Type_Call => 'Call',
        self::Type_Chat => 'Chat',
        self::Type_Email => 'Email',
        self::Type_Other => 'Other',
    ];

    const EngagementLevel_None = 'none'; // Belum ada indikasi ketertarikan. Mungkin hanya interaksi awal (misalnya tanya-tanya tanpa tujuan jelas).
    const EngagementLevel_Cold = 'cold'; // Sudah ada interaksi, tapi belum menunjukkan minat atau belum layak untuk dikejar lebih lanjut
    const EngagementLevel_Warm = 'warm'; // Ada minat, ada komunikasi, tapi masih perlu diyakinkan atau belum ada urgensi.
    const EngagementLevel_Hot = 'hot'; // Sangat tertarik dan kemungkinan besar akan closing jika ditindaklanjuti dengan benar.
    const EngagementLevel_Customer = 'customer'; // Sudah menjadi pelanggan aktif dan telah menggunakan layanan.
    const EngagementLevel_Churned = 'churned'; // Pernah menjadi pelanggan, tapi saat ini sudah berhenti atau tidak aktif lagi.
    const EngagementLevel_Lost = 'lost'; // Potensi pelanggan yang hilang. Sudah pasti tidak tertarik lagi atau menolak secara langsung.

    const EngagementLevels = [
        self::EngagementLevel_None => 'None',
        self::EngagementLevel_Cold => 'Cold',
        self::EngagementLevel_Warm => 'Warm',
        self::EngagementLevel_Hot => 'Hot',
        self::EngagementLevel_Customer => 'Customer',
        self::EngagementLevel_Churned => 'Churned',
        self::EngagementLevel_Lost => 'Lost',
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

    public static function activePlanCount()
    {
        return self::where('status', self::Status_Planned)->count();
    }

    public static function interactionCount($start_date = null, $end_date = null)
    {
        $query = self::query();

        if ($start_date) {
            $query->where('date', '>=', $start_date);
        }
        if ($end_date) {
            $query->where('date', '<=', $end_date);
        }

        return $query->count();
    }

    public static function interactionCountByStatus($start_date = null, $end_date = null)
    {
        $query = self::query();

        if ($start_date) {
            $query->where('date', '>=', $start_date);
        }
        if ($end_date) {
            $query->where('date', '<=', $end_date);
        }

        $interactions = $query->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            [
                'name' => self::Statuses[self::Status_Planned],
                'value' => $interactions[self::Status_Planned] ?? 0,
            ],
            [
                'name' => self::Statuses[self::Status_Done],
                'value' => $interactions[self::Status_Done] ?? 0,
            ],
            [
                'name' => self::Statuses[self::Status_Cancelled],
                'value' => $interactions[self::Status_Cancelled] ?? 0,
            ]
        ];
    }

    public static function getTopInteractions($start_date, $end_date, $limit = 5)
    {
        $items = DB::table('interactions as i')
            ->join('users as u', 'i.user_id', '=', 'u.id')
            ->select('u.name', DB::raw('COUNT(*) as total_interactions'))
            ->whereBetween('i.date', [$start_date, $end_date])
            ->where('i.status', 'done')
            ->groupBy('i.user_id', 'u.name')
            ->orderByDesc('total_interactions')
            ->limit($limit)
            ->get();

        return [
            'labels' => $items->pluck('name')->toArray(),
            'data' => $items->pluck('total_interactions')->toArray(),
        ];
    }

    public static function recentInteractions($limit)
    {
        $query = self::with([
            'customer',
            'service',
            'user'
        ]);

        return $query->where('status', '=', self::Status_Done)
            ->limit($limit)
            ->orderByDesc('date')
            ->get();
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($item) {
            if ($item->image_path && file_exists(public_path($item->image_path))) {
                @unlink(public_path($item->image_path));
            }
        });

        static::updating(function ($item) {
            $item->old_image_path = $item->getOriginal('image_path');
        });

        static::updated(function ($item) {
            if (
                isset($item->old_image_path) &&
                $item->old_image_path !== $item->image_path &&
                file_exists(public_path($item->old_image_path))
            ) {
                @unlink(public_path($item->old_image_path));
            }
        });
    }
}
