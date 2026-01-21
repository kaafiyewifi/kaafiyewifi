<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // haddii Laravel 11+; haddii kale ka saar
    ];

    public function locations()
    {
        return $this->belongsToMany(\App\Models\Location::class)->withTimestamps();
    }

    /**
     * Returns a Collection of location IDs user is allowed to access.
     * Super Admin => all location IDs
     * Others => only assigned location IDs
     */
    public function allowedLocationIds()
    {
        if ($this->hasRole('super_admin')) {
            return \App\Models\Location::query()->pluck('id');
        }

        return $this->locations()->pluck('locations.id');
    }

    /**
     * Quick boolean check.
     */
    public function canAccessLocation(int $locationId): bool
    {
        if ($this->hasRole('super_admin')) return true;

        return $this->locations()->where('locations.id', $locationId)->exists();
    }
}
