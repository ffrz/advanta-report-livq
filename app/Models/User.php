<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    public const Role_Admin = 'admin';
    public const Role_BS = 'bs';
    public const Role_Agronomist = 'agronomist';
    public const Role_ASM = 'asm';

    // Display role di hardcode saja, tidak diambil dari translations
    public const Roles = [
        self::Role_BS => 'BS',
        self::Role_Agronomist => 'Agronomis',
        self::Role_ASM => 'ASM',
        self::Role_Admin => 'Administrator',
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
        'work_area',
        'parent_id',
        'last_login_datetime',
        'last_activity_description',
        'last_activity_datetime'
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'active' => 'boolean',
        'last_login_datetime' => 'datetime',
        'last_activity_datetime' => 'datetime',
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

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }
}
