<?php

declare(strict_types=1);

namespace App\Http\Controllers\Provision;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Services\Routers\ProvisionTokenService;
use App\Services\Routers\RouterEventService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProvisionController extends Controller
{
    public function __construct(
        private readonly ProvisionTokenService $tokens
    ) {}

    /**
     * GET /provision/{token}
     * MikroTik fetches provisioning script here (text/plain).
     */
    public function script(Request $request, string $token): Response
    {
        [$row, $router] = $this->tokens->findValidTokenAndRouter($token);

        if (!$row || !$router) {
            return response("Invalid or expired token\n", 404, [
                'Content-Type'  => 'text/plain; charset=UTF-8',
                'Cache-Control' => 'no-store',
            ]);
        }

        $this->tokens->markServed($row);

        $script = $this->buildRouterOsScript($router, $token);

        app(RouterEventService::class)->log(
            $router,
            'provision.script_served',
            ['ip' => $request->ip(), 'ua' => (string) $request->userAgent()]
        );

        return response($script . "\n", 200, [
            'Content-Type'  => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }

    private function buildRouterOsScript(Router $router, string $token): string
    {
        $apiPort  = (int) env('MIKROTIK_API_PORT', 8728);
        $apiUser  = (string) env('ROUTER_API_USER', 'kaafiye');
        $apiPass  = (string) env('ROUTER_API_PASS', 'SuperStrongPasswordHere');
        $timezone = (string) env('ROUTER_TIMEZONE', 'Africa/Mogadishu');

        // ✅ Token-based callback (router will call this)
        $callbackUrl  = url('/api/provision/callback/' . $token);

        // ✅ Heartbeat endpoint
        $heartbeatUrl = url('/api/routers/heartbeat');

        $template = <<<'RSC'
# ================================
# KAAFIYE PROVISION SCRIPT (FINAL)
# RouterOS v7 compatible (RB951 tested)
# ================================

:global kfCallbackUrl "{{CALLBACK_URL}}";
:global kfHeartbeatUrl "{{HEARTBEAT_URL}}";

:global kfApiPort {{API_PORT}};
:global kfApiUser "{{API_USER}}";
:global kfApiPass "{{API_PASS}}";
:global kfTz "{{TZ}}";

:put "-----------------Downloading configuration-----------------";
:put "Downloading configuration...";
:put "-----------------Applying configuration-----------------";
:put "Applying configuration...";

:log info "Starting Kaafiye provisioning...";

# --- Timezone ---
:do {
  /system clock set time-zone-name=$kfTz;
  :log info "Timezone configured successfully";
  :put "-----------------Timezone configured successfully-----------------";
} on-error={
  :log warning "Timezone set failed";
  :put "-----------------Timezone set FAILED-----------------";
}

# --- Detect mgmt IP (best effort) ---
:local kfMgmtIp "";

:do {
  :local idx [/ip address find where dynamic=yes disabled=no];
  :if ([:len $idx] > 0) do={
    :local a [/ip address get [:pick $idx 0] address];
    :set kfMgmtIp [:pick $a 0 [:find $a "/"]];
  }
} on-error={}

:if ($kfMgmtIp = "") do={
  :foreach ifn in={"bridgeLocal";"bridge";"ether1";"ether2"} do={
    :do {
      :local idx2 [/ip address find where interface=$ifn disabled=no];
      :if ([:len $idx2] > 0) do={
        :local a2 [/ip address get [:pick $idx2 0] address];
        :set kfMgmtIp [:pick $a2 0 [:find $a2 "/"]];
      }
    } on-error={}
    :if ($kfMgmtIp != "") do={ :break; }
  }
}

:log info ("Kaafiye mgmt_ip=" . $kfMgmtIp);
:put ("-----------------Detected mgmt_ip=" . $kfMgmtIp . "-----------------");

# =====================================================================
# CALLBACK (GET) - RouterOS 7.21 safe
# Uses output=file to avoid Winbox wrap issues
# =====================================================================
:put "-----------------Notifying server (callback)-----------------";

:local kfIdent [/system identity get name];
:local kfCbUrl ($kfCallbackUrl . "?identity=" . $kfIdent . "&mgmt_ip=" . $kfMgmtIp . "&api_port=" . $kfApiPort);

:do {
  /tool fetch mode=https check-certificate=no output=file dst-path=cb.txt url=$kfCbUrl;
  :put "-----------------Callback sent successfully-----------------";
  :log info "Callback sent successfully";
} on-error={
  :log warning "Callback failed";
  :put "-----------------Callback FAILED-----------------";
}

:do { /file remove cb.txt; } on-error={}

# =====================================================================
# HEARTBEAT Scheduler (1m) - GET (RouterOS 7.21 safe)
# =====================================================================
:put "-----------------Configuring heartbeat-----------------";

:do { /system scheduler remove [find name="kaafiye-heartbeat"]; } on-error={}

:do {
  /system scheduler add name="kaafiye-heartbeat" start-time=startup interval=1m on-event="\
:local ident [/system identity get name];\
:local cpu [/system resource get cpu-load];\
:local freeMem [/system resource get free-memory];\
:local totalMem [/system resource get total-memory];\
:local freeHdd [/system resource get free-hdd-space];\
:local totalHdd [/system resource get total-hdd-space];\
:local uptime [/system resource get uptime];\
:local verFull [/system resource get version];\
:local sp [:find \$verFull \" \"];\
:local ver \$verFull;\
:if (\$sp != nil) do={ :set ver [:pick \$verFull 0 \$sp]; };\
:local board [/system resource get board-name];\
:local arch [/system resource get architecture-name];\
:local hbUrl \$kfHeartbeatUrl;\
:set hbUrl (\$hbUrl . \"?identity=\" . \$ident);\
:set hbUrl (\$hbUrl . \"&cpu_load=\" . \$cpu);\
:set hbUrl (\$hbUrl . \"&free_memory=\" . \$freeMem);\
:set hbUrl (\$hbUrl . \"&total_memory=\" . \$totalMem);\
:set hbUrl (\$hbUrl . \"&free_hdd_space=\" . \$freeHdd);\
:set hbUrl (\$hbUrl . \"&total_hdd_space=\" . \$totalHdd);\
:set hbUrl (\$hbUrl . \"&uptime=\" . \$uptime);\
:set hbUrl (\$hbUrl . \"&version=\" . \$ver);\
:set hbUrl (\$hbUrl . \"&board_name=\" . \$board);\
:set hbUrl (\$hbUrl . \"&architecture_name=\" . \$arch);\
:do { /tool fetch mode=https check-certificate=no output=file dst-path=hb.txt url=\$hbUrl; } on-error={ :log warning (\"Kaafiye heartbeat failed url=\" . \$hbUrl); };\
:do { /file remove hb.txt; } on-error={};";
  :log info "Heartbeat scheduler added";
  :put "-----------------Heartbeat scheduler added-----------------";
} on-error={
  :log warning "Heartbeat scheduler add failed";
  :put "-----------------Heartbeat scheduler add FAILED-----------------";
}

:put "-----------------Provisioning completed-----------------";
:put "Provisioning completed";
:log info "Configuration completed successfully.";
RSC;

        return strtr($template, [
            '{{CALLBACK_URL}}'  => $this->rosEscape($callbackUrl),
            '{{HEARTBEAT_URL}}' => $this->rosEscape($heartbeatUrl),
            '{{API_PORT}}'      => (string) $apiPort,
            '{{API_USER}}'      => $this->rosEscape($apiUser),
            '{{API_PASS}}'      => $this->rosEscape($apiPass),
            '{{TZ}}'            => $this->rosEscape($timezone),
        ]);
    }

    private function rosEscape(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\"'], $value);
    }
}
