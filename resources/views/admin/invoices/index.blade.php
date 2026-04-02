@extends('layouts.admin')

@section('title', 'Invoices')
@section('page_title', 'Invoices')

@section('content')
@php
    $invoiceCollection = collect($invoices->items() ?? []);

    $incomingStats = is_array($stats ?? null) ? $stats : [];

    $allStatsZero = ((int) ($incomingStats['total'] ?? 0) === 0)
        && ((int) ($incomingStats['paid'] ?? 0) === 0)
        && ((int) ($incomingStats['pending'] ?? 0) === 0)
        && ((float) ($incomingStats['paid_amount'] ?? 0) == 0.0);

    $displayStats = $incomingStats;

    if ($allStatsZero && $invoiceCollection->isNotEmpty()) {
        $displayStats = [
            'total' => method_exists($invoices, 'total') ? $invoices->total() : $invoiceCollection->count(),
            'paid' => $invoiceCollection->where('status', 'paid')->count(),
            'pending' => $invoiceCollection->where('status', 'pending')->count(),
            'paid_amount' => (float) $invoiceCollection->where('status', 'paid')->sum('amount'),
        ];
    }
@endphp

<div class="space-y-6">

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-sm text-slate-500 dark:text-slate-400">Total Invoices</div>
            <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($displayStats['total'] ?? 0) }}</div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-sm text-slate-500 dark:text-slate-400">Paid</div>
            <div class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($displayStats['paid'] ?? 0) }}</div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-sm text-slate-500 dark:text-slate-400">Pending</div>
            <div class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($displayStats['pending'] ?? 0) }}</div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-sm text-slate-500 dark:text-slate-400">Paid Amount</div>
            <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">${{ number_format((float) ($displayStats['paid_amount'] ?? 0), 2) }}</div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-200 p-5 dark:border-slate-800">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Search</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Invoice ID / customer / phone"
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
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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

                <div class="md:col-span-4 flex items-center gap-3">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-xl bg-[#ff5437] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#e94b32]"
                    >
                        Filter
                    </button>

                    <a
                        href="{{ route('admin.invoices.index') }}"
                        class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="p-5">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800">
                <div class="max-h-[520px] overflow-auto rounded-2xl">
                    <table class="min-w-[980px] w-full text-sm">
                        <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-800">
                            <tr class="text-left text-slate-600 dark:text-slate-300">
                                <th class="px-4 py-3">Invoice</th>
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3">Phone</th>
                                <th class="px-4 py-3 text-center">Amount</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Paid At</th>
                                <th class="px-4 py-3 text-center">Created</th>
                                <th class="px-4 py-3 text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($invoices as $invoice)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                    <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white whitespace-nowrap">
                                        INV-{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}
                                    </td>

                                    <td class="px-4 py-3 text-slate-900 dark:text-white whitespace-nowrap">
                                        {{ $invoice->full_name ?? $invoice->name ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-slate-900 dark:text-white whitespace-nowrap">
                                        {{ $invoice->phone ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-center font-semibold text-green-600 dark:text-green-400 whitespace-nowrap">
                                        ${{ number_format((float) $invoice->amount, 2) }}
                                    </td>

                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium
                                            {{ $invoice->status === 'paid'
                                                ? 'bg-green-100 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-900/30 dark:text-green-300 dark:ring-green-800'
                                                : ($invoice->status === 'pending'
                                                    ? 'bg-yellow-100 text-yellow-700 ring-1 ring-inset ring-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300 dark:ring-yellow-800'
                                                    : 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700') }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-center text-slate-900 dark:text-white whitespace-nowrap">
                                        {{ $invoice->paid_at ? \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y H:i') : '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-center text-slate-900 dark:text-white whitespace-nowrap">
                                        {{ $invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at)->format('d M Y H:i') : '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <a
                                            href="{{ route('admin.invoices.show', $invoice->id) }}"
                                            class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-600 transition hover:bg-blue-100 dark:border-blue-900 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/30"
                                        >
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">
                                        No invoices found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection