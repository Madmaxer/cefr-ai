<?php

namespace App\Http\Controllers;

use App\Enums\Language;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $tests = $user->languageTests()->latest()->get();
        $languages = Language::cases();

        return view('home', compact('user', 'tests', 'languages'));
    }
}
