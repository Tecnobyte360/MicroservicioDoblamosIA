<?php

namespace App\Http\Controllers\Apisdoblamos\Autenticacion;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;


class LoginSanctumController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ], [
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El formato del correo no es válido.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            ]);

            if (!Auth::attempt($credentials)) {
                throw new \Exception('Credenciales inválidas.');
            }

            $user = $request->user();
            $token = $user->createToken('lolocal-token')->plainTextToken;
            $expiration = config('sanctum.expiration');

            return response()->json([
                'message' => 'Autenticación exitosa.',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->toArray(),
                ],
                'expires_in' => $expiration ? $expiration * 60 : null,
                'expires_at' => $expiration ? now()->addMinutes($expiration)->toIso8601String() : null
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error durante el inicio de sesión.',
                'error' => $e->getMessage()
            ], 401);
        }
    }


    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user || !$user->currentAccessToken()) {
                throw new \Exception('No se encontró un token activo para cerrar sesión.');
            }

            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Sesión cerrada correctamente.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al cerrar la sesión.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
