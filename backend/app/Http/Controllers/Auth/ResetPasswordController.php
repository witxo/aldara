<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class ResetPasswordController extends Controller
{
    public function showResetForm($token)
    {
        return view('auth.passwords.reset', compact('token'));
    }

    public function reset()
    {
        return redirect()->route('login')->with('error', 'Funcionalidad no disponible temporalmente');
    }
}
