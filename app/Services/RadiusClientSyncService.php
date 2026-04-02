<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Router;
use RuntimeException;

class RadiusClientSyncService
{
    private string $stagingFile;
    private string $syncScript = '/usr/local/bin/kaafiye-radius-sync';

    public function __construct()
    {
        $this->stagingFile = storage_path('app/radius/routers.conf');
    }

    public function sync(): void
    {
        $content = $this->buildConfig();

        $this->ensureDirectoryExists(dirname($this->stagingFile));

        if (@file_put_contents($this->stagingFile, $content) === false) {
            throw new RuntimeException('Failed to write staging FreeRADIUS clients file.');
        }

        $this->reloadRadius();
    }

    private function buildConfig(): string
    {
        $routers = Router::query()
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'retired');
            })
            ->orderBy('id')
            ->get();

        $content = "# AUTO-GENERATED FILE. DO NOT EDIT MANUALLY.\n\n";

        foreach ($routers as $router) {
            $ip = $this->resolveRouterIp($router);
            $secret = trim((string) ($router->radius_secret ?? ''));

            if ($ip === '' || $secret === '') {
                continue;
            }

            $clientName = $this->makeClientName($router);

            $content .= "client {$clientName} {\n";
            $content .= "    ipaddr = {$ip}\n";
            $content .= "    secret = {$secret}\n";
            $content .= "    shortname = {$clientName}\n";
            $content .= "    nastype = other\n";
            $content .= "    require_message_authenticator = yes\n";
            $content .= "}\n\n";
        }

        return $content;
    }

    private function resolveRouterIp(object $router): string
    {
        $candidates = [
            $router->wg_ip ?? null,
        ];

        foreach ($candidates as $candidate) {
            $value = trim((string) $candidate);

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function makeClientName(object $router): string
    {
        $base = '';

        if (!empty($router->identity)) {
            $base = (string) $router->identity;
        } elseif (!empty($router->name)) {
            $base = (string) $router->name;
        } else {
            $base = 'router_' . (string) $router->id;
        }

        $base = strtolower($base);
        $base = preg_replace('/[^a-z0-9_]+/', '_', $base) ?? '';
        $base = trim($base, '_');

        if ($base === '') {
            $base = 'router_' . (string) $router->id;
        }

        return $base;
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (!@mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException("Failed to create directory: {$directory}");
        }
    }

    private function reloadRadius(): void
    {
        $command = 'sudo ' . escapeshellarg($this->syncScript) . ' ' . escapeshellarg($this->stagingFile) . ' 2>&1';

        exec($command, $output, $code);

        if ($code !== 0) {
            throw new RuntimeException(
                "Failed to reload FreeRADIUS:\n" . implode("\n", $output)
            );
        }
    }
}