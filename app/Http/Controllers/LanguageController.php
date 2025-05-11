<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

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
        \Log::info('Language change request received', ['locale' => $request->locale]);
        
        $validated = $request->validate([
            'locale' => 'required|string|in:en,sr,de,fr,es'
        ]);

        Session::put('locale', $validated['locale']);
        App::setLocale($validated['locale']);
        
        \Log::info('Language changed', [
            'session_locale' => Session::get('locale'), 
            'app_locale' => App::getLocale(),
            'session_id' => Session::getId()
        ]);
        
        // Force save session
        Session::save();
        
        return redirect()->back()->with('success', 'Language changed to ' . strtoupper($validated['locale']));
    }
}