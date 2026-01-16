@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@endpush

<div
    x-data="{
        isVisibleEditUserModal: $wire.entangle('isVisibleEditUserModal').live,
        isVisibleChangePassUserModal: $wire.entangle('isVisibleChangePassUserModal').live,
        isVisibleCreateUserModal: $wire.entangle('isVisibleCreateUserModal').live,
    }"
>

    <!-- Alerta de mensaje -->
    @if ($mensaje)
    <div 
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 4000)"
        class="mb-6 px-6 py-3 rounded-xl shadow text-sm text-center transition-all duration-300
        {{ $tipoMensaje === 'exito' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }}">
        {{ $mensaje }}
    </div>
    @endif

    <!-- Contenedor principal -->
    <div class="p-10 bg-gradient-to-br from-white via-gray-50 to-gray-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 rounded-3xl shadow-2xl space-y-12">

        <!-- Sección: Información General -->
        <section class="p-8 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl shadow-xl space-y-10">

            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-600 pb-4">
                <h2 class="text-2xl font-extrabold text-gray-800 dark:text-white tracking-wide">👥 Información General de Usuarios</h2>
                <button 
                    wire:click="openNewUser"
                    class="bg-[#E56830] hover:bg-[#d35428] text-white font-semibold py-2 px-4 rounded-lg shadow transition-all flex items-center gap-2">
                    <i class="fas fa-user-plus"></i> <span class="hidden sm:inline">Nuevo Usuario</span>
                </button>
            </div>

            <!-- Tabla de usuarios -->
            <div class="overflow-auto rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-white via-gray-50 to-gray-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
                <table class="min-w-full text-sm text-gray-700 dark:text-gray-300">
                    <thead class="bg-[#F6EFEA] dark:bg-[#1d2a45] text-[#132742] dark:text-[#F6EFEA] uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-5 py-3 font-bold"><i class="fas fa-id-badge mr-1"></i> Código</th>
                            <th class="px-5 py-3 font-bold"><i class="fas fa-user-tag mr-1"></i> Nombre</th>
                            <th class="px-5 py-3 font-bold"><i class="fas fa-envelope mr-1"></i> Email</th>
                            <th class="px-5 py-3 font-bold"><i class="fas fa-user-shield mr-1"></i> Rol</th>
                            <th class="px-5 py-3 font-bold"><i class="fas fa-cogs mr-1"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($usuarios as $usuario)
                        <tr class="hover:bg-[#E56830]/10 dark:hover:bg-gray-800 transition duration-200">
                            <td class="px-5 py-3 text-center font-bold text-[#132742] dark:text-white">
                                {{ $usuario->id }}
                            </td>
                            <td class="px-5 py-3 font-medium">
                                {{ $usuario->name }}
                            </td>
                            <td class="px-5 py-3 hidden sm:table-cell">
                                {{ $usuario->email }}
                            </td>
                            <td class="px-5 py-3 hidden sm:table-cell">
                                {{ $usuario->roles->first()->name ?? 'N/A' }}
                            </td>
                            <td class="px-5 py-3 flex justify-center space-x-3">
                                <button wire:click="editarUsuario({{ $usuario->id }})"
                                    class="text-blue-500 hover:text-blue-700 transition transform hover:scale-110"
                                    title="Editar usuario">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="changepassworduser({{ $usuario->id }})"
                                    class="text-yellow-500 hover:text-yellow-600 transition transform hover:scale-110"
                                    title="Cambiar contraseña">
                                    <i class="fas fa-key"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </section>
    </div>


    <!-- Componente modal de cambio de contraseña -->
    <div 
        x-show="isVisibleEditUserModal" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90" id="isVisibleEditUserModal"
        class="fixed inset-0 flex items-center justify-center z-50 backdrop-blur-sm bg-black/40 dark:bg-gray-900/60"
        style="display: none;"
    >
        @if($isVisibleEditUserModal)
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 w-full max-w-md">
                <h2 class="text-2xl font-bold mb-5 text-gray-800 dark:text-white text-center">
                    <i class="fas fa-user-plus text-violet-600 mr-2"></i> Editar
                </h2>

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

                    @error('name') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Email --}}
                <div class="relative mt-4">
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

                    @error('email') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Rol --}}
                <div class="relative mt-4">
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

                    @error('selectedRole') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Botones --}}
                <div class="flex justify-end space-x-3 pt-6">
                    <button wire:click="$toggle('isVisibleEditUserModal')"
                        class="px-4 py-2 bg-gray-300 rounded-xl text-sm hover:bg-gray-400 transition">
                        Cancelar
                    </button>
                    <button wire:click="saveEditUsuario"
                        class="px-4 py-2 bg-violet-600 text-white rounded-xl text-sm hover:bg-violet-700 transition">
                        Guardar
                    </button>
                </div>
            </div>
        @endif

    </div>


<div 
    x-show="isVisibleChangePassUserModal" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-90" id="isVisibleChangePassUserModal"
    class="fixed inset-0 flex items-center justify-center z-50 backdrop-blur-sm bg-black/40 dark:bg-gray-900/60"
    style="display: none;"
>
@if($isVisibleChangePassUserModal)
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 w-full max-w-md">
        <h2 class="text-2xl font-bold mb-5 text-gray-800 dark:text-white text-center">
            <i class="fas fa-key text-violet-600 mr-2"></i> Cambiar contraseña
        </h2>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nueva contraseña</label>
                <input type="password" wire:model.defer="nuevaPassword" class="w-full mt-1 rounded-xl border-gray-300 dark:bg-gray-700 dark:text-white shadow-inner focus:ring-2 focus:ring-violet-500">
                @error('nuevaPassword') 
                    <span class="text-red-500 text-sm">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmar contraseña</label>
                <input type="password" wire:model.defer="nuevaPassword_confirmation" class="w-full mt-1 rounded-xl border-gray-300 dark:bg-gray-700 dark:text-white shadow-inner focus:ring-2 focus:ring-violet-500">
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <button wire:click="$toggle('isVisibleChangePassUserModal')" class="px-4 py-2 bg-gray-300 rounded-xl text-sm">Cancelar</button>
            <button wire:click="cambiarpass()" class="px-4 py-2 bg-violet-600 text-white rounded-xl text-sm hover:bg-violet-700">Guardar</button>
        </div>

        @if (session()->has('mensaje'))
            <div class="mt-4 text-green-600 text-sm text-center">
                {{ session('mensaje') }}
            </div>
        @endif
    </div>
@endif
</div>



<div 

    x-show="isVisibleCreateUserModal" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-90" id="isVisibleCreateUserModal"
    class="fixed inset-0 flex items-center justify-center z-50 backdrop-blur-sm bg-black/40 dark:bg-gray-900/60"
    style="display: none;"
>
@if($isVisibleCreateUserModal)
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
 <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 w-full max-w-md">
    <h2 class="text-2xl font-bold mb-5 text-gray-800 dark:text-white text-center">
        <i class="fas fa-user-plus text-violet-600 mr-2"></i> Crear nuevo usuario
    </h2>

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

        @error('name') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
    </div>

    {{-- Email --}}
    <div class="relative mt-4">
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

        @error('email') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
    </div>

    {{-- Contraseña --}}
    <div class="relative mt-4">
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

        @error('password') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
    </div>

    {{-- Rol --}}
    <div class="relative mt-4">
        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Rol Seleccionado</label>
        <select wire:model="selectedRole" wire:change="loadRolePermissions($event.target.value)"
            class="w-full px-4 py-3 rounded-2xl border transition-all shadow-inner
            @error('selectedRole') border-red-500 bg-red-50 focus:ring-red-300 focus:border-red-500
            @else border-gray-300 focus:ring-violet-300 focus:border-violet-500 @enderror
            dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-4">
            <option disabled selected value="">Seleccione un rol</option>
            @foreach($availableRoles as $role)
                <option value="{{ $role->id }}">{{ $role->name }}</option>
            @endforeach
        </select>

        @error('selectedRole') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
    </div>

    {{-- Botones --}}
    <div class="flex justify-end space-x-3 pt-6">
        <button wire:click="$toggle('isVisibleCreateUserModal')"
            class="px-4 py-2 bg-gray-300 rounded-xl text-sm hover:bg-gray-400 transition">
            Cancelar
        </button>
        <button wire:click="guardarUsuario"
            class="px-5 py-2 bg-gray-600 text-white rounded-xl text-sm hover:bg-gray-700 shadow-md flex items-center gap-2 transition">
            <i class="fas fa-save"></i>
            Guardar
        </button>
    </div>
</div>


    </div>


@endif
    </div>
</div>




</div>

</div>