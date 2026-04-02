<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerSubscription;
use App\Services\Radius\RadiusUserService;
use App\Services\Radius\RadiusCoaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';
    protected $description = 'Expire subscriptions + disconnect users';

    private function getActiveUserIp(string $username): ?string
    {
        return DB::connection('radius')
            ->table('radacct')
            ->where('username', $username)
            ->whereNull('acctstoptime')
            ->orderByDesc('radacctid')
            ->value('framedipaddress');
    }

    public function handle()
    {
        $this->info('Checking expired subscriptions...');

        $subs = CustomerSubscription::with('customer')
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($subs as $sub) {

            $username = $sub->customer->username;

            // update status
            $sub->update(['status' => 'expired']);

            // disable radius
            app(RadiusUserService::class)->setUserInactive($username);
            app(RadiusUserService::class)->clearUserSpeed($username);

            // disconnect
            $ip = $this->getActiveUserIp($username);

            app(RadiusCoaService::class)->disconnect($username, $ip);

            Log::info('EXPIRED + DISCONNECTED', [
                'user' => $username,
                'ip' => $ip,
            ]);

            $this->info("Expired: {$username}");
        }

        return 0;
    }
}