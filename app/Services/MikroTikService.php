<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use App\Models\Hotspot;
use Illuminate\Support\Facades\Crypt;

class MikroTikService
{
    /* =========================
       CONNECT
    =========================*/
    protected function connect(Hotspot $hotspot)
    {
        return new Client([
            'host'    => $hotspot->router_ip,
            'user'    => $hotspot->api_user,
            'pass'    => $hotspot->api_pass
                ? Crypt::decryptString($hotspot->api_pass)
                : null,
            'port'    => $hotspot->api_port ?? 8728,
            'timeout' => 10,
        ]);
    }

    /* =========================
       TEST CONNECTION
    =========================*/
    public function testConnection(Hotspot $hotspot)
    {
        $client = $this->connect($hotspot);
        return $client->query('/system/resource/print')->read();
    }

    /* =========================
       ONLINE USERS
    =========================*/
    public function getOnlineUsers(Hotspot $hotspot)
    {
        $client = $this->connect($hotspot);
        return $client->query('/ip/hotspot/active/print')->read();
    }

    /* =========================
       USER CONTROL
    =========================*/
    public function disableUser(Hotspot $hotspot, $id)
    {
        $client = $this->connect($hotspot);

        $q = new Query('/ip/hotspot/user/disable');
        $q->equal('numbers', $id);

        return $client->query($q)->read();
    }

    public function enableUser(Hotspot $hotspot, $id)
    {
        $client = $this->connect($hotspot);

        $q = new Query('/ip/hotspot/user/enable');
        $q->equal('numbers', $id);

        return $client->query($q)->read();
    }

    public function deleteUser(Hotspot $hotspot, $id)
    {
        $client = $this->connect($hotspot);

        $q = new Query('/ip/hotspot/user/remove');
        $q->equal('numbers', $id);

        return $client->query($q)->read();
    }

    /* =========================
       PUSH SCRIPT (AUTO PUSH)
    =========================*/
    public function pushScript(Hotspot $hotspot, string $script)
    {
        $client = $this->connect($hotspot);

        // remove old script if exists
        $remove = new Query('/system/script/remove');
        $remove->equal('numbers', 'powerlynx-script');
        $client->query($remove)->read();

        // add script
        $add = new Query('/system/script/add');
        $add->equal('name', 'powerlynx-script');
        $add->equal('source', $script);
        $client->query($add)->read();

        // run script
        $run = new Query('/system/script/run');
        $run->equal('numbers', 'powerlynx-script');

        return $client->query($run)->read();
    }

    /* =========================
       ROUTER STATUS
    =========================*/
    public function getRouterStatus(Hotspot $hotspot)
    {
        try {
            $client = $this->connect($hotspot);

            $data = $client->query('/system/resource/print')->read()[0];

            return [
                'status'        => 'online',
                'cpu'           => $data['cpu-load'] ?? 0,
                'memory_free'   => $data['free-memory'] ?? 0,
                'uptime'        => $data['uptime'] ?? '',
                'version'       => $data['version'] ?? '',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'offline',
                'error'  => $e->getMessage()
            ];
        }
    }

    /* =========================
       VPN STATUS (WIREGUARD)
    =========================*/
    public function getVpnStatus(Hotspot $hotspot)
    {
        try {
            $client = $this->connect($hotspot);

            $wg = $client->query('/interface/wireguard/print')->read();

            if (count($wg) == 0) {
                return [
                    'status' => 'not_configured'
                ];
            }

            $iface = $wg[0];

            return [
                'status'      => 'connected',
                'name'        => $iface['name'] ?? '',
                'listen_port' => $iface['listen-port'] ?? '',
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'offline',
                'error'  => $e->getMessage()
            ];
        }
    }
}
