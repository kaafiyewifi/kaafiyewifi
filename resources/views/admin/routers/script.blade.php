@extends('layouts.admin')

@section('title', 'Provisioning Script')

@section('content')
<div class="flex items-start justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold">Provisioning Script</h1>
        <p class="text-sm text-gray-600 mt-1">
            Router: <span class="font-semibold">{{ $router->name }}</span>
            @if($router->location_name) • Location: <span class="font-semibold">{{ $router->location_name }}</span>@endif
        </p>
    </div>

    <form method="POST" action="{{ route('admin.routers.regenerate', $router) }}">
        @csrf
        <button class="rounded-xl px-4 py-2 border font-semibold"
                style="border-color:#ff4b2b;color:#ff4b2b">
            Regenerate Token
        </button>
    </form>
</div>

@if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 p-4 text-green-800">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
    <div class="bg-white rounded-2xl shadow p-4">
        <div class="text-sm text-gray-500">One-time Token</div>
        <div class="font-mono text-sm break-all mt-1">{{ $plain_token }}</div>
    </div>
    <div class="bg-white rounded-2xl shadow p-4">
        <div class="text-sm text-gray-500">API User</div>
        <div class="font-mono text-sm mt-1">kaafiye_api</div>
    </div>
    <div class="bg-white rounded-2xl shadow p-4">
        <div class="text-sm text-gray-500">API Password (save it)</div>
        <div class="font-mono text-sm break-all mt-1">{{ $api_password }}</div>
    </div>
</div>

<div x-data="{ copied:false }" class="bg-white rounded-2xl shadow overflow-hidden">
    <div class="flex items-center justify-between p-4 border-b">
        <div class="font-semibold">RouterOS Script (paste into MikroTik Terminal)</div>
        <button
            class="rounded-xl px-4 py-2 text-white font-semibold"
            style="background:#ff4b2b"
            @click="navigator.clipboard.writeText($refs.script.innerText); copied=true; setTimeout(()=>copied=false,1500);">
            <span x-show="!copied">Copy</span>
            <span x-show="copied">Copied ✓</span>
        </button>
    </div>

    <pre class="p-4 text-xs overflow-auto bg-gray-50" style="max-height:560px;"><code x-ref="script">{{ $script }}</code></pre>
</div>

<div class="mt-6 bg-white rounded-2xl shadow p-5">
    <h2 class="font-bold mb-2">Run Steps (MikroTik)</h2>
    <ol class="list-decimal ml-5 text-sm text-gray-700 space-y-1">
        <li>Router Terminal fur (Winbox / SSH).</li>
        <li>Paste script-ka oo dhan, kadib Enter.</li>
        <li>Haddii callback-ku guuleysto, status-ka router-ka wuxuu noqonayaa <b>provisioned</b>.</li>
        <li>Haddii callback fail: hubi DNS/WAN + walled garden + server access.</li>
    </ol>
</div>
@endsection
