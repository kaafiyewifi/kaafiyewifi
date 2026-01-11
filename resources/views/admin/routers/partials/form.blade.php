<div class="space-y-4">

    <div>
        <label class="form-label">Location</label>
        <select name="location_id" class="form-input">
            <option value="">-- Select Location --</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}"
                    @selected(old('location_id', $router->location_id ?? '') == $location->id)>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>
        @error('location_id') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label">Router Name</label>
        <input type="text" name="name"
               value="{{ old('name', $router->name ?? '') }}"
               class="form-input">
        @error('name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label">IP Address</label>
        <input type="text" name="ip_address"
               value="{{ old('ip_address', $router->ip_address ?? '') }}"
               class="form-input">
        @error('ip_address') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label">API Port</label>
        <input type="number" name="api_port"
               value="{{ old('api_port', $router->api_port ?? 8728) }}"
               class="form-input">
    </div>

    <div>
        <label class="form-label">Username</label>
        <input type="text" name="username"
               value="{{ old('username', $router->username ?? '') }}"
               class="form-input">
    </div>

    <div>
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-input">
        @if(isset($router))
            <p class="text-xs text-gray-500 mt-1">
                Leave blank to keep current password
            </p>
        @endif
    </div>

    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1"
               @checked(old('is_active', $router->is_active ?? true))>
        <span>Active</span>
    </div>

</div>
