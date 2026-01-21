<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Hotspot extends Model
{
    protected $fillable = [
        'location_id',
        'ssid_name',
        'physical_address',
        'nat_type',
        'setup_type',
        'setup_profile',
        'status',
        'vpn_ip',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'script_generated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Hotspot $hotspot) {
            if (empty($hotspot->token)) {
                $hotspot->token = Str::random(48);
            }
        });
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function getIsOnlineAttribute(): bool
    {
        if (!$this->last_seen_at) return false;
        return $this->last_seen_at->gt(now()->subMinutes(3)); // online window
    }
}
