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
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if we have a locale in the session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            App::setLocale($locale);
            Log::debug('Setting locale from session', ['locale' => $locale, 'session_id' => Session::getId()]);
        } else {
            // Default to browser preference if available
            $browserLocale = $request->getPreferredLanguage();
            
            // Only set if the browser locale is supported in our application
            $supportedLocales = config('app.available_locales', ['en']);
            if (in_array($browserLocale, $supportedLocales)) {
                App::setLocale($browserLocale);
                Session::put('locale', $browserLocale);
                Log::debug('Setting locale from browser preference', ['locale' => $browserLocale]);
            }
        }
        
        return $next($request);
    }
}