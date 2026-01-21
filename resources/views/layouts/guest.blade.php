<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Kaafiye WIFI') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased bg-gray-100 text-gray-900">
        <div class="min-h-screen flex items-center justify-center px-4 py-8">
            {{-- Hal container oo keliya (NO Laravel logo, NO outer extra card) --}}
            <div class="w-full max-w-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
