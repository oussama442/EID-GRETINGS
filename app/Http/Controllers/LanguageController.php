<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switchLang($lang)
    {
        if (in_array($lang, ['en', 'fr', 'ar'])) {
            Session::put('locale', $lang);
            Session::put('applocale', $lang);

            cookie()->queue(cookie()->forever('filament_language_switch_locale', $lang));
        }

        return redirect()->back();
    }
}
