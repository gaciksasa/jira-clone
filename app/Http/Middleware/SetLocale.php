<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // First try to get locale from session
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale')); // Use App facade directly
        }
        // Fallback to cookie if session doesn't have locale
        elseif ($request->cookie('app_locale')) {
            $locale = $request->cookie('app_locale');
            Session::put('locale', $locale);
            App::setLocale($locale);
        }
        
        Log::debug('LOCALE MIDDLEWARE EXECUTED', [
            'app_locale' => App::getLocale(),
            'session_locale' => Session::get('locale'),
            'cookie_locale' => $request->cookie('app_locale')
        ]);
        
        return $next($request);
    }
}