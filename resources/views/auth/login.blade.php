<x-authentication-layout>
    <!-- Header -->
    <div class="mb-8">
        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 py-1 text-xs font-medium text-slate-600 dark:text-slate-300">
            <span class="h-2 w-2 rounded-full bg-[#E56830]"></span>
            Acceso a tu cuenta
        </div>

        <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
            Bienvenido a <span class="text-[#E56830]">Lolocal</span>
        </h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            Inicia sesión para continuar y gestionar tu experiencia.
        </p>
    </div>

    <!-- Card -->
    <div class="rounded-3xl border border-slate-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 backdrop-blur-xl shadow-[0_18px_50px_-20px_rgba(2,6,23,.35)] p-6 sm:p-8">
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email -->
            <div>
                <x-label for="email" value="Correo electrónico" class="text-sm font-semibold text-slate-800 dark:text-slate-100" />

                <div class="mt-2 relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <!-- icon mail -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16v16H4z" opacity=".2"></path>
                            <path d="M4 6l8 7 8-7"></path>
                        </svg>
                    </span>

                    <x-input
                        id="email"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus
                        class="w-full pl-11 rounded-xl border-slate-200 dark:border-white/10 bg-white/80 dark:bg-white/5
                               text-slate-900 dark:text-white placeholder:text-slate-400
                               focus:border-[#E56830] focus:ring-[#E56830]/40"
                        placeholder="tucorreo@empresa.com"
                    />
                </div>
            </div>

            <!-- Password -->
            <div>
                <x-label for="password" value="Contraseña" class="text-sm font-semibold text-slate-800 dark:text-slate-100" />

                <div class="mt-2 relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <!-- icon lock -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M7 11V8a5 5 0 0 1 10 0v3" />
                            <path d="M6 11h12v10H6z" opacity=".2"></path>
                            <path d="M6 11h12v10H6z" />
                        </svg>
                    </span>

                    <x-input
                        id="password"
                        type="password"
                        name="password"
                        required
                        class="w-full pl-11 rounded-xl border-slate-200 dark:border-white/10 bg-white/80 dark:bg-white/5
                               text-slate-900 dark:text-white placeholder:text-slate-400
                               focus:border-[#E56830] focus:ring-[#E56830]/40"
                        placeholder="••••••••"
                    />
                </div>
            </div>

            <!-- Row -->
            <div class="flex items-center justify-between pt-1">
                <label for="remember_me" class="inline-flex items-center gap-2">
                    <input id="remember_me" type="checkbox"
                        class="rounded-md border-slate-300 dark:border-white/10 text-[#E56830] focus:ring-[#E56830]/40"
                        name="remember">
                    <span class="text-sm text-slate-600 dark:text-slate-300">Recordarme</span>
                </label>

                <a class="text-sm font-medium text-[#E56830] hover:text-[#d4551c] hover:underline" href="{{ route('password.request') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <!-- Button -->
            <button
                type="submit"
                class="group relative w-full overflow-hidden rounded-xl bg-[#E56830] px-4 py-3 text-white font-semibold shadow-lg
                       transition hover:bg-[#d4551c] focus:outline-none focus:ring-4 focus:ring-[#E56830]/35"
            >
                <span class="relative z-10 flex items-center justify-center gap-2">
                    Iniciar sesión
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14"></path>
                        <path d="M13 5l7 7-7 7"></path>
                    </svg>
                </span>
                <span class="absolute inset-0 -translate-x-full bg-white/20 transition-transform duration-700 group-hover:translate-x-0"></span>
            </button>

           
        </form>
    </div>
</x-authentication-layout>
