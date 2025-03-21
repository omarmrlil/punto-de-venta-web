<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class logoutController extends Controller
{
    public function logout(): RedirectResponse{
        Session::flush();
        Auth::logout();

        return redirect()->route('login');
    }
}
