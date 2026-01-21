@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Admin Panel')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Welcome / Actions --}}
        <div class="xl:col-span-2 card p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold">Admin Panel</h1>
                    <p class="mt-1 text-sm muted">
                        Everything is stable. Next: Locations & Hotspots.
                    </p>
                </div>

                <span class="inline-flex items-center gap-2 text-xs px-3 py-1 rounded-full
                             bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    System OK
                </span>
            </div>

            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('admin.locations.index') }}" class="btn-primary">
                    Locations
                </a>

                <a href="{{ route('admin.hotspots.index') }}" class="btn-outline">
                    Hotspots
                </a>

                @role('super_admin')
                <a href="{{ route('sa.users.index') }}" class="btn-outline">
                    Manage Users
                </a>
                @endrole
            </div>

            {{-- Performance placeholder --}}
            <div class="mt-6 placeholder-box">
                <div class="flex items-center justify-between">
                    <div class="font-semibold">Performance (placeholder)</div>
                    <div class="text-xs muted">Last 7 days</div>
                </div>
                <div class="mt-4 h-32 rounded-xl bg-gray-50 dark:bg-gray-950/40"></div>
                <div class="mt-3 text-xs muted">
                    (Chart will be connected later: revenue / online hotspots / active users)
                </div>
            </div>
        </div>

        {{-- Quick Status --}}
        <div class="card p-6">
            <h3 class="font-semibold">Quick Status</h3>
            <ul class="mt-4 space-y-3 text-sm">
                @foreach($quickStatus as $row)
                    <li class="flex items-center justify-between">
                        <span class="text-gray-700 dark:text-gray-200">{{ $row['label'] }}</span>
                        <span class="inline-flex items-center gap-2 {{ $row['ok'] ? 'text-green-700 dark:text-green-200' : 'text-red-700 dark:text-red-200' }}">
                            <span class="w-2 h-2 rounded-full {{ $row['ok'] ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            {{ $row['ok'] ? 'OK' : 'Issue' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>

    </div>

    {{-- Stats cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        @php
            $cards = [
                ['label' => 'Locations', 'value' => $stats['locations']],
                ['label' => 'Hotspots', 'value' => $stats['hotspots']],
                ['label' => 'Online Hotspots', 'value' => $stats['online_hotspots']],
                ['label' => 'Active Users', 'value' => $stats['active_users']],
                ['label' => 'Revenue Today', 'value' => '$' . number_format($stats['revenue_today'], 2)],
            ];
        @endphp

        @foreach($cards as $c)
            <div class="card p-5">
                <div class="text-xs muted">{{ $c['label'] }}</div>
                <div class="mt-2 text-2xl font-bold">{{ $c['value'] }}</div>

                <div class="mt-3 h-2 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                    <div class="h-full rounded-full" style="width:45%; background:#ff4b2b;"></div>
                </div>

                <div class="mt-2 text-xs muted">placeholder trend</div>
            </div>
        @endforeach
    </div>

    {{-- Bottom --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Activity --}}
        <div class="xl:col-span-2 card p-6">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold">Recent Activity</h3>
                <a href="{{ route('admin.audit.index') }}" class="text-sm hover:underline" style="color:#ff4b2b;">
                    View audit
                </a>
            </div>

            <div class="mt-4 space-y-3">
                @foreach($recentActivity as $a)
                    <div class="row-soft flex items-start justify-between gap-4">
                        <div>
                            <div class="font-semibold">{{ $a['title'] }}</div>
                            <div class="text-sm muted">{{ $a['meta'] }}</div>
                        </div>
                        <div class="text-xs muted whitespace-nowrap">{{ $a['time'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Next Module --}}
        <div class="card p-6">
            <h3 class="font-semibold">Next Module</h3>
            <p class="mt-2 text-sm muted">
                Phase 3: Locations + Hotspots + Monitoring fields (last_seen_at, active_users, vpn_ip, reported_ip)
            </p>

            <div class="mt-4 space-y-2 text-sm">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" style="background:#ff4b2b;"></span>
                    Locations CRUD
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" style="background:#ff4b2b;"></span>
                    Hotspots under Location
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" style="background:#ff4b2b;"></span>
                    Heartbeat status UI
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
