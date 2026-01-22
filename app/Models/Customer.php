<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'phone',
        'username',
        'password',
        'location_id',
        'status',
        'is_active',
    ];

    protected static function booted()
    {
        static::creating(function ($customer) {
            // username = phone
            if (empty($customer->username)) {
                $customer->username = $customer->phone;
            }

            // default password = 123456
            if (empty($customer->password)) {
                $customer->password = Hash::make('123456');
            }

            // default active
            if (is_null($customer->is_active)) {
                $customer->is_active = true;
            }

            // optional: keep status in sync
            if (empty($customer->status)) {
                $customer->status = 'active';
            }
        });
    }
}