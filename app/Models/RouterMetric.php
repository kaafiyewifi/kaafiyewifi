<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RouterMetric extends Model
{
    use HasFactory;

    protected $table = 'router_metrics';

    protected $fillable = [
        'router_id',
        'cpu_load',
        'free_memory',
        'total_memory',
        'free_hdd_space',
        'total_hdd_space',
        'uptime',
        'version',
        'board_name',
        'architecture_name',
        'collected_at',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}
