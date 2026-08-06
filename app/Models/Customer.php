<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'type',
        'full_name',
        'phone',
        'username',
        'password',
        'device_limit',
        'status',
        'speed_override_enabled',
        'download_speed',
        'download_unit',
        'upload_speed',
        'upload_unit',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'speed_override_enabled' => 'boolean',
        'download_speed' => 'integer',
        'download_unit' => 'string',
        'upload_speed' => 'integer',
        'upload_unit' => 'string',
    ];

    // 🔥 DEFAULT VALUES (STABILITY FIX)
    protected $attributes = [
        'device_limit' => 1,
        'status' => 'active',
        'speed_override_enabled' => false,
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(CustomerSubscription::class);
    }

    // 🔥 SAFE SPEED CHECK (USED BY SERVICES)
    public function hasSpeedOverride(): bool
    {
        return $this->speed_override_enabled && !is_null($this->download_speed);
    }
}