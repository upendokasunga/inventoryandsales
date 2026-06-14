<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\Group;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class CacheDashboardStats implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function handle(): void
    {
        $stats = [
            'users' => User::count(),
            'groups' => Group::count(),
            'audit_logs' => AuditLog::count(),
        ];

        Cache::put('dashboard.stats.admin', $stats, 3600);
    }
}
