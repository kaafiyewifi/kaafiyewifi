<?php

namespace Tests\Feature;

use App\Models\Router;
use App\Models\RouterProvisionToken;
use App\Services\Provisioning\ProvisionTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProvisioningTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_provision_script_requires_valid_token(): void
    {
        $this->get('/provision/invalidtoken')->assertStatus(404);
    }

    public function test_provision_script_marks_token_used_and_returns_text(): void
    {
        $router = Router::factory()->create(['identity' => 'R1']);
        $svc = app(ProvisionTokenService::class);

        [$raw, $row] = $svc->createForRouter($router, 10);

        $res = $this->get("/provision/{$raw}");
        $res->assertOk();
        $this->assertStringContainsString('Provisioning Bootstrap Script', $res->getContent());

        $row->refresh();
        $this->assertNotNull($row->used_at);
    }
}
