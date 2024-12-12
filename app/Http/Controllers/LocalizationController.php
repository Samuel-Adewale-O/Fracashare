<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocalizationController extends Controller
{
    public function setLocale(Request $request)
    {
        $request->validate([
            'locale' => 'required|string|size:2'
        ]);

        $locale = $request->locale;
        if (!array_key_exists($locale, config('languages.supported'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unsupported language'
            ], 400);
        }

        Session::put('locale', $locale);

        return response()->json([
            'status' => 'success',
            'message' => 'Language updated successfully',
            'data' => [
                'locale' => $locale,
                'name' => config("languages.supported.{$locale}.name"),
                'native' => config("languages.supported.{$locale}.native")
            ]
        ]);
    }

    public function getLocales()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'current' => Session::get('locale', config('languages.default')),
                'supported' => config('languages.supported')
            ]
        ]);
    }
}