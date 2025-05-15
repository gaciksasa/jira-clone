<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
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

// Set locale as early as possible in the bootstrap process
// This ensures the locale is set before any views are rendered
if (isset($_COOKIE['app_locale'])) {
    // Check for unencrypted cookie first (most reliable)
    $app->setLocale($_COOKIE['app_locale']);
} elseif (isset($_SESSION['locale'])) {
    // Fallback to session if available
    $app->setLocale($_SESSION['locale']);
} elseif ($app->bound('session') && $app->make('session')->has('locale')) {
    // Try Laravel session if initialized
    $app->setLocale($app->make('session')->get('locale'));
}

// Register a booting callback to ensure locale is set 
// at application boot phase as well
$app->booted(function($app) {
    if (isset($_COOKIE['app_locale'])) {
        $app->setLocale($_COOKIE['app_locale']);
    } elseif ($app->bound('session') && 
              $app->make('session')->has('locale')) {
        $app->setLocale($app->make('session')->get('locale'));
    }
});

return $app;