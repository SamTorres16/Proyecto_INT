<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Docente;
use App\Models\Carrera;
use App\Models\ActExtraescolar;
use App\Models\TipoUsuario;
use App\Models\Actividad;
use App\Models\HistorialExtraescolar;

class GeneralApiController extends Controller
{
    public function usuarios() {
        return response()->json(Usuario::with(['tipo', 'actividad'])->get());
    }

    public function docentes() {
        return response()->json(Docente::all());
    }

    public function carreras() {
        return response()->json(Carrera::all());
    }

    public function actividadesExtraescolares() {
        return response()->json(ActExtraescolar::all());
    }

    public function tiposUsuario() {
        return response()->json(TipoUsuario::all());
    }

    public function eventos() {
        return response()->json(Actividad::all());
    }

    public function historiales() {
        return response()->json(HistorialExtraescolar::with(['actividadExtraescolar'])->get());
    }
}
