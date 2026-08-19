<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$rolesOrPermissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        foreach ($rolesOrPermissions as $roleOrPerm) {
            if ($user->role === $roleOrPerm || $user->hasRole($roleOrPerm) || $user->hasPermission($roleOrPerm)) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Forbidden: Insufficient permissions.'], 403);
    }
}
