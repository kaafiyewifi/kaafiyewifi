{{-- resources/views/admin/routers/service-setup.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="container-fluid">

  <div class="card">
    <div class="card-header">
      <h4 class="mb-0">Device Configuration</h4>
      <small class="text-muted">Step 3: Configure PPPoE and Hotspot</small>
    </div>

    <div class="card-body">

      <div class="mb-3">
        <strong>Router:</strong> {{ $router->name }} <br>
        <small class="text-muted"><strong>Identity:</strong> {{ $router->router_identity }}</small>
      </div>

      @if ($errors->any())
        <div class="alert alert-danger">
          <strong>Please fix the errors below:</strong>
          <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('admin.routers.service-setup.store', $router) }}">
        @csrf

        <div class="mb-4">
          <label class="form-label fw-bold">Service Types *</label>
          <div class="d-flex gap-3 flex-wrap">
            <label class="border rounded px-3 py-2">
              <input type="checkbox" name="services[]" value="pppoe"
                {{ in_array('pppoe', old('services', [])) ? 'checked' : '' }}>
              PPPoE Server
            </label>

            <label class="border rounded px-3 py-2">
              <input type="checkbox" name="services[]" value="hotspot"
                {{ in_array('hotspot', old('services', [])) ? 'checked' : '' }}>
              Hotspot
            </label>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold">
            <input type="checkbox" name="use_custom_subnet" value="1" {{ old('use_custom_subnet') ? 'checked' : '' }}>
            Use Custom Subnet Configuration
          </label>

          <div class="mt-2" style="max-width: 360px;">
            <input type="text" name="subnet" class="form-control"
              value="{{ old('subnet') }}"
              placeholder="Default: 172.31.0.0/16">
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold">Ethernet Ports *</label>
          <small class="text-muted d-block mb-2">Select the ethernet ports to add to the bridge.</small>

          <div class="d-flex gap-3 flex-wrap">
            @foreach($ports as $p)
              <label class="border rounded px-3 py-2">
                <input type="checkbox" name="ports[]" value="{{ $p }}"
                  {{ in_array($p, old('ports', [])) ? 'checked' : '' }}>
                {{ $p }}
              </label>
            @endforeach
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold">
            <input type="checkbox" name="anti_sharing" value="1" {{ old('anti_sharing') ? 'checked' : '' }}>
            Anti-sharing
          </label>
        </div>

        <div class="d-flex justify-content-between">
          <a href="{{ route('admin.routers.show', $router) }}" class="btn btn-outline-secondary">
            ← Previous
          </a>

          <button type="submit" class="btn btn-primary">
            Configure Services →
          </button>
        </div>
      </form>

    </div>
  </div>

</div>
@endsection
