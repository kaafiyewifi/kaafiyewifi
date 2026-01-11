<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /**
     * Fields allowed for mass assignment
     */
    protected $fillable = [
        // OLD (Phase 2 / legacy)
        'user_id',
        'reference',
        'amount',
        'method',
        'status',

        // NEW (Phase 3)
        'customer_id',
        'subscription_id',
        'paid_at',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'decimal:2',
    ];

    /* =========================
     |  RELATIONSHIPS
     |=========================*/

    /**
     * Admin / system user who recorded payment
     * (Backward compatible)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Customer who made the payment (Phase 3)
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Subscription linked to this payment
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /* =========================
     |  HELPERS / SCOPES
     |=========================*/

    /**
     * Scope: only paid payments
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope: payments for current month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }
}
