<?php

namespace App\Services\Radius;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StaleSessionService
{
    public function clearCustomerSessions(Customer $customer): array
    {
        $username = trim((string) ($customer->username ?? ''));

        if ($username === '') {
            return [
                'ok' => false,
                'message' => 'Customer username not found.',
                'username' => null,
                'open_sessions' => 0,
                'closed_sessions' => 0,
            ];
        }

        $openSessions = DB::connection('radius')
            ->table('radacct')
            ->where('username', $username)
            ->whereNull('acctstoptime')
            ->orderByDesc('radacctid')
            ->get();

        $openCount = $openSessions->count();
        $closedCount = 0;

        foreach ($openSessions as $session) {
            $ip = $session->framedipaddress ?: null;

            try {
                app(RadiusCoaService::class)->disconnect($username, $ip);
            } catch (\Throwable $e) {
                Log::warning('Stale session disconnect failed', [
                    'username' => $username,
                    'ip' => $ip,
                    'radacctid' => $session->radacctid ?? null,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::connection('radius')
                ->table('radacct')
                ->where('radacctid', $session->radacctid)
                ->update([
                    'acctstoptime' => now(),
                    'acctterminatecause' => 'Admin-Reset',
                ]);

            $closedCount++;
        }

        Log::info('Customer stale sessions cleared', [
            'customer_id' => $customer->id,
            'username' => $username,
            'open_sessions' => $openCount,
            'closed_sessions' => $closedCount,
        ]);

        return [
            'ok' => true,
            'message' => $closedCount > 0
                ? "Cleared {$closedCount} stale session(s) for {$username}."
                : "No open stale sessions found for {$username}.",
            'username' => $username,
            'open_sessions' => $openCount,
            'closed_sessions' => $closedCount,
        ];
    }
}