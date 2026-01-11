<!DOCTYPE html>
<html lang="en"
x-data="{
    sidebarOpen: window.innerWidth >= 1024,
    dark: localStorage.getItem('theme') === 'dark'
}"
x-init="
    document.documentElement.classList.toggle('dark', dark);
    $watch('dark', v => {
        localStorage.setItem('theme', v ? 'dark' : 'light');
        document.documentElement.classList.toggle('dark', v);
    });
"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin – Kaafiye WiFi</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>[x-cloak]{display:none}</style>
</head>

<body class="bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100">

<div class="flex min-h-screen">

    {{-- SIDEBAR --}}
    @include('partials.sidebar')

    {{-- MAIN --}}
    <div
        :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'"
        class="flex-1 flex flex-col transition-all duration-300"
    >

        {{-- NAVBAR --}}
        @include('partials.navbar')

        {{-- CONTENT --}}
        <main class="flex-1 p-6">
            {{ $slot }}
        </main>

    </div>
</div>

</body>
</html>
