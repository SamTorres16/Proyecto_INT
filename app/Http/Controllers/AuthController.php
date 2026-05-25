<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Usuario;
use App\Models\TwoFactorCode;
use App\Mail\TwoFactorCodeMail;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('cpanel.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'correo'     => 'required',
            'contrasena' => 'required'
        ]);

        $throttleKey = strtolower($request->input('correo')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);
            return back()->withErrors([
                'correo' => "Demasiados intentos fallidos. Por favor, espere $minutes minuto(s) antes de volver a intentarlo.",
            ])->withInput($request->only('correo'));
        }

        // Buscar usuario por correo o número de control
        $user = Usuario::where('correo_inst', $request->correo)
                       ->orWhere('num_control', $request->correo)
                       ->first();

        if ($user && Hash::check($request->contrasena, $user->contrasena)) {
            RateLimiter::clear($throttleKey);

            // Verificar si docente está activo
            if ($user->id_tipo == 4) {
                $docente = \App\Models\Docente::where('no_empleado', $user->num_control)->first();
                if ($docente && !$docente->activo) {
                    return back()->withErrors([
                        'correo' => 'Esta cuenta de docente se encuentra inactiva. Contacte al administrador.',
                    ])->withInput($request->only('correo'));
                }
            }

            // Invalidar códigos anteriores del usuario
            TwoFactorCode::where('num_control', $user->num_control)->update(['used' => true]);

            // Generar código de 6 dígitos
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            TwoFactorCode::create([
                'num_control' => $user->num_control,
                'code'        => $code,
                'expires_at'  => now()->addMinutes(10),
            ]);

            // Enviar el código por correo
            try {
                Mail::to($user->correo_inst)->send(new TwoFactorCodeMail($code, $user->nombre));
            } catch (\Exception $e) {
                return back()->withErrors([
                    'correo' => 'No se pudo enviar el código de verificación. Verifica tu correo o intenta más tarde.',
                ])->withInput($request->only('correo'));
            }

            // Guardar el num_control en sesión temporal (NO hacer login aún)
            session(['2fa_user_id' => $user->num_control]);

            return redirect()->route('2fa.form');
        }

        // Credenciales inválidas
        RateLimiter::hit($throttleKey, 300);

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
