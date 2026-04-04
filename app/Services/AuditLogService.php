<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public function log(
        string $action,
        ?string $description = null,
        mixed $target = null,
        array $properties = [],
        ?int $userId = null
    ): void {
        AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'target_type' => $this->resolveTargetType($target),
            'target_id' => $this->resolveTargetId($target),
            'description' => $description,
            'ip_address' => Request::ip(),
            'properties' => !empty($properties) ? $properties : null,
        ]);
    }

    protected function resolveTargetType(mixed $target): ?string
    {
        if (!$target) {
            return null;
        }

        if (is_object($target)) {
            return get_class($target);
        }

        if (is_string($target)) {
            return $target;
        }

        return null;
    }

    protected function resolveTargetId(mixed $target): ?int
    {
        if (!$target) {
            return null;
        }

        if (is_object($target) && isset($target->id)) {
            return (int) $target->id;
        }

        return null;
    }
}