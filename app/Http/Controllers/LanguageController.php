<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

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
        Log::info('Language change request received', ['locale' => $request->locale]);
        
        $validated = $request->validate([
            'locale' => 'required|string|in:en,sr,de,fr,es'
        ]);

        $locale = $validated['locale'];
        
        // Store in session with a more reliable method
        session(['locale' => $locale]);
        App::setLocale($locale);
        
        // Force session save
        $request->session()->save();
        
        Log::info('Language changed', [
            'session_locale' => session('locale'), 
            'app_locale' => App::getLocale(),
            'session_id' => Session::getId()
        ]);
        
        return redirect()->back()->with('success', 'Language changed to ' . strtoupper($locale));
    }
}