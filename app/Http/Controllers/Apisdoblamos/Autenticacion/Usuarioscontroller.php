<?php

namespace App\Http\Controllers\Apisdoblamos\Autenticacion;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class Usuarioscontroller extends Controller
{
    // Listar todos los usuarios con sus roles
    public function index()
    {
        try {
            $usuarios = User::with('roles')->get();

            if ($usuarios->isEmpty()) {
                throw new \Exception('No hay usuarios registrados en el sistema.');
            }

            return response()->json([
                'message' => 'Usuarios obtenidos correctamente.',
                'data' => $usuarios
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al obtener los usuarios.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Crear nuevo usuario
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|max:255',
                'selectedRole' => 'required|exists:roles,id',
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'name.string' => 'El nombre debe ser una cadena de texto.',
                'name.max' => 'El nombre no debe exceder los 255 caracteres.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El formato del correo electrónico no es válido.',
                'email.unique' => 'Este correo ya está registrado.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.string' => 'La contraseña debe ser una cadena de texto.',
                'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
                'password.max' => 'La contraseña no debe exceder los 255 caracteres.',
                'selectedRole.required' => 'Debe seleccionar un rol.',
                'selectedRole.exists' => 'El rol seleccionado no es válido.',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'activo' => true,
            ]);

            if (!$user->assignRole((int) $validated['selectedRole'])) {
                throw new \Exception('No se pudo asignar el rol al usuario.');
            }

            return response()->json([
                'message' => 'Usuario creado correctamente',
                'user' => $user->load('roles')
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al crear el usuario.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Editar usuario
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => "required|email|unique:users,email,{$id}",
                'selectedRole' => 'required|integer',
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'name.string' => 'El nombre debe ser una cadena de texto.',
                'name.max' => 'El nombre no debe exceder los 255 caracteres.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El formato del correo no es válido.',
                'email.unique' => 'Este correo ya está en uso por otro usuario.',
                'selectedRole.required' => 'Debe seleccionar un rol.',
                'selectedRole.integer' => 'El ID del rol debe ser un número entero.',
            ]);

            $rol = Role::find((int) $validated['selectedRole']);
            if (!$rol) {
                throw new \Exception('El rol seleccionado no existe en el sistema.');
            }

            $user = User::find($id);
            if (!$user) {
                throw new \Exception("El usuario no existe o no está disponible.");
            }

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            $user->syncRoles([$rol->id]);

            return response()->json([
                'message' => 'Usuario actualizado correctamente.',
                'user' => $user->load('roles')
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al actualizar el usuario.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Cambiar contraseña
    public function updatePassword(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ], [
                'password.required' => 'La contraseña es obligatoria.',
                'password.confirmed' => 'La confirmación de la contraseña no coincide.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            ]);

            $user = User::find($id);

            if (!$user) {
                throw new \Exception("El usuario no existe o no está disponible.");
            }

            $user->password = Hash::make($validated['password']);
            $user->save();

            return response()->json([
                'message' => 'Contraseña actualizada correctamente.'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al actualizar la contraseña.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Activar o desactivar usuario
   public function toggleEstado($id)
{
    try {
        $user = User::find($id);

        if (!$user) {
            throw new \Exception("El usuario no existe o no está disponible.");
        }

        $user->activo = !$user->activo;
        $user->save();

        return response()->json([
            'message' => 'Estado actualizado correctamente.',
            'estado' => $user->activo ? 'Activo' : 'Inactivo',
            'activo' => $user->activo
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Ocurrió un error al actualizar el estado del usuario.',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
