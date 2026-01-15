<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;

class AutoRenewSubscriptions extends Command
{
    protected $signature = 'subscriptions:auto-renew';
    protected $description = 'Auto renew active subscriptions';

    public function handle()
    {
        $now = Carbon::now();

        // 1️⃣ Auto-renew subscriptions
        $renewables = Subscription::where('auto_renew', true)
            ->where('status', 'active')
            ->where('expires_at', '<=', $now)
            ->with('plan')
            ->get();

        foreach ($renewables as $sub) {
            $plan = $sub->plan;

            // Duration logic (days or hours)
            if ($plan->duration_type === 'hours') {
                $newExpiry = $now->copy()->addHours($plan->base_duration);
            } else {
                $newExpiry = $now->copy()->addDays($plan->base_duration);
            }

            $sub->update([
                'starts_at'  => $now,
                'expires_at' => $newExpiry,
                'price'      => $plan->price,
            ]);

            $this->info("Subscription #{$sub->id} renewed");
        }

        // 2️⃣ Expire non-auto-renew subscriptions
        Subscription::where('auto_renew', false)
            ->where('status', 'active')
            ->where('expires_at', '<=', $now)
            ->update([
                'status' => 'expired'
            ]);

        $this->info('Auto-renew process completed');
    }
}
