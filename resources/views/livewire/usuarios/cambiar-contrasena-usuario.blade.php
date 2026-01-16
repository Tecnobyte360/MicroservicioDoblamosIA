<div 
    x-data="{ open: false }" 
    @mostrar-modal-contrasena.window="open = true" 
    @cerrar-modal.window="open = false"
    x-show="open"
    x-transition.opacity
    class="fixed inset-0 flex items-center justify-center z-50 backdrop-blur-sm bg-black/40 dark:bg-gray-900/60"
    style="display: none;"
    wire:key="modal-cambiar-contrasena-{{ $usuarioId }}"
>
    <div @click.away="open = false" class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 w-full max-w-md">
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
            <button @click="open = false" class="px-4 py-2 bg-gray-300 rounded-xl text-sm">Cancelar</button>
            <button wire:click="cambiar" class="px-4 py-2 bg-violet-600 text-white rounded-xl text-sm hover:bg-violet-700">Guardar</button>
        </div>

        @if (session()->has('mensaje'))
            <div class="mt-4 text-green-600 text-sm text-center">
                {{ session('mensaje') }}
            </div>
        @endif
    </div>
</div>
