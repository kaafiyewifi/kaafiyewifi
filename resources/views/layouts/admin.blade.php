<!DOCTYPE html>
<html lang="en"
x-data="{ sidebarOpen: false, dark: localStorage.getItem('theme') === 'dark' }"
x-init="
    document.documentElement.classList.toggle('dark', dark);
    if (window.innerWidth >= 1024) sidebarOpen = true;
"
x-data="{
    sidebarOpen: window.innerWidth >= 1024
}"


>

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Dashboard' }} – Kaafiye WiFi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css','resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100">

<div class="flex min-h-screen">

    {{-- ================= SIDEBAR ================= --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-40 w-64
               bg-white dark:bg-slate-900
               border-r dark:border-slate-700
               transform transition-transform duration-300
               lg:translate-x-0"
    >
        @include('partials.sidebar')
    </aside>

    {{-- MOBILE OVERLAY --}}
    <div
        x-show="sidebarOpen && window.innerWidth < 1024"
        @click="sidebarOpen=false"
        class="fixed inset-0 bg-black/40 z-30 lg:hidden"
    ></div>

    {{-- ================= MAIN ================= --}}
    <div class="flex-1 flex flex-col lg:ml-64">

        {{-- ================= NAVBAR (DESKTOP + MOBILE) ================= --}}
        <header class="sticky top-0 z-20
                       bg-white dark:bg-slate-900
                       border-b dark:border-slate-700">

            <div class="h-14 px-4 flex items-center justify-between">

                {{-- LEFT --}}
                <div class="flex items-center gap-3">
                    {{-- Hamburger (mobile only) --}}
                    <button
                        class="lg:hidden text-2xl"
                        @click="sidebarOpen = true"
                    >
                        ☰
                    </button>

                    <span class="font-semibold text-sm">
                        {{ $title ?? 'Dashboard' }}
                    </span>
                </div>

                {{-- RIGHT --}}
                <div class="flex items-center gap-3">

                    {{-- Dark mode --}}
                    <button
                        @click="dark = !dark"
                        class="px-2 py-1 rounded border text-sm"
                    >
                        <span x-show="!dark">🌙</span>
                        <span x-show="dark">☀️</span>
                    </button>

                    {{-- User --}}
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white
                                    flex items-center justify-center text-sm">
                            {{ substr(auth()->user()->name,0,1) }}
                        </div>
                        <span class="hidden sm:block text-sm font-medium">
                            {{ auth()->user()->name }}
                        </span>
                    </div>
                </div>

            </div>
        </header>

        {{-- ================= CONTENT ================= --}}
  <main class="flex-1 bg-gray-100">
    <div class="max-w-7xl mx-auto px-6 py-6">
        @yield('content')
    </div>
</main>



    </div>
</div>

</body>
</html>
