<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Models\AuditLog;

class LogSuccessfulLogin
{
    public function handle(UserLoggedIn $event): void
    {
        AuditLog::create([
            'auditable_type' => get_class($event->user),
            'auditable_id' => $event->user->id,
            'user_id' => $event->user->id,
            'event' => 'login',
            'old_values' => null,
            'new_values' => null,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
        ]);

        $event->user->update(['last_login_at' => now()]);
    }
}
