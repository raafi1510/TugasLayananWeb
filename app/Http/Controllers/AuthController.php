<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:admin,mahasiswa,dosen',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        Auth::login($user);

        // Arahkan sesuai role
        switch ($user->role) {
            case 'admin':
                return redirect('/admin/dashboard');
            case 'mahasiswa':
                return redirect('/mahasiswa/dashboard');
            case 'dosen':
                return redirect('/dosen/dashboard');
            default:
                return redirect('/login');
        }
    }
}