{{-- resources/views/admin/routers/create.blade.php --}}

<form method="POST" action="{{ route('admin.routers.store') }}">
  @csrf

  <div>
    <label>MikroTik Identity *</label>
    <input type="text" name="identity" value="{{ old('identity') }}" required maxlength="120">
    @error('identity') <div style="color:red">{{ $message }}</div> @enderror
    <small style="color:#666">System → Identity (RouterOS)</small>
  </div>

  <div style="margin-top:12px">
    <label>Display Name</label>
    <input type="text" name="name" value="{{ old('name') }}" maxlength="120">
    @error('name') <div style="color:red">{{ $message }}</div> @enderror
  </div>

  <div style="margin-top:12px">
    <label>Management Host (optional)</label>
    <input type="text" name="mgmt_host" value="{{ old('mgmt_host') }}" maxlength="255" placeholder="e.g. 41.xx.xx.xx or router.example.com">
    @error('mgmt_host') <div style="color:red">{{ $message }}</div> @enderror
  </div>

  <div style="margin-top:12px">
    <label>API Port</label>
    <input type="number" name="api_port" value="{{ old('api_port', 8728) }}" min="1" max="65535">
    @error('api_port') <div style="color:red">{{ $message }}</div> @enderror
  </div>

  <div style="margin-top:12px">
    <label>
      <input type="checkbox" name="use_tls" value="1" {{ old('use_tls') ? 'checked' : '' }}>
      Use TLS (api-ssl)
    </label>
  </div>

  <div style="margin-top:12px">
    <label>Notes</label>
    <textarea name="notes" rows="3">{{ old('notes') }}</textarea>
  </div>

  <div style="margin-top:18px">
    <button type="submit">Save</button>
  </div>
</form>
