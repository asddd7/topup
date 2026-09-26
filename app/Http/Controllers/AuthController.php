<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        return view('auth.login', [
            'adminLogin' => $request->boolean('admin')
                || (bool) session('maintenance_admin'),
        ]);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'=>'required|max:255',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|min:8|confirmed'
        ]);

        User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
        ]);

        return redirect()->route('login')
            ->with('success','Registrasi berhasil. Silakan login.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'=>'required|email',
            'password'=>'required',
        ]);

        if(Auth::attempt($credentials,$request->filled('remember'))){

            $request->session()->regenerate();

            $adminLogin = $request->boolean('admin')
                || (bool) session('maintenance_admin');

            if (
                ($adminLogin || (string) setting('maintenance', '0') === '1')
                && (int) Auth::user()->role_id !== 1
            ) {
                Auth::logout();

                return back()
                    ->withInput($request->only('email'))
                    ->with('maintenance_admin', true)
                    ->withErrors([
                        'email' => 'Website sedang maintenance. Hanya akun admin yang dapat masuk saat ini.',
                    ]);
            }

            if (Auth::user()->role_id == 1) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('dashboard');
            }

            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}