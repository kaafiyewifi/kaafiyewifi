<aside class="w-64 bg-white dark:bg-gray-800 border-r dark:border-gray-700">
    <div class="p-4 text-lg font-bold text-indigo-600">
        Kaafiye WiFi
    </div>

    <nav class="px-4 space-y-2 text-sm">
        <a href="{{ route('dashboard') }}"
           class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
            Dashboard
        </a>

        <a href="{{ route('admin.customers.index') }}"
           class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
            Customers
        </a>

        <a href="{{ route('admin.locations.index') }}"
           class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
            Locations
        </a>

        <a href="{{ route('admin.routers.index') }}"
           class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
            Routers
        </a>

        <a href="{{ route('admin.hotspots.index') }}"
           class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
            Hotspots
        </a>
    </nav>
</aside>
