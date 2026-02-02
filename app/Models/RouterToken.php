<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouterToken extends Model
{
    protected $fillable = [
        'router_id',
        'token',
        'expires_at',
        'is_used',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}
