<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected string $masterPassword = 'YPF2026WOOPI';

    public function showLogin()
    {
        if (session('authenticated')) {
            return redirect()->route('chat.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if ($request->password === $this->masterPassword) {
            session(['authenticated' => true]);
            // Store token in session for the frontend to use in API calls
            session(['api_token' => base64_encode($this->masterPassword)]);
            return redirect()->route('chat.index');
        }

        return back()->withErrors(['password' => 'Contraseña incorrecta']);
    }

    public function logout()
    {
        session()->forget('authenticated');
        return redirect()->route('login');
    }
}
