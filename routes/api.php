<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\Api\QRController;
use App\Http\Controllers\Api\GeneralApiController;

// Rutas API Generales (Públicas para pruebas)
Route::get('/usuarios', [GeneralApiController::class, 'usuarios']);
Route::get('/docentes', [GeneralApiController::class, 'docentes']);
Route::get('/carreras', [GeneralApiController::class, 'carreras']);
Route::get('/actividades-extraescolares', [GeneralApiController::class, 'actividadesExtraescolares']);
Route::get('/tipos-usuario', [GeneralApiController::class, 'tiposUsuario']);
Route::get('/eventos', [GeneralApiController::class, 'eventos']);
Route::get('/historiales', [GeneralApiController::class, 'historiales']);

// Ruta pública para obtener el token
Route::post('/login', [QRController::class, 'login']);

// Rutas protegidas por token (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    // Ruta para generar el QR
    Route::get('/generar-qr', [QRController::class, 'generarQR']);

    // Ruta de prueba por defecto
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
