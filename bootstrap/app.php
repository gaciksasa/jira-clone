<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

    $app->bootingCallbacks[] = function ($app) {
        if ($locale = request()->session()->get('locale')) {
            $app->setLocale($locale);
        } elseif ($locale = request()->cookie('app_locale')) {
            $app->setLocale($locale);
        }
    };
