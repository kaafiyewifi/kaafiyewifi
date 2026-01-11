<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [

        'name',
        'price',

        'download_speed',
        'download_unit',

        'upload_speed',
        'upload_unit',

        'data_type',
        'data_limit',
        'data_unit',

        'devices',
    ];

    /* ======================
     | ACCESSORS (OPTIONAL)
     |======================*/

    public function getSpeedTextAttribute()
    {
        return "↓ {$this->download_speed} {$this->download_unit} / ↑ {$this->upload_speed} {$this->upload_unit}";
    }

    public function getDataTextAttribute()
    {
        if ($this->data_type === 'unlimited') {
            return 'Unlimited';
        }

        return "{$this->data_limit} {$this->data_unit}";
    }
    public function subscriptions()
    {
    return $this->hasMany(CustomerSubscription::class);
    }

}
