<?php

// app/Models/Radius/Voucher.php
namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $connection = 'radius';
    protected $table = 'vouchers';

    protected $fillable = [
        'code','username','password','plan','max_time','max_data','is_used','used_at','expires_at'
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
