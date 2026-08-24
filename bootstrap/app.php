<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        then: function () {

                Route::prefix('api/v1')
                    ->middleware('api')
                    ->group(base_path('routes/api/v1/user.php'));

                Route::prefix('api/v1')
                    ->middleware('api')
                    ->group(base_path('routes/api/v1/admin.php'));

            },
    )
    ->withMiddleware(function ($middleware) {
                
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'role'  => RoleMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
