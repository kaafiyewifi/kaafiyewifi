<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    /* ===========================
     * MASS ASSIGNABLE
     * =========================== */
    protected $fillable = [
        'location_id',
        'created_by',
        'name',
        'price',
        'base_days',
        'upload_speed',
        'upload_unit',
        'download_speed',
        'download_unit',
        'status',
        'description',
    ];

    /* ===========================
     * CASTS
     * =========================== */
    protected $casts = [
        'price'      => 'decimal:2',
        'base_days'  => 'integer',
    ];

    /* ===========================
     * RELATIONSHIPS
     * =========================== */
    public function customerSubscriptions()
    {
        return $this->hasMany(CustomerSubscription::class, 'subscription_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ===========================
     * SCOPES
     * =========================== */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /* ===========================
     * HELPERS
     * =========================== */
    public function calculatePriceForDays(int $days): float
    {
        $days = max(1, $days);
        $baseDays = max(1, (int) $this->base_days);

        return round(((float) $this->price / $baseDays) * $days, 2);
    }

    public function calculatePriceForHours(int $hours): float
    {
        $hours = max(1, $hours);
        $baseDays = max(1, (int) $this->base_days);
        $baseHours = $baseDays * 24;

        return round(((float) $this->price / $baseHours) * $hours, 2);
    }

    public function uploadLabel(): string
    {
        if (!$this->upload_speed) {
            return '—';
        }

        return $this->upload_speed . ' ' . ($this->upload_unit ?: 'Mbps');
    }

    public function downloadLabel(): string
    {
        if (!$this->download_speed) {
            return '—';
        }

        return $this->download_speed . ' ' . ($this->download_unit ?: 'Mbps');
    }
}