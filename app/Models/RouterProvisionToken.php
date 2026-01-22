<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouterProvisionToken extends Model
{
    protected $fillable = [
        'router_id', 'token_hash', 'expires_at', 'used_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}
