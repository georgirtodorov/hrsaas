<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function change(Request $request)
    {
        $locale = $request->input('locale', config('app.fallback_locale'));

        $request->session()->put('locale', $locale);

        return back();
    }
}
