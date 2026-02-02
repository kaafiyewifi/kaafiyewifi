@extends('layouts.admin')

@section('title', 'Provision MikroTik Router')

@section('content')
<div class="max-w-4xl mx-auto">

    <h2 class="text-2xl font-semibold mb-2">
        Provision Router: {{ $router->name }}
    </h2>

    <p class="text-gray-600 mb-6">
        Copy and run the following command in MikroTik terminal.
    </p>

    {{-- Command Box --}}
    <div class="bg-gray-900 text-green-400 rounded-lg p-4 relative">
        <button
            onclick="navigator.clipboard.writeText(`{{ $command }}`)"
            class="absolute top-2 right-2 bg-gray-700 text-white px-3 py-1 rounded text-sm hover:bg-gray-600">
            Copy
        </button>

        <pre class="whitespace-pre-wrap text-sm">{{ $command }}</pre>
    </div>

    {{-- Help --}}
    <div class="mt-4 bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded">
        <b>Note:</b><br>
        If MikroTik shows <code>device mode not allowed</code>, run:
        <pre class="mt-2 bg-white p-2 rounded text-sm">
/system/device-mode/update mode=advanced
        </pre>
        Then reboot the router and retry the command.
    </div>

    {{-- Actions --}}
    <div class="mt-6 flex gap-3">
        <form method="POST" action="{{ route('admin.routers.provision-token', $router) }}">
            @csrf
            <button class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700">
                Regenerate Token
            </button>
        </form>

        <a href="{{ route('admin.routers.show', $router) }}"
           class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">
            Back
        </a>
    </div>

</div>
@endsection
