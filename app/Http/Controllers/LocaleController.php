<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function change(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(config('app.supported_locales', ['en', 'bg']))],
        ]);

        $request->session()->put('locale', $validated['locale']);

        return back();
    }
}
