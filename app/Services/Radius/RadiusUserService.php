<?php

namespace App\Services\Radius;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RadiusUserService
{
   public function createOrUpdateUser(string $username, string $password, string $serviceType = 'hotspot'): void
{
// REMOVE OLD PASSWORD
DB::connection('radius')
->table('radcheck')
->where('username', $username)
->where('attribute', 'Cleartext-Password')
->delete();


// CREATE OR UPDATE PASSWORD
DB::connection('radius')
    ->table('radcheck')
    ->updateOrInsert(
        [
            'username'  => $username,
            'attribute' => 'Cleartext-Password',
        ],
        [
            'op'    => ':=',
            'value' => $password,
        ]
    );

// REMOVE ANY OLD REJECT
DB::connection('radius')
    ->table('radcheck')
    ->where('username', $username)
    ->where('attribute', 'Auth-Type')
    ->delete();

$this->syncServiceAttributes(
    $username,
    $serviceType
);


}


public function setUserActive(string $username, string $serviceType = 'hotspot'): void
{
// REMOVE ANY REJECT RULES
DB::connection('radius')
->table('radcheck')
->where('username', $username)
->where('attribute', 'Auth-Type')
->delete();


// GET CUSTOMER
$customer = Customer::where('username', $username)->first();

if ($customer) {

$this->syncServiceAttributes(
$username,
$serviceType
);

$radiusPassword = $customer->radius_password;

if (empty($radiusPassword)) {

// No stored cleartext password to restore.
//
// This used to assign '123456' and persist it, which handed every
// such account the same well-known credential. It must not invent one.
//
// Leave radcheck exactly as it is rather than deleting below: a legacy
// account may already hold a working password here that simply was
// never mirrored into customers.radius_password, and wiping it would
// cut off a paying customer. Reactivation is still complete — the
// Auth-Type reject was already cleared and service attributes synced.
Log::warning('RADIUS password not restored: customers.radius_password is empty', [
    'username' => $username,
    'action'   => 'set a password via the customer password form to manage this account',
]);

return;

}


// REMOVE OLD PASSWORD
DB::connection('radius')
    ->table('radcheck')
    ->where('username', $username)
    ->where('attribute', 'Cleartext-Password')
    ->delete();

// RESTORE PASSWORD
DB::connection('radius')
->table('radcheck')
->updateOrInsert(
[
'username'  => $username,
'attribute' => 'Cleartext-Password',
],
[
'op'    => ':=',
'value' => $radiusPassword,
]
);



}

}

    /**
     * SAFE INACTIVE METHOD
     * DO NOT INSERT Auth-Type Reject
     */
 public function setUserInactive(string $username): void
{
// REMOVE ACTIVE HOTSPOT SESSIONS
DB::connection('radius')
->table('radacct')
->where('username', $username)
->whereNull('acctstoptime')
->update([
'acctstoptime' => now(),
]);


// REMOVE SPEED LIMITS
DB::connection('radius')
    ->table('radreply')
    ->where('username', $username)
    ->where('attribute', 'Mikrotik-Rate-Limit')
    ->delete();

// REMOVE SERVICE ATTRIBUTES
DB::connection('radius')
    ->table('radreply')
    ->where('username', $username)
    ->whereIn('attribute', [
        'Service-Type',
        'Framed-Protocol',
    ])
    ->delete();

// REMOVE PASSWORD TO BLOCK AUTHENTICATION
DB::connection('radius')
->table('radcheck')
->where('username', $username)
->where('attribute', 'Cleartext-Password')
->delete();

// REMOVE ANY AUTH-TYPE RULES
DB::connection('radius')
->table('radcheck')
->where('username', $username)
->where('attribute', 'Auth-Type')
->delete();

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

        // CLEAR OLD SPEED
        $this->clearUserSpeed($username);

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

        if (!$activeSubscription) {
            return null;
        }

        $plan = $activeSubscription->subscription ?? $activeSubscription->subscription()->first();

        if (!$plan || is_null($plan->download_speed)) {
            return null;
        }

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