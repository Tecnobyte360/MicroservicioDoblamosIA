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

     
        $rolAdministrador = Role::firstOrCreate(
            ['name' => 'Administrador'],
            ['descripcion' => 'Rol con acceso total al sistema']
        );

        
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => 'Stiven Madrid',
                'password' => Hash::make($password),
                'activo'   => true,
            ]
        );

        
        if (!$user->roles()->where('roles.id', $rolAdministrador->id)->exists()) {
            $user->roles()->attach($rolAdministrador->id);
        }

        $this->command->info(' Usuario Administrador creado/actualizado correctamente');
    }
}
