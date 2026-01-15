<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hotspot extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'title',
        'nas_type',
        'physical_address',
        'ssid',
        'download_speed',
        'upload_speed',
        'speed_unit',

        // Router
        'router_id',
        'router_ip',
        'api_port',
        'api_user',
        'api_pass',

        // WireGuard
        'wg_private_key',
        'wg_public_key',

        // Other
        'radius_secret',
        'status'
    ];

    public function location(){
        return $this->belongsTo(Location::class);
    }
}
