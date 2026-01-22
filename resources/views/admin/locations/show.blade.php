{{-- resources/views/admin/locations/show.blade.php --}}
@extends('layouts.admin')

@section('content')
@php
    // Routers collection (controller-kaaga wuxuu sameeyay $location->load('routers') + loadCount)
    $routers = $location->routers ?? collect();

    // Status helpers
    $isOnline = fn($r) => ($r->status ?? '') === 'connected';
    $isOffline = fn($r) => in_array(($r->status ?? ''), ['offline','failed'], true);

    $allCount     = $routers->count();
    $onlineCount  = $routers->filter($isOnline)->count();
    $offlineCount = $routers->filter($isOffline)->count();

    $filter = request('status', 'all'); // all | online | offline

    $filteredRouters = match($filter) {
        'online'  => $routers->filter($isOnline)->values(),
        'offline' => $routers->filter($isOffline)->values(),
        default   => $routers->values(),
    };

    $pill = function(string $key) use ($filter) {
        $active = $filter === $key;
        return $active
            ? 'bg-[#ff4b2b] text-white'
            : 'bg-white dark:bg-gray-950 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900';
    };

    $badgeStatus = function($router) {
        $status = $router->status ?? 'pending';

        return match($status) {
            'connected' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200',
            'offline'   => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-200',
            'failed'    => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-200',
            default     => 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-200',
        };
    };

    // Provisioning label (simple)
    $provisionLabel = function($router) {
        return (($router->status ?? '') === 'connected') ? 'Completed' : 'Pending';
    };
@endphp

<div class="space-y-6">

    {{-- Location header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-extrabold text-gray-900 dark:text-white truncate">
                    {{ $location->name }}
                </h2>

                @if(isset($location->status))
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                        {{ $location->status === 'active'
                            ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200'
                            : 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-200' }}">
                        {{ strtoupper($location->status) }}
                    </span>
                @endif
            </div>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $location->address ?? 'No address set' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.locations.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-800
                      bg-white dark:bg-gray-950 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-900">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6"/>
                </svg>
                Back
            </a>

            <a href="{{ route('admin.locations.edit', $location) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-800
                      bg-white dark:bg-gray-950 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-900">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/>
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/>
                </svg>
                Edit Location
            </a>
        </div>
    </div>

    {{-- Routers card (SAMPLE like screenshot) --}}
    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 overflow-hidden">

        {{-- Top bar --}}
        <div class="p-5 flex flex-col gap-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">MikroTik Routers</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        To get started, add a MikroTik router, by clicking the “Link a MikroTik” button.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="#"
                       class="inline-flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-800
                              bg-white dark:bg-gray-950 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-900">
                        <svg class="w-4 h-4 text-[#ff4b2b]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 18h.01M9.5 9.5a2.5 2.5 0 1 1 4.2 1.8c-.6.5-1.2 1-1.2 2v.2"/>
                            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 22C6.5 22 2 17.5 2 12S6.5 2 12 2s10 4.5 10 10-4.5 10-10 10Z"/>
                        </svg>
                        Tutorial
                    </a>

                    @role('super_admin')
                        <a href="{{ route('admin.locations.routers.create', $location) }}"
                           class="inline-flex items-center gap-2 rounded-lg bg-[#ff4b2b] text-white px-4 py-2 text-sm hover:opacity-90">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 5v14M5 12h14"/>
                            </svg>
                            Link a MikroTik
                        </a>
                    @endrole
                </div>
            </div>

            {{-- Filters row (All / Online / Offline) --}}
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.locations.show', $location) }}?status=all"
                   class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm {{ $pill('all') }}">
                    <span>All</span>
                    <span class="inline-flex items-center justify-center rounded-full bg-black/5 dark:bg-white/10 px-2 py-0.5 text-xs">
                        {{ $allCount }}
                    </span>
                </a>

                <a href="{{ route('admin.locations.show', $location) }}?status=online"
                   class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm {{ $pill('online') }}">
                    <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                    <span>Online</span>
                    <span class="inline-flex items-center justify-center rounded-full bg-black/5 dark:bg-white/10 px-2 py-0.5 text-xs">
                        {{ $onlineCount }}
                    </span>
                </a>

                <a href="{{ route('admin.locations.show', $location) }}?status=offline"
                   class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm {{ $pill('offline') }}">
                    <span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>
                    <span>Offline</span>
                    <span class="inline-flex items-center justify-center rounded-full bg-black/5 dark:bg-white/10 px-2 py-0.5 text-xs">
                        {{ $offlineCount }}
                    </span>
                </a>
            </div>
        </div>

        {{-- Table + Search --}}
        <div class="border-t border-gray-200 dark:border-gray-800">
            <div class="p-4 sm:p-5 flex items-center justify-end">
                <div class="relative w-full sm:w-72">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3"/>
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/>
                    </svg>
                    <input type="text" placeholder="Search"
                           class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 pl-9 pr-3 py-2 text-sm
                                  focus:ring-2 focus:ring-[#ff4b2b]/30 focus:border-[#ff4b2b]">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40 text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="text-left font-semibold px-5 py-3">Board Name</th>
                            <th class="text-left font-semibold px-5 py-3">Provisioning</th>
                            <th class="text-left font-semibold px-5 py-3">CPU</th>
                            <th class="text-left font-semibold px-5 py-3">Memory</th>
                            <th class="text-left font-semibold px-5 py-3">Status</th>
                            <th class="text-left font-semibold px-5 py-3">Remote Winbox</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($filteredRouters as $router)
                            @php
                                $status = $router->status ?? 'pending';
                                $prov   = $provisionLabel($router);

                                // Optional fields (haddii aadan haysan CPU/Memory columns weli)
                                $cpu    = $router->cpu_percent ?? null;
                                $mem    = $router->memory_mb ?? null;

                                // Remote winbox URL (optional column)
                                $winbox = $router->remote_winbox ?? ($router->public_ip ? ('winbox://'.$router->public_ip.':'.($router->api_port ?? 8728)) : null);
                            @endphp

                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                                <td class="px-5 py-3">
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        {{ $router->identity ?? $router->name ?? 'Router' }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $router->mgmt_ip ?? '-' }}
                                    </div>
                                </td>

                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                                        {{ $prov === 'Completed'
                                            ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200'
                                            : 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-200' }}">
                                        {{ $prov }}
                                    </span>
                                </td>

                                <td class="px-5 py-3 text-gray-700 dark:text-gray-200">
                                    {{ is_null($cpu) ? '—' : (rtrim(rtrim(number_format((float)$cpu, 1), '0'), '.') . '%') }}
                                </td>

                                <td class="px-5 py-3 text-gray-700 dark:text-gray-200">
                                    {{ is_null($mem) ? '—' : ($mem . ' MB') }}
                                </td>

                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeStatus($router) }}">
                                        {{ strtoupper($status) }}
                                    </span>
                                </td>

                                <td class="px-5 py-3">
                                    @if($winbox)
                                        <a href="{{ $winbox }}"
                                           class="text-[#ff4b2b] hover:underline font-semibold">
                                            {{ $winbox }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12">
                                    <div class="text-center">
                                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-900">
                                            <svg class="w-6 h-6 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16v4H4V6Zm0 8h16v4H4v-4Zm3-6h.01M7 16h.01M17 8h.01M17 16h.01"/>
                                            </svg>
                                        </div>
                                        <p class="font-semibold text-gray-900 dark:text-white">No NAS devices</p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Add your first NAS device by clicking the button above.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile bottom button (only on small screens) --}}
        @role('super_admin')
            <div class="p-4 border-t border-gray-200 dark:border-gray-800 sm:hidden">
                <a href="{{ route('admin.locations.routers.create', $location) }}"
                   class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-[#ff4b2b] text-white px-4 py-2 text-sm hover:opacity-90">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                    </svg>
                    Link a MikroTik
                </a>
            </div>
        @endrole

    </div>

</div>
@endsection
