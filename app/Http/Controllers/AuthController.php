<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Get the portal from the hidden input in the form
        $portal = $request->input('role');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $role = Auth::user()->role;

            // Block student from accessing guidance login
            if ($portal === 'guidance' && $role !== 'guidance') {
                Auth::logout();
                return back()->withErrors(['username' => 'These credentials are not authorized for the Guidance portal.'])->withInput();
            }

            // Block guidance from accessing student login
            if ($portal === 'student' && $role !== 'student') {
                Auth::logout();
                return back()->withErrors(['username' => 'These credentials are not authorized for the Student portal.'])->withInput();
            }

            // Block anyone from accessing admin login unless they are admin
            if ($portal === 'admin' && $role !== 'admin') {
                Auth::logout();
                return back()->withErrors(['username' => 'These credentials are not authorized for the Admin portal.'])->withInput();
            }

            // Redirect based on role
            if ($role === 'admin') {
                return redirect()->intended('admin/dashboard');
            } elseif ($role === 'guidance') {
                return redirect()->intended('guidance/dashboard');
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['username' => 'Incorrect ID or password. Please try again.'])->withInput();
    }
}