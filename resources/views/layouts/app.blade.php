<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Lolocal') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400..700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Livewire Styles -->
    @livewireStyles

    <!-- Dark Mode Handler -->
    <script>
        if (localStorage.getItem('dark-mode') === 'false' || !('dark-mode' in localStorage)) {
            document.querySelector('html').classList.remove('dark');
            document.querySelector('html').style.colorScheme = 'light';
        } else {
            document.querySelector('html').classList.add('dark');
            document.querySelector('html').style.colorScheme = 'dark';
        }
    </script>

    <!-- Estilo personalizado con la paleta de Lolocal -->
    <style>
        body {
            background-color: #F6EFEA;
            color: #132742;
        }

        .dark body {
            background-color: #132742;
            color: #F6EFEA;
        }

       
        a:hover {
            color: #c9511d;
        }

        .sidebar-expanded .transition-all {
            transition: all 0.3s ease-in-out;
        }
    </style>
</head>

<body
    class="font-inter antialiased bg-[#F6EFEA] dark:bg-[#132742] text-[#132742] dark:text-[#F6EFEA]"
    :class="{ 'sidebar-expanded': sidebarExpanded }"
    x-data="{ sidebarOpen: false, sidebarExpanded: localStorage.getItem('sidebar-expanded') == 'true' }"
    x-init="$watch('sidebarExpanded', value => localStorage.setItem('sidebar-expanded', value))"
>
    <!-- Forzar clase si el sidebar está expandido -->
    <script>
        if (localStorage.getItem('sidebar-expanded') === 'true') {
            document.body.classList.add('sidebar-expanded');
        } else {
            document.body.classList.remove('sidebar-expanded');
        }
    </script>

    <!-- Toaster (notificaciones) -->
    <x-toaster-hub />

    <!-- Wrapper principal -->
    <div class="flex h-[100dvh] overflow-hidden">

        <!-- Sidebar -->
        <x-app.sidebar :variant="$attributes['sidebarVariant']" />

        <!-- Área de contenido -->
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden @if($attributes['background']){{ $attributes['background'] }}@endif" x-ref="contentarea">

            <!-- Encabezado -->
            <x-app.header :variant="$attributes['headerVariant']" />

            <!-- Contenido principal -->
            <main class="grow bg-[#F6EFEA] dark:bg-[#132742] transition-colors duration-500">
                {{ $slot }}
            </main>
        </div>
    </div>
<script>
  window.ChatWidgetConfig = {
    token: 'wgt_9adce5a0cfa64c90872903de2d222dc3',
    apiUrl: 'https://wa-api.tecnobyteapp.com:1422'
  };
</script>
<script src="https://wa-api.tecnobyteapp.com:1422/widget/chat-widget.js"></script>
    @livewireScripts
</body>
</html>
