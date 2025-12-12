<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            switch ($user->role) {
                case 'superuser':
                    return redirect()->route('home'); // misalnya dashboard admin
                case 'admin':
                    return redirect()->route('realisasi.rekap'); // misalnya dashboard admin
                case 'user':
                default:
                    return redirect()->route('home'); // misalnya dashboard user
            }
        }
        return back()->with('error', 'Email atau kata sandi salah.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
