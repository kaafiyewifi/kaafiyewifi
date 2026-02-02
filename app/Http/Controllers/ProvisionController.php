<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RouterToken;
use App\Models\Router;
use App\Models\RouterStatus;
use App\Models\RouterLog;
use Carbon\Carbon;

class ProvisionController extends Controller
{
    public function handle(string $token)
    {
        $routerToken = RouterToken::where('token', $token)->first();

        // ❌ Token not found
        if (!$routerToken) {
            return response('Invalid token', 403);
        }

        // ❌ Token expired
        if ($routerToken->expires_at && $routerToken->expires_at->isPast()) {
            return response('Token expired', 403);
        }

        // ❌ Token already used
        if ($routerToken->is_used) {
            return response('Token already used', 403);
        }

        $router = $routerToken->router;

        // Generate script
        $script = $this->generateBaseScript($router);

        // Mark token as used
        $routerToken->update([
            'is_used' => true,
            'used_at' => now(),
        ]);

        // Update router status
        $router->update([
            'provisioning_status' => 'provisioned',
        ]);

        RouterStatus::updateOrCreate(
            ['router_id' => $router->id],
            [
                'status' => 'provisioned',
                'last_seen_at' => now(),
            ]
        );

        RouterLog::create([
            'router_id' => $router->id,
            'level' => 'info',
            'message' => 'Provisioning script generated and delivered',
        ]);

        return response($script, 200)
            ->header('Content-Type', 'text/plain');
    }

    private function generateBaseScript(Router $router): string
    {
        return <<<MIKROTIK
:log info "Starting provisioning for {$router->router_identity}"

# Basic identity verification
:if ([/system identity get name] != "{$router->router_identity}") do={
    :log error "Router identity mismatch"
    :error "Identity mismatch"
}

:log info "Identity verified"

# Mark provisioning complete
:log info "Base provisioning completed successfully"
MIKROTIK;
    }
}

