<?php
// app/Services/Routers/ServiceApplyBuilder.php
namespace App\Services\Routers;

class ServiceApplyBuilder
{
    public function build(array $ports, string $subnetCidr, array $services): array
    {
        // NOTE: commands are RouterOS terminal commands
        $bridge = 'br-kaafiye';

        $cmds = [];

        // Create bridge if not exists
        $cmds[] = ':if ([:len [/interface bridge find name="'.$bridge.'"]] = 0) do={/interface bridge add name="'.$bridge.'";}';

        // Add ports to bridge (idempotent)
        foreach ($ports as $p) {
            $cmds[] = ':if ([:len [/interface bridge port find interface="'.$p.'" && bridge="'.$bridge.'"]] = 0) do={/interface bridge port add bridge="'.$bridge.'" interface="'.$p.'";}';
        }

        // IP on bridge (simple baseline)
        // Phase 4: parse CIDR properly (gateway ip calculation)
        $gatewayIp = '172.31.0.1/16';
        if ($subnetCidr === '172.31.0.0/16') {
            $gatewayIp = '172.31.0.1/16';
        } else {
            // placeholder: we’ll compute in Phase 4 robustly
            $gatewayIp = $subnetCidr;
        }

        $cmds[] = ':if ([:len [/ip address find interface="'.$bridge.'"]] = 0) do={/ip address add address="'.$gatewayIp.'" interface="'.$bridge.'";}';

        // Services stubs
        if (in_array('hotspot', $services, true)) {
            $cmds[] = ':log info "Hotspot selected - Phase 4 will create full hotspot server + profile + walled garden";';
        }

        if (in_array('pppoe', $services, true)) {
            $cmds[] = ':log info "PPPoE selected - Phase 4 will create PPPoE server + profiles + radius integration";';
        }

        return $cmds;
    }
}
