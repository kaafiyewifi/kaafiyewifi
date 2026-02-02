<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouterService extends Model
{
    protected $table = 'router_services';

    protected $fillable = [
        'router_id',
        'pppoe_enabled',
        'hotspot_enabled',
        'ethernet_ports',
        'anti_sharing',
    ];

    protected $casts = [
        'pppoe_enabled' => 'boolean',
        'hotspot_enabled' => 'boolean',
        'anti_sharing' => 'boolean',
        // ✅ si aad u hesho array mar kasta:
        'ethernet_ports' => 'array',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class, 'router_id', 'id');
    }
}
