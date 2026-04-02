<?php

declare(strict_types=1);

namespace App\Services\Routers;

use App\Models\Router;
use App\Models\RouterProvision;
use Illuminate\Support\Carbon;

class ProvisionTokenService
{
    /**
     * Create one-time provisioning token
     * @return array{token:string, provision:RouterProvision}
     */
    public function create(Router $router, string $scriptVersion = 'v1', int $minutes = 20): array
    {
        if (empty($router->radius_secret)) {
            $router->radius_secret = bin2hex(random_bytes(16));
            $router->save();
        }

        $plain = $this->makePlainToken();

        $row = new RouterProvision();
        $row->router_id      = $router->id;
        $row->token_hash     = $this->hashToken($plain);
        $row->status         = 'generated';
        $row->script_version = $scriptVersion;

        // Track request + expiry
        $row->requested_at = now();
        $row->expires_at   = now()->addMinutes($minutes);
        $row->used_at      = null;

        $row->save();

        return ['token' => $plain, 'provision' => $row];
    }

    /**
     * Validate token and return [provisionRow, router] or [null, null]
     *
     * IMPORTANT:
     * - token_hash is sha256(token)
     * - allow multiple "in-progress" statuses (generated/served/started)
     *
     * @return array{0:RouterProvision|null,1:Router|null}
     */
    public function findValidTokenAndRouter(string $plainToken): array
    {
        $hash = $this->hashToken($plainToken);

        /** @var RouterProvision|null $row */
        $row = RouterProvision::where('token_hash', $hash)->orderByDesc('id')->first();
        if (!$row) {
            return [null, null];
        }

        // one-time
        if ($row->used_at !== null) {
            return [null, null];
        }

        // expiry
        if ($row->expires_at !== null && now()->greaterThanOrEqualTo($row->expires_at)) {
            return [null, null];
        }

        // ✅ Allow in-progress states (Centipid style)
        // generated: token created
        // served: script served to router
        // started: router started applying config
        $allowed = ['generated', 'served', 'started'];
        if (!in_array($row->status, $allowed, true)) {
            return [null, null];
        }

        $router = Router::withoutGlobalScopes()->find($row->router_id);
        if (!$router) {
            return [null, null];
        }

        return [$row, $router];
    }

    /**
     * Called when provisioning script is served (GET /provision/{token})
     * Do NOT invalidate token.
     */
    public function markServed(RouterProvision $row): void
    {
        if ($row->status === 'generated') {
            $row->status = 'served';
        }

        // keep tracking
        if ($row->requested_at === null) {
            $row->requested_at = now();
        }

        // started_at = first time we actually began provisioning
        if ($row->started_at === null) {
            $row->started_at = now();
        }

        $row->save();
    }

    /**
     * Called when callback received successfully.
     * This consumes the token.
     */
    public function markUsed(RouterProvision $row): void
    {
        $row->used_at     = now();
        $row->finished_at = now();
        $row->status      = 'completed'; // ✅ better than "used" for UI/reporting
        $row->save();
    }

    /**
     * If provisioning fails, call this.
     */
    public function markFailed(RouterProvision $row, string $code, string $message): void
    {
        $row->finished_at   = now();
        $row->status        = 'failed';
        $row->error_code    = $code;
        $row->error_message = $message;
        $row->save();
    }

    private function makePlainToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    private function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}