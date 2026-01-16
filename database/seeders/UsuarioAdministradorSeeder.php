<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioAdministradorSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'stivenmadrid6@gmail.com';
        $password = 'THUU79**++R';

        // Sanctum normalmente usa guard 'web'
        $guard = 'web';

        // 1) Crear o buscar rol Administrador (con guard_name obligatorio)
        $rolAdministrador = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => $guard,
        ]);

        // 2) Crear o buscar usuario
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => 'Stiven Madrid',
                'password' => Hash::make($password),
                'activo'   => true,
            ]
        );

        // 3) Asignar rol con Spatie
        if (!$user->hasRole($rolAdministrador->name)) {
            $user->assignRole($rolAdministrador);
        }

        $this->command->info(' Usuario Administrador creado/actualizado y rol asignado (Spatie).');
    }
}
