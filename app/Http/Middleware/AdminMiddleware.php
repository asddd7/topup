<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ((int) $request->user()->role_id !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Akses hanya untuk administrator.',
            ], 403);
        }

        return $next($request);
    }
}