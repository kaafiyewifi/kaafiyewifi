<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotspot;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Services\MikroTikService;
use App\Services\WireGuardService;
use Illuminate\Support\Facades\Crypt;

class HotspotWizardController extends Controller
{
    /* =========================
       STEP 1 – HOTSPOT INFO
    ========================= */
    public function step1(Location $location)
    {
        return view('admin.hotspots.wizard.step1', compact('location'));
    }

    public function storeStep1(Request $request, Location $location, WireGuardService $wg)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'nas_type'         => 'required|string|max:100',
            'physical_address' => 'nullable|string|max:255',
            'ssid'             => 'required|string|max:255',
            'download_speed'   => 'required|numeric|min:1',
            'upload_speed'     => 'required|numeric|min:1',
            'speed_unit'       => 'required|string|max:10',
        ]);

        // 🔐 Auto generate WireGuard keys
        $keys = $wg->generateKeys();

        $hotspot = Hotspot::create([
            'location_id'      => $location->id,
            'title'            => $data['title'],
            'nas_type'         => $data['nas_type'],
            'physical_address' => $data['physical_address'] ?? null,
            'ssid'             => $data['ssid'],
            'download_speed'   => $data['download_speed'],
            'upload_speed'     => $data['upload_speed'],
            'speed_unit'       => $data['speed_unit'],
            'wg_private_key'   => $keys['private'],
            'wg_public_key'    => $keys['public'],
        ]);

        return redirect()->route('admin.hotspots.wizard.step2', [$location,$hotspot]);
    }

    /* =========================
       STEP 2 – ROUTER CONFIG
    ========================= */
    public function step2(Location $location, Hotspot $hotspot)
    {
        return view('admin.hotspots.wizard.step2', compact('location','hotspot'));
    }

    public function storeStep2(Request $request, Location $location, Hotspot $hotspot)
    {
        $data = $request->validate([
            'router_ip' => 'nullable|string|max:50',
            'api_port'  => 'nullable|integer|min:1|max:65535',
            'api_user'  => 'nullable|string|max:100',
            'api_pass'  => 'nullable|string|max:255',
        ]);

        $hotspot->update([
            'router_ip' => $data['router_ip'] ?? null,
            'api_port'  => $data['api_port'] ?? 8728,
            'api_user'  => $data['api_user'] ?? null,
            'api_pass'  => isset($data['api_pass']) && $data['api_pass'] !== ''
                ? Crypt::encryptString($data['api_pass'])
                : null,
        ]);

        return redirect()->route('admin.hotspots.wizard.done', [$location,$hotspot]);
    }

    /* =========================
       STEP 3 – RESULT PAGE
    ========================= */
    public function done(Location $location, Hotspot $hotspot)
    {
        $script = $this->generateScript($hotspot);

        return view('admin.hotspots.wizard.done', compact(
            'location','hotspot','script'
        ));
    }

    /* =========================
       AUTO PUSH TO ROUTER
    ========================= */
    public function autoPush(Location $location, Hotspot $hotspot, MikroTikService $mikrotik)
    {
        try {

            if(!$hotspot->router_ip){
                throw new \Exception("Router IP not configured");
            }

            $script = $this->generateScript($hotspot);

            $mikrotik->pushScript($hotspot, $script);

            return response()->json([
                'status' => 'success',
                'message' => 'Script pushed and executed successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ],500);
        }
    }

    /* =========================
       VPN STATUS CHECKER
    ========================= */
    public function vpnStatus(Location $location, Hotspot $hotspot, MikroTikService $mikrotik)
    {
        $status = $mikrotik->getVpnStatus($hotspot);

        return response()->json($status);
    }

    /* =========================
       SCRIPT GENERATOR
    ========================= */
    private function generateScript(Hotspot $hotspot): string
    {
        $template = file_get_contents(resource_path('scripts/mikrotik_template.rsc'));

        return str_replace([
            '{{TITLE}}',
            '{{SSID}}',
            '{{DOWNLOAD}}',
            '{{UPLOAD}}',
            '{{RADIUS_SECRET}}',
            '{{WG_PRIVATE_KEY}}',
            '{{WG_PUBLIC_KEY}}',
        ],[
            $hotspot->title,
            $hotspot->ssid,
            $hotspot->download_speed.$hotspot->speed_unit,
            $hotspot->upload_speed.$hotspot->speed_unit,
            $hotspot->radius_secret ?? '123456',
            $hotspot->wg_private_key,
            $hotspot->wg_public_key,
        ], $template);
    }
}
