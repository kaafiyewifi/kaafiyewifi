<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouterLog extends Model
{
    protected $table = 'router_logs';

    protected $fillable = [
        'router_id',
        'type',
        'success',
        'message',
        'meta',
    ];

    protected $casts = [
        'success' => 'boolean',
        'meta'    => 'array',
    ];
}
