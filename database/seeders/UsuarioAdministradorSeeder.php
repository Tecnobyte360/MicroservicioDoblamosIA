<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioAdministradorSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'stivenmadrid6@gmail.com';
        $password = 'THUU79**++R';

        // 1) Crear o buscar rol Administrador (SOLO name)
        $rolAdministrador = Role::firstOrCreate([
            'name' => 'Administrador',
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

        // 3) Asignar rol si no lo tiene
        if (!$user->roles()->where('roles.id', $rolAdministrador->id)->exists()) {
            $user->roles()->attach($rolAdministrador->id);
        }

        $this->command->info('✅ Usuario Administrador creado/actualizado y rol asignado.');
    }
}
