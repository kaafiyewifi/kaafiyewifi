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
    'router_username',
    'router_password',


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

    /**
     * Active subscriptions (status + time)
     */
    public function scopeActive($query)
    {
        return $query
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Expired subscriptions
     */
    public function scopeExpired($query)
    {
        return $query
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /* ===========================
     * STATUS HELPERS
     * =========================== */

    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->expires_at
            && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    /* ===========================
     * REMAINING TIME
     * =========================== */

    /**
     * Remaining days (integer)
     */
    public function daysRemaining(): int
    {
        if (!$this->expires_at) {
            return 0;
        }

        return now()->diffInDays($this->expires_at, false);
    }

    /**
     * Remaining hours (integer)
     */
    public function hoursRemaining(): int
    {
        if (!$this->expires_at) {
            return 0;
        }

        return now()->diffInHours($this->expires_at, false);
    }

    /**
     * Remaining time (human readable)
     * Example: "4 days", "3 hours", "Expired"
     */
    public function remainingLabel(): string
    {
        if (!$this->expires_at) {
            return '—';
        }

        if ($this->expires_at->isPast()) {
            return 'Expired';
        }

        $days  = $this->daysRemaining();
        $hours = $this->hoursRemaining();

        if ($days >= 1) {
            return $days . ' day' . ($days > 1 ? 's' : '');
        }

        if ($hours >= 1) {
            return $hours . ' hour' . ($hours > 1 ? 's' : '');
        }

        return 'Less than 1 hour';
    }

    /* ===========================
     * ACTION HELPERS
     * =========================== */

    /**
     * Extend by days
     */
    public function extendDays(int $days): void
    {
        if ($days <= 0 || !$this->expires_at) {
            return;
        }

        $this->update([
            'expires_at' => $this->expires_at->copy()->addDays($days),
        ]);
    }

    /**
     * Extend by hours
     */
    public function extendHours(int $hours): void
    {
        if ($hours <= 0 || !$this->expires_at) {
            return;
        }

        $this->update([
            'expires_at' => $this->expires_at->copy()->addHours($hours),
        ]);
    }

    /**
     * Pause subscription
     */
    public function pause(): void
    {
        if ($this->status !== 'paused') {
            $this->update(['status' => 'paused']);
        }
    }

    /**
     * Resume subscription
     */
    public function resume(): void
    {
        if ($this->status !== 'active') {
            $this->update(['status' => 'active']);
        }
    }

    /**
     * Cancel subscription permanently
     */
    public function cancel(): void
    {
        $this->update([
            'status'     => 'cancelled',
            'auto_renew' => false,
        ]);
    }
}
