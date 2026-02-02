<?php

declare(strict_types=1);

namespace App\Http\Controllers\Routers;

use App\Http\Controllers\Controller;
use App\Models\Router;
use Illuminate\Http\Response;

class HotspotFilesController extends Controller
{
    /**
     * Serve an HTML view with safe no-cache headers (recommended for hotspot files).
     */
    private function html(string $view, array $data = []): Response
    {
        return response()
            ->view($view, $data, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function login(Router $router): Response
    {
        return $this->html('hotspot.login', [
            'router' => $router,
            'brand'  => 'Kaafiye WiFi',
        ]);
    }

    public function alogin(Router $router): Response
    {
        return $this->html('hotspot.alogin', [
            'router' => $router,
            'brand'  => 'Kaafiye WiFi',
        ]);
    }

    public function error(Router $router): Response
    {
        // Optional: show message from session if you set it elsewhere
        $msg = session('msg');

        return $this->html('hotspot.error', [
            'router' => $router,
            'brand'  => 'Kaafiye WiFi',
            'msg'    => $msg,
        ]);
    }

    public function logout(Router $router): Response
    {
        return $this->html('hotspot.logout', [
            'router' => $router,
            'brand'  => 'Kaafiye WiFi',
        ]);
    }
}
