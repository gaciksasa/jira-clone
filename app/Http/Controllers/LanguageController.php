<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;

class LanguageController extends Controller
{
    /**
     * Change the application language.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changeLanguage(Request $request)
    {
        $validated = $request->validate([
            'locale' => 'required|string|in:en,sr,de,fr,es'
        ]);

        $locale = $validated['locale'];
        
        // Set for current request
        app()->setLocale($locale);
        
        // Store in session
        session(['locale' => $locale]);
        
        // Create a cookie
        $cookie = cookie()->forever('app_locale', $locale);
        
        // Redirect with a normal (not encrypted) cookie
        return redirect()->back()
            ->withCookie($cookie)
            ->with('success', 'Language changed to ' . strtoupper($locale));
    }
}