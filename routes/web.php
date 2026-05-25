<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\TipoUsuariosController;
use App\Http\Controllers\ActExtraescolarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\ActividadController;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\DocentePortalController;
use App\Http\Controllers\ChatbotController;

// RUTA POR DEFECTO: Si entra en /, que vaya al panel directamente (o a login si no está logeado gracias al middleware)
Route::redirect('/', '/admon');

// --- RUTA PÚBLICA DE VALIDACIÓN ---
Route::get('/validar-constancia/{num_control}', [\App\Http\Controllers\ConstanciaController::class, 'validarPublica'])->name('validar.constancia');

// --- RUTAS DE AUTENTICACIÓN (LOGIN) ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- RUTAS DEL PANEL PROTEGIDAS POR AUTH ---
Route::prefix('admon')->middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/resumen', [DashboardController::class, 'resumen'])->name('resumen');

    // MI PERFIL
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::post('/perfil/actualizar-foto', [PerfilController::class, 'actualizarFoto'])->name('perfil.foto');
    Route::post('/perfil/actualizar-datos', [PerfilController::class, 'actualizarDatos'])->name('perfil.datos');

    // CRUD COMPLETO DE USUARIOS (ESTUDIANTES)
    Route::get('/reportes/usuarios-data', [UsuariosController::class, 'reportData'])->name('reportes.usuarios-data');
    Route::resource('usuarios', UsuariosController::class);

    // CRUD COMPLETO DE ADMINISTRADORES
    Route::resource('administradores', \App\Http\Controllers\AdministradorController::class);

    Route::resource('tipousuarios', TipoUsuariosController::class);


    Route::get('/tipousuarios', [TipoUsuariosController::class, 'index'])
        ->name('tipousuarios.index');

    Route::resource('actextraescolar', ActExtraescolarController::class);
    
    // Validar calificaciones (Admin)
    Route::get('/actextraescolar/{id}/calificaciones', [ActExtraescolarController::class, 'calificaciones'])->name('actextraescolar.calificaciones');
    Route::post('/actextraescolar/validar-calificacion', [ActExtraescolarController::class, 'validarCalificacion'])->name('actextraescolar.validar');

    // CRUD DE CARRERAS
    Route::resource('carreras', CarreraController::class);

    // --- EVENTOS (Antes Grupos) ---
    Route::resource('actividades', ActividadController::class);
    
    // --- CONSTANCIAS ---
    Route::get('/constancias', [\App\Http\Controllers\ConstanciaController::class, 'index'])->name('constancias.index');
    Route::get('/constancias/pdf/{num_control}', [\App\Http\Controllers\ConstanciaController::class, 'generarPdf'])->name('constancias.pdf');
    Route::post('/constancias/toggle-descarga/{num_control}', [\App\Http\Controllers\ConstanciaController::class, 'toggleDescarga'])->name('constancias.toggleDescarga');

    // CRUD DE DOCENTES
    Route::resource('docentes', DocenteController::class);

    // Toggle status
    Route::post('/tipousuarios/{id}/toggle', [TipoUsuariosController::class, 'toggle'])
        ->name('tipousuarios.toggle');
    Route::post('/actextraescolar/{actextraescolar}/toggle', [ActExtraescolarController::class, 'toggle'])
        ->name('actextraescolar.toggle');
    Route::post('/actextraescolar/{actextraescolar}/toggle-inscripcion', [ActExtraescolarController::class, 'toggleInscripcion'])
        ->name('actextraescolar.toggleInscripcion');
    Route::post('/actextraescolar/{actextraescolar}/cerrar-parcial', [ActExtraescolarController::class, 'cerrarParcial'])
        ->name('actextraescolar.cerrarParcial');
    Route::post('/carreras/{carrera}/toggle', [CarreraController::class, 'toggle'])
        ->name('carreras.toggle');
    Route::post('/docentes/{docente}/toggle', [DocenteController::class, 'toggle'])
        ->name('docentes.toggle');


});

// --- RUTAS DEL ESTUDIANTE PROTEGIDAS POR AUTH ---
Route::prefix('estudiante')->middleware('auth')->group(function () {
    Route::get('/', [EstudianteController::class, 'index'])->name('estudiante.index');
    Route::post('/inscribir', [EstudianteController::class, 'inscribir'])->name('estudiante.inscribir');
    Route::post('/firmar-conformidad/{id}', [EstudianteController::class, 'firmarConformidad'])->name('estudiante.firmar');
});

// --- RUTAS DEL DOCENTE PROTEGIDAS POR AUTH ---
Route::middleware('auth')->group(function () {
    Route::get('/docente/portal', [DocentePortalController::class, 'inicio'])->name('docente.index');
    Route::get('/docente/dashboard', [DocentePortalController::class, 'index'])->name('docente.dashboard');
    
    // Eventos CRUD
    Route::get('/docente/eventos', [DocentePortalController::class, 'eventos'])->name('docente.eventos');
    Route::post('/docente/eventos', [DocentePortalController::class, 'guardarEvento'])->name('docente.eventos.guardar');
    Route::put('/docente/eventos/{id}', [DocentePortalController::class, 'actualizarEvento'])->name('docente.eventos.actualizar');
    Route::delete('/docente/eventos/{id}', [DocentePortalController::class, 'eliminarEvento'])->name('docente.eventos.eliminar');
    Route::get('/docente/eventos/{id}/pdf', [DocentePortalController::class, 'generarEventosPdf'])->name('docente.eventos.pdf');
    Route::get('/docente/eventos/{id}/excel', [DocentePortalController::class, 'generarEventosExcel'])->name('docente.eventos.excel');

    // Horarios CRUD
    Route::get('/docente/horarios', [DocentePortalController::class, 'horarios'])->name('docente.horarios');

    Route::get('/docente/grupo/{id_act}', [DocentePortalController::class, 'grupo'])->name('docente.grupo');
    Route::post('/docente/grupo/{id_act}/parcial', [DocentePortalController::class, 'guardarParcial'])->name('docente.guardarParcial');

    // --- CHATBOT ---
    Route::post('/chatbot/mensaje', [ChatbotController::class, 'sendMessage'])->name('chatbot.mensaje');
});
