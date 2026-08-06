<?php

namespace App\Observers;

use App\Models\Customer;
use App\Services\Radius\RadiusUserService;
use Illuminate\Support\Facades\Log;

class CustomerObserver
{
    public function created(Customer $customer): void
    {
        $this->sync($customer);
    }

    public function updated(Customer $customer): void
    {
        $this->sync($customer);
    }

    public function deleted(Customer $customer): void
    {
        try {
            app(RadiusUserService::class)->deleteUser($customer->username);

            Log::info('CustomerObserver deleted user from RADIUS', [
                'customer_id' => $customer->id,
                'username' => $customer->username,
            ]);
        } catch (\Throwable $e) {
            Log::error('CustomerObserver delete failed', [
                'customer_id' => $customer->id,
                'username' => $customer->username,
                'error' => $e->getMessage(),
            ]);
        }
    }

  private function sync(Customer $customer): void
{
try {

    $radius = app(RadiusUserService::class);

    // =====================================================
    // IMPORTANT:
    // DO NOT SYNC PASSWORD HERE
    //
    // Customer password in DB is hashed (bcrypt).
    // FreeRADIUS PAP requires plaintext passwords.
    //
    // Password sync must ONLY happen in controllers/services
    // that still have access to the raw password.
    // =====================================================

    // STATUS CONTROL
    if ($customer->status === 'active') {

        $radius->setUserActive(
            $customer->username
        );

    } else {

        $radius->setUserInactive(
            $customer->username
        );
    }

    // DEVICE LIMIT
    $radius->setUserDeviceLimit(
        $customer->username,
        $customer->device_limit ?? 1
    );

    // SPEED CONTROL
    if ($customer->hasSpeedOverride()) {

        $radius->setUserSpeed(
            $customer->username,
            $customer->download_speed,
            $customer->download_unit ?? 'Mbps',
            $customer->upload_speed,
            $customer->upload_unit
        );

    } else {

        $radius->clearUserSpeed(
            $customer->username
        );
    }

    Log::info(
        'CustomerObserver synced successfully',
        [
            'customer_id'   => $customer->id,
            'username'      => $customer->username,
            'status'        => $customer->status,
            'device_limit'  => $customer->device_limit,
            'speed_override'=> $customer->speed_override_enabled,
        ]
    );

} catch (\Throwable $e) {

    Log::error(
        'CustomerObserver sync failed',
        [
            'customer_id' => $customer->id,
            'username'    => $customer->username,
            'error'       => $e->getMessage(),
        ]
    );
}


}
}