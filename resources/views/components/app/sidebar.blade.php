<div class="min-w-fit">
    <!-- Backdrop móvil -->
    <div
        class="fixed inset-0 bg-black/40 z-40 lg:hidden transition-opacity duration-300"
        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'"
        aria-hidden="true"
        x-cloak
    ></div>

    <!-- Sidebar -->
    <div
        id="sidebar"
        class="flex flex-col absolute z-40 left-0 top-0 lg:static lg:translate-x-0 h-[100dvh] overflow-y-auto no-scrollbar w-64 lg:w-20 lg:sidebar-expanded:w-64 2xl:w-64 shrink-0
               bg-gradient-to-b from-[#F6EFEA] via-[#ffffff] to-[#e7e7e7] dark:from-[#132742] dark:via-[#1d2a45] dark:to-[#1f2937]
               p-4 transition-all duration-300 ease-in-out border-r border-gray-200 dark:border-gray-700/60 rounded-r-3xl shadow-2xl"
        :class="sidebarOpen ? 'max-lg:translate-x-0' : 'max-lg:-translate-x-64'"
        @click.outside="sidebarOpen = false"
        @keydown.escape.window="sidebarOpen = false"
    >

        <!-- Encabezado con Título y Logo -->
        <div class="flex flex-col items-center mb-10 px-3 py-3 rounded-2xl shadow-lg bg-white/60 dark:bg-white/5 backdrop-blur-md space-y-3">
            <!-- Botón cerrar (móvil) -->
            <div class="w-full flex justify-between items-center">
                <button 
                    class="lg:hidden text-gray-600 dark:text-gray-300 hover:text-red-500 transition-transform hover:scale-110"
                    @click.stop="sidebarOpen = !sidebarOpen" 
                    aria-controls="sidebar"
                    :aria-expanded="sidebarOpen"
                >
                    <span class="sr-only">Cerrar menú lateral</span>
                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                        <path d="M10.7 18.7l1.4-1.4L7.8 13H20v-2H7.8l4.3-4.3-1.4-1.4L4 12z" />
                    </svg>
                </button>
            </div>

            <!-- Título LOLOCAL -->
            <h1 class="text-2xl font-extrabold tracking-wide text-[#E56830] dark:text-[#F6EFEA] uppercase">
                LOLOCAL
            </h1>

            <!-- Logo -->
            <a href="{{ route('dashboard') }}"
               class="block px-4 py-2 rounded-2xl bg-white/50 dark:bg-white/10 shadow-xl hover:scale-105 transition-all duration-300">
                <img src="{{ asset('images/lolo.jpeg') }}" alt="Logo Lolocal" class="h-16 w-auto mx-auto drop-shadow-md rounded-xl">
            </a>
        </div>

        <!-- Navegación -->
        <div class="space-y-10">
            <!-- Módulos -->
            <div>
                <h3 class="text-xs uppercase text-[#E56830] dark:text-[#F6EFEA] font-semibold pl-3 tracking-widest">Módulos</h3>
                <ul class="mt-4 space-y-1">
                    <li class="group">
                        <a href="{{ route('dashboard') }}"
                           class="flex items-center gap-3 pl-5 pr-4 py-3 rounded-xl text-[#132742] dark:text-[#F6EFEA] hover:bg-[#E56830]/10 dark:hover:bg-[#F6EFEA]/10 hover:shadow-md transition-all">
                            <i class="fas fa-house text-[#E56830] text-lg animate-pulse"></i>
                            <span class="text-sm font-semibold lg:hidden lg:sidebar-expanded:block">Dashboard</span>
                        </a>
                    </li>

                    <!-- Proveedores -->
                    <li x-data="{ open: {{ in_array(Request::segment(1), ['ecommerce']) ? 1 : 0 }} }">
                        <a href="#" @click.prevent="open = !open; sidebarExpanded = true"
                           class="flex items-center justify-between pl-5 pr-4 py-3 rounded-xl text-[#132742] dark:text-[#F6EFEA] hover:bg-[#E56830]/10 dark:hover:bg-[#F6EFEA]/10 hover:shadow-md transition-all">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-boxes-stacked text-[#E56830] text-lg"></i>
                                <span class="text-sm font-semibold lg:hidden lg:sidebar-expanded:block">Proveedores</span>
                            </div>
                            <svg class="w-3 h-3 transition-transform duration-300" :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                            </svg>
                        </a>
                        <div x-show="open" class="pl-10 mt-2 space-y-1">
                            <a href="{{ route('Evaluacion-Proveedores') }}"
                               class="block text-sm text-[#132742]/70 dark:text-[#F6EFEA]/70 hover:text-[#E56830] dark:hover:text-[#E56830] transition">
                                Evaluación Proveedor
                            </a>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Ajustes -->
            <div>
                <h3 class="text-xs uppercase text-[#E56830] dark:text-[#F6EFEA] font-semibold pl-3 tracking-widest">Ajustes</h3>
                <ul class="mt-4 space-y-1">
                    <li x-data="{ open: {{ in_array(Request::segment(1), ['settings']) ? 1 : 0 }} }">
                        <a href="#" @click.prevent="open = !open; sidebarExpanded = true"
                           class="flex items-center justify-between pl-5 pr-4 py-3 rounded-xl text-[#132742] dark:text-[#F6EFEA] hover:bg-[#E56830]/10 dark:hover:bg-[#F6EFEA]/10 hover:shadow-md transition-all">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-gear text-[#E56830] text-lg"></i>
                                <span class="text-sm font-semibold lg:hidden lg:sidebar-expanded:block">Configuración</span>
                            </div>
                            <svg class="w-3 h-3 transition-transform duration-300" :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                            </svg>
                        </a>
                        <div x-show="open" class="pl-10 mt-2 space-y-1">
                            <a href="{{ route('Usuarios') }}" class="block text-sm text-[#132742]/70 dark:text-[#F6EFEA]/70 hover:text-[#E56830] dark:hover:text-[#E56830] transition">Usuarios</a>
                            <a href="{{ route('roles.index') }}" class="block text-sm text-[#132742]/70 dark:text-[#F6EFEA]/70 hover:text-[#E56830] dark:hover:text-[#E56830] transition">Roles</a>
                            <a href="{{ route('roles.index2') }}" class="block text-sm text-[#132742]/70 dark:text-[#F6EFEA]/70 hover:text-[#E56830] dark:hover:text-[#E56830] transition">Permisos</a>
                            <a href="{{ route('sap.ConfiguracionSAP') }}" class="block text-sm text-[#132742]/70 dark:text-[#F6EFEA]/70 hover:text-[#E56830] dark:hover:text-[#E56830] transition">Conexiones SAP</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

       
    </div>
</div>
