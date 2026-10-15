<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Escaleon · Grupo Escanor</title>
        <link rel="icon" type="image/png" href="{{ asset('images/brand/isotipo-escanor.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="escaleon-site escaleon-guest font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <span class="escaleon-brand"><x-application-logo class="w-12 h-12" /><span>escaleon<span class="escaleon-brand-dot">.</span></span></span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
            <img class="escaleon-auth-company" src="{{ asset('images/brand/grupo-escanor.png') }}" alt="Grupo Escanor · Ecosistema integral de soluciones tecnológicas" width="220" height="45">
        </div>
    </body>
</html>
