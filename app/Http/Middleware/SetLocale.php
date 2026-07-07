<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Session::get('locale')
            ?? Session::get('applocale')
            ?? $request->cookie('filament_language_switch_locale')
            ?? config('app.locale');

        if (in_array($locale, ['en', 'fr', 'ar'], true)) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            Session::put('applocale', $locale);
        }

        return $next($request);
    }
}
