<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    protected $fillable = [
        'location_id',
        'name',
        'ip_address',
        'api_port',
        'username',
        'password',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // 🔗 Relations
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function hotspots()
    {
        return $this->hasMany(Hotspot::class);
    }
}
