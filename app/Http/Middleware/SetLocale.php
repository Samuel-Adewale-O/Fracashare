<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = Session::get('locale', config('languages.default'));
        
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            if ($this->isValidLocale($locale)) {
                Session::put('locale', $locale);
            }
        }

        App::setLocale($locale);
        return $next($request);
    }

    private function isValidLocale(string $locale): bool
    {
        return array_key_exists($locale, config('languages.supported'));
    }
}