{{-- resources/views/layouts/admin.blade.php --}}

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') - KaafiyeWiFi</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full">
<div class="h-screen overflow-hidden">

    @include('partials.admin-sidebar')

    <div id="appShell"
         data-sidebar="expanded"
         class="h-screen transition-[margin] duration-200 ease-in-out
                lg:ml-64 lg:[data-sidebar=collapsed]:ml-20">

        {{-- Grid: navbar row + content row --}}
        <div class="h-screen grid grid-rows-[64px_1fr]">

            {{-- Navbar (no scroll) --}}
            <div class="row-start-1 row-end-2 z-[60]
                        bg-white dark:bg-gray-900
                        border-b border-gray-200 dark:border-gray-800">
                @include('partials.admin-navbar')
            </div>

            {{-- Content scroll only when needed --}}
            <main id="main" class="row-start-2 row-end-3 overflow-y-auto relative z-0 scrollbar-soft">
                <div class="px-4 sm:px-6 py-8">
                    <div class="w-full max-w-6xl mx-auto">
                        @yield('content')
                    </div>
                </div>
            </main>

        </div>
    </div>
</div>
</body>
</html>
