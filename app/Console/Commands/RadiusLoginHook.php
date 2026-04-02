<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\Radius\RadiusSessionService;
use App\Services\Radius\RadiusUserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class RadiusLoginHook extends Command
{
    protected $signature = 'radius:login-hook
                            {username : RADIUS username}
                            {ip? : Framed-IP-Address}';

    protected $description = 'Real-time FreeRADIUS login hook for customer status, subscription, and device limit enforcement';

    public function handle(): int
    {
        $username = trim((string) $this->argument('username'));
        $ip = trim((string) ($this->argument('ip') ?? ''));

        Log::info('RadiusLoginHook START', [
            'username' => $username,
            'ip' => $ip !== '' ? $ip : null,
        ]);

        if ($username === '') {
            Log::warning('RadiusLoginHook called without username');
            return self::SUCCESS;
        }

        try {
            /** @var Customer|null $customer */
            $customer = Customer::query()
                ->with(['subscriptions' => function ($query) {
                    $query->orderByDesc('created_at');
                }])
                ->where('username', $username)
                ->first();

            if (!$customer) {
                Log::warning('RadiusLoginHook customer not found', [
                    'username' => $username,
                    'ip' => $ip !== '' ? $ip : null,
                ]);

                Log::info('RadiusLoginHook END', ['username' => $username]);

                return self::SUCCESS;
            }

            $radiusUserService = app(RadiusUserService::class);
            $radiusSessionService = app(RadiusSessionService::class);

            $activeSubscription = $customer->subscriptions
                ->first(function ($sub) {
                    if (($sub->status ?? null) !== 'active') {
                        return false;
                    }

                    if (!empty($sub->expires_at) && now()->greaterThan($sub->expires_at)) {
                        return false;
                    }

                    return true;
                });

            $customerActive = ($customer->status === 'active');
            $subscriptionActive = ($activeSubscription !== null);

            if (!$customerActive || !$subscriptionActive) {
                $radiusUserService->setUserInactive($username);
                $radiusUserService->clearUserSpeed($username);

                Log::info('RadiusLoginHook blocked customer login', [
                    'customer_id' => $customer->id,
                    'username' => $username,
                    'customer_status' => $customer->status,
                    'subscription_active' => $subscriptionActive,
                    'ip' => $ip !== '' ? $ip : null,
                ]);

                Log::info('RadiusLoginHook END', ['username' => $username]);

                return self::SUCCESS;
            }

            // IMPORTANT:
            // Ensure any old Auth-Type := Reject is removed for active customers
            // before device/session logic is applied.
            $radiusUserService->setUserActive($username);

            $deviceLimit = max(1, (int) ($customer->device_limit ?? 1));
            $radiusUserService->setUserDeviceLimit($username, $deviceLimit);

            // Clean up stale sessions first so Simultaneous-Use doesn't falsely reject.
            try {
                $radiusSessionService->clearStaleSessions($username);
            } catch (Throwable $e) {
                Log::warning('RadiusLoginHook stale session cleanup failed', [
                    'customer_id' => $customer->id,
                    'username' => $username,
                    'error' => $e->getMessage(),
                ]);
            }

            $radiusSessionService->enforceDeviceLimit($username, $deviceLimit);

            Log::info('RadiusLoginHook processed successfully', [
                'customer_id' => $customer->id,
                'username' => $username,
                'ip' => $ip !== '' ? $ip : null,
                'device_limit' => $deviceLimit,
                'subscription_id' => $activeSubscription->id ?? null,
            ]);

            Log::info('RadiusLoginHook END', [
                'username' => $username,
            ]);

            return self::SUCCESS;
        } catch (Throwable $e) {
            Log::error('RadiusLoginHook FAILED', [
                'username' => $username,
                'ip' => $ip !== '' ? $ip : null,
                'error' => $e->getMessage(),
            ]);

            return self::SUCCESS;
        }
    }
}