<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BandwidthProfile extends Model
{
    protected $fillable = [
        'name','download_speed','upload_speed','unit'
    ];
}


