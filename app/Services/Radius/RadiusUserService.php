<?php

namespace App\Services\Radius;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RadiusUserService
{
    public function createOrUpdateUser(string $username, string $password, string $serviceType = 'hotspot'): void
    {
        // CLEAN OLD PASSWORD
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

        // REMOVE REJECT IF EXISTS
        DB::connection('radius')
            ->table('radcheck')
            ->where('username', $username)
            ->where('attribute', 'Auth-Type')
            ->delete();

        $this->syncServiceAttributes($username, $serviceType);
    }

    public function setUserActive(string $username, string $serviceType = 'hotspot'): void
    {
        DB::connection('radius')
            ->table('radcheck')
            ->where('username', $username)
            ->where('attribute', 'Auth-Type')
            ->delete();

        $this->syncServiceAttributes($username, $serviceType);
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
        ?string $uploadUnit = null,
        string $serviceType = 'hotspot'
    ): void {
        $uploadSpeed = $uploadSpeed ?? (int) $downloadSpeed;
        $uploadUnit  = $uploadUnit ?? $downloadUnit;

        $rateLimit = $this->formatMikrotikRateLimit(
            $uploadSpeed,
            $uploadUnit,
            $downloadSpeed,
            $downloadUnit
        );

        // REMOVE OLD SPEED
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

        $this->syncServiceAttributes($username, $serviceType);
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
        DB::connection('radius')->table('radcheck')->where('username', $username)->delete();
        DB::connection('radius')->table('radreply')->where('username', $username)->delete();
        DB::connection('radius')->table('radusergroup')->where('username', $username)->delete();
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
            ->filter(fn ($cs) => ($cs->status ?? null) === 'active' &&
                (empty($cs->expires_at) || now()->lessThan($cs->expires_at)))
            ->sortByDesc('created_at')
            ->first();

        if (!$activeSubscription) return null;

        $plan = $activeSubscription->subscription ?? $activeSubscription->subscription()->first();
        if (!$plan) return null;

        if (is_null($plan->download_speed)) return null;

        return [
            'download_speed' => (int) $plan->download_speed,
            'download_unit'  => $plan->download_unit ?: 'Mbps',
            'upload_speed'   => $plan->upload_speed ?? $plan->download_speed,
            'upload_unit'    => $plan->upload_unit ?: $plan->download_unit,
            'source'         => 'subscription',
        ];
    }

    public function resolveServiceType(Customer $customer): string
    {
        if (
            Schema::hasColumn($customer->getTable(), 'service_type') &&
            in_array(strtolower((string) $customer->service_type), ['hotspot', 'pppoe'], true)
        ) {
            return strtolower((string) $customer->service_type);
        }

        return 'hotspot';
    }

    protected function syncServiceAttributes(string $username, string $serviceType = 'hotspot'): void
    {
        $serviceType = strtolower(trim($serviceType));

        // CLEAN OLD ATTRIBUTES (IMPORTANT FIX)
        DB::connection('radius')
            ->table('radreply')
            ->where('username', $username)
            ->whereIn('attribute', ['Service-Type', 'Framed-Protocol'])
            ->delete();

        if ($serviceType === 'pppoe') {
            DB::connection('radius')
                ->table('radreply')
                ->insert([
                    [
                        'username'  => $username,
                        'attribute' => 'Service-Type',
                        'op'        => ':=',
                        'value'     => 'Framed-User',
                    ],
                    [
                        'username'  => $username,
                        'attribute' => 'Framed-Protocol',
                        'op'        => ':=',
                        'value'     => 'PPP',
                    ],
                ]);
        }
    }

    protected function formatMikrotikRateLimit(
        int|float $uploadSpeed,
        string $uploadUnit,
        int|float $downloadSpeed,
        string $downloadUnit
    ): string {
        return $this->toBitsPerSecond($uploadSpeed, $uploadUnit)
            . '/' .
            $this->toBitsPerSecond($downloadSpeed, $downloadUnit);
    }

    protected function toBitsPerSecond(int|float $speed, string $unit): int
    {
        return match (strtolower(trim($unit))) {
            'kbps' => (int) round($speed * 1000),
            'mbps' => (int) round($speed * 1000000),
            'gbps' => (int) round($speed * 1000000000),
            default => (int) round($speed * 1000000),
        };
    }
}