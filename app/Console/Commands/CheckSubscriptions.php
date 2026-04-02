<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerSubscription;
use App\Services\Radius\RadiusUserService;
use App\Services\Radius\RadiusCoaService;
use App\Services\Radius\RadiusSessionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckSubscriptions extends Command
{
    /**
     * Command name
     */
    protected $signature = 'subscriptions:check';

    /**
     * Command description
     */
    protected $description = 'Check and expire subscriptions and disable router users';

    private function getActiveUserIp(string $username): ?string
    {
        return DB::connection('radius')
            ->table('radacct')
            ->where('username', $username)
            ->whereNull('acctstoptime')
            ->orderByDesc('radacctid')
            ->value('framedipaddress');
    }

    /**
     * Execute the console command
     */
    public function handle(): int
    {
        $now = Carbon::now();

        $radiusUserService = app(RadiusUserService::class);
        $radiusCoaService = app(RadiusCoaService::class);
        $radiusSessionService = app(RadiusSessionService::class);

        $expiredSubs = CustomerSubscription::with('customer')
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->get();

        if ($expiredSubs->isEmpty()) {
            $this->info('✅ No subscriptions to expire');
            return Command::SUCCESS;
        }

        foreach ($expiredSubs as $sub) {
            $customer = $sub->customer;
            $username = $customer?->username;

            if ($username) {
                try {
                    $radiusUserService->setUserInactive($username);
                    $radiusUserService->clearUserSpeed($username);
                    $radiusUserService->setUserDeviceLimit($username, 1);

                    $ip = $this->getActiveUserIp($username);

                    Log::info('EXPIRE DISCONNECT', [
                        'subscription_id' => $sub->id,
                        'customer_id' => $customer?->id,
                        'username' => $username,
                        'ip' => $ip,
                    ]);

                    if ($ip) {
                        $radiusCoaService->disconnect($username, $ip);
                    } else {
                        $radiusCoaService->disconnect($username);
                    }

                    $radiusSessionService->enforceDeviceLimit($username, 1);

                    DB::connection('radius')
                        ->table('radacct')
                        ->where('username', $username)
                        ->whereNull('acctstoptime')
                        ->update([
                            'acctstoptime' => now(),
                            'acctterminatecause' => 'Admin-Reset',
                        ]);

                    $this->info("📡 Router user {$username} disabled");
                } catch (\Throwable $e) {
                    Log::error('Subscription expire disconnect failed', [
                        'subscription_id' => $sub->id,
                        'customer_id' => $customer?->id,
                        'username' => $username,
                        'error' => $e->getMessage(),
                    ]);

                    $this->error("❌ Router disable failed for {$username}: {$e->getMessage()}");
                }
            }

            $sub->update([
                'status' => 'expired',
            ]);

            $this->info("⛔ Subscription ID {$sub->id} expired");
        }

        $this->info('🎯 Subscription check completed successfully');

        return Command::SUCCESS;
    }
}