<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSubscription extends Model
{
    protected $fillable = [
        'customer_id',
        'subscription_plan_id',
        'price',
        'duration_days',
        'starts_at',
        'expires_at',
        'status'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function plan(){
        return $this->belongsTo(SubscriptionPlan::class,'subscription_plan_id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class);
    }
}
