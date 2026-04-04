@extends('layouts.admin')

@section('title', 'Audit Logs')
@section('page_title', 'Audit Logs')

@section('content')
@php
    $logs = $logs ?? collect();
    $actions = $actions ?? collect();
    $users = $users ?? collect();

    $totalLogs = method_exists($logs, 'total') ? $logs->total() : collect($logs)->count();
    $todayLogs = collect($logs->items() ?? $logs)->filter(function ($log) {
        return optional($log->created_at)->isToday();
    })->count();

    $activeFilters = collect([
        request('search'),
        request('action'),
        request('user_id'),
        request('from'),
        request('to'),
    ])->filter(fn ($v) => filled($v))->count();

    $formatTarget = function ($log) {
        if (!empty($log->target_type) && !empty($log->target_id)) {
            $base = class_basename($log->target_type);
            return $base . ' #' . $log->target_id;
        }

        if (!empty($log->target_type)) {
            return class_basename($log->target_type);
        }

        return '—';
    };

    $formatUser = function ($log) {
        if (!empty($log->user?->name)) {
            return $log->user->name;
        }

        return 'System';
    };

    $formatProperties = function ($properties) {
        if (empty($properties)) {
            return null;
        }

        if (is_string($properties)) {
            $decoded = json_decode($properties, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            return $properties;
        }

        return $properties;
    };
@endphp

<div class="space-y-6">

    {{-- HEADER CARDS --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Logs</div>
            <div class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                {{ number_format($totalLogs) }}
            </div>
            <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">All audit records</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-sm font-medium text-slate-500 dark:text-slate-400">Today Logs</div>
            <div class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                {{ number_format($todayLogs) }}
            </div>
            <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">Today activity</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-sm font-medium text-slate-500 dark:text-slate-400">Actions</div>
            <div class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                {{ number_format(collect($actions)->count()) }}
            </div>
            <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">Unique action types</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-sm font-medium text-slate-500 dark:text-slate-400">Active Filters</div>
            <div class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                {{ number_format($activeFilters) }}
            </div>
            <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">Current search filters</div>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Search & Filters</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Search by action, user, description, IP address, and date range
            </p>
        </div>

        <form method="GET" action="{{ route('admin.audit.index') }}" class="p-6">
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-5">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">Search</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Action / user / description / IP"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-orange-500 dark:focus:ring-orange-500/10"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">Action</label>
                    <select
                        name="action"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-orange-500 dark:focus:ring-orange-500/10"
                    >
                        <option value="">All</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                {{ $action }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">User</label>
                    <select
                        name="user_id"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-orange-500 dark:focus:ring-orange-500/10"
                    >
                        <option value="">All</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ (string) request('user_id') === (string) $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">From</label>
                    <input
                        type="date"
                        name="from"
                        value="{{ request('from') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-orange-500 dark:focus:ring-orange-500/10"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">To</label>
                    <input
                        type="date"
                        name="to"
                        value="{{ request('to') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-orange-500 dark:focus:ring-orange-500/10"
                    >
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <button
                    type="submit"
                    class="inline-flex items-center rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600"
                >
                    Filter
                </button>

                <a
                    href="{{ route('admin.audit.index') }}"
                    class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Audit Trail</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Complete action history for admins and system events
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    {{ number_format($totalLogs) }} records
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr class="text-left text-slate-600 dark:text-slate-300">
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Time</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">User</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Action</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Target</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Description</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">IP Address</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Properties</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($logs as $log)
                        @php
                            $properties = $formatProperties($log->properties ?? null);
                        @endphp

                        <tr class="align-top hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="px-6 py-4 whitespace-nowrap text-slate-800 dark:text-slate-200">
                                {{ optional($log->created_at)->format('d M Y H:i:s') ?: '—' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-900 dark:text-white">
                                    {{ $formatUser($log) }}
                                </div>
                                @if(!empty($log->user?->email))
                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $log->user->email }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                                    {{ $log->action ?: '—' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-slate-800 dark:text-slate-200">
                                {{ $formatTarget($log) }}
                            </td>

                            <td class="px-6 py-4 min-w-[260px] text-slate-700 dark:text-slate-300">
                                {{ $log->description ?: '—' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(!empty($log->ip_address))
                                    <span class="inline-flex rounded-xl bg-slate-100 px-3 py-1 font-mono text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        {{ $log->ip_address }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-6 py-4 min-w-[260px]">
                                @if(is_array($properties) && count($properties))
                                    <details class="group">
                                        <summary class="cursor-pointer list-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                            View properties
                                        </summary>
                                        <pre class="mt-2 overflow-x-auto rounded-2xl bg-slate-950 p-4 text-xs leading-6 text-slate-100">{{ json_encode($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                @elseif(is_string($properties) && $properties !== '')
                                    <div class="rounded-xl bg-slate-100 px-3 py-2 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        {{ $properties }}
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="mx-auto max-w-md">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                                        <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-6m3 6V7m3 10v-4M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                        </svg>
                                    </div>
                                    <h4 class="mt-4 text-base font-semibold text-slate-900 dark:text-white">No audit logs found</h4>
                                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                        Try changing your filters or perform some actions in the system to generate audit records.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($logs, 'links'))
            <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection