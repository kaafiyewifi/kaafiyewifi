<?php

namespace App\Support;

use App\Enums\ProvisionStatus;

final class ProvisionStatusMapper
{
    public static function toRouterState(ProvisionStatus $s): string
    {
        return match ($s) {
            ProvisionStatus::Generated => 'token_issued',
            ProvisionStatus::Ran       => 'provisioned',
            ProvisionStatus::Success   => 'configured',
            ProvisionStatus::Failed,
            ProvisionStatus::Expired   => 'error',
        };
    }
}
