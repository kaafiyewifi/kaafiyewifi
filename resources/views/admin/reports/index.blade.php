@extends('layouts.admin')

@section('title','Reports')
@section('header','Reports')

@section('content')
@php
    $rangeSales = $salesSummary['range_sales'] ?? 0;
    $todaySales = $salesSummary['today_sales'] ?? 0;
    $monthSales = $salesSummary['month_sales'] ?? 0;

    $totalCustomers = $customerSummary['total'] ?? 0;
    $activeCustomers = $customerSummary['active'] ?? 0;
    $inactiveCustomers = $customerSummary['inactive'] ?? 0;
    $suspendedCustomers = $customerSummary['suspended'] ?? 0;
    $hotspotCustomers = $customerSummary['hotspot'] ?? 0;
    $pppoeCustomers = $customerSummary['pppoe'] ?? 0;

    $totalSubscriptions = $subscriptionSummary['total'] ?? 0;
    $activeSubscriptions = $subscriptionSummary['active'] ?? 0;
    $expiredSubscriptions = $subscriptionSummary['expired'] ?? 0;
    $pausedSubscriptions = $subscriptionSummary['paused'] ?? 0;
    $cancelledSubscriptions = $subscriptionSummary['cancelled'] ?? 0;

    $chartLabels = $salesChart['labels'] ?? [];
    $chartValues = $salesChart['values'] ?? [];

    $user = auth()->user();
    $isSuperAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('super_admin');
@endphp

<div class="space-y-6">

    {{-- Filters --}}
    <div class="bg-white shadow rounded-2xl p-6">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Start Date</label>
                <input
                    type="date"
                    name="start_date"
                    value="{{ $startDate ?? '' }}"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">End Date</label>
                <input
                    type="date"
                    name="end_date"
                    value="{{ $endDate ?? '' }}"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10"
                >
            </div>

            <div class="md:col-span-2 flex items-end gap-3">
                <button
                    type="submit"
                    class="inline-flex items-center rounded-xl bg-[#ff5437] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#e94b32]"
                >
                    Apply Filter
                </button>

                <a
                    href="{{ route('admin.reports.index') }}"
                    class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Sales Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-2xl p-6">
            <p class="text-sm text-slate-500">Today Sales</p>
            <h3 class="mt-2 text-3xl font-bold text-green-600">${{ number_format($todaySales, 2) }}</h3>
        </div>

        <div class="bg-white shadow rounded-2xl p-6">
            <p class="text-sm text-slate-500">Monthly Sales</p>
            <h3 class="mt-2 text-3xl font-bold text-blue-600">${{ number_format($monthSales, 2) }}</h3>
        </div>

        <div class="bg-white shadow rounded-2xl p-6">
            <p class="text-sm text-slate-500">Range Sales</p>
            <h3 class="mt-2 text-3xl font-bold text-[#ff5437]">${{ number_format($rangeSales, 2) }}</h3>
        </div>
    </div>

    {{-- Customer Summary --}}
    <div class="bg-white shadow rounded-2xl p-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Customer Summary</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4">
            <div class="rounded-xl bg-slate-50 p-4">
                <p class="text-xs text-slate-500">Total</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($totalCustomers) }}</p>
            </div>

            <div class="rounded-xl bg-green-50 p-4">
                <p class="text-xs text-green-600">Active</p>
                <p class="mt-2 text-2xl font-bold text-green-700">{{ number_format($activeCustomers) }}</p>
            </div>

            <div class="rounded-xl bg-slate-50 p-4">
                <p class="text-xs text-slate-500">Inactive</p>
                <p class="mt-2 text-2xl font-bold text-slate-700">{{ number_format($inactiveCustomers) }}</p>
            </div>

            <div class="rounded-xl bg-red-50 p-4">
                <p class="text-xs text-red-600">Suspended</p>
                <p class="mt-2 text-2xl font-bold text-red-700">{{ number_format($suspendedCustomers) }}</p>
            </div>

            <div class="rounded-xl bg-blue-50 p-4">
                <p class="text-xs text-blue-600">Hotspot</p>
                <p class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($hotspotCustomers) }}</p>
            </div>

            <div class="rounded-xl bg-purple-50 p-4">
                <p class="text-xs text-purple-600">PPPoE</p>
                <p class="mt-2 text-2xl font-bold text-purple-700">{{ number_format($pppoeCustomers) }}</p>
            </div>
        </div>
    </div>

    {{-- Subscription Summary --}}
    <div class="bg-white shadow rounded-2xl p-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Subscription Summary</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            <div class="rounded-xl bg-slate-50 p-4">
                <p class="text-xs text-slate-500">Total</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($totalSubscriptions) }}</p>
            </div>

            <div class="rounded-xl bg-green-50 p-4">
                <p class="text-xs text-green-600">Active</p>
                <p class="mt-2 text-2xl font-bold text-green-700">{{ number_format($activeSubscriptions) }}</p>
            </div>

            <div class="rounded-xl bg-red-50 p-4">
                <p class="text-xs text-red-600">Expired</p>
                <p class="mt-2 text-2xl font-bold text-red-700">{{ number_format($expiredSubscriptions) }}</p>
            </div>

            <div class="rounded-xl bg-yellow-50 p-4">
                <p class="text-xs text-yellow-600">Paused</p>
                <p class="mt-2 text-2xl font-bold text-yellow-700">{{ number_format($pausedSubscriptions) }}</p>
            </div>

            <div class="rounded-xl bg-slate-50 p-4">
                <p class="text-xs text-slate-500">Cancelled</p>
                <p class="mt-2 text-2xl font-bold text-slate-700">{{ number_format($cancelledSubscriptions) }}</p>
            </div>
        </div>
    </div>

    {{-- Sales Chart --}}
    <div class="bg-white shadow rounded-2xl p-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Sales Chart</h2>

        @if(count($chartLabels))
            <div class="overflow-x-auto">
                <div class="min-w-[700px]">
                    <canvas id="salesChart" height="110"></canvas>
                </div>
            </div>
        @else
            <div class="rounded-xl bg-slate-50 p-6 text-sm text-slate-500">
                No sales data found for the selected period.
            </div>
        @endif
    </div>

    {{-- Sales by Location --}}
    <div class="bg-white shadow rounded-2xl p-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Sales by Location</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Location</th>
                        <th class="px-4 py-3 text-center font-semibold">Subscriptions</th>
                        <th class="px-4 py-3 text-center font-semibold">Sales</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($salesByLocation as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">{{ $row->location_name }}</td>
                            <td class="px-4 py-3 text-center">{{ number_format($row->total_subscriptions) }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-green-600">${{ number_format($row->total_sales, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-400">No location sales found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sales by Admin --}}
    <div class="bg-white shadow rounded-2xl p-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">
            {{ $isSuperAdmin ? 'Sales by Admin' : 'My Sales' }}
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Admin</th>
                        <th class="px-4 py-3 text-center font-semibold">Subscriptions</th>
                        <th class="px-4 py-3 text-center font-semibold">Sales</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($salesByAdmin as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">{{ $row->admin_name }}</td>
                            <td class="px-4 py-3 text-center">{{ number_format($row->total_subscriptions) }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-[#ff5437]">${{ number_format($row->total_sales, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-400">No admin sales found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@if(count($chartLabels))
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('salesChart');

        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Sales',
                        data: @json($chartValues),
                        borderColor: '#ff5437',
                        backgroundColor: 'rgba(255,84,55,0.08)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    </script>
@endif
@endsection