<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Lolocal') }}</title>

    <link rel="icon" type="image/png" href="https://imagenes.20minutos.es/files/image_990_556/uploads/imagenes/2020/10/15/todos-los-mensajes-y-llamadas-de-whatsapp-estan-cifrados-de-extremo-a-extremo.jpeg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400..700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <script>
        if (localStorage.getItem('dark-mode') === 'false' || !('dark-mode' in localStorage)) {
            document.documentElement.classList.remove('dark');
            document.documentElement.style.colorScheme = 'light';
        } else {
            document.documentElement.classList.add('dark');
            document.documentElement.style.colorScheme = 'dark';
        }
    </script>
</head>

<body class="h-full overflow-hidden font-inter antialiased bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300">
    <main class="relative h-[100dvh] overflow-hidden isolation-isolate flex items-center">
        <!-- Fondo -->
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-24 -left-24 h-72 w-72 sm:h-96 sm:w-96 rounded-full bg-[#E56830]/25 blur-3xl"></div>
            <div class="absolute top-1/4 -right-24 h-80 w-80 sm:h-[28rem] sm:w-[28rem] rounded-full bg-[#132742]/25 blur-3xl"></div>
            <div class="absolute bottom-[-6rem] left-1/3 h-72 w-72 sm:h-96 sm:w-96 rounded-full bg-amber-200/25 dark:bg-amber-400/10 blur-3xl"></div>

            <div class="absolute inset-0 opacity-[0.06] dark:opacity-[0.08]"
                style="background-image: linear-gradient(to right, rgba(15,23,42,.2) 1px, transparent 1px), linear-gradient(to bottom, rgba(15,23,42,.2) 1px, transparent 1px); background-size: 48px 48px;">
            </div>
        </div>

        <div class="relative mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <!-- Menos padding vertical para que NO cree scroll -->
            <div class="py-4 sm:py-6 lg:py-8">
                <!-- Card con altura máxima del viewport -->
                <div class="grid grid-cols-1 overflow-hidden rounded-3xl border border-slate-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 backdrop-blur-xl shadow-[0_20px_60px_-20px_rgba(2,6,23,.35)]
                            max-h-[calc(100dvh-2rem)] sm:max-h-[calc(100dvh-3rem)] lg:max-h-[calc(100dvh-4rem)]
                            lg:grid-cols-2">

                    <!-- Panel Izquierdo (si algo no cabe, scroll interno SOLO aquí) -->
                    <div class="relative flex items-center justify-center p-5 sm:p-8">
                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-white/60 to-transparent dark:from-white/10"></div>

                        <div class="relative w-full max-w-md overflow-y-auto
                                    max-h-[calc(100dvh-8rem)] sm:max-h-[calc(100dvh-9rem)] lg:max-h-[calc(100dvh-6rem)]
                                    pr-1">
                            <div class="[&_a]:break-words [&_*]:break-words">
                                {{ $slot }}
                            </div>
                        </div>
                    </div>

                    <!-- Panel Derecho (solo desktop) -->
                    <div class="relative hidden lg:flex items-center justify-center p-8">
                        <div class="absolute inset-0 bg-gradient-to-br from-[#132742] via-[#132742] to-[#E56830]"></div>

                        <div class="absolute inset-0 opacity-20"
                            style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,.35) 1px, transparent 0);
                                   background-size: 22px 22px;">
                        </div>

                        <div class="relative flex flex-col items-center text-center">
                            <div class="group relative">
                                <div class="absolute -inset-6 rounded-[2.25rem] bg-white/10 blur-2xl opacity-70 transition group-hover:opacity-100"></div>

                                <div class="relative rounded-[2.25rem] bg-white/10 ring-1 ring-white/15 p-3 shadow-2xl">
                                    <img
                                        src="{{ asset('images/lolo.jpeg') }}"
                                        alt="Lolocal"
                                        class="w-[16rem] xl:w-[20rem] rounded-2xl shadow-xl transition duration-500 group-hover:scale-[1.02]"
                                        loading="lazy"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer pegado y sin empujar el layout -->
                <div class="mt-3 text-center text-xs text-slate-500 dark:text-slate-500">
                    © {{ date('Y') }} Lolocal. Todos los derechos reservados.
                </div>
            </div>
        </div>
    </main>

    @livewireScripts
</body>
</html>
