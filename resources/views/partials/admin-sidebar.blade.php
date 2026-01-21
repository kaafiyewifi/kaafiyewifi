{{-- resources/views/partials/admin-sidebar.blade.php --}}

@php
    $item = fn($active) => $active
        ? 'bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white'
        : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-900';

    $wrap = fn($active) => 'flex items-center gap-3 rounded-xl px-3 py-2 transition-colors ' . $item($active);

    $centerOnCollapsed = 'lg:[data-sidebar=collapsed]:justify-center';
    $hideOnCollapsed   = 'lg:block lg:[data-sidebar=collapsed]:hidden';
@endphp

<aside id="sidebar"
       data-sidebar="expanded"
       class="fixed top-0 left-0 h-screen
              w-64 lg:w-64 lg:[data-sidebar=collapsed]:w-20
              bg-white dark:bg-gray-950 border-r border-gray-200 dark:border-gray-800
              shadow-sm z-50 transition-[width,transform] duration-200 ease-in-out
              -translate-x-full lg:translate-x-0 flex flex-col">

    {{-- Brand --}}
    <div class="sticky top-0 z-10 h-16 flex items-center gap-3 px-4
                border-b border-gray-200 dark:border-gray-800
                bg-white/95 dark:bg-gray-950/95 backdrop-blur">

        <div class="w-10 h-10 rounded-xl bg-[#ff4b2b] text-white font-bold flex items-center justify-center">
            K
        </div>

        <div class="min-w-0">
            <div class="font-bold text-lg truncate {{ $hideOnCollapsed }}">KaafiyeWiFi</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 {{ $hideOnCollapsed }}">Admin Panel</div>
        </div>

        {{-- Mobile close --}}
        <button id="sidebar-close" type="button"
                class="ml-auto inline-flex lg:hidden items-center justify-center w-9 h-9 rounded-lg
                       border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900"
                aria-label="Close sidebar">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6l-12 12"/>
            </svg>
        </button>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-1 text-sm">
        <a href="{{ route('admin.home') }}"
           class="{{ $wrap(request()->routeIs('admin.home')) }} {{ $centerOnCollapsed }}">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1v-10.5Z"/>
            </svg>
            <span class="{{ $hideOnCollapsed }}">Admin Home</span>
        </a>

        @role('super_admin')
        <a href="{{ route('sa.users.index') }}"
           class="{{ $wrap(request()->routeIs('sa.users.*')) }} {{ $centerOnCollapsed }}">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      d="M8 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/>
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span class="{{ $hideOnCollapsed }}">Users</span>
        </a>
        @endrole

        <a href="{{ route('admin.locations.index') }}"
           class="{{ $wrap(request()->routeIs('admin.locations.*')) }} {{ $centerOnCollapsed }}">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      d="M12 21s6-4.35 6-10a6 6 0 1 0-12 0c0 5.65 6 10 6 10Z"/>
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      d="M12 11a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
            </svg>
            <span class="{{ $hideOnCollapsed }}">Locations</span>
        </a>

        <a href="{{ route('admin.hotspots.index') }}"
           class="{{ $wrap(request()->routeIs('admin.hotspots.*')) }} {{ $centerOnCollapsed }}">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      d="M5 12.55a11 11 0 0 1 14 0"/>
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      d="M8.5 16.1a6 6 0 0 1 7 0"/>
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      d="M12 20h.01"/>
            </svg>
            <span class="{{ $hideOnCollapsed }}">Hotspots</span>
        </a>

        <a href="{{ route('admin.reports.index') }}"
           class="{{ $wrap(request()->routeIs('admin.reports.*')) }} {{ $centerOnCollapsed }}">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      d="M4 19V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14"/>
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      d="M8 13h8M8 9h8M8 17h5"/>
            </svg>
            <span class="{{ $hideOnCollapsed }}">Reports</span>
        </a>

        <a href="{{ route('admin.audit.index') }}"
           class="{{ $wrap(request()->routeIs('admin.audit.*')) }} {{ $centerOnCollapsed }}">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 4h14v16H5z"/>
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M8 8h8M8 12h8M8 16h5"/>
            </svg>
            <span class="{{ $hideOnCollapsed }}">Audit Logs</span>
        </a>
    </nav>

    <div class="sticky bottom-0 z-10 px-3 py-3 border-t border-gray-200 dark:border-gray-800
                bg-white/95 dark:bg-gray-950/95 backdrop-blur">
        <div class="text-xs text-gray-500 dark:text-gray-400 {{ $hideOnCollapsed }}">
            © {{ date('Y') }} KaafiyeWiFi
        </div>
    </div>
</aside>

{{-- Overlay --}}
<div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/40 z-40 lg:hidden"></div>
