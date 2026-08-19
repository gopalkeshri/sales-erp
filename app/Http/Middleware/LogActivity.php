<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && $request->user()) {
            $path = $request->path();
            $action = match ($request->method()) {
                'POST' => 'create',
                'PUT', 'PATCH' => 'update',
                'DELETE' => 'delete',
                default => 'create'
            };

            // Extract entity type from route
            $segments = explode('/', trim($path, '/'));
            $entityType = $segments[1] ?? 'unknown';

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => is_numeric(end($segments)) ? (int) end($segments) : 0,
                'new_values' => $request->except(['password', 'password_confirmation']),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        }

        return $response;
    }
}
