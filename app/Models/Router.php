<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    // ✅ Explicit table (safe haddii magac kale dhacdo)
    protected $table = 'routers';

    protected $fillable = [
        'name','identity','mgmt_ip','public_ip','api_port','api_user','api_pass_enc',
        'status','last_seen_at','last_error','location_id',
        'radius_enabled','radius_server_ip','radius_secret_enc',
    ];

    protected $casts = [
        'radius_enabled' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function service()
    {
        return $this->hasOne(RouterService::class);
    }

    public function logs()
    {
        return $this->hasMany(RouterLog::class);
    }

    public function provisionTokens()
    {
        return $this->hasMany(RouterProvisionToken::class);
    }

    public function statusChecks()
    {
        return $this->hasMany(RouterStatusCheck::class);
    }
}
