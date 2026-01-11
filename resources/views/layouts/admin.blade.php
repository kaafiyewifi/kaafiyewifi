<!DOCTYPE html>
<html lang="en"
x-data="{ sidebarOpen: true }"
class="h-full"
>
<head>
    <meta charset="UTF-8">
    <title>Admin – Kaafiye WiFi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css','resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-slate-100 text-slate-800">

<div class="flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-40 w-64
               bg-white border-r
               transition-transform duration-300
               lg:translate-x-0"
    >
        @include('partials.sidebar')
    </aside>

    {{-- MAIN --}}
    <div
        :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'"
        class="flex-1 flex flex-col transition-all duration-300"
    >

        {{-- NAVBAR --}}
        @include('partials.navbar')

        {{-- CONTENT --}}
        <main class="flex-1 overflow-y-auto bg-slate-50">
            <div class="max-w-7xl mx-auto px-6 py-6">
                @yield('content')
            </div>
        </main>

    </div>
</div>

</body>
</html>
