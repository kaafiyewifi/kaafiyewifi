<?php

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

function audit_log(string $action): void
{
    AuditLog::create([
        'user_id' => Auth::id(),
        'action' => $action,
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);
}
