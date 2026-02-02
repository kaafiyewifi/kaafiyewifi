<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouterStatus extends Model
{
    protected $table = 'router_status'; // 🔴 MUHIIM

    protected $fillable = [
        'router_id',
        'status',
        'last_seen_at',
        'last_error'
    ];
}
