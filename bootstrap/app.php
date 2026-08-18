<?php

use App\Http\Middleware\EstAgent;
use App\Http\Middleware\EstProprietaire;
use App\Http\Middleware\EstSuperAdmin;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\VerifierPlanTenant;
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
        $middleware->redirectGuestsTo('/');
        $middleware->web(append: [SetLocale::class]);
        $middleware->alias([
            'proprietaire' => EstProprietaire::class,
            'agent' => EstAgent::class,
            'plan.multi-points' => VerifierPlanTenant::class,
            'super-admin' => EstSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
