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
    protected $signature = 'subscriptions:check';

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

            DB::beginTransaction();

            try {
                $customer = $sub->customer;

                if (!$customer) {
                    $sub->update(['status' => 'expired']);
                    DB::commit();
                    continue;
                }

                $username = $customer->username;

                if ($username) {

                    $ip = $this->getActiveUserIp($username);

                    Log::info('EXPIRE START', [
                        'subscription_id' => $sub->id,
                        'customer_id' => $customer->id,
                        'username' => $username,
                        'ip' => $ip,
                    ]);

                    // 🔥 Disable user
                    $radiusUserService->setUserInactive($username);

                    // 🔥 Clear speed
                    $radiusUserService->clearUserSpeed($username);

                    // 🔥 Reset device limit
                    $radiusUserService->setUserDeviceLimit($username, 1);

                    // 🔥 Disconnect if online
                    if ($ip) {
                        $radiusCoaService->disconnect($username, $ip);
                    } else {
                        $radiusCoaService->disconnect($username);
                    }

                    // 🔥 Enforce device limit
                    $radiusSessionService->enforceDeviceLimit($username, 1);

                    // 🔥 Force close sessions in DB
                    DB::connection('radius')
                        ->table('radacct')
                        ->where('username', $username)
                        ->whereNull('acctstoptime')
                        ->update([
                            'acctstoptime' => now(),
                            'acctterminatecause' => 'Admin-Reset',
                        ]);

                    $this->info("📡 Router user {$username} disabled");
                }

                // 🔥 Mark expired
                $sub->update([
                    'status' => 'expired',
                ]);

                DB::commit();

                $this->info("⛔ Subscription ID {$sub->id} expired");

            } catch (\Throwable $e) {

                DB::rollBack();

                Log::error('Subscription expire FAILED', [
                    'subscription_id' => $sub->id,
                    'customer_id' => $sub->customer?->id,
                    'username' => $sub->customer?->username,
                    'error' => $e->getMessage(),
                ]);

                $this->error("❌ Failed for sub {$sub->id}: {$e->getMessage()}");
            }
        }

        $this->info('🎯 Subscription check completed successfully');

        return Command::SUCCESS;
    }
}