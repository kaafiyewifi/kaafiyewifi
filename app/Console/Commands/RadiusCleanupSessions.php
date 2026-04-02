<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\Radius\RadiusSessionService;
use Illuminate\Console\Command;

class RadiusCleanupSessions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'radius:cleanup-sessions';

    /**
     * The console command description.
     */
    protected $description = 'Clean up active RADIUS sessions that exceed customer device limits';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking radius sessions against device limits...');

        $customers = Customer::query()
            ->whereNotNull('username')
            ->where('username', '!=', '')
            ->get(['id', 'username', 'device_limit']);

        $service = app(RadiusSessionService::class);

        foreach ($customers as $customer) {
            $limit = max(1, (int) ($customer->device_limit ?? 1));

            try {
                $service->enforceDeviceLimit($customer->username, $limit);

                $this->info("Checked {$customer->username} (limit: {$limit})");
            } catch (\Throwable $e) {
                $this->error("Failed {$customer->username}: {$e->getMessage()}");
            }
        }

        $this->info('Radius session cleanup completed.');

        return self::SUCCESS;
    }
}