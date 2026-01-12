<header class="bg-white dark:bg-slate-800 border-b dark:border-slate-700">
    <div class="flex items-center justify-between px-4 py-3">

        {{-- LEFT --}}
        <div class="flex items-center gap-3">
            <button
                class="lg:hidden text-2xl"
                @click="sidebarOpen = true"
            >☰</button>

            <span class="font-semibold">Dashboard</span>
        </div>

        {{-- RIGHT --}}
        <div class="flex items-center gap-4">

            {{-- DARK MODE --}}
            <button
                @click="dark = !dark"
                class="px-3 py-1 border rounded"
            >
                <span x-show="!dark">🌙</span>
                <span x-show="dark">☀️</span>
            </button>

            {{-- USER DROPDOWN --}}
            <div x-data="{open:false}" class="relative">
                <button
                    @click="open=!open"
                    class="flex items-center gap-2"
                >
                    <div class="w-9 h-9 rounded-full bg-indigo-600 text-white flex items-center justify-center">
                        {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                    </div>
                    <span class="hidden sm:block text-sm font-medium">
                        {{ auth()->user()->name }}
                    </span>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    @click.outside="open=false"
                    class="absolute right-0 mt-2 w-40 bg-white dark:bg-slate-800 border dark:border-slate-700 rounded shadow"
                >
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-full text-left px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-700">
                            Logout
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</header>
