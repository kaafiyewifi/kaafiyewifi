<?php

// app/Services/Radius/VoucherService.php
namespace App\Services\Radius;

use App\Models\Radius\Radcheck;
use App\Models\Radius\Radreply;
use App\Models\Radius\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VoucherService
{
    public function create(string $planKey, ?\DateTimeInterface $expiresAt = null): Voucher
    {
        $plan = config("voucher_plans.$planKey");

        if (!$plan) {
            throw new \InvalidArgumentException("Invalid plan: $planKey");
        }

        return DB::connection('radius')->transaction(function () use ($planKey, $plan, $expiresAt) {
            $code = strtoupper(Str::random(10)); // voucher code user sees
            $username = 'VC-' . strtoupper(Str::random(6)); // radius username
            $password = strtoupper(Str::random(6));

            $voucher = Voucher::create([
                'code' => $code,
                'username' => $username,
                'password' => $password,
                'plan' => $plan['name'] ?? $planKey,
                'expires_at' => $expiresAt,
            ]);

            // Auth
            Radcheck::create([
                'username' => $username,
                'attribute' => 'Cleartext-Password',
                'op' => ':=',
                'value' => $password,
            ]);

            // Reply attributes (limits)
            if (!empty($plan['rate_limit'])) {
                Radreply::create([
                    'username' => $username,
                    'attribute' => 'Mikrotik-Rate-Limit',
                    'op' => ':=',
                    'value' => $plan['rate_limit'],
                ]);
            }

            if (!empty($plan['session_timeout'])) {
                Radreply::create([
                    'username' => $username,
                    'attribute' => 'Session-Timeout',
                    'op' => ':=',
                    'value' => (string)$plan['session_timeout'],
                ]);
            }

            return $voucher;
        });
    }

    /** @return array<int,Voucher> */
    public function bulkCreate(string $planKey, int $count): array
    {
        if ($count < 1 || $count > 5000) {
            throw new \InvalidArgumentException("Count must be between 1 and 5000");
        }

        $out = [];
        for ($i=0; $i<$count; $i++) {
            $out[] = $this->create($planKey);
        }
        return $out;
    }
}
