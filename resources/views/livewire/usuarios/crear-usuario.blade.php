

<div 

    x-data="{ open: false }"
    @mostrar-modal-crear-usuario.window="open = true"
    @usuario-creado.window="open = false"
    x-show="open"
    x-transition.opacity
    class="fixed inset-0 flex items-center justify-center z-50 backdrop-blur-sm bg-black/40 dark:bg-gray-900/60"
    style="display: none;"
>
@if (session()->has('mensaje'))
    <div class="mt-4 text-green-600 bg-green-100 dark:bg-green-900 dark:text-green-300 text-sm text-center px-4 py-2 rounded-xl shadow">
        {{ session('mensaje') }}
    </div>
@endif

@if (session()->has('error'))
    <div class="mt-4 text-red-600 bg-red-100 dark:bg-red-900 dark:text-red-300 text-sm text-center px-4 py-2 rounded-xl shadow">
        {{ session('error') }}
    </div>
@endif
    <div @click.away="open = false" class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 w-full max-w-md">
   <h2 class="text-2xl font-bold mb-5 text-red-600 dark:text-white text-center">
    <i class="fas fa-user-plus mr-2"></i> Crear nuevo usuario
</h2>



      <form wire:submit.prevent="guardarUsuario" class="space-y-5">

    {{-- Nombre --}}
    <div class="relative">
        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Nombre *</label>
        <input type="text" wire:model.lazy="name"
            class="w-full px-4 py-3 rounded-2xl border transition-all shadow-inner
            @error('name') border-red-500 focus:ring-red-300 focus:border-red-500
            @else border-gray-300 focus:ring-violet-300 focus:border-violet-500 @enderror
            dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-4"
            placeholder="Ingresa el nombre completo">

        @if(!empty($name) && !$errors->has('name'))
            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                <i class="fas fa-check-circle text-green-500 text-lg"></i>
            </div>
        @endif

        @error('name')
            <span class="text-red-600 text-xs">{{ $message }}</span>
        @enderror
    </div>

    {{-- Email --}}
    <div class="relative">
        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Correo electrónico *</label>
        <input type="email" wire:model.lazy="email"
            class="w-full px-4 py-3 rounded-2xl border transition-all shadow-inner
            @error('email') border-red-500 focus:ring-red-300 focus:border-red-500
            @else border-gray-300 focus:ring-violet-300 focus:border-violet-500 @enderror
            dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-4"
            placeholder="ejemplo@correo.com">

        @if(!empty($email) && !$errors->has('email'))
            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                <i class="fas fa-check-circle text-green-500 text-lg"></i>
            </div>
        @endif

        @error('email')
            <span class="text-red-600 text-xs">{{ $message }}</span>
        @enderror
    </div>

    {{-- Contraseña --}}
    <div class="relative">
        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Contraseña *</label>
        <input type="password" wire:model.lazy="password"
            class="w-full px-4 py-3 rounded-2xl border transition-all shadow-inner
            @error('password') border-red-500 focus:ring-red-300 focus:border-red-500
            @else border-gray-300 focus:ring-violet-300 focus:border-violet-500 @enderror
            dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-4"
            placeholder="Mínimo 6 caracteres">

        @if(!empty($password) && !$errors->has('password'))
            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                <i class="fas fa-check-circle text-green-500 text-lg"></i>
            </div>
        @endif

        @error('password')
            <span class="text-red-600 text-xs">{{ $message }}</span>
        @enderror
    </div>

    {{-- Rol --}}
    <div class="relative">
        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Rol Seleccionado</label>
        <select wire:model="selectedRole"
            class="w-full px-4 py-3 rounded-2xl border transition-all shadow-inner
            @error('selectedRole') border-red-500 bg-red-50 focus:ring-red-300 focus:border-red-500
            @else border-gray-300 focus:ring-violet-300 focus:border-violet-500 @enderror
            dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-4">
            <option disabled selected value="">Seleccione un rol</option>
            @foreach($availableRoles as $role)
                <option value="{{ $role->id }}">{{ $role->name }}</option>
            @endforeach
        </select>
        @error('selectedRole')
            <span class="text-red-600 text-xs">{{ $message }}</span>
        @enderror
    </div>

    {{-- Botones --}}
    <div class="flex justify-end space-x-3 pt-2">
        <button type="button" @click="open = false"
            class="px-4 py-2 bg-gray-300 rounded-xl text-sm hover:bg-gray-400 transition">
            Cancelar
        </button>
        <button type="submit"
            class="px-4 py-2 bg-gray-600 dark:bg-gray-500 text-white rounded-xl text-sm hover:bg-gray-700 dark:hover:bg-gray-600 transition">
            Guardar
        </button>
    </div>

</form>

    </div>
</div>
