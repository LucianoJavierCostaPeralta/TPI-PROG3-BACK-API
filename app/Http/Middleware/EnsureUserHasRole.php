<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! collect($roles)->contains(fn (string $role) => $user->hasRole($role))) {
            return response()->json([
                'message' => 'No tenes permisos para realizar esta accion.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
