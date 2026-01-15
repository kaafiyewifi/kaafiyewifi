<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="
        fixed inset-y-0 left-0 z-40 w-64
        bg-white dark:bg-slate-900
        border-r dark:border-slate-700
        transform transition-transform duration-300
        lg:translate-x-0
    "
>

    <div class="h-full flex flex-col">

        {{-- LOGO + CLOSE --}}
        <div class="flex items-center justify-between px-4 py-4 border-b dark:border-slate-700">
            <span class="font-bold text-indigo-600 text-lg">
                Kaafiye WiFi
            </span>

            <button
                class="lg:hidden text-xl text-gray-600 dark:text-gray-300"
                @click="sidebarOpen=false"
            >
                ✕
            </button>
        </div>

        {{-- MENU --}}
        <nav class="flex-1 p-4 space-y-1 text-sm font-medium">

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="block px-4 py-2 rounded
               {{ request()->routeIs('dashboard')
                    ? 'bg-indigo-600 text-white'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-slate-700' }}">
                🏠 Dashboard
            </a>

            {{-- Customers --}}
            <a href="{{ route('admin.customers.index') }}"
               class="block px-4 py-2 rounded
               {{ request()->routeIs('admin.customers.*')
                    ? 'bg-indigo-600 text-white'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-slate-700' }}">
                👥 Customers
            </a>

            {{-- Subscription Plans --}}
            <a href="{{ route('admin.subscription-plans.index') }}"
               class="block px-4 py-2 rounded
               {{ request()->routeIs('admin.subscription-plans.*')
                    ? 'bg-indigo-600 text-white'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-slate-700' }}">
                💳 Subscription Plans
            </a>

            {{-- Locations --}}
            <a href="{{ route('admin.locations.index') }}"
               class="block px-4 py-2 rounded
               {{ request()->routeIs('admin.locations.*')
                    ? 'bg-indigo-600 text-white'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-slate-700' }}">
                📍 Locations
            </a>

            {{-- Routers --}}
            <a href="{{ route('admin.routers.index') }}"
               class="block px-4 py-2 rounded
               {{ request()->routeIs('admin.routers.*')
                    ? 'bg-indigo-600 text-white'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-slate-700' }}">
                📡 Routers
            </a>

            {{-- Hotspots --}}
            <a href="{{ route('admin.hotspots.index') }}"
               class="block px-4 py-2 rounded
               {{ request()->routeIs('admin.hotspots.*')
                    ? 'bg-indigo-600 text-white'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-slate-700' }}">
                📶 Hotspots
            </a>
                <a href="{{ route('admin.router.online') }}" class="sidebar-link">
                🌐 Online Users
                </a>

        </nav>

        {{-- FOOTER --}}
        <div class="px-4 py-3 text-xs text-gray-400 border-t dark:border-slate-700">
            © {{ date('Y') }} Kaafiye WiFi
        </div>

    </div>
</aside>
