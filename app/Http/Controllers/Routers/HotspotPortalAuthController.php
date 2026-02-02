<?php

declare(strict_types=1);

namespace App\Http\Controllers\Routers;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HotspotPortalAuthController extends Controller
{
    public function login(Request $request, Router $router): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['required', 'in:voucher,account'],

            'voucher' => ['nullable', 'string', 'max:40'],
            'username' => ['nullable', 'string', 'max:64'],
            'password' => ['nullable', 'string', 'max:64'],

            // MikroTik vars
            'link_login_only' => ['required', 'string'],
            'chap_id' => ['nullable', 'string'],
            'chap_challenge' => ['nullable', 'string'],

            // Optional info
            'dst' => ['nullable', 'string'],
            'mac' => ['nullable', 'string'],
            'ip'  => ['nullable', 'string'],
            'popup' => ['nullable', 'string'],
        ]);

        // 1) Determine credentials (username/password)
        [$username, $password] = $this->resolveCredentials($router, $data);

        // 2) Build MikroTik redirect URL (CHAP)
        $url = $this->buildMikrotikLoginUrl(
            linkLoginOnly: $data['link_login_only'],
            username: $username,
            password: $password,
            chapId: $data['chap_id'] ?? null,
            chapChallenge: $data['chap_challenge'] ?? null
        );

        return redirect()->away($url);
    }

    /**
     * @return array{0:string,1:string}
     */
    private function resolveCredentials(Router $router, array $data): array
    {
        if ($data['mode'] === 'voucher') {
            $code = strtoupper(trim((string) ($data['voucher'] ?? '')));
            if ($code === '') {
                abort(422, 'Voucher is required.');
            }

            // If you have tenant_id, keep it. If not, remove tenant filter.
            $q = Voucher::query()->where('code', $code);

            if (isset($router->tenant_id) && $router->tenant_id) {
                $q->where('tenant_id', $router->tenant_id);
            }

            $v = $q->first();

            if (!$v || ($v->status ?? 'active') !== 'active') {
                abort(422, 'Invalid voucher.');
            }

            if (!empty($v->expires_at) && $v->expires_at->isPast()) {
                $v->status = 'expired';
                $v->save();
                abort(422, 'Voucher expired.');
            }

            // Voucher mapping (simple): user=code pass=code
            // (FreeRADIUS later will enforce session limits via radreply)
            $v->first_used_at = $v->first_used_at ?? now();
            $v->last_used_at  = now();
            if (empty($v->router_id)) $v->router_id = $router->id;
            $v->save();

            return [$code, $code];
        }

        // Account mode
        $u = trim((string) ($data['username'] ?? ''));
        $p = trim((string) ($data['password'] ?? ''));

        if ($u === '' || $p === '') {
            abort(422, 'Username and password are required.');
        }

        return [$u, $p];
    }

    private function buildMikrotikLoginUrl(
        string $linkLoginOnly,
        string $username,
        string $password,
        ?string $chapId,
        ?string $chapChallenge
    ): string {
        // If CHAP values exist -> response=00{md5hex}
        if ($chapId !== null && $chapChallenge !== null && $chapId !== '' && $chapChallenge !== '') {
            $respHex = $this->chapResponseHex($chapId, $password, $chapChallenge);

            // RouterOS expects: response=00 + md5hex
            $params = [
                'username' => $username,
                'response' => '00' . $respHex,
            ];

            return $this->appendQuery($linkLoginOnly, $params);
        }

        // Fallback (PAP) - not used if http-chap enabled, but safe fallback
        $params = [
            'username' => $username,
            'password' => $password,
        ];

        return $this->appendQuery($linkLoginOnly, $params);
    }

    /**
     * CHAP response: md5( chap-id (1 byte) + password + chap-challenge (bytes) )
     * chap-challenge usually is HEX string from $(chap-challenge)
     */
    private function chapResponseHex(string $chapId, string $password, string $chapChallenge): string
    {
        $idByte = chr((int) $chapId);

        $cleanHex = preg_replace('/[^0-9a-fA-F]/', '', $chapChallenge) ?? '';
        $challengeBytes = hex2bin($cleanHex);

        if ($challengeBytes === false) {
            // fallback: treat as raw string
            $challengeBytes = $chapChallenge;
        }

        $raw = md5($idByte . $password . $challengeBytes, true);

        return bin2hex($raw);
    }

    private function appendQuery(string $url, array $params): string
    {
        $sep = str_contains($url, '?') ? '&' : '?';
        return $url . $sep . http_build_query($params);
    }
}
