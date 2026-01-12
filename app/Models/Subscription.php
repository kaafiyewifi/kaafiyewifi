<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    /* ===========================
     * MASS ASSIGNABLE
     * =========================== */
    protected $fillable = [
        'customer_id',
        'plan_id',
        'price',
        'starts_at',
        'expires_at',
        'status',
        'auto_renew',
    ];

    /* ===========================
     * CASTS
     * =========================== */
    protected $casts = [
        'starts_at'  => 'datetime',
        'expires_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    /* ===========================
     * RELATIONSHIPS
     * =========================== */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /* ===========================
     * SCOPES
     * =========================== */
    public function scopeActive($query)
    {
        return $query
            ->where('status', 'active')
            ->where('expires_at', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /* ===========================
     * HELPERS / LOGIC
     * =========================== */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function daysRemaining(): int
    {
        return now()->diffInDays($this->expires_at, false);
    }

    public function extendDays(int $days): void
    {
        $this->update([
            'expires_at' => Carbon::parse($this->expires_at)->addDays($days),
        ]);
    }

    public function pause(): void
    {
        $this->update(['status' => 'paused']);
    }

    public function resume(): void
    {
        $this->update(['status' => 'active']);
    }

    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'auto_renew' => false,
        ]);
    }
}
