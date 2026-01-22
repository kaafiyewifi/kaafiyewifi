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

<body class="bg-slate-100 text-slate-800 dark:bg-gray-950 dark:text-gray-100">

{{-- ✅ Toast (single) --}}
@if(session('toast'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3500)"
        x-show="show"
        x-transition.opacity.duration.300ms
        class="fixed top-5 right-5 z-[9999]"
    >
        <div
            class="flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-white
            {{ session('toast.type') === 'success' ? 'bg-green-600' : '' }}
            {{ session('toast.type') === 'error' ? 'bg-red-600' : '' }}
            {{ session('toast.type') === 'warning' ? 'bg-yellow-500 text-black' : '' }}"
        >
            {{-- ICON --}}
            <span class="text-lg">
                @if(session('toast.type') === 'success') ✅ @endif
                @if(session('toast.type') === 'error') ❌ @endif
                @if(session('toast.type') === 'warning') ⚠️ @endif
            </span>

            {{-- MESSAGE --}}
            <span class="text-sm font-medium">
                {{ session('toast.message') }}
            </span>

            {{-- CLOSE --}}
            <button @click="show=false" class="ml-2 opacity-70 hover:opacity-100">
                ✕
            </button>
        </div>
    </div>
@endif

<div class="flex min-h-screen">

    {{-- ✅ SIDEBAR (fixed include name) --}}
    @include('partials.admin-sidebar')

    {{-- MAIN --}}
    <div
        :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'"
        class="flex-1 flex flex-col transition-all duration-300"
    >

        {{-- ✅ NAVBAR (fixed include name) --}}
        @include('partials.admin-navbar')

        {{-- CONTENT --}}
        <main class="flex-1 p-6">
            {{ $slot }}
        </main>

    </div>
</div>

</body>
</html>
