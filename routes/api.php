<?php

use App\Http\Controllers\Api\ApisIA\PoliticasLogisticas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Apisdoblamos\Autenticacion\LoginSanctumController;
use App\Http\Controllers\Apisdoblamos\Autenticacion\UsuariosController;
use App\Http\Controllers\Api\SAPDoblamos\InventarioDisponibleController;
use App\Http\Controllers\WolkvoxWebhookController;

Route::post('/login', [LoginSanctumController::class, 'login']);
Route::post('/logout', [LoginSanctumController::class, 'logout'])->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', fn(Request $request) => $request->user());

    // Usuarios
    Route::get('/usuarios', [UsuariosController::class, 'index']);
    Route::post('/usuarios', [UsuariosController::class, 'store']);
    Route::put('/usuarios/{id}', [UsuariosController::class, 'update']);
    Route::patch('/usuarios/{id}/password', [UsuariosController::class, 'updatePassword']);
    Route::patch('/usuarios/{id}/estado', [UsuariosController::class, 'toggleEstado']);
    Route::get('/sap/inventario-disponible', [InventarioDisponibleController::class, 'index']);
    Route::get('/sap/inventario-disponible/query', [InventarioDisponibleController::class, 'query']);
    Route::get('/ia/politicasVentas', [PoliticasLogisticas::class, 'query']);
});

Route::get('/wolkvox/webhook', [WolkvoxWebhookController::class, 'handle']);
