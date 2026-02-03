<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PlaceholderController extends Controller
{
    public function show(string $view, string $title, string $description): View
    {
        return view('placeholders.page', [
            'title' => $title,
            'description' => $description,
        ]);
    }
}
