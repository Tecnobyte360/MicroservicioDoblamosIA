<?php

namespace App\Livewire\Usuarios;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class CambiarContrasenaUsuario extends Component
{
    public $usuarioId;
    public $nuevaPassword = '';
    public $nuevaPassword_confirmation = '';

    protected $listeners = ['mostrarFormularioContrasena' => 'abrirModal'];

    protected $rules = [
        'nuevaPassword' => 'required|string|min:8|confirmed',
    ];

    protected $messages = [
        'nuevaPassword.confirmed' => 'La confirmación de la contraseña no coincide.',
        'nuevaPassword.min' => 'La contraseña debe tener al menos 8 caracteres.',
    ];

    public function abrirModal($id)
    {
        $this->resetValidation();
        $this->usuarioId = $id;
        $this->reset(['nuevaPassword', 'nuevaPassword_confirmation']);
    }

    public function cambiar()
    {
        $this->validate();

        $usuario = User::findOrFail($this->usuarioId);
        $usuario->password = Hash::make($this->nuevaPassword);
        $usuario->save();

        session()->flash('mensaje', 'Contraseña actualizada correctamente.');
        $this->dispatchBrowserEvent('cerrar-modal');
    }

    public function render()
    {
        return view('livewire.usuarios.cambiar-contrasena-usuario');
    }
}
