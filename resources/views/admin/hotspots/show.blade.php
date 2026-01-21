@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold">{{ $hotspot->name }}</h1>
            <div class="text-sm text-gray-600 mt-1">
                Location: <span class="font-medium">{{ $hotspot->location?->name ?? '-' }}</span>
            </div>
        </div>

        <a href="{{ route('admin.hotspots.index') }}" class="px-4 py-2 rounded border">Back</a>
    </div>

    <div class="mt-6 bg-white rounded shadow p-4 space-y-2 text-sm">
        <div><b>Status:</b> {{ strtoupper($hotspot->status) }}</div>
        <div><b>Online:</b> {{ $hotspot->is_online ? 'ONLINE' : 'OFFLINE' }}</div>
        <div><b>Last Seen:</b> {{ $hotspot->last_seen_at?->format('Y-m-d H:i:s') ?? '-' }}</div>
        <div><b>Active Users:</b> {{ $hotspot->active_users }}</div>
        <div><b>VPN IP:</b> {{ $hotspot->vpn_ip ?? '-' }}</div>
        <div><b>Router ID:</b> {{ $hotspot->router_id ?? '-' }}</div>
        <div><b>Address:</b> {{ $hotspot->effective_address ?? '-' }}</div>
    </div>
</div>
@endsection
