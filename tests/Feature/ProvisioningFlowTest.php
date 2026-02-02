<?php

namespace Tests\Feature;

use App\Models\Router;
use App\Models\RouterProvisionToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProvisioningFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_provision_endpoint_returns_script_and_marks_token_used(): void
    {
        $router = Router::create(['identity' => 'wavecore', 'name' => 'wavecore']);
        $token = RouterProvisionToken::create([
            'router_id' => $router->id,
            'token' => Str::random(64),
            'expires_at' => now()->addMinutes(10),
        ]);

        $res = $this->get("/api/provision/{$token->token}");
        $res->assertOk();
        $this->assertStringContainsString('/ip service enable api', $res->getContent());

        $token->refresh();
        $this->assertNotNull($token->used_at);
    }

    public function test_callback_connects_router_and_encrypts_password(): void
    {
        $router = Router::create(['identity' => 'wavecore', 'name' => 'wavecore']);
        $token = RouterProvisionToken::create([
            'router_id' => $router->id,
            'token' => Str::random(64),
            'expires_at' => now()->addMinutes(10),
        ]);

        $payload = [
            'token' => $token->token,
            'identity' => 'wavecore',
            'mgmt_ip' => '10.0.0.1',
            'api_port' => 8728,
            'api_username' => 'system_api',
            'api_password' => 'secret12345',
        ];

        $this->postJson('/api/router/callback', $payload)
            ->assertOk()
            ->assertJson(['ok' => true]);

        $router->refresh();
        $this->assertEquals('connected', $router->status);
        $this->assertNotEquals('secret12345', $router->api_password); // encrypted text
    }
}
