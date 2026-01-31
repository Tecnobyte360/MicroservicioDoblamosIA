<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ApisIA\PoliticasLogisticas;
use App\Http\Controllers\Apisdoblamos\Autenticacion\LoginSanctumController;
use App\Http\Controllers\Apisdoblamos\Autenticacion\UsuariosController;
use App\Http\Controllers\Api\SAPDoblamos\InventarioDisponibleController;
use App\Http\Controllers\WolkvoxWebhookController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

// Auth
Route::post('/login', [LoginSanctumController::class, 'login']);

// 👉 ESTA ES LA QUE USAS EN "LINK DE INTEGRACIÓN" DE WOLKVOX
Route::get('/wolkvox/view', [WolkvoxWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [LoginSanctumController::class, 'logout']);

    Route::get('/user', fn (Request $request) => $request->user());

    // Usuarios
    Route::get('/usuarios', [UsuariosController::class, 'index']);
    Route::post('/usuarios', [UsuariosController::class, 'store']);
    Route::put('/usuarios/{id}', [UsuariosController::class, 'update']);
    Route::patch('/usuarios/{id}/password', [UsuariosController::class, 'updatePassword']);
    Route::patch('/usuarios/{id}/estado', [UsuariosController::class, 'toggleEstado']);

    // SAP
    Route::get('/sap/inventario-disponible', [InventarioDisponibleController::class, 'index']);
    Route::get('/sap/inventario-disponible/query', [InventarioDisponibleController::class, 'query']);

    // IA
    Route::get('/ia/politicasVentas', [PoliticasLogisticas::class, 'query']);
});
