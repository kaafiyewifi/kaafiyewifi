{{-- resources/views/partials/admin-sidebar.blade.php --}}

@php
    $isActive = fn($pattern) => request()->routeIs($pattern);

    $item = fn(bool $active) => $active
        ? 'bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white'
        : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-900';

    $wrap = fn(bool $active) => 'flex items-center gap-3 rounded-xl px-3 py-2 transition-colors ' . $item($active);

    $centerOnCollapsed = 'lg:[data-sidebar=collapsed]:justify-center';
    $hideOnCollapsed   = 'lg:block lg:[data-sidebar=collapsed]:hidden';

    $user = auth()->user();
    $isSuperAdmin = auth()->check() && $user && method_exists($user, 'hasRole') && $user->hasRole('super_admin');
    $isAdmin      = auth()->check() && $user && method_exists($user, 'hasRole') && $user->hasRole('admin');
    $isSupport    = auth()->check() && $user && method_exists($user, 'hasRole') && $user->hasRole('support');
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

        {{-- Admin Home --}}
        <a href="{{ route('admin.home') }}"
           class="{{ $wrap($isActive('admin.home')) }} {{ $centerOnCollapsed }}">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1v-10.5Z"/>
            </svg>
            <span class="{{ $hideOnCollapsed }}">Admin Home</span>
        </a>

        {{-- Customers --}}
        <a href="{{ route('admin.customers.index') }}"
           class="{{ $wrap($isActive('admin.customers.*')) }} {{ $centerOnCollapsed }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span class="{{ $hideOnCollapsed }}">Customers</span>
        </a>

        @if($isAdmin || $isSuperAdmin)
            {{-- Subscriptions --}}
            <a href="{{ route('admin.subscriptions.index') }}"
               class="{{ $wrap($isActive('admin.subscriptions.*')) }} {{ $centerOnCollapsed }}">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8c-3.866 0-7 1.79-7 4v2h14v-2c0-2.21-3.134-4-7-4Z"/>
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8V6a3 3 0 0 1 6 0v2M12 8V6a3 3 0 0 0-6 0v2"/>
                </svg>
                <span class="{{ $hideOnCollapsed }}">Subscriptions</span>
            </a>

            {{-- Vouchers --}}
            <a href="{{ route('admin.vouchers.index') }}"
               class="{{ $wrap($isActive('admin.vouchers.*')) }} {{ $centerOnCollapsed }}">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M20 12a2 2 0 0 1 0 4v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2a2 2 0 0 1 0-4V8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2a2 2 0 0 1 0 4Z"/>
                </svg>
                <span class="{{ $hideOnCollapsed }}">Vouchers</span>
            </a>

            {{-- Billing --}}
            <a href="{{ route('admin.invoices.index') }}"
               class="{{ $wrap($isActive('admin.invoices.*')) }} {{ $centerOnCollapsed }}">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M9 14h6m-6-4h6m2 11H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"/>
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M14 3v5h5"/>
                </svg>
                <span class="{{ $hideOnCollapsed }}">Billing</span>
            </a>

            {{-- MikroTik Routers --}}
            <a href="{{ route('admin.routers.index') }}"
               class="{{ $wrap($isActive('admin.routers.*')) }} {{ $centerOnCollapsed }}">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <rect x="3" y="7" width="18" height="10" rx="2" stroke-width="2"/>
                    <path d="M7 12h.01M11 12h.01M15 12h.01" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span class="{{ $hideOnCollapsed }}">MikroTik Routers</span>
            </a>

            {{-- User Management --}}
            <a href="{{ route('admin.users.index') }}"
               class="{{ $wrap($isActive('admin.users.*')) }} {{ $centerOnCollapsed }}">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span class="{{ $hideOnCollapsed }}">User Management</span>
            </a>
        @endif

        @if($isSuperAdmin)
            {{-- Locations --}}
            <a href="{{ route('admin.locations.index') }}"
               class="{{ $wrap($isActive('admin.locations.*')) }} {{ $centerOnCollapsed }}">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M12 21s6-4.35 6-10a6 6 0 1 0-12 0c0 5.65 6 10 6 10Z"/>
                </svg>
                <span class="{{ $hideOnCollapsed }}">Location</span>
            </a>

            {{-- Audit Logs --}}
            <a href="{{ route('admin.audit.index') }}"
               class="{{ $wrap($isActive('admin.audit.*')) }} {{ $centerOnCollapsed }}">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M9 17v-6m4 6V7m4 10v-3M7 3h10l4 4v14H3V3h4"/>
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M13 3v5h5"/>
                </svg>
                <span class="{{ $hideOnCollapsed }}">Audit Logs</span>
            </a>

            {{-- Reports --}}
            <a href="{{ route('admin.reports.index') }}"
               class="{{ $wrap($isActive('admin.reports.*')) }} {{ $centerOnCollapsed }}">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M7 20V10m5 10V4m5 16v-7"/>
                </svg>
                <span class="{{ $hideOnCollapsed }}">Reports</span>
            </a>
        @endif

    </nav>

    <div class="sticky bottom-0 z-10 px-3 py-3 border-t border-gray-200 dark:border-gray-800
                bg-white/95 dark:bg-gray-950/95 backdrop-blur">
        <div class="text-xs text-gray-500 dark:text-gray-400 {{ $hideOnCollapsed }}">
            © {{ date('Y') }} KaafiyeWiFi
        </div>
    </div>
</aside>

<div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/40 z-40 lg:hidden"></div>