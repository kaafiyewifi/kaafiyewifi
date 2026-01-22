{{-- resources/views/admin/routers/show.blade.php --}}
@extends('layouts.admin')

@section('content')
@php
    /** @var \App\Models\Router $router */

    $status = $router->status ?? 'pending';

    $statusBadge = match($status) {
        'connected' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200',
        'offline'   => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-200',
        'failed'    => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-200',
        default     => 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-200',
    };

    $tab = request('tab', 'system'); // system | events | financial | users | payments | backups

    $tabClass = function(string $key) use ($tab) {
        $active = $tab === $key;

        return $active
            ? 'text-[#ff4b2b] border-b-2 border-[#ff4b2b]'
            : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent hover:border-gray-200 dark:hover:border-gray-800';
    };

    $label = fn($v, $fallback='—') => (filled($v) ? $v : $fallback);

    // Winbox string (optional)
    $remoteWinbox = $router->remote_winbox
        ?? ($router->public_ip ? ('vpn.kaafiye.online:' . ($router->api_port ?? 8728)) : null);
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-xl font-extrabold text-gray-900 dark:text-white truncate">
                    {{ $router->identity ?? $router->name ?? 'Router' }}
                </h2>

                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadge }}">
                    {{ strtoupper($status) }}
                </span>

                @if($router->location)
                    <a href="{{ route('admin.locations.show', $router->location) }}"
                       class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold
                              bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-200 hover:opacity-90">
                        <span class="inline-block w-2 h-2 rounded-full bg-[#ff4b2b]"></span>
                        {{ $router->location->name }}
                    </a>
                @endif
            </div>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Management IP: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $label($router->mgmt_ip) }}</span>
                <span class="mx-2">•</span>
                API Port: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $label($router->api_port, 8728) }}</span>
            </p>
        </div>

        {{-- Right actions --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.routers.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-800
                      bg-white dark:bg-gray-950 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-900">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6"/>
                </svg>
                Back
            </a>

            <a href="{{ route('admin.routers.edit', $router) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-800
                      bg-white dark:bg-gray-950 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-900">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/>
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/>
                </svg>
                Edit
            </a>

            {{-- Actions dropdown (UI only / placeholder) --}}
            <div x-data="{open:false}" class="relative">
                <button type="button"
                        @click="open=!open"
                        class="inline-flex items-center gap-2 rounded-lg bg-[#ff4b2b] text-white px-3 py-2 text-sm hover:opacity-90">
                    Actions
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                    </svg>
                </button>

                <div x-cloak x-show="open" @click.outside="open=false"
                     class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800
                            bg-white dark:bg-gray-950 shadow-lg z-50">
                    <a href="#"
                       class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-900">
                        <span class="text-[#ff4b2b]">⟲</span> Change identity
                    </a>
                    <a href="#"
                       class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-900">
                        <span class="text-[#ff4b2b]">⬇</span> Redownload hotspot files
                    </a>
                    <a href="#"
                       class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-900">
                        <span class="text-[#ff4b2b]">☎</span> Change support number
                    </a>
                    <a href="#"
                       class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-900">
                        <span class="text-[#ff4b2b]">📶</span> Ping router
                    </a>
                    <div class="border-t border-gray-200 dark:border-gray-800"></div>
                    <a href="#"
                       class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                        <span>⏻</span> Reboot
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs row (like screenshot) --}}
    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 overflow-hidden">
        <div class="px-4 sm:px-6 pt-4">
            <div class="flex flex-wrap gap-6 border-b border-gray-200 dark:border-gray-800">
                <a href="{{ route('admin.routers.show', $router) }}?tab=system"
                   class="py-3 text-sm font-semibold {{ $tabClass('system') }}">
                    System Information
                </a>
                <a href="{{ route('admin.routers.show', $router) }}?tab=events"
                   class="py-3 text-sm font-semibold {{ $tabClass('events') }}">
                    Events
                </a>
                <a href="{{ route('admin.routers.show', $router) }}?tab=financial"
                   class="py-3 text-sm font-semibold {{ $tabClass('financial') }}">
                    Financial Reports
                </a>
                <a href="{{ route('admin.routers.show', $router) }}?tab=users"
                   class="py-3 text-sm font-semibold {{ $tabClass('users') }}">
                    Users
                </a>
                <a href="{{ route('admin.routers.show', $router) }}?tab=payments"
                   class="py-3 text-sm font-semibold {{ $tabClass('payments') }}">
                    Payments
                </a>
                <a href="{{ route('admin.routers.show', $router) }}?tab=backups"
                   class="py-3 text-sm font-semibold {{ $tabClass('backups') }}">
                    Backups
                </a>
            </div>
        </div>

        {{-- Tab content --}}
        <div class="p-4 sm:p-6">

            {{-- SYSTEM --}}
            @if($tab === 'system')
                <div class="space-y-6">

                    {{-- General Information + RADIUS --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-5">
                            <h3 class="font-semibold text-gray-900 dark:text-white">General Information</h3>

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div class="rounded-xl bg-gray-50 dark:bg-gray-900 p-4">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">MANAGEMENT IP</div>
                                    <div class="mt-1 font-semibold text-gray-900 dark:text-white flex items-center justify-between gap-2">
                                        <span>{{ $label($router->mgmt_ip) }}</span>
                                        <button type="button" class="text-xs rounded-lg border border-gray-200 dark:border-gray-800 px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                                            Copy
                                        </button>
                                    </div>
                                </div>

                                <div class="rounded-xl bg-gray-50 dark:bg-gray-900 p-4">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">USERNAME</div>
                                    <div class="mt-1 font-semibold text-gray-900 dark:text-white flex items-center justify-between gap-2">
                                        <span>{{ $label($router->api_user) }}</span>
                                        <button type="button" class="text-xs rounded-lg border border-gray-200 dark:border-gray-800 px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                                            Copy
                                        </button>
                                    </div>
                                </div>

                                <div class="rounded-xl bg-gray-50 dark:bg-gray-900 p-4">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">PASSWORD</div>
                                    <div class="mt-1 font-semibold text-gray-900 dark:text-white flex items-center justify-between gap-2">
                                        <span>••••••••</span>
                                        <button type="button" class="text-xs rounded-lg border border-gray-200 dark:border-gray-800 px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                                            Copy
                                        </button>
                                    </div>
                                </div>

                                <div class="rounded-xl bg-gray-50 dark:bg-gray-900 p-4">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">API PORT</div>
                                    <div class="mt-1 font-semibold text-gray-900 dark:text-white flex items-center justify-between gap-2">
                                        <span>{{ $label($router->api_port, 8728) }}</span>
                                        <button type="button" class="text-xs rounded-lg border border-gray-200 dark:border-gray-800 px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                                            Copy
                                        </button>
                                    </div>
                                </div>

                                <div class="rounded-xl bg-gray-50 dark:bg-gray-900 p-4 sm:col-span-2">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">REMOTE WINBOX</div>
                                    <div class="mt-1 font-semibold text-gray-900 dark:text-white">
                                        @if($remoteWinbox)
                                            <span class="text-[#ff4b2b]">{{ $remoteWinbox }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                                Last seen: <span class="font-semibold">{{ optional($router->last_seen_at)->diffForHumans() ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-5">
                            <h3 class="font-semibold text-gray-900 dark:text-white">RADIUS Configuration</h3>

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div class="rounded-xl bg-gray-50 dark:bg-gray-900 p-4">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">RADIUS ADDRESS</div>
                                    <div class="mt-1 font-semibold text-gray-900 dark:text-white flex items-center justify-between gap-2">
                                        <span>{{ $label($router->radius_server_ip) }}</span>
                                        <button type="button" class="text-xs rounded-lg border border-gray-200 dark:border-gray-800 px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                                            Copy
                                        </button>
                                    </div>
                                </div>

                                <div class="rounded-xl bg-gray-50 dark:bg-gray-900 p-4">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">SECRET</div>
                                    <div class="mt-1 font-semibold text-gray-900 dark:text-white flex items-center justify-between gap-2">
                                        <span>••••••••</span>
                                        <button type="button" class="text-xs rounded-lg border border-gray-200 dark:border-gray-800 px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                                            Copy
                                        </button>
                                    </div>
                                </div>

                                <div class="rounded-xl bg-gray-50 dark:bg-gray-900 p-4">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">ACCOUNTING PORT</div>
                                    <div class="mt-1 font-semibold text-gray-900 dark:text-white flex items-center justify-between gap-2">
                                        <span>{{ $label($router->radius_accounting_port ?? 1813) }}</span>
                                        <button type="button" class="text-xs rounded-lg border border-gray-200 dark:border-gray-800 px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                                            Copy
                                        </button>
                                    </div>
                                </div>

                                <div class="rounded-xl bg-gray-50 dark:bg-gray-900 p-4">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">AUTH PORT</div>
                                    <div class="mt-1 font-semibold text-gray-900 dark:text-white flex items-center justify-between gap-2">
                                        <span>{{ $label($router->radius_auth_port ?? 1812) }}</span>
                                        <button type="button" class="text-xs rounded-lg border border-gray-200 dark:border-gray-800 px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                                            Copy
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between text-sm">
                                <div class="text-gray-600 dark:text-gray-300">
                                    RADIUS Enabled:
                                    <span class="font-semibold {{ $router->radius_enabled ? 'text-green-600 dark:text-green-300' : 'text-gray-400' }}">
                                        {{ $router->radius_enabled ? 'Yes' : 'No' }}
                                    </span>
                                </div>

                                <a href="{{ route('admin.routers.edit', $router) }}"
                                   class="inline-flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-800
                                          bg-white dark:bg-gray-950 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-900">
                                    Edit Config
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Small stats row (placeholders like screenshot cards) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">CPU USAGE</div>
                                    <div class="mt-2 text-2xl font-extrabold text-gray-900 dark:text-white">
                                        {{ $label($router->cpu_percent, '0') }}%
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Current load average</div>
                                </div>
                                <div class="w-12 h-12 rounded-2xl bg-[#ff4b2b]/10 flex items-center justify-center">
                                    <span class="text-[#ff4b2b] text-xl">⚙</span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">MEMORY USAGE</div>
                                    <div class="mt-2 text-2xl font-extrabold text-gray-900 dark:text-white">
                                        {{ $label($router->memory_percent, '0') }}%
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $label($router->memory_mb, '0') }}MB / {{ $label($router->memory_total_mb, '0') }}MB
                                    </div>
                                </div>
                                <div class="w-12 h-12 rounded-2xl bg-[#ff4b2b]/10 flex items-center justify-center">
                                    <span class="text-[#ff4b2b] text-xl">💾</span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">DISK USAGE</div>
                                    <div class="mt-2 text-2xl font-extrabold text-gray-900 dark:text-white">
                                        {{ $label($router->disk_percent, '0') }}%
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $label($router->disk_used_mb, '0') }}MB / {{ $label($router->disk_total_mb, '0') }}MB
                                    </div>
                                </div>
                                <div class="w-12 h-12 rounded-2xl bg-[#ff4b2b]/10 flex items-center justify-center">
                                    <span class="text-[#ff4b2b] text-xl">🗄</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            @endif

            {{-- EVENTS (placeholder) --}}
            @if($tab === 'events')
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Events</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Router events/logs will appear here (connect/disconnect, provisioning, errors).
                    </p>
                </div>
            @endif

            {{-- FINANCIAL (placeholder) --}}
            @if($tab === 'financial')
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Financial Reports</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Revenue, usage, subscriptions summary will appear here (filtered by this router/location).
                    </p>
                </div>
            @endif

            {{-- USERS (placeholder) --}}
            @if($tab === 'users')
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Users</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Active hotspot/PPPoE users for this router will appear here.
                    </p>
                </div>
            @endif

            {{-- PAYMENTS (placeholder) --}}
            @if($tab === 'payments')
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Payments</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Payments related to this router/location will appear here.
                    </p>
                </div>
            @endif

            {{-- BACKUPS (placeholder) --}}
            @if($tab === 'backups')
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Backups</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Router backup files list (download/restore) will appear here.
                    </p>
                </div>
            @endif

        </div>
    </div>

</div>
@endsection
