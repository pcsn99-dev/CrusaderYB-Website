<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $allowedRoutes = [
            'password.force.edit',
            'password.force.update',
            'logout',
        ];

        if (
            $user->must_change_password &&
            ! $request->routeIs($allowedRoutes)
        ) {
            return redirect()->route('password.force.edit');
        }

        return $next($request);
    }
}
