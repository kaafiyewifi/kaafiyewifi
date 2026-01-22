<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Auth\Passwords\CanResetPassword;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, CanResetPassword;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Laravel 11+
    ];

    public function locations()
    {
        return $this->belongsToMany(Location::class)->withTimestamps();
    }

    public function allowedLocationIds()
    {
        if ($this->hasRole('super_admin')) {
            return Location::query()->pluck('id');
        }

        return $this->locations()->pluck('locations.id');
    }

    public function canAccessLocation(int $locationId): bool
    {
        if ($this->hasRole('super_admin')) return true;

        return $this->locations()->where('locations.id', $locationId)->exists();
    }
}
