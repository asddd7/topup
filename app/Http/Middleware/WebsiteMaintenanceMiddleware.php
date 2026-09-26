<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebsiteMaintenanceMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedPaths = [
            'login',
            'register',
            'forgot-password',
            'reset-password/*',
            'email/verification-notification',
            'up',
        ];

        if (
            (string) setting('maintenance', '0') !== '1'
            || $request->is($allowedPaths)
            || $request->is('admin', 'admin/*')
            || ($request->user() && (int) $request->user()->role_id === 1)
        ) {
            return $next($request);
        }

        return response()->view('maintenance', status: 503);
    }
}