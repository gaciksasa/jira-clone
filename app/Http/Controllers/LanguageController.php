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
        // Validate the locale
        $validated = $request->validate([
            'locale' => 'required|string|in:en,sr,de,fr,es'
        ]);

        $locale = $validated['locale'];
        
        // Store in session
        session(['locale' => $locale]);
        App::setLocale($locale);
        
        // Create a plain cookie (not encrypted)
        $cookie = cookie()->forever('app_locale', $locale);
        
        // Log and redirect
        Log::info("Setting language to: {$locale}");
        
        return redirect()->back()
            ->with('success', 'Language changed to ' . strtoupper($locale))
            ->withCookie($cookie);
    }
}