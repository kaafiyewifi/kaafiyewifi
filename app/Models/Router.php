<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Router extends Model
{
    protected $table = 'routers';

    protected $fillable = [
        'name',

        // identity fields (support both schemas)
        'identity',
        'router_identity',

        // device info
        'model',
        'router_os',
        'public_ip',
        'mac_address',

        // statuses
        'status',
        'provisioning_status',

        // misc
        'tenant_id',
        'mgmt_host',
        'api_port',
        'use_tls',
        'notes',
        'uuid',
        'is_active',

        // timestamps/heartbeat fields
        'last_seen_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'use_tls'      => 'boolean',
        'api_port'     => 'integer',
        'last_seen_at' => 'datetime',
    ];

    /**
     * ✅ Normalized identity getter (DO NOT override identity column)
     * Use: $router->normalized_identity
     */
    public function getNormalizedIdentityAttribute(): ?string
    {
        $identity = $this->getAttributeFromArray('identity');
        if (is_string($identity) && trim($identity) !== '') {
            return trim($identity);
        }

        $routerIdentity = $this->getAttributeFromArray('router_identity');
        if (is_string($routerIdentity) && trim($routerIdentity) !== '') {
            return trim($routerIdentity);
        }

        return null;
    }

    /**
     * ✅ Set identity into correct column (identity OR router_identity)
     * Use: $router->setNormalizedIdentity("MikroTik");
     */
    public function setNormalizedIdentity(string $value): void
    {
        $value = trim($value);

        if ($this->routersHasColumn('identity')) {
            $this->attributes['identity'] = $value;
        } else {
            $this->attributes['router_identity'] = $value;
        }

        // Optional: keep name in sync if empty
        if ((empty($this->attributes['name']) || trim((string)$this->attributes['name']) === '') && $value !== '') {
            $this->attributes['name'] = $value;
        }
    }

    private function routersHasColumn(string $column): bool
    {
        try {
            return Schema::hasColumn($this->getTable(), $column);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * ✅ Router has MANY services (hotspot + pppoe etc.)
     */
    public function services(): HasMany
    {
        return $this->hasMany(RouterService::class, 'router_id', 'id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(RouterEvent::class, 'router_id', 'id');
    }

    public function provisions(): HasMany
    {
        return $this->hasMany(RouterProvision::class, 'router_id', 'id');
    }

    /**
     * ✅ Router has ONE credentials row
     */
    public function credential(): HasOne
    {
        return $this->hasOne(RouterCredential::class, 'router_id', 'id');
    }

    /**
     * Backward-compatible alias (if older code uses $router->credentials)
     */
    public function credentials(): HasOne
    {
        return $this->credential();
    }

    /**
     * ✅ All metrics (charts)
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(RouterMetric::class, 'router_id', 'id');
    }

    /**
     * ✅ Latest metric row for badges
     */
    public function latestMetric(): HasOne
    {
        return $this->hasOne(RouterMetric::class, 'router_id', 'id')
            ->latestOfMany('collected_at');
    }
}
