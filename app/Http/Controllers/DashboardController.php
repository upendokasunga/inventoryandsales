<?php

namespace App\Http\Controllers;

use App\Jobs\CacheDashboardStats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $stats = [];

        if ($user->isSuperAdmin()) {
            $stats = Cache::remember('dashboard.stats.admin', 3600, function () {
                CacheDashboardStats::dispatch();

                return [
                    'users' => \App\Models\User::count(),
                    'groups' => \App\Models\Group::count(),
                    'audit_logs' => \App\Models\AuditLog::count(),
                ];
            });
        }

        return view('dashboard', compact('stats'));
    }
}
