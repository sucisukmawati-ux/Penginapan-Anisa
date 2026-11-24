<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        foreach ($roles as $role) {
            if ($role === 'guest' && $user->role === UserRole::GUEST) {
                return $next($request);
            }

            if ($role === 'staff' && ($user->isStaff() || $user->isAdmin())) {
                return $next($request);
            }

            if ($role === 'admin' && $user->isAdmin()) {
                return $next($request);
            }
        }

        abort(403, 'Akses ditolak untuk role ini.');
    }
}
