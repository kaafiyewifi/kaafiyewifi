<?php

// app/Support/Sanitize.php
namespace App\Support;

class Sanitize
{
    public static function payload(array $data): array
    {
        $blocked = [
            'password', 'pass', 'secret', 'private_key',
            'radius_secret', 'api_pass', 'ROUTER_API_PASS',
        ];

        array_walk_recursive($data, function (&$value, $key) use ($blocked) {
            if (in_array(strtolower((string)$key), $blocked, true)) {
                $value = '***REDACTED***';
            }
        });

        return $data;
    }
}
