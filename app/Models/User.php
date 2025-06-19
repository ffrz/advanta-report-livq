<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    public const Role_Admin = 'admin';
    public const Role_Sales = 'sales';

    // Display role di hardcode saja, tidak diambil dari translations
    public const Roles = [
        self::Role_Admin => 'Administrator',
        self::Role_Sales => 'Salesman',
    ];

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'active',
        'password',
        'role',
        'last_login_datetime',
        'last_activity_description',
        'last_activity_datetime'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function setLastLogin()
    {
        $this->last_login_datetime = now();
        $this->save();
    }

    public function setLastActivity($description)
    {
        $this->last_activity_description = $description;
        $this->last_activity_datetime = now();
        $this->save();
    }

    public static function activeUserCount()
    {
        return DB::select(
            'select count(0) as count from users where active = 1'
        )[0]->count;
    }

    public static function activeSalesCount()
    {
        return DB::select(
            'select count(0) as count from users where active = 1 and role = \'' . self::Role_Sales . '\''
        )[0]->count;
    }

    public function closings()
    {
        return $this->hasMany(Closing::class, 'user_id');
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class, 'user_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'assigned_user_id');
    }

    public function activeCustomers()
    {
        return $this->hasMany(Customer::class, 'assigned_user_id')->where('active', true);
    }
}
