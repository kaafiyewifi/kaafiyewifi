@extends('layouts.admin')

@section('content')
@php
    $m = $router->latestMetric ?? null;

    // --------------------------
    // Safe dates
    // --------------------------
    $lastSeenText = '-';
    if (!empty($router->last_seen_at)) {
        try { $lastSeenText = \Carbon\Carbon::parse($router->last_seen_at)->diffForHumans(); }
        catch (\Throwable $e) { $lastSeenText = (string) $router->last_seen_at; }
    }

    $collectedText = '-';
    if ($m && !empty($m->collected_at)) {
        try { $collectedText = \Carbon\Carbon::parse($m->collected_at)->diffForHumans(); }
        catch (\Throwable $e) { $collectedText = (string) $m->collected_at; }
    }

    // --------------------------
    // CPU
    // --------------------------
    $cpu = (is_object($m) && is_numeric($m->cpu_load)) ? (int) $m->cpu_load : null;
    $cpuPercent = is_numeric($cpu) ? max(0, min(100, $cpu)) : 0;

    // --------------------------
    // Memory
    // --------------------------
    $memTotal = (is_object($m) && is_numeric($m->total_memory)) ? (int) $m->total_memory : null;
    $memFree  = (is_object($m) && is_numeric($m->free_memory))  ? (int) $m->free_memory  : null;
    $memUsed  = (is_numeric($memTotal) && is_numeric($memFree)) ? max(0, $memTotal - $memFree) : null;

    $memTotalMB = is_numeric($memTotal) ? ($memTotal / 1024 / 1024) : null;
    $memUsedMB  = is_numeric($memUsed)  ? ($memUsed  / 1024 / 1024) : null;

    $memPercent = (is_numeric($memUsed) && is_numeric($memTotal) && $memTotal > 0)
        ? (int) round(($memUsed / $memTotal) * 100)
        : 0;
    $memPercent = max(0, min(100, $memPercent));

    // --------------------------
    // Disk
    // --------------------------
    $diskTotal = (is_object($m) && is_numeric($m->total_hdd_space)) ? (int) $m->total_hdd_space : null;
    $diskFree  = (is_object($m) && is_numeric($m->free_hdd_space))  ? (int) $m->free_hdd_space  : null;
    $diskUsed  = (is_numeric($diskTotal) && is_numeric($diskFree)) ? max(0, $diskTotal - $diskFree) : null;

    $diskTotalMB = is_numeric($diskTotal) ? ($diskTotal / 1024 / 1024) : null;
    $diskUsedMB  = is_numeric($diskUsed)  ? ($diskUsed  / 1024 / 1024) : null;

    $diskPercent = (is_numeric($diskUsed) && is_numeric($diskTotal) && $diskTotal > 0)
        ? (int) round(($diskUsed / $diskTotal) * 100)
        : 0;
    $diskPercent = max(0, min(100, $diskPercent));

    // --------------------------
    // Labels (IMPORTANT FIXES)
    // --------------------------
    $routerOs = !empty($router->routeros_version)
        ? $router->routeros_version
        : (!empty($router->routeros)
            ? $router->routeros
            : (!empty($router->router_os)
                ? $router->router_os
                : (($m && !empty($m->version)) ? $m->version : '-')));

    $mgmtIp   = !empty($router->mgmt_host) ? $router->mgmt_host : '-';
    $apiPort  = !empty($router->api_port) ? $router->api_port : (int) env('MIKROTIK_API_PORT', 8728);

    // ✅ Username from router_credentials table (relation: credential)
    $username = $router->credential?->username
        ?? ($router->api_username ?? env('ROUTER_API_USER', '-'));

    // Radius (fallback defaults)
    $radiusAddress      = !empty($router->radius_address) ? $router->radius_address : env('RADIUS_IP', '10.9.0.1');
    $radiusSecretMasked = '********';
    $radiusAuthPort     = !empty($router->radius_auth_port) ? $router->radius_auth_port : ((int) env('RADIUS_PORT', 1812) ?: 1812);
    $radiusAcctPort     = !empty($router->radius_acct_port) ? $router->radius_acct_port : ((int) env('RADIUS_ACCOUNTING_PORT', 1813) ?: 1813);

    // Tabs
    $tab = request('tab', 'system');

    $tabs = [
        'system'   => 'System Information',
        'events'   => 'Device Events',
        'reports'  => 'Reports',
        'users'    => 'Internet Users',
        'payments' => 'Payments',
        'backups'  => 'Backups',
    ];

    // --------------------------
    // History for charts (last 30 points)
    // NOTE: uses relationship if exists: $router->metrics()
    // --------------------------
    $history = collect();

    try {
        if (method_exists($router, 'metrics')) {
            $history = $router->metrics()
                ->orderBy('collected_at', 'desc')
                ->limit(30)
                ->get()
                ->reverse()
                ->values();
        }
    } catch (\Throwable $e) {
        $history = collect();
    }

    $chartLabels = [];
    $cpuSeries   = [];
    $memSeries   = [];
    $diskSeries  = [];

    foreach ($history as $hm) {
        $label = '-';
        if (!empty($hm->collected_at)) {
            try { $label = \Carbon\Carbon::parse($hm->collected_at)->format('H:i:s'); }
            catch (\Throwable $e) { $label = (string) $hm->collected_at; }
        }

        $hCpu = is_numeric($hm->cpu_load) ? (int) $hm->cpu_load : null;

        $hMemTotal = is_numeric($hm->total_memory) ? (int) $hm->total_memory : null;
        $hMemFree  = is_numeric($hm->free_memory)  ? (int) $hm->free_memory  : null;
        $hMemUsed  = (is_numeric($hMemTotal) && is_numeric($hMemFree)) ? max(0, $hMemTotal - $hMemFree) : null;
        $hMemPct   = (is_numeric($hMemUsed) && is_numeric($hMemTotal) && $hMemTotal > 0)
            ? (int) round(($hMemUsed / $hMemTotal) * 100)
            : null;

        $hDiskTotal = is_numeric($hm->total_hdd_space) ? (int) $hm->total_hdd_space : null;
        $hDiskFree  = is_numeric($hm->free_hdd_space)  ? (int) $hm->free_hdd_space  : null;
        $hDiskUsed  = (is_numeric($hDiskTotal) && is_numeric($hDiskFree)) ? max(0, $hDiskTotal - $hDiskFree) : null;
        $hDiskPct   = (is_numeric($hDiskUsed) && is_numeric($hDiskTotal) && $hDiskTotal > 0)
            ? (int) round(($hDiskUsed / $hDiskTotal) * 100)
            : null;

        $chartLabels[] = $label;
        $cpuSeries[]   = is_numeric($hCpu) ? max(0, min(100, $hCpu)) : null;
        $memSeries[]   = is_numeric($hMemPct) ? max(0, min(100, $hMemPct)) : null;
        $diskSeries[]  = is_numeric($hDiskPct) ? max(0, min(100, $hDiskPct)) : null;
    }
@endphp

<div class="px-6 py-6 max-w-6xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col gap-1 mb-4">
        <h1 class="text-xl font-semibold text-gray-900">{{ $router->name }}</h1>
        <div class="text-sm text-gray-500 flex flex-wrap gap-x-3 gap-y-1">
            <span>{{ !empty($router->identity) ? $router->identity : $mgmtIp }}</span>
            <span class="text-gray-300">•</span>
            <span>Last seen: <b class="text-gray-700">{{ $lastSeenText }}</b></span>
            <span class="text-gray-300">•</span>
            <span>Collected: <b class="text-gray-700">{{ $collectedText }}</b></span>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b mb-6">
        <nav class="flex flex-wrap gap-6 text-sm">
            @foreach($tabs as $key => $label)
                <a href="{{ url()->current() }}?tab={{ $key }}"
                   class="pb-3 -mb-px border-b-2 {{ $tab === $key ? 'border-orange-500 text-orange-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- ===================== --}}
    {{-- EVENTS TAB --}}
    {{-- ===================== --}}
    @if($tab === 'events')
        <div class="rounded-2xl border bg-white p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Device Events</h2>

                <a href="{{ url()->current() }}?tab=events"
                   class="text-sm font-semibold text-orange-600 hover:underline">
                    Refresh
                </a>
            </div>

            @php $eventsCount = (isset($router->events) && $router->events) ? $router->events->count() : 0; @endphp

            @if($eventsCount === 0)
                <p class="text-sm text-gray-500">No events yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($router->events as $e)
                        @php
                            $p = is_array($e->payload ?? null) ? $e->payload : [];
                            $status = isset($p['status']) ? $p['status'] : 'info';

                            $badge = 'bg-gray-100 text-gray-700 border-gray-200';
                            if ($status === 'success') $badge = 'bg-green-100 text-green-700 border-green-200';
                            elseif ($status === 'warning') $badge = 'bg-amber-100 text-amber-700 border-amber-200';
                            elseif ($status === 'error') $badge = 'bg-red-100 text-red-700 border-red-200';
                            elseif ($status === 'done') $badge = 'bg-blue-100 text-blue-700 border-blue-200';

                            $when = '-';
                            if (!empty($e->created_at)) {
                                try { $when = \Carbon\Carbon::parse($e->created_at)->diffForHumans(); }
                                catch (\Throwable $ex) { $when = (string) $e->created_at; }
                            }
                        @endphp

                        <div class="flex items-start justify-between gap-4 border rounded-xl p-4">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900 break-words">
                                    {{ isset($p['message']) ? $p['message'] : ($e->type ?? '-') }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $when }}
                                    @if(!empty($p['step'])) • {{ $p['step'] }} @endif
                                </div>
                            </div>

                            <span class="shrink-0 inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $badge }}">
                                {{ strtoupper($status) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-6">
                <a href="{{ route('admin.routers.index') }}"
                   class="inline-flex items-center text-sm font-semibold text-orange-600 hover:underline">
                    ← Back to routers
                </a>
            </div>
        </div>
    @endif

    {{-- ===================== --}}
    {{-- SYSTEM TAB --}}
    {{-- ===================== --}}
    @if($tab === 'system')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- General Information --}}
            <div class="rounded-2xl border bg-white p-6">
                <h2 class="font-semibold text-gray-900 mb-4">General Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="rounded-xl border bg-gray-50 p-4">
                        <div class="text-xs text-gray-500 mb-2">MANAGEMENT IP</div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="font-semibold text-gray-900 truncate">{{ $mgmtIp }}</div>
                            <button type="button"
                                    class="px-3 py-1.5 rounded-lg border bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                    onclick="navigator.clipboard.writeText('{{ $mgmtIp }}')">
                                Copy
                            </button>
                        </div>
                        <div class="text-xs text-gray-500 mt-2">RouterOS: {{ $routerOs }}</div>
                    </div>

                    <div class="rounded-xl border bg-gray-50 p-4">
                        <div class="text-xs text-gray-500 mb-2">USERNAME</div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="font-semibold text-gray-900 truncate">{{ $username }}</div>
                            <button type="button"
                                    class="px-3 py-1.5 rounded-lg border bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                    onclick="navigator.clipboard.writeText('{{ $username }}')">
                                Copy
                            </button>
                        </div>
                        <div class="text-xs text-gray-500 mt-2">Identity: {{ !empty($router->identity) ? $router->identity : '-' }}</div>
                    </div>

                    <div class="rounded-xl border bg-gray-50 p-4">
                        <div class="text-xs text-gray-500 mb-2">PASSWORD</div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="font-semibold text-gray-900 truncate">********</div>
                            <button type="button"
                                    class="px-3 py-1.5 rounded-lg border bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                    onclick="navigator.clipboard.writeText('********')">
                                Copy
                            </button>
                        </div>
                        <div class="text-xs text-gray-500 mt-2">Uptime: {{ ($m && !empty($m->uptime)) ? $m->uptime : '-' }}</div>
                    </div>

                    <div class="rounded-xl border bg-gray-50 p-4">
                        <div class="text-xs text-gray-500 mb-2">API PORT</div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="font-semibold text-gray-900 truncate">{{ $apiPort }}</div>
                            <button type="button"
                                    class="px-3 py-1.5 rounded-lg border bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                    onclick="navigator.clipboard.writeText('{{ $apiPort }}')">
                                Copy
                            </button>
                        </div>
                        <div class="text-xs text-gray-500 mt-2">Collected: {{ $collectedText }}</div>
                    </div>

                </div>
            </div>

            {{-- RADIUS Configuration --}}
            <div class="rounded-2xl border bg-white p-6">
                <h2 class="font-semibold text-gray-900 mb-4">RADIUS Configuration</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="rounded-xl border bg-gray-50 p-4">
                        <div class="text-xs text-gray-500 mb-2">RADIUS ADDRESS</div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="font-semibold text-gray-900 truncate">{{ $radiusAddress }}</div>
                            <button type="button"
                                    class="px-3 py-1.5 rounded-lg border bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                    onclick="navigator.clipboard.writeText('{{ $radiusAddress }}')">
                                Copy
                            </button>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-gray-50 p-4">
                        <div class="text-xs text-gray-500 mb-2">SECRET</div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="font-semibold text-gray-900 truncate">{{ $radiusSecretMasked }}</div>
                            <button type="button"
                                    class="px-3 py-1.5 rounded-lg border bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                    onclick="navigator.clipboard.writeText('{{ $radiusSecretMasked }}')">
                                Copy
                            </button>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-gray-50 p-4">
                        <div class="text-xs text-gray-500 mb-2">ACCOUNTING PORT</div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="font-semibold text-gray-900 truncate">{{ $radiusAcctPort }}</div>
                            <button type="button"
                                    class="px-3 py-1.5 rounded-lg border bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                    onclick="navigator.clipboard.writeText('{{ $radiusAcctPort }}')">
                                Copy
                            </button>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-gray-50 p-4">
                        <div class="text-xs text-gray-500 mb-2">AUTH PORT</div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="font-semibold text-gray-900 truncate">{{ $radiusAuthPort }}</div>
                            <button type="button"
                                    class="px-3 py-1.5 rounded-lg border bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                    onclick="navigator.clipboard.writeText('{{ $radiusAuthPort }}')">
                                Copy
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- Metrics cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">

            {{-- CPU --}}
            <div class="rounded-2xl border bg-white p-6">
                <div class="text-xs font-semibold text-gray-500">CPU USAGE</div>
                <div class="mt-2 text-3xl font-bold text-orange-600">
                    {{ $m ? ($cpuPercent . '%') : '—' }}
                </div>
                <div class="text-xs text-gray-500 mt-1">Current load</div>

                <div class="mt-4 h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-2 rounded-full bg-orange-500" style="width: {{ $m ? $cpuPercent : 0 }}%"></div>
                </div>
            </div>

            {{-- Memory --}}
            <div class="rounded-2xl border bg-white p-6">
                <div class="text-xs font-semibold text-gray-500">MEMORY USAGE</div>
                <div class="mt-2 text-3xl font-bold text-orange-600">
                    {{ $m ? ($memPercent . '%') : '—' }}
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    @if($m && is_numeric($memUsedMB) && is_numeric($memTotalMB))
                        {{ number_format($memUsedMB, 1) }}MB / {{ number_format($memTotalMB, 1) }}MB
                    @else
                        No data
                    @endif
                </div>

                <div class="mt-4 h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-2 rounded-full bg-orange-500" style="width: {{ $m ? $memPercent : 0 }}%"></div>
                </div>
            </div>

            {{-- Disk --}}
            <div class="rounded-2xl border bg-white p-6">
                <div class="text-xs font-semibold text-gray-500">DISK USAGE</div>
                <div class="mt-2 text-3xl font-bold text-orange-600">
                    {{ $m ? ($diskPercent . '%') : '—' }}
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    @if($m && is_numeric($diskUsedMB) && is_numeric($diskTotalMB))
                        {{ number_format($diskUsedMB, 1) }}MB / {{ number_format($diskTotalMB, 1) }}MB
                    @else
                        No data
                    @endif
                </div>

                <div class="mt-4 h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-2 rounded-full bg-orange-500" style="width: {{ $m ? $diskPercent : 0 }}%"></div>
                </div>
            </div>

        </div>

        {{-- DASHBOARD CHARTS --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            <div class="rounded-2xl border bg-white p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-900">CPU Trend</h3>
                    <span class="text-xs text-gray-500">last 30</span>
                </div>
                <div class="h-48">
                    <canvas id="cpuChart"></canvas>
                </div>
            </div>

            <div class="rounded-2xl border bg-white p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-900">Memory Trend</h3>
                    <span class="text-xs text-gray-500">last 30</span>
                </div>
                <div class="h-48">
                    <canvas id="memChart"></canvas>
                </div>
            </div>

            <div class="rounded-2xl border bg-white p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-900">Disk Trend</h3>
                    <span class="text-xs text-gray-500">last 30</span>
                </div>
                <div class="h-48">
                    <canvas id="diskChart"></canvas>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('admin.routers.index') }}"
               class="inline-flex items-center text-sm font-semibold text-orange-600 hover:underline">
                ← Back to routers
            </a>
        </div>
    @endif

    {{-- Other tabs placeholder --}}
    @if(!in_array($tab, ['system','events'], true))
        <div class="rounded-2xl border bg-white p-6">
            <p class="text-sm text-gray-500">This tab is coming next:
                <b class="text-gray-700">{{ isset($tabs[$tab]) ? $tabs[$tab] : $tab }}</b>
            </p>
        </div>
    @endif

</div>
@endsection

{{-- ✅ JS (Chart.js) --}}
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels  = @json($chartLabels);
    const cpuData = @json($cpuSeries);
    const memData = @json($memSeries);
    const diskData = @json($diskSeries);

    function mkLineChart(canvasId, label, data) {
        const el = document.getElementById(canvasId);
        if (!el) return;

        new Chart(el, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    tension: 0.35,
                    spanGaps: true,
                    pointRadius: 0,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true }
                },
                scales: {
                    y: {
                        suggestedMin: 0,
                        suggestedMax: 100,
                        ticks: { callback: (v) => v + '%' }
                    },
                    x: { ticks: { maxRotation: 0, autoSkip: true } }
                }
            }
        });
    }

    mkLineChart('cpuChart',  'CPU %',    cpuData);
    mkLineChart('memChart',  'Memory %', memData);
    mkLineChart('diskChart', 'Disk %',   diskData);
})();
</script>
@endsection