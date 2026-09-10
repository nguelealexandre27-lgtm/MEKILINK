<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isDonneur()) {
            return redirect()->route('donneur.dashboard');
        }

        if ($user->isDemandeur()) {
            return redirect()->route('demandeur.dashboard');
        }

        return redirect()->route('home');
    }
}
