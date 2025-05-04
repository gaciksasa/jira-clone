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
        $validated = $request->validate([
            'locale' => 'required|string|in:en,sr,de,fr,es'
        ]);

        Session::put('locale', $validated['locale']);
        App::setLocale($validated['locale']);
        
        return redirect()->back();
    }
}