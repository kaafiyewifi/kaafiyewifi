<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
   protected $fillable = [
    'user_id',
    'action',
    'target_type',
    'target_id',
    'description',
    'ip_address',
    'properties',
];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}