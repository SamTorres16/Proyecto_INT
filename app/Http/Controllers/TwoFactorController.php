<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TwoFactorCode;
use App\Models\Usuario;

class TwoFactorController extends Controller
{
    /**
     * Muestra el formulario para ingresar el código de verificación.
     */
    public function showForm()
    {
        // Si no hay usuario pendiente en sesión, redirigir al login
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('cpanel.two_factor');
    }

    /**
     * Valida el código ingresado por el usuario.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ], [
            'code.required' => 'Por favor ingresa el código de verificación.',
            'code.digits'   => 'El código debe ser de 6 dígitos.',
        ]);

        $numControl = session('2fa_user_id');

        if (!$numControl) {
            return redirect()->route('login')->withErrors(['code' => 'Sesión expirada. Inicia sesión nuevamente.']);
        }

        $record = TwoFactorCode::where('num_control', $numControl)
            ->where('code', $request->code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$record) {
            return back()->withErrors(['code' => 'El código es inválido o ha expirado. Por favor vuelve a iniciar sesión.']);
        }

        // Marcar el código como usado
        $record->update(['used' => true]);

        // Limpiar la sesión temporal
        session()->forget('2fa_user_id');

        // Autenticar al usuario definitivamente
        $user = Usuario::where('num_control', $numControl)->firstOrFail();
        Auth::login($user);
        $request->session()->regenerate();

        // Redirigir según el tipo de usuario
        if ($user->id_tipo == 1) {
            return redirect()->route('dashboard');
        } elseif ($user->id_tipo == 2) {
            return redirect()->route('estudiante.index');
        } elseif ($user->id_tipo == 4) {
            return redirect()->route('docente.index');
        }

        return redirect()->route('dashboard');
    }

    /**
     * Reenvía un nuevo código de verificación.
     */
    public function resend()
    {
        $numControl = session('2fa_user_id');

        if (!$numControl) {
            return redirect()->route('login');
        }

        $user = Usuario::where('num_control', $numControl)->firstOrFail();

        // Invalidar códigos anteriores
        TwoFactorCode::where('num_control', $numControl)->update(['used' => true]);

        // Generar y enviar nuevo código
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        TwoFactorCode::create([
            'num_control' => $numControl,
            'code'        => $code,
            'expires_at'  => now()->addMinutes(10),
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($user->correo_inst)
                ->send(new \App\Mail\TwoFactorCodeMail($code, $user->nombre));
        } catch (\Exception $e) {
            return back()->withErrors(['code' => 'No se pudo reenviar el correo. Intenta de nuevo.']);
        }

        return back()->with('resent', 'Se envió un nuevo código a tu correo institucional.');
    }
}
