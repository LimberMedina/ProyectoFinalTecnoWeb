<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PedidoOnlineController;
use App\Http\Controllers\EmailVerificationController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas públicas para verificación
Route::post('/verify-email', [EmailVerificationController::class, 'verifyEmail']);

// Verificación de estado de pago para ventas online
// Usar middleware 'web' para tener acceso a sesiones + auth para verificar usuario autenticado
Route::middleware(['web', 'auth'])->get('/ventas/{ventaId}/verificar-pago', [PedidoOnlineController::class, 'verificarEstadoPago']);
