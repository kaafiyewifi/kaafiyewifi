<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouterEvent extends Model
{
    protected $table = 'router_events';

    // table-kaaga wuxuu u egyahay created_at la keydiyo manual
    public $timestamps = false;

    protected $fillable = [
        'router_id',
        'type',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class, 'router_id', 'id');
    }
}
