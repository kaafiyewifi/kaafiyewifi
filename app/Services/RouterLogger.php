<?php

namespace App\Services;

use App\Models\RouterLog;

class RouterLogger
{
    public static function info(int $routerId, string $message): void
    {
        RouterLog::create([
            'router_id' => $routerId,
            'level' => 'info',
            'message' => $message,
        ]);
    }

    public static function warning(int $routerId, string $message): void
    {
        RouterLog::create([
            'router_id' => $routerId,
            'level' => 'warning',
            'message' => $message,
        ]);
    }

    public static function error(int $routerId, string $message): void
    {
        RouterLog::create([
            'router_id' => $routerId,
            'level' => 'error',
            'message' => $message,
        ]);
    }
}
