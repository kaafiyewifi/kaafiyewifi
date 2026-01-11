<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Device extends Model
{
    use HasFactory;

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'customer_id',
        'device_name',
        'mac_address',
        'ip_address',
        'last_seen',
        'status',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'last_seen' => 'datetime',
    ];

    /* =========================
     |  RELATIONSHIPS
     |=========================*/

    /**
     * Customer who owns this device
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /* =========================
     |  SCOPES
     |=========================*/

    /**
     * Only active devices
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Only blocked devices
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    /* =========================
     |  HELPERS
     |=========================*/

    /**
     * Mark device as seen now
     */
    public function markSeen(): void
    {
        $this->update([
            'last_seen' => now(),
        ]);
    }

    /**
     * Block this device
     */
    public function block(): void
    {
        $this->update([
            'status' => 'blocked',
        ]);
    }

    /**
     * Unblock this device
     */
    public function unblock(): void
    {
        $this->update([
            'status' => 'active',
        ]);
    }
}
