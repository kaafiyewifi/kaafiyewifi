@extends('layouts.admin')

@section('title', 'MikroTik Routers')
@section('page_title', 'MikroTik Routers')

@section('content')
@php
    $activeStatus = request('status');
    $q = request('q');
    $perPage = (int) request('per_page', 10);

    $tabBase = ['q' => $q, 'per_page' => $perPage];
    $isAll = empty($activeStatus);
    $isOnlineTab = ($activeStatus === 'connected');
    $isOfflineTab = ($activeStatus === 'offline');

    $onlineCutoff = now()->subMinutes(3);
@endphp

<div class="space-y-6 overflow-x-hidden">

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-sm text-slate-500 dark:text-slate-400">Total Routers</div>
            <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($total ?? 0) }}</div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-sm text-slate-500 dark:text-slate-400">Online</div>
            <div class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($onlineCount ?? 0) }}</div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-sm text-slate-500 dark:text-slate-400">Offline</div>
            <div class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($offlineCount ?? 0) }}</div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-sm text-slate-500 dark:text-slate-400">Per Page</div>
            <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($perPage) }}</div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-200 p-5 dark:border-slate-800">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">MikroTik Routers</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage your MikroTik routers on this page</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="#"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/>
                        </svg>
                        Tutorial
                    </a>

                    <a href="{{ route('admin.routers.wizard.stage1') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-[#ff5437] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#e94b32]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Link a MikroTik
                    </a>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-6 border-b border-slate-200 text-sm dark:border-slate-800">
                <a href="{{ route('admin.routers.index', array_filter($tabBase)) }}"
                   class="relative inline-flex items-center gap-2 pb-3 text-sm {{ $isAll ? 'font-semibold text-slate-900 dark:text-slate-100' : 'font-medium text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $isAll ? 'text-[#ff5437]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    All
                    <span class="rounded-md {{ $isAll ? 'bg-orange-50 text-orange-600 dark:bg-orange-900/20 dark:text-orange-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }} px-2 py-0.5 text-xs font-semibold">
                        {{ $total ?? 0 }}
                    </span>
                    @if($isAll)
                        <span class="absolute -bottom-[1px] left-0 h-[2px] w-full bg-[#ff5437]"></span>
                    @endif
                </a>

                <a href="{{ route('admin.routers.index', array_filter(['status' => 'connected'] + $tabBase)) }}"
                   class="relative inline-flex items-center gap-2 pb-3 text-sm {{ $isOnlineTab ? 'font-semibold text-slate-900 dark:text-slate-100' : 'font-medium text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $isOnlineTab ? 'text-[#ff5437]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.53 16.11a6 6 0 0 1 6.94 0M5.07 12.66a11 11 0 0 1 13.86 0M1.64 9.2a16 16 0 0 1 20.72 0"/>
                    </svg>
                    Online
                    <span class="rounded-md bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-600 dark:bg-green-900/20 dark:text-green-300">
                        {{ $onlineCount ?? 0 }}
                    </span>
                    @if($isOnlineTab)
                        <span class="absolute -bottom-[1px] left-0 h-[2px] w-full bg-[#ff5437]"></span>
                    @endif
                </a>

                <a href="{{ route('admin.routers.index', array_filter(['status' => 'offline'] + $tabBase)) }}"
                   class="relative inline-flex items-center gap-2 pb-3 text-sm {{ $isOfflineTab ? 'font-semibold text-slate-900 dark:text-slate-100' : 'font-medium text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $isOfflineTab ? 'text-[#ff5437]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.36 5.64L5.64 18.36M6.5 6.5a16 16 0 0 1 11 0M9.2 9.2a11 11 0 0 1 5.6 0M11.5 11.5a6 6 0 0 1 1 0"/>
                    </svg>
                    Offline
                    <span class="rounded-md bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-600 dark:bg-red-900/20 dark:text-red-300">
                        {{ $offlineCount ?? 0 }}
                    </span>
                    @if($isOfflineTab)
                        <span class="absolute -bottom-[1px] left-0 h-[2px] w-full bg-[#ff5437]"></span>
                    @endif
                </a>
            </div>

            <form method="GET" class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Search</label>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Board / identity / IP"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Status</label>
                    <select
                        name="status"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                        <option value="">All</option>
                        <option value="connected" {{ request('status') === 'connected' ? 'selected' : '' }}>Online</option>
                        <option value="offline" {{ request('status') === 'offline' ? 'selected' : '' }}>Offline</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Per Page</label>
                    <select
                        name="per_page"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                        @foreach([10,25,50] as $n)
                            <option value="{{ $n }}" @selected((int) request('per_page', 10) === $n)>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-3">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-xl bg-[#ff5437] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#e94b32]"
                    >
                        Filter
                    </button>

                    <a
                        href="{{ route('admin.routers.index') }}"
                        class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="p-5">
            <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800">
                <div class="w-full overflow-x-auto overflow-y-auto max-h-[520px] overscroll-contain rounded-2xl">
                    <div class="min-w-[1200px]">
                        <table class="w-full text-sm">
                            <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-800">
                                <tr class="text-left text-slate-600 dark:text-slate-300">
                                    <th class="px-6 py-3 whitespace-nowrap">Board Name</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Provisioning</th>
                                    <th class="px-6 py-3 whitespace-nowrap">CPU</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Memory</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Status</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Remote Winbox</th>
                                    <th class="px-6 py-3 text-right whitespace-nowrap">Action</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse($routers as $router)
                                    @php
                                        $status = is_object($router->status) && property_exists($router->status, 'value')
                                            ? $router->status->value
                                            : (string) $router->status;

                                        $provLabel = match ($status) {
                                            'connected', 'online' => 'Completed',
                                            'services_pending' => 'Services Pending',
                                            'pending', 'provisioning' => 'Command Pending',
                                            'error' => 'Failed',
                                            default => 'Unknown',
                                        };

                                        $m = $router->latestMetric ?? null;
                                        $cpu = $m?->cpu_load;

                                        $memUsedMB = null;
                                        if ($m?->total_memory && $m?->free_memory !== null) {
                                            $memUsedMB = ($m->total_memory - $m->free_memory) / 1024 / 1024;
                                        }

                                        $isOnline = false;
                                        if (!empty($router->last_seen_at)) {
                                            try {
                                                $seenAt = $router->last_seen_at instanceof \Illuminate\Support\Carbon
                                                    ? $router->last_seen_at
                                                    : \Illuminate\Support\Carbon::parse($router->last_seen_at);

                                                $isOnline = $seenAt->gte($onlineCutoff);
                                            } catch (\Throwable $e) {
                                                $isOnline = false;
                                            }
                                        }
                                    @endphp

                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-semibold text-slate-900 dark:text-white">{{ $router->name }}</div>
                                            <div class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">
                                                {{ $router->identity ?? $router->mgmt_host }}
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium
                                                {{ $provLabel === 'Completed'
                                                    ? 'bg-green-100 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-900/30 dark:text-green-300 dark:ring-green-800'
                                                    : ($provLabel === 'Failed'
                                                        ? 'bg-red-100 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-900/30 dark:text-red-300 dark:ring-red-800'
                                                        : 'bg-orange-100 text-orange-700 ring-1 ring-inset ring-orange-200 dark:bg-orange-900/30 dark:text-orange-300 dark:ring-orange-800') }}">
                                                {{ $provLabel }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($cpu !== null)
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-900/30 dark:text-green-300 dark:ring-green-800">
                                                    {{ $cpu }}%
                                                </span>
                                            @else
                                                <span class="text-slate-400 dark:text-slate-500">—</span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($memUsedMB !== null)
                                                <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-200 dark:bg-orange-900/30 dark:text-orange-300 dark:ring-orange-800">
                                                    {{ number_format($memUsedMB, 2) }} MB
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">
                                                    0.00 MB
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($isOnline)
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium bg-green-100 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-900/30 dark:text-green-300 dark:ring-green-800">
                                                    Online
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium bg-red-100 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-900/30 dark:text-red-300 dark:ring-red-800">
                                                    Offline
                                                </span>
                                            @endif

                                            <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                                last_seen: {{ $router->last_seen_at ? $router->last_seen_at : '-' }}
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($router->mgmt_host)
                                                <div class="flex items-center gap-3 text-slate-600 dark:text-slate-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17h4.5M4.5 6.75h15v9h-15z"/>
                                                    </svg>

                                                    <a class="text-sm font-semibold text-[#ff5437] hover:underline"
                                                       href="http://{{ $router->mgmt_host }}"
                                                       target="_blank"
                                                       rel="noopener noreferrer"
                                                       title="Open WebFig">
                                                        Open
                                                    </a>

                                                    <button type="button"
                                                            class="text-xs text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200"
                                                            onclick="navigator.clipboard.writeText(@js($router->mgmt_host))">
                                                        Copy IP
                                                    </button>
                                                </div>
                                            @else
                                                <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17h4.5M4.5 6.75h15v9h-15z"/>
                                                    </svg>
                                                    <span class="font-semibold text-red-500">-</span>
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <a
                                                    href="{{ route('admin.routers.show', $router) }}"
                                                    class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-blue-50 p-2 text-blue-600 transition hover:bg-blue-100 dark:border-blue-900 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/30"
                                                    title="View"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>

                                                <form method="POST"
                                                      action="{{ route('admin.routers.destroy', $router) }}"
                                                      onsubmit="return confirm('Delete this router?');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 p-2 text-red-600 transition hover:bg-red-100 dark:border-red-900 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
                                                        title="Delete"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7v10m6-10v10M10 4h4a1 1 0 011 1v2H9V5a1 1 0 011-1z"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                            No MikroTik routers
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3 text-center text-xs text-slate-500 dark:text-slate-400 sm:hidden">
                Swipe left/right inside the card to see full table
            </div>
        </div>

        <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800 overflow-x-auto">
            <div class="flex items-center justify-between gap-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Showing {{ $routers->firstItem() ?? 0 }} to {{ $routers->lastItem() ?? 0 }} of {{ $routers->total() ?? 0 }} results
                </p>

                <div>
                    {{ $routers->links() }}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection