<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RouterProvision extends Model
{
    use HasFactory;

    protected $table = 'router_provisions';

    protected $fillable = [
        'router_id',
        'token_hash',
        'status',
        'script_version',
        'requested_at',
        'expires_at',
        'used_at',
        'started_at',
        'finished_at',
        'error_code',
        'error_message',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'expires_at'   => 'datetime',
        'used_at'      => 'datetime',
        'started_at'   => 'datetime',
        'finished_at'  => 'datetime',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    /* =====================
     | Helper Methods
     ===================== */

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }
}
