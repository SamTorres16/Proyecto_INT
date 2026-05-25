<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\ActExtraescolar;
use App\Models\TipoUsuario;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('cpanel.inicio');
    }

    public function resumen()
    {
        // Periodos académicos disponibles (en orden cronológico)
        $periodosDisponibles = [
            'Enero-junio 2025',
            'Agosto-diciembre 2025',
            'Enero-junio 2026',
            'Agosto-diciembre 2026',
            'Enero-junio 2027',
        ];

        // Obtener el periodo seleccionado del query param (?periodo=Enero-junio 2025)
        $periodoSeleccionado = request()->query('periodo', 'Enero-junio 2025');

        // Validar que sea un periodo válido; si no, usar el primero disponible
        if (!in_array($periodoSeleccionado, $periodosDisponibles)) {
            // Verificar si existe en la BD aunque no esté en la lista fija
            $existeEnBD = DB::table('usuario')
                ->where('generacion', $periodoSeleccionado)
                ->exists();
            if (!$existeEnBD) {
                $periodoSeleccionado = 'Enero-junio 2025';
            }
        }

        // Obtener los periodos que SÍ tienen alumnos en la BD (para las pestañas dinámicas)
        $periodosConAlumnos = DB::table('usuario')
            ->select('generacion', DB::raw('COUNT(*) as total'))
            ->whereNotNull('generacion')
            ->where('generacion', '!=', '')
            ->groupBy('generacion')
            ->orderByRaw("FIELD(generacion,
                'Enero-junio 2025',
                'Agosto-diciembre 2025',
                'Enero-junio 2026',
                'Agosto-diciembre 2026',
                'Enero-junio 2027'
            )")
            ->get();

        // ── KPI: Total de Alumnos en el periodo ─────────────────────
        $totalUsuarios = Usuario::where('generacion', $periodoSeleccionado)->count();

        // ── KPI: Actividades únicas con alumnos en ese periodo ───────
        $totalActividades = Usuario::where('generacion', $periodoSeleccionado)
            ->whereNotNull('actividad_extraescolar')
            ->distinct('actividad_extraescolar')
            ->count('actividad_extraescolar');

        $totalTipos = TipoUsuario::count();

        // ── Estados del Periodo (desde historial si existen) ────────
        $totalCursando = \App\Models\HistorialExtraescolar::whereHas('usuario', function ($q) use ($periodoSeleccionado) {
            $q->where('generacion', $periodoSeleccionado);
        })->where('estado', 'Cursando')->count();

        $totalAprobados = \App\Models\HistorialExtraescolar::whereHas('usuario', function ($q) use ($periodoSeleccionado) {
            $q->where('generacion', $periodoSeleccionado);
        })->where('estado', 'Aprobado')->count();

        $totalReprobados = \App\Models\HistorialExtraescolar::whereHas('usuario', function ($q) use ($periodoSeleccionado) {
            $q->where('generacion', $periodoSeleccionado);
        })->where('estado', 'Reprobado')->count();

        // ── Género predominante ──────────────────────────────────────
        $generoPred = Usuario::where('generacion', $periodoSeleccionado)
            ->select('genero', DB::raw('COUNT(*) as total'))
            ->groupBy('genero')
            ->orderByDesc('total')
            ->first();

        // ── Turno predominante ───────────────────────────────────────
        $turnoPred = Usuario::where('generacion', $periodoSeleccionado)
            ->select('turno', DB::raw('COUNT(*) as total'))
            ->groupBy('turno')
            ->orderByDesc('total')
            ->first();

        // ── Actividad predominante ───────────────────────────────────
        $actividadPred = Usuario::where('generacion', $periodoSeleccionado)
            ->whereNotNull('actividad_extraescolar')
            ->join('act_extraesc', 'usuario.actividad_extraescolar', '=', 'act_extraesc.id_act')
            ->select('act_extraesc.nombre as actividad_extraescolar', DB::raw('COUNT(*) as total'))
            ->groupBy('act_extraesc.nombre')
            ->orderByDesc('total')
            ->first();

        // ── Carrera predominante ─────────────────────────────────────
        $carreraPred = Usuario::where('generacion', $periodoSeleccionado)
            ->select('carrera', DB::raw('COUNT(*) as total'))
            ->groupBy('carrera')
            ->orderByDesc('total')
            ->first();

        // ── Gráfica 1: Usuarios por Carrera ─────────────────────────
        $porCarrera = Usuario::where('generacion', $periodoSeleccionado)
            ->select('carrera', DB::raw('COUNT(*) as total'))
            ->groupBy('carrera')
            ->orderByDesc('total')
            ->get();

        // ── Gráfica 2: Usuarios por Género ───────────────────────────
        $porGenero = Usuario::where('generacion', $periodoSeleccionado)
            ->select('genero', DB::raw('COUNT(*) as total'))
            ->groupBy('genero')
            ->get();

        // ── Gráfica 3: Usuarios por Turno ────────────────────────────
        $porTurno = Usuario::where('generacion', $periodoSeleccionado)
            ->select('turno', DB::raw('COUNT(*) as total'))
            ->groupBy('turno')
            ->get();

        // ── Gráfica 4: Usuarios por Generación (todos los periodos) ──
        $porGeneracion = Usuario::select('generacion', DB::raw('COUNT(*) as total'))
            ->whereNotNull('generacion')
            ->groupBy('generacion')
            ->orderBy('generacion')
            ->get();

        // ── Gráfica 5: Usuarios por Tipo ─────────────────────────────
        $porTipo = Usuario::where('generacion', $periodoSeleccionado)
            ->join('tipo_usuario', 'usuario.id_tipo', '=', 'tipo_usuario.id_tipo')
            ->select('tipo_usuario.descripcion as id_tipo', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo_usuario.descripcion')
            ->orderBy('tipo_usuario.descripcion')
            ->get();

        // ── Gráfica 6: Usuarios por Actividad ────────────────────────
        $porActividad = Usuario::where('generacion', $periodoSeleccionado)
            ->whereNotNull('actividad_extraescolar')
            ->join('act_extraesc', 'usuario.actividad_extraescolar', '=', 'act_extraesc.id_act')
            ->select('act_extraesc.nombre as actividad_extraescolar', DB::raw('COUNT(*) as total'))
            ->groupBy('act_extraesc.nombre')
            ->orderByDesc('total')
            ->get();

        // ── Gráfica 7: Estados del Historial ─────────────────────────
        $porEstado = \App\Models\HistorialExtraescolar::whereHas('usuario', function ($q) use ($periodoSeleccionado) {
            $q->where('generacion', $periodoSeleccionado);
        })->select('estado', DB::raw('COUNT(*) as total'))
          ->groupBy('estado')
          ->get();

        return view('cpanel.dashboard', compact(
            'periodoSeleccionado',
            'periodosConAlumnos',
            'totalUsuarios',
            'totalActividades',
            'totalTipos',
            'totalCursando',
            'totalAprobados',
            'totalReprobados',
            'generoPred',
            'turnoPred',
            'actividadPred',
            'carreraPred',
            'porCarrera',
            'porGenero',
            'porTurno',
            'porGeneracion',
            'porTipo',
            'porActividad',
            'porEstado'
        ));
    }
}
