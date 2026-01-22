<?php

namespace App\Services\Provisioning;

use App\Models\Router;
use App\Models\RouterProvisionToken;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProvisionTokenService
{
    public function createForRouter(Router $router, int $expiresMinutes = 10): array
    {
        // raw token shown once to admin
        $raw = Str::random(48);

        // store hash only
        $hash = hash('sha256', $raw);

        $row = RouterProvisionToken::create([
            'router_id' => $router->id,
            'token_hash' => $hash,
            'expires_at' => Carbon::now()->addMinutes($expiresMinutes),
            'used_at' => null,
        ]);

        return [$raw, $row];
    }

    public function findValidByRawToken(string $rawToken): ?RouterProvisionToken
    {
        $hash = hash('sha256', $rawToken);

        return RouterProvisionToken::query()
            ->where('token_hash', $hash)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function markUsed(RouterProvisionToken $token): void
    {
        $token->forceFill(['used_at' => now()])->save();
    }
}
