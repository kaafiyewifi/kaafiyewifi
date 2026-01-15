<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Services\MikroTikService;
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

    /**
     * Execute the console command
     */
    public function handle(): int
    {
        $now = Carbon::now();

        $mikrotik = app(MikroTikService::class);

        $expiredSubs = Subscription::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->get();

        if ($expiredSubs->isEmpty()) {
            $this->info('✅ No subscriptions to expire');
            return Command::SUCCESS;
        }

        foreach ($expiredSubs as $sub) {

            // Disable router user
            if ($sub->router_username) {
                try {
                    $mikrotik->disableUser($sub->router_username);
                    $this->info("📡 Router user {$sub->router_username} disabled");
                } catch (\Exception $e) {
                    $this->error("❌ Router disable failed for {$sub->router_username}");
                }
            }

            // Update DB status
            $sub->update([
                'status' => 'expired',
            ]);

            $this->info("⛔ Subscription ID {$sub->id} expired");
        }

        $this->info('🎯 Subscription check completed successfully');

        return Command::SUCCESS;
    }
}
