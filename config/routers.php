<?php

declare(strict_types=1);

// config/routers.php

return [

    /*
    |--------------------------------------------------------------------------
    | Server IP (VPS)
    |--------------------------------------------------------------------------
    | Uses PROVISION_SERVER_IP if set, otherwise falls back to VPS_PUBLIC_IP
    */
    'server_ip' => env('PROVISION_SERVER_IP', env('VPS_PUBLIC_IP', '62.171.140.146')),
    // config/routers.php
    'provision_allow_cidrs' => array_filter(explode(',', env('PROVISION_ALLOW_CIDRS', ''))),

    /*
    |--------------------------------------------------------------------------
    | RouterOS API
    |--------------------------------------------------------------------------
    | Supports ROUTER_API_PORT, otherwise MIKROTIK_API_PORT
    */
    'api_port' => (int) env('ROUTER_API_PORT', (int) env('MIKROTIK_API_PORT', 8728)),

    'bootstrap_api_user' => env('ROUTER_BOOTSTRAP_API_USER', 'system_api'),

    // Optional static API user/pass (if you ever want to use them).
    // No fallback for the password on purpose: a default here would be a
    // published credential, and callers should fail loudly on a missing
    // ROUTER_API_PASS rather than silently authenticate with a known value.
    'api_user' => env('ROUTER_API_USER', 'kaafiye'),
    'api_pass' => env('ROUTER_API_PASS', ''),

    /*
    |--------------------------------------------------------------------------
    | SNMP
    |--------------------------------------------------------------------------
    */
    'snmp_community' => env('ROUTER_SNMP_COMMUNITY', 'kaafiye-snmp'),

    /*
    |--------------------------------------------------------------------------
    | RADIUS
    |--------------------------------------------------------------------------
    | If ROUTER_RADIUS_IP exists use it, else use ROUTER_RADIUS_HOST.
    | Your .env uses ROUTER_RADIUS_HOST=10.50.0.1 (WireGuard), so that will work.
    */
    'radius_ip' => env('ROUTER_RADIUS_IP', ''),
    'radius_host' => env('ROUTER_RADIUS_HOST', 'app.kaafiye.online'),
    'radius_secret' => env('ROUTER_RADIUS_SECRET', 'ChangeMeRadiusSecret!'),

    'radius_auth_port' => (int) env('RADIUS_AUTH_PORT', 1812),
    'radius_acct_port' => (int) env('RADIUS_ACCT_PORT', 1813),

    'effective_radius_address' => (static function (): string {
        $ip = (string) env('ROUTER_RADIUS_IP', '');
        return $ip !== '' ? $ip : (string) env('ROUTER_RADIUS_HOST', 'app.kaafiye.online');
    })(),

    /*
    |--------------------------------------------------------------------------
    | Timezone
    |--------------------------------------------------------------------------
    */
    'timezone' => env('ROUTER_TIMEZONE', 'Africa/Mogadishu'),

    /*
    |--------------------------------------------------------------------------
    | Hotspot / Portal
    |--------------------------------------------------------------------------
    */
    'portal_domain' => env('ROUTER_PORTAL_DOMAIN', 'login.kaafiye.online'),

    'hotspot_profile' => env('ROUTER_HOTSPOT_PROFILE', 'hsprof-kaafiye'),
    'hotspot_server'  => env('ROUTER_HOTSPOT_SERVER', 'hotspot-kaafiye'),

    'hotspot_files_base' => rtrim(
        env('ROUTER_HOTSPOT_FILES_BASE', 'https://app.kaafiye.online/hotspot-files'),
        '/'
    ),

    /*
    |--------------------------------------------------------------------------
    | Walled garden hosts
    |--------------------------------------------------------------------------
    */
    'walled_garden_hosts' => (static function (): array {
        $raw = (string) env(
            'ROUTER_WALLED_GARDEN_HOSTS',
            'app.kaafiye.online,login.kaafiye.online,kaafiye.online'
        );

        $hosts = array_map(static function ($h): string {
            $h = strtolower(trim((string) $h));
            $h = preg_replace('#^https?://#', '', $h) ?? $h;
            $h = rtrim($h, '/');
            return $h;
        }, explode(',', $raw));

        $hosts = array_filter($hosts, static fn ($h) => $h !== '');
        return array_values(array_unique($hosts));
    })(),
];
