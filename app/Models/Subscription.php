<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'customer_id',
        'plan_id',
        'start_date',
        'end_date',
        'status',
        'auto_renew',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'auto_renew' => 'boolean',
    ];

    /**
     * ===========================
     * RELATIONSHIPS
     * ===========================
     */

    /**
     * Subscription belongs to a customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Subscription has many payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * ===========================
     * SCOPES
     * ===========================
     */

    /**
     * Only active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Only expired subscriptions
     */
    public function scopeExpired($query)
    {
        return $query->whereDate('end_date', '<', now());
    }

    /**
     * ===========================
     * HELPERS / LOGIC
     * ===========================
     */

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->end_date >= now();
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->end_date < now();
    }

    /**
     * Days remaining until expiry
     */
    public function daysRemaining(): int
    {
        return now()->diffInDays($this->end_date, false);
    }

    /**
     * Extend subscription by days
     */
    public function extendDays(int $days): void
    {
        $this->end_date = Carbon::parse($this->end_date)->addDays($days);
        $this->save();
    }

    /**
     * Pause subscription
     */
    public function pause(): void
    {
        $this->update([
            'status' => 'paused',
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'auto_renew' => false,
        ]);
    }

    /**
     * Resume subscription
     */
    public function resume(): void
    {
        $this->update([
            'status' => 'active',
        ]);
    }
}
