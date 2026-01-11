<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hotspot extends Model
{
    protected $fillable=[
        'location_id',
        'name',
        'ssid',
        'max_users',
        'download_speed',
        'upload_speed',
        'speed_unit'
    ];

    public function location(){
        return $this->belongsTo(Location::class);
    }
}
