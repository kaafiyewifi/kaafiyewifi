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
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
