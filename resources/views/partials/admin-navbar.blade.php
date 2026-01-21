{{-- resources/views/partials/admin-navbar.blade.php --}}

<header id="navbar" data-sidebar="expanded" class="h-16 bg-white dark:bg-gray-900">
    <div class="h-full flex items-center justify-between px-4 sm:px-6">

        <div class="flex items-center gap-3 min-w-0">
            <button id="sidebar-toggle" type="button"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-lg
                           border border-gray-200 dark:border-gray-800
                           hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="min-w-0">
                <div class="font-semibold text-gray-900 dark:text-gray-100 truncate">
                    @yield('page_title', 'Admin Panel')
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ config('app.name', 'KaafiyeWiFi') }}
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">

            <button id="theme-toggle" type="button"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-lg
                           border border-gray-200 dark:border-gray-800
                           hover:bg-gray-100 dark:hover:bg-gray-800"
                    aria-label="Toggle theme">
                <svg id="icon-moon" class="w-5 h-5 hidden" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M21 14.5A8.5 8.5 0 0 1 9.5 3a7 7 0 1 0 11.5 11.5Z"/>
                </svg>
                <svg id="icon-sun" class="w-5 h-5 hidden" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12Zm0-16h1v3h-1V2ZM12 19h1v3h-1v-3ZM2 11h3v1H2v-1Zm17 0h3v1h-3v-1ZM4.2 4.2l2.1 2.1-.7.7-2.1-2.1.7-.7Zm14.2 14.2 2.1 2.1-.7.7-2.1-2.1.7-.7ZM19.8 4.2l.7.7-2.1 2.1-.7-.7 2.1-2.1ZM6.3 17.7l.7.7-2.1 2.1-.7-.7 2.1-2.1Z"/>
                </svg>
            </button>

            <div class="relative">
                <button id="avatar-btn" type="button"
                        class="w-10 h-10 rounded-full bg-primary text-white font-bold
                               flex items-center justify-center shadow-soft">
                    {{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}
                </button>

                <div id="avatar-menu"
                     class="hidden absolute right-0 mt-2 w-56 rounded-xl overflow-hidden
                            bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800
                            shadow-soft z-[80]">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                        <div class="font-semibold">{{ auth()->user()->name ?? 'User' }}</div>
                        <div class="text-sm muted">{{ auth()->user()->email ?? '' }}</div>
                    </div>

                    <a href="{{ route('profile.edit') }}"
                       class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">
                        Profile
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">
                            Logout
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</header>
