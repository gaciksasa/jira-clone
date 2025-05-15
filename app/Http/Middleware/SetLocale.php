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
        // Get the starting locale for debugging
        $startingLocale = App::getLocale();
        
        // CRITICAL CHANGE: Directly grab and apply the locale from the session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            // Apply it immediately
            app()->setLocale($locale);
            
            // Debug output to confirm the change
            Log::debug('LOCALE MIDDLEWARE EXECUTED', [
                'old_locale' => $startingLocale,
                'new_locale' => app()->getLocale(),
                'session_locale' => $locale
            ]);
        }
        
        // Continue with the request
        return $next($request);
    }
}