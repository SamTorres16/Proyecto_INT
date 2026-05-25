<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('cpanel.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'correo' => 'required',
            'contrasena' => 'required'
        ]);

        $throttleKey = strtolower($request->input('correo')) . '|' . $request->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);
            return back()->withErrors([
                'correo' => "Demasiados intentos fallidos. Por favor, espere $minutes minuto(s) antes de volver a intentarlo.",
            ])->withInput($request->only('correo'));
        }

        // Buscamos al usuario por correo O por número de control/empleado
        $user = Usuario::where('correo_inst', $request->correo)
                       ->orWhere('num_control', $request->correo)
                       ->first();

        // Validamos que el usuario exista y la contraseña coincida
        if ($user && \Illuminate\Support\Facades\Hash::check($request->contrasena, $user->contrasena)) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);

            Auth::login($user);
            $request->session()->regenerate();

            // Redirigir según el tipo de usuario (1 = Administrador, 2 = Estudiante, 4 = Docente)
            if ($user->id_tipo == 1) {
                return redirect()->route('dashboard');
            } elseif ($user->id_tipo == 2) {
                return redirect()->route('estudiante.index');
            } elseif ($user->id_tipo == 4) {
                $docente = \App\Models\Docente::where('no_empleado', $user->num_control)->first();
                if ($docente && !$docente->activo) {
                    Auth::logout();
                    return back()->withErrors([
                        'correo' => 'Esta cuenta de docente se encuentra inactiva. Por favor contacte al administrador.',
                    ])->withInput($request->only('correo'));
                }
                return redirect()->route('docente.index');
            }

            return redirect()->route('dashboard');
        }

        // Si falla
        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);

        return back()->withErrors([
            'correo' => 'El correo/usuario o la contraseña no coinciden.',
        ])->withInput($request->only('correo'));
    }



    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
