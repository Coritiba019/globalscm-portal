<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias do seu middleware de aprovação
        $middleware->alias([
            'approved' => \App\Http\Middleware\EnsureUserApproved::class,
        ]);

        // (Opcional) throttle “api”:
        // $middleware->group('api', [ \Illuminate\Routing\Middleware\ThrottleRequests::class.':api' ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Deixe vazio por ora; aqui você personaliza o tratamento se quiser.
        // Ex.: $exceptions->shouldRenderJsonWhen(fn ($request) => $request->expectsJson());
    })
    ->create();
