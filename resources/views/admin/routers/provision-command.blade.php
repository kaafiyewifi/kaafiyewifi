@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">Provision MikroTik Router</div>

    <div class="card-body">

        <p><strong>Router:</strong> {{ $router->name }} ({{ $router->identity }})</p>

        <form method="POST" action="{{ route('admin.routers.provision-token', $router->id) }}">
            @csrf
            <button class="btn btn-primary mb-3">Generate Provision Command</button>
        </form>

        @if(session('command'))
            <div class="alert alert-info">
                <strong>Copy this command and paste into MikroTik Terminal:</strong>
                <pre style="white-space: pre-wrap;">{{ session('command') }}</pre>
            </div>
        @endif

        <a href="{{ route('admin.routers.service-setup', $router->id) }}" class="btn btn-success">
            Continue to Service Setup →
        </a>

    </div>
</div>
@endsection
