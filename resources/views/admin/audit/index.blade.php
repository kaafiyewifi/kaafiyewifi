
@extends('layouts.admin')

@section('title', 'Audit Logs')
@section('page_title', 'Audit Logs')

@section('content')
<div class="space-y-6">

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-200 p-5 dark:border-slate-800">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-5">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Search</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Action / user / description / IP"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Action</label>
                    <select
                        name="action"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
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
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">User</label>
                    <select
                        name="user_id"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
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
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">From</label>
                    <input
                        type="date"
                        name="from"
                        value="{{ request('from') }}"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">To</label>
                    <input
                        type="date"
                        name="to"
                        value="{{ request('to') }}"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                </div>

                <div class="md:col-span-5 flex items-center gap-3">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-xl bg-[#ff5437] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#e94b32]"
                    >
                        Filter
                    </button>

                    <a
                        href="{{ route('admin.audit.index') }}"
                        class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="p-5">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800">
                <div class="max-h-[600px] overflow-auto rounded-2xl">
                    <table class="min-w-[1200px] w-full text-sm">
                        <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-800">
                            <tr class="text-left text-slate-600 dark:text-slate-300">
                                <th class="px-4 py-3">Time</th>
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3">Action</th>
                                <th class="px-4 py-3">Target</th>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3">IP Address</th>
                                <th class="px-4 py-3">Properties</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($logs as $log)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 align-top">
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-900 dark:text-white">
                                        {{ $log->created_at?->format('d M Y H:i:s') }}
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-slate-900 dark:text-white">
                                        {{ $log->user->name ?? 'System' }}
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-800">
                                            {{ $log->action }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-slate-900 dark:text-white">
                                        {{ $log->target_type ?? '—' }}
                                        @if($log->target_id)
                                            <span class="text-slate-400">#{{ $log->target_id }}</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 min-w-[320px] text-slate-900 dark:text-white">
                                        {{ $log->description ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-slate-900 dark:text-white">
                                        {{ $log->ip_address ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 min-w-[260px] text-slate-900 dark:text-white">
                                        @if(!empty($log->properties))
                                            <pre class="whitespace-pre-wrap break-words rounded-xl bg-slate-50 p-3 text-xs text-slate-700 dark:bg-slate-950 dark:text-slate-300">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">
                                        No audit logs found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection