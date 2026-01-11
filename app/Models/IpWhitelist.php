<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpWhitelist extends Model
{
    protected $fillable = [
        'role_name',
        'ip_address',
    ];
}
