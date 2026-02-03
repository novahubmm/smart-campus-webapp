<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    /**
     * Switch application language
     */
    public function switch(string $locale): RedirectResponse
    {
        if (!in_array($locale, ['en', 'mm','zh'])) {
            abort(400, 'Invalid language code');
        }
        session(['locale' => $locale]);

        return redirect()->back()->with('success', __('Language changed successfully'));
    }
}
