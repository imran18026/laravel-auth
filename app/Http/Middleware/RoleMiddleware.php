<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string ...$roles  The roles allowed to access the route.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthorized: Authentication required.'], 401);
        }

        if (! $request->user()->hasAnyRole($roles)) {
            return response()->json(['message' => 'Forbidden: You do not have the required role(s).'], 403);
        }

        return $next($request);
}
}
