@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
@php
    $stats = is_array($stats ?? null) ? $stats : [];
    $devices = collect($devices ?? [])->map(function ($device) {
        if (is_array($device)) {
            return $device;
        }

        if (is_object($device)) {
            return [
                'ip' => $device->ip ?? $device->framedipaddress ?? '-',
                'mac' => $device->mac ?? $device->callingstationid ?? '-',
                'uptime' => $device->uptime ?? '-',
                'usage' => $device->usage ?? '-',
                'last_connected' => $device->last_connected ?? $device->acctstarttime ?? '-',
            ];
        }

        return [
            'ip' => '-',
            'mac' => '-',
            'uptime' => '-',
            'usage' => '-',
            'last_connected' => '-',
        ];
    })->values()->all();

    $totalCustomers = (int) ($stats['total_customers'] ?? 0);
    $totalHotspot = (int) ($stats['total_hotspot_customers'] ?? 0);
    $totalPppoe = (int) ($stats['total_pppoe_customers'] ?? 0);
    $onlineUsers = (int) ($stats['online_users'] ?? 0);
    $todaySales = (float) ($stats['today_sales'] ?? 0);
    $monthlySales = (float) ($stats['monthly_sales'] ?? 0);

    $incomeChart = is_array($incomeChart ?? null) ? $incomeChart : [
        'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        'today' => [0, 0, 0, 0, 0, 0, 0],
        'monthly' => [0, 0, 0, 0, 0, 0, 0],
    ];

    $routerResources = is_array($routerResources ?? null) ? $routerResources : [
        'labels' => ['R1', 'R2', 'R3', 'R4', 'R5'],
        'cpu' => [0, 0, 0, 0, 0],
        'ram' => [0, 0, 0, 0, 0],
        'storage' => [0, 0, 0, 0, 0],
    ];

    $incomeChart['labels'] = array_values($incomeChart['labels'] ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']);
    $incomeChart['today'] = array_map('floatval', array_values($incomeChart['today'] ?? [0, 0, 0, 0, 0, 0, 0]));
    $incomeChart['monthly'] = array_map('floatval', array_values($incomeChart['monthly'] ?? [0, 0, 0, 0, 0, 0, 0]));

    $routerResources['labels'] = array_values($routerResources['labels'] ?? ['R1', 'R2', 'R3', 'R4', 'R5']);
    $routerResources['cpu'] = array_map('floatval', array_values($routerResources['cpu'] ?? [0, 0, 0, 0, 0]));
    $routerResources['ram'] = array_map('floatval', array_values($routerResources['ram'] ?? [0, 0, 0, 0, 0]));
    $routerResources['storage'] = array_map('floatval', array_values($routerResources['storage'] ?? [0, 0, 0, 0, 0]));

    $cpuAvg = count($routerResources['cpu']) ? round(collect($routerResources['cpu'])->avg()) : 0;
    $ramAvg = count($routerResources['ram']) ? round(collect($routerResources['ram'])->avg()) : 0;
    $storageAvg = count($routerResources['storage']) ? round(collect($routerResources['storage'])->avg()) : 0;

    $usageToMb = function ($usage) {
        if (!is_string($usage) || trim($usage) === '' || trim($usage) === '-') {
            return 0;
        }

        if (!preg_match('/([\d\.]+)\s*(B|KB|MB|GB|TB)/i', trim($usage), $matches)) {
            return 0;
        }

        $value = (float) $matches[1];
        $unit = strtoupper($matches[2]);

        return match ($unit) {
            'TB' => $value * 1024 * 1024,
            'GB' => $value * 1024,
            'MB' => $value,
            'KB' => $value / 1024,
            'B' => $value / (1024 * 1024),
            default => 0,
        };
    };

    $usageByDay = [];
    $totalInternetUsageMb = 0;

    for ($i = 6; $i >= 0; $i--) {
        $dayKey = now()->subDays($i)->format('Y-m-d');
        $usageByDay[$dayKey] = 0;
    }

    foreach ($devices as $device) {
        $usageMb = round($usageToMb($device['usage'] ?? '-'), 2);
        $lastConnected = $device['last_connected'] ?? null;

        if ($lastConnected && $lastConnected !== '-') {
            try {
                $dayKey = \Carbon\Carbon::parse($lastConnected)->format('Y-m-d');
                if (array_key_exists($dayKey, $usageByDay)) {
                    $usageByDay[$dayKey] += $usageMb;
                }
            } catch (\Throwable $e) {
            }
        }

        $totalInternetUsageMb += $usageMb;
    }

    $internetUsageLabels = [];
    $internetUsageData = [];

    foreach ($usageByDay as $dayKey => $totalMb) {
        $internetUsageLabels[] = \Carbon\Carbon::parse($dayKey)->format('D');
        $internetUsageData[] = round($totalMb, 2);
    }

    $cards = [
        [
            'title' => 'Total Customers',
            'value' => number_format($totalCustomers),
            'sub' => 'All registered customers',
            'tone' => 'blue',
        ],
        [
            'title' => 'Total Customer of Hotspot Users',
            'value' => number_format($totalHotspot),
            'sub' => 'All hotspot customers',
            'tone' => 'green',
        ],
        [
            'title' => 'Total PPPoE Customers',
            'value' => number_format($totalPppoe),
            'sub' => 'All PPPoE customers',
            'tone' => 'indigo',
        ],
        [
            'title' => 'Online Users',
            'value' => number_format($onlineUsers),
            'sub' => 'Users online right now',
            'tone' => 'emerald',
        ],
        [
            'title' => 'Today Sales',
            'value' => '$' . number_format($todaySales, 2),
            'sub' => 'Today total sales',
            'tone' => 'purple',
        ],
        [
            'title' => 'Monthly Sales',
            'value' => '$' . number_format($monthlySales, 2),
            'sub' => 'Current month sales',
            'tone' => 'blue',
        ],
    ];

    $tones = [
        'blue' => [
            'soft' => 'bg-blue-50 text-blue-600 dark:bg-blue-950/30 dark:text-blue-300',
            'dot' => 'bg-blue-500',
            'bar' => 'bg-blue-500',
        ],
        'green' => [
            'soft' => 'bg-green-50 text-green-600 dark:bg-green-950/30 dark:text-green-300',
            'dot' => 'bg-green-500',
            'bar' => 'bg-green-500',
        ],
        'indigo' => [
            'soft' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-300',
            'dot' => 'bg-indigo-500',
            'bar' => 'bg-indigo-500',
        ],
        'emerald' => [
            'soft' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-300',
            'dot' => 'bg-emerald-500',
            'bar' => 'bg-emerald-500',
        ],
        'purple' => [
            'soft' => 'bg-purple-50 text-purple-600 dark:bg-purple-950/30 dark:text-purple-300',
            'dot' => 'bg-purple-500',
            'bar' => 'bg-purple-500',
        ],
    ];
@endphp

<div class="space-y-4 overflow-x-hidden">

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach($cards as $card)
            @php
                $tone = $tones[$card['tone']] ?? $tones['blue'];
            @endphp

            <div class="rounded-[22px] border border-slate-200 bg-white px-5 py-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">
                            {{ $card['title'] }}
                        </div>
                        <div class="mt-3 break-words text-[18px] font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-[19px]">
                            {{ $card['value'] }}
                        </div>
                    </div>

                    <div class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $tone['soft'] }}">
                        <span class="h-3 w-3 rounded-full {{ $tone['dot'] }}"></span>
                    </div>
                </div>

                <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-full w-[8%] rounded-full {{ $tone['bar'] }}"></div>
                </div>

                <div class="mt-3 text-sm text-slate-600 dark:text-slate-300">
                    {{ $card['sub'] }}
                </div>
            </div>
        @endforeach
    </div>

    {{-- REVENUE + INTERNET USAGE CHARTS --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="text-[15px] font-semibold text-slate-900 dark:text-white">Chart Revenue</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Today vs monthly revenue trend</p>
                </div>
            </div>

            <div id="income-chart" class="mt-4 h-[285px] w-full"></div>
        </div>

        <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="text-[15px] font-semibold text-slate-900 dark:text-white">Internet Usage Total</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Total internet usage by days</p>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Total Usage</div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white">{{ number_format($totalInternetUsageMb, 2) }} MB</div>
                </div>
            </div>

            <div id="internet-usage-chart" class="mt-4 h-[285px] w-full"></div>
        </div>
    </div>

    {{-- BOTTOM TWO CARDS --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-[2fr_1fr]">
        <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div>
                <h3 class="text-[15px] font-semibold text-slate-900 dark:text-white">Routers CPU / RAM / Storage</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Per-router resource usage</p>
            </div>

            <div id="router-resource-chart" class="mt-4 h-[270px] w-full"></div>
        </div>

        <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div>
                <h3 class="text-[15px] font-semibold text-slate-900 dark:text-white">Router Resource Averages</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Average CPU, RAM, and storage usage</p>
            </div>

            <div id="router-radial-chart" class="mt-3 h-[230px] w-full"></div>

            <div class="mt-3 grid grid-cols-3 gap-3">
                <div class="rounded-2xl bg-slate-50 py-2 text-center dark:bg-slate-950/40">
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">CPU</div>
                    <div class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $cpuAvg }}%</div>
                </div>
                <div class="rounded-2xl bg-slate-50 py-2 text-center dark:bg-slate-950/40">
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">RAM</div>
                    <div class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $ramAvg }}%</div>
                </div>
                <div class="rounded-2xl bg-slate-50 py-2 text-center dark:bg-slate-950/40">
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Storage</div>
                    <div class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $storageAvg }}%</div>
                </div>
            </div>
        </div>
    </div>

    {{-- CONNECTED DEVICES --}}
    <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div>
            <h3 class="text-[15px] font-semibold text-slate-900 dark:text-white">Connected Devices</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                IP address, MAC address, up time, usage, and last connected
            </p>
        </div>

        <div class="mt-4 overflow-hidden">
            <div class="w-full overflow-x-auto overscroll-contain">
                <div class="min-w-[760px]">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr class="text-left text-slate-600 dark:text-slate-300">
                                <th class="px-4 py-3 whitespace-nowrap">IP Address</th>
                                <th class="px-4 py-3 whitespace-nowrap">MAC Address</th>
                                <th class="px-4 py-3 whitespace-nowrap">Up Time</th>
                                <th class="px-4 py-3 whitespace-nowrap">Usage</th>
                                <th class="px-4 py-3 whitespace-nowrap">Last Connected</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($devices as $device)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-900 dark:text-white">
                                        {{ $device['ip'] ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-mono text-xs text-slate-900 dark:text-white">
                                        {{ $device['mac'] ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-900 dark:text-white">
                                        {{ $device['uptime'] ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-900 dark:text-white">
                                        {{ $device['usage'] ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-900 dark:text-white">
                                        {{ $device['last_connected'] ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">
                                        No connected devices found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pt-3 text-center text-xs text-slate-500 dark:text-slate-400 sm:hidden">
                Swipe left/right inside the card to see full table
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const isDark = document.documentElement.classList.contains('dark');

        const textColor = isDark ? '#e2e8f0' : '#0f172a';
        const mutedColor = isDark ? '#94a3b8' : '#64748b';
        const borderColor = isDark ? '#1e293b' : '#e2e8f0';

        const incomeLabels = @json($incomeChart['labels'] ?? []);
        const incomeToday = @json($incomeChart['today'] ?? []);
        const incomeMonthly = @json($incomeChart['monthly'] ?? []);

        const internetUsageLabels = @json($internetUsageLabels ?? []);
        const internetUsageData = @json($internetUsageData ?? []);

        const routerLabels = @json($routerResources['labels'] ?? []);
        const routerCpu = @json($routerResources['cpu'] ?? []);
        const routerRam = @json($routerResources['ram'] ?? []);
        const routerStorage = @json($routerResources['storage'] ?? []);

        const cpuAvg = {{ (float) $cpuAvg }};
        const ramAvg = {{ (float) $ramAvg }};
        const storageAvg = {{ (float) $storageAvg }};

        if (typeof ApexCharts === 'undefined') {
            return;
        }

        const incomeChartEl = document.querySelector('#income-chart');
        if (incomeChartEl) {
            new ApexCharts(incomeChartEl, {
                chart: {
                    type: 'area',
                    height: 285,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    foreColor: textColor
                },
                series: [
                    { name: 'Today Revenue', data: incomeToday },
                    { name: 'Monthly Revenue', data: incomeMonthly }
                ],
                colors: ['#f97316', '#6366f1'],
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.24,
                        opacityTo: 0.03,
                        stops: [0, 95, 100]
                    }
                },
                dataLabels: { enabled: false },
                grid: {
                    borderColor: borderColor,
                    strokeDashArray: 5
                },
                xaxis: {
                    categories: incomeLabels,
                    labels: { style: { colors: mutedColor } },
                    axisBorder: { color: borderColor },
                    axisTicks: { color: borderColor }
                },
                yaxis: {
                    labels: {
                        style: { colors: mutedColor },
                        formatter: function (value) {
                            return '$' + value;
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    labels: { colors: textColor }
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function (value) {
                            return '$' + value;
                        }
                    }
                }
            }).render();
        }

        const internetUsageChartEl = document.querySelector('#internet-usage-chart');
        if (internetUsageChartEl) {
            new ApexCharts(internetUsageChartEl, {
                chart: {
                    type: 'bar',
                    height: 285,
                    toolbar: { show: false },
                    foreColor: textColor
                },
                series: [
                    { name: 'Usage MB', data: internetUsageData }
                ],
                colors: ['#22c55e'],
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '50%'
                    }
                },
                dataLabels: { enabled: false },
                grid: {
                    borderColor: borderColor,
                    strokeDashArray: 5
                },
                xaxis: {
                    categories: internetUsageLabels,
                    labels: {
                        style: { colors: mutedColor }
                    },
                    axisBorder: { color: borderColor },
                    axisTicks: { color: borderColor }
                },
                yaxis: {
                    labels: {
                        style: { colors: mutedColor },
                        formatter: function (value) {
                            return value + ' MB';
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    labels: { colors: textColor }
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function (value) {
                            return value + ' MB';
                        }
                    }
                }
            }).render();
        }

        const routerResourceChartEl = document.querySelector('#router-resource-chart');
        if (routerResourceChartEl) {
            new ApexCharts(routerResourceChartEl, {
                chart: {
                    type: 'bar',
                    height: 270,
                    toolbar: { show: false },
                    foreColor: textColor
                },
                series: [
                    { name: 'CPU %', data: routerCpu },
                    { name: 'RAM %', data: routerRam },
                    { name: 'Storage %', data: routerStorage }
                ],
                colors: ['#ef4444', '#8b5cf6', '#10b981'],
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '42%'
                    }
                },
                dataLabels: { enabled: false },
                stroke: { show: false },
                grid: {
                    borderColor: borderColor,
                    strokeDashArray: 5
                },
                xaxis: {
                    categories: routerLabels,
                    labels: { style: { colors: mutedColor } },
                    axisBorder: { color: borderColor },
                    axisTicks: { color: borderColor }
                },
                yaxis: {
                    max: 100,
                    labels: {
                        style: { colors: mutedColor },
                        formatter: function (value) {
                            return value + '%';
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    labels: { colors: textColor }
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function (value) {
                            return value + '%';
                        }
                    }
                }
            }).render();
        }

        const routerRadialChartEl = document.querySelector('#router-radial-chart');
        if (routerRadialChartEl) {
            new ApexCharts(routerRadialChartEl, {
                chart: {
                    type: 'radialBar',
                    height: 230,
                    foreColor: textColor
                },
                series: [cpuAvg, ramAvg, storageAvg],
                labels: ['CPU', 'RAM', 'Storage'],
                colors: ['#ef4444', '#8b5cf6', '#10b981'],
                plotOptions: {
                    radialBar: {
                        hollow: { size: '30%' },
                        dataLabels: {
                            name: {
                                color: mutedColor,
                                fontSize: '13px'
                            },
                            value: {
                                color: textColor,
                                fontSize: '16px',
                                formatter: function (val) {
                                    return Math.round(val) + '%';
                                }
                            },
                            total: {
                                show: true,
                                label: 'Average',
                                color: textColor,
                                formatter: function () {
                                    const avg = Math.round((cpuAvg + ramAvg + storageAvg) / 3);
                                    return avg + '%';
                                }
                            }
                        },
                        track: {
                            background: isDark ? '#0f172a' : '#f1f5f9'
                        }
                    }
                },
                stroke: {
                    lineCap: 'round'
                },
                legend: {
                    show: false
                }
            }).render();
        }
    });
</script>
@endsection