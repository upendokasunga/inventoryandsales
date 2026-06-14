<?php

namespace App\Listeners;

use App\Events\UserLoginFailed;
use App\Models\AuditLog;

class LogFailedLogin
{
    public function handle(UserLoginFailed $event): void
    {
        AuditLog::create([
            'auditable_type' => 'auth',
            'auditable_id' => 0,
            'user_id' => null,
            'event' => 'failed_login',
            'old_values' => null,
            'new_values' => json_encode(['email' => $event->email]),
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
        ]);
    }
}
