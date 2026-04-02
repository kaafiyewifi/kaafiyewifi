<?php

namespace App\Services\Radius;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class RadiusUserService
{
    public function createOrUpdateUser(string $username, string $password): void
    {
        DB::connection('radius')
            ->table('radcheck')
            ->where('username', $username)
            ->whereIn('attribute', ['Cleartext-Password'])
            ->delete();

        DB::connection('radius')
            ->table('radcheck')
            ->insert([
                'username'  => $username,
                'attribute' => 'Cleartext-Password',
                'op'        => ':=',
                'value'     => $password,
            ]);

        DB::connection('radius')
            ->table('radcheck')
            ->where('username', $username)
            ->where('attribute', 'Auth-Type')
            ->delete();
    }

    public function setUserActive(string $username): void
    {
        DB::connection('radius')
            ->table('radcheck')
            ->where('username', $username)
            ->where('attribute', 'Auth-Type')
            ->delete();
    }

    public function setUserInactive(string $username): void
    {
        DB::connection('radius')
            ->table('radcheck')
            ->updateOrInsert(
                [
                    'username'  => $username,
                    'attribute' => 'Auth-Type',
                ],
                [
                    'op'    => ':=',
                    'value' => 'Reject',
                ]
            );
    }

    // ✅ NEW: DEVICE LIMIT (Simultaneous-Use)
    public function setUserDeviceLimit(string $username, int $limit): void
    {
        $limit = max(1, $limit);

        DB::connection('radius')
            ->table('radcheck')
            ->where('username', $username)
            ->where('attribute', 'Simultaneous-Use')
            ->delete();

        DB::connection('radius')
            ->table('radcheck')
            ->insert([
                'username'  => $username,
                'attribute' => 'Simultaneous-Use',
                'op'        => ':=',
                'value'     => (string) $limit,
            ]);
    }

    public function setUserSpeed(
        string $username,
        int|float $downloadSpeed,
        string $downloadUnit = 'Mbps',
        ?int $uploadSpeed = null,
        ?string $uploadUnit = null
    ): void {
        $uploadSpeed = $uploadSpeed ?? (int) $downloadSpeed;
        $uploadUnit  = $uploadUnit ?? $downloadUnit;

        $rateLimit = $this->formatMikrotikRateLimit(
            $uploadSpeed,
            $uploadUnit,
            $downloadSpeed,
            $downloadUnit
        );

        DB::connection('radius')
            ->table('radreply')
            ->where('username', $username)
            ->where('attribute', 'Mikrotik-Rate-Limit')
            ->delete();

        DB::connection('radius')
            ->table('radreply')
            ->insert([
                'username'  => $username,
                'attribute' => 'Mikrotik-Rate-Limit',
                'op'        => ':=',
                'value'     => $rateLimit,
            ]);
    }

    public function clearUserSpeed(string $username): void
    {
        DB::connection('radius')
            ->table('radreply')
            ->where('username', $username)
            ->where('attribute', 'Mikrotik-Rate-Limit')
            ->delete();
    }

    public function deleteUser(string $username): void
    {
        DB::connection('radius')
            ->table('radcheck')
            ->where('username', $username)
            ->delete();

        DB::connection('radius')
            ->table('radreply')
            ->where('username', $username)
            ->delete();

        DB::connection('radius')
            ->table('radusergroup')
            ->where('username', $username)
            ->delete();
    }

    public function resolveEffectiveSpeed(Customer $customer): ?array
    {
        if (
            $customer->speed_override_enabled &&
            !is_null($customer->download_speed)
        ) {
            return [
                'download_speed' => (int) $customer->download_speed,
                'download_unit'  => $customer->download_unit ?: 'Mbps',
                'upload_speed'   => !is_null($customer->upload_speed)
                    ? (int) $customer->upload_speed
                    : (int) $customer->download_speed,
                'upload_unit'    => $customer->upload_unit ?: ($customer->download_unit ?: 'Mbps'),
                'source'         => 'customer_override',
            ];
        }

        $subscriptions = $customer->relationLoaded('subscriptions')
            ? $customer->subscriptions
            : $customer->subscriptions()->with('subscription')->get();

        $activeSubscription = $subscriptions
            ->filter(function ($customerSubscription) {
                if (($customerSubscription->status ?? null) !== 'active') {
                    return false;
                }

                if (!empty($customerSubscription->expires_at) && now()->greaterThan($customerSubscription->expires_at)) {
                    return false;
                }

                return true;
            })
            ->sortByDesc('created_at')
            ->first();

        if (!$activeSubscription) {
            return null;
        }

        $plan = $activeSubscription->relationLoaded('subscription')
            ? $activeSubscription->subscription
            : $activeSubscription->subscription()->first();

        if (!$plan) {
            return null;
        }

        $downloadSpeed = data_get($plan, 'download_speed');
        $downloadUnit  = data_get($plan, 'download_unit', 'Mbps');
        $uploadSpeed   = data_get($plan, 'upload_speed');
        $uploadUnit    = data_get($plan, 'upload_unit', $downloadUnit);

        if (is_null($downloadSpeed)) {
            return null;
        }

        return [
            'download_speed' => (int) $downloadSpeed,
            'download_unit'  => $downloadUnit ?: 'Mbps',
            'upload_speed'   => !is_null($uploadSpeed) ? (int) $uploadSpeed : (int) $downloadSpeed,
            'upload_unit'    => $uploadUnit ?: ($downloadUnit ?: 'Mbps'),
            'source'         => 'subscription',
        ];
    }

    protected function formatMikrotikRateLimit(
        int|float $uploadSpeed,
        string $uploadUnit,
        int|float $downloadSpeed,
        string $downloadUnit
    ): string {
        $uploadbps = $this->toBitsPerSecond($uploadSpeed, $uploadUnit);
        $downloadbps = $this->toBitsPerSecond($downloadSpeed, $downloadUnit);

        return $uploadbps . '/' . $downloadbps;
    }

    protected function toBitsPerSecond(int|float $speed, string $unit): int
    {
        $unit = strtolower(trim($unit));

        return match ($unit) {
            'kbps' => (int) round($speed * 1000),
            'mbps' => (int) round($speed * 1000 * 1000),
            'gbps' => (int) round($speed * 1000 * 1000 * 1000),
            default => (int) round($speed * 1000 * 1000),
        };
    }
}