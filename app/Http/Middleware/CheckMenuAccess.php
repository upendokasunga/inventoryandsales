<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    public function handle(Request $request, Closure $next, string $permission = 'can_view'): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return $next($request);
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if ($user->hasMenuAccess($routeName, $permission)) {
            return $next($request);
        }

        abort(403, 'Unauthorized. You do not have access to this resource.');
    }
}
