<?php

namespace App\Livewire\Usuarios;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class CrearUsuario extends Component
{
    public $name, $email, $password, $availableRoles = [], $selectedRole = '';
    public $rolePermissions = [];

    protected $rules = [
        'name' => 'required|string|min:3',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
    ];

    public function boot(){
        $this->availableRoles = Role::all();
    }

    
    // public function loadRolePermissions($roleId)
    // {
    //     if ($roleId) {
    //         $role = Role::findOrFail($roleId);
    //         $this->rolePermissions = $role->permissions->pluck('name')->toArray();
    //     } else {
    //         $this->rolePermissions = [];
    //     }
    // }


    public function render()
    {
        return view('livewire.usuarios.crear-usuario');
    }
}
