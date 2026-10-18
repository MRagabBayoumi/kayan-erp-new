<?php

use App\Http\Middleware\EnsureSalesRepScopedToPortal;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            '_boots/*',
        ]);
        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->user()?->isSalesRep()) {
                return route('portal.index');
            }

            return route('dashboard');
        });
        $middleware->web(append: [
            EnsureSalesRepScopedToPortal::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
