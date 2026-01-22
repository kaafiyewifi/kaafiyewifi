<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address',
        'status',
    ];

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function subscriptions()
    {
        // ✅ SAXITAAN
        return $this->hasMany(Subscription::class);
    }
}
