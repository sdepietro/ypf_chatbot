<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MasterPasswordAuth
{
    protected string $masterPassword = 'YPF2026WOOPI';

    public function handle(Request $request, Closure $next): Response
    {
        // Check session auth (for web routes)
        if (session('authenticated')) {
            return $next($request);
        }

        // Check header auth (for API routes) - X-Auth-Token with base64 encoded password
        $token = $request->header('X-Auth-Token');
        if ($token && base64_decode($token) === $this->masterPassword) {
            return $next($request);
        }

        // Unauthorized
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Unauthorized',
                'errors' => [],
            ], 401);
        }

        return redirect()->route('login');
    }
}
