<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class EarlyLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check cookie first (more reliable across requests)
        if ($locale = $request->cookie('app_locale')) {
            App::setLocale($locale);
            session(['locale' => $locale]); // Ensure session is in sync
        }
        // Then check session
        elseif ($locale = session('locale')) {
            App::setLocale($locale);
        }
        
        return $next($request);
    }
}