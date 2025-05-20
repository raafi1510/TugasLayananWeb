<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            switch ($user->role) {
                case 'admin':
                    return redirect('/admin/dashboard');
                case 'mahasiswa':
                    return redirect('/mahasiswa/dashboard');
                case 'dosen':
                    return redirect('/dosen/dashboard');
                default:
                    Auth::logout();
                    return redirect('/login')->withErrors(['role' => 'Role tidak dikenali']);
            }
        }

        return redirect()->back()->withErrors(['email' => 'Email atau password salah']);
    }
}