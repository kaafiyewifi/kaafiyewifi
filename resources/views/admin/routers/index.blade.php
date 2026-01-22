{{-- resources/views/admin/routers/index.blade.php --}}
@extends('layouts.admin')

@section('content')
@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection $routers */

    $primary = '#ff4b2b';

    $btnPrimary = "inline-flex items-center gap-2 rounded-lg bg-[{$primary}] text-white px-4 py-2 text-sm font-semibold hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[{$primary}]/30";
    $btnGhost   = "inline-flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-900";
@endphp

<div class="w-full max-w-6xl mx-auto px-2 sm:px-4">
    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">Routers</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage all MikroTik routers (Super Admin only).
                </p>
            </div>

            <div class="flex items-center gap-2">
                {{-- optional search UI --}}
                <div class="relative hidden sm:block">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3"/>
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/>
                    </svg>
                    <input
                        type="text"
                        placeholder="Search…"
                        class="w-64 rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 pl-9 pr-3 py-2 text-sm
                               text-gray-900 dark:text-gray-100 placeholder:text-gray-400
                               focus:ring-2 focus:ring-[{{$primary}}]/30 focus:border-[{{$primary}}]"
                    >
                </div>

                <a href="{{ route('admin.routers.create') }}" class="{{ $btnPrimary }}">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                    </svg>
                    Add Router
                </a>
            </div>
        </div>

        {{-- Card --}}
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 overflow-hidden">

            {{-- mobile search --}}
            <div class="p-4 border-b border-gray-200 dark:border-gray-800 sm:hidden">
                <div class="relative">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3"/>
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/>
                    </svg>
                    <input
                        type="text"
                        placeholder="Search…"
                        class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 pl-9 pr-3 py-2 text-sm
                               text-gray-900 dark:text-gray-100 placeholder:text-gray-400
                               focus:ring-2 focus:ring-[{{$primary}}]/30 focus:border-[{{$primary}}]"
                    >
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40 text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="text-left font-semibold px-5 py-3">Name</th>
                            <th class="text-left font-semibold px-5 py-3">IP</th>
                            <th class="text-left font-semibold px-5 py-3">Location</th>
                            <th class="text-left font-semibold px-5 py-3">Status</th>
                            <th class="text-right font-semibold px-5 py-3 w-36">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @php
                            $rows = $routers ?? collect();

                            $badge = function($status) {
                                $status = $status ?? 'pending';
                                return match($status) {
                                    'connected' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200',
                                    'offline'   => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-200',
                                    'failed'    => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-200',
                                    default     => 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-200',
                                };
                            };
                        @endphp

                        @forelse($rows as $router)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                                <td class="px-5 py-3">
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        {{ $router->identity ?? $router->name ?? 'Router' }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $router->public_ip ?? '—' }}
                                    </div>
                                </td>

                                <td class="px-5 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $router->mgmt_ip ?? '—' }}
                                </td>

                                <td class="px-5 py-3 text-gray-700 dark:text-gray-200">
                                    @if($router->location)
                                        <a href="{{ route('admin.locations.show', $router->location) }}"
                                           class="text-[{{$primary}}] font-semibold hover:underline">
                                            {{ $router->location->name }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge($router->status) }}">
                                        {{ strtoupper($router->status ?? 'pending') }}
                                    </span>
                                </td>

                                <td class="px-5 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.routers.show', $router) }}"
                                           class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-xs font-semibold
                                                  border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900">
                                            View
                                        </a>
                                        <a href="{{ route('admin.routers.edit', $router) }}"
                                           class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-xs font-semibold
                                                  border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14">
                                    <div class="mx-auto max-w-md text-center">
                                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-900">
                                            <svg class="w-6 h-6 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                      d="M4 6h16v4H4V6Zm0 8h16v4H4v-4Zm3-6h.01M7 16h.01M17 8h.01M17 16h.01"/>
                                            </svg>
                                        </div>
                                        <p class="font-semibold text-gray-900 dark:text-white">No routers found</p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Click “Add Router” to link your first MikroTik router.
                                        </p>

                                        <a href="{{ route('admin.routers.create') }}"
                                           class="mt-4 inline-flex items-center justify-center gap-2 rounded-lg bg-[{{$primary}}] text-white px-4 py-2 text-sm font-semibold hover:opacity-90">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                                            </svg>
                                            Add Router
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination (if paginator) --}}
            @if(method_exists($rows, 'links'))
                <div class="px-4 sm:px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                    {{ $rows->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
