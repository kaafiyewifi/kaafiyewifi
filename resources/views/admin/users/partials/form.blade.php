@php
    $isEdit = isset($user);

    $input = 'mt-2 w-full rounded-xl border border-gray-300 dark:border-gray-800
              bg-white dark:bg-gray-950 px-4 py-3 text-sm
              text-gray-900 dark:text-gray-100
              focus:border-[#ff4b2b] focus:ring-2 focus:ring-[#ff4b2b]/25 focus:outline-none';

    $label = 'block text-sm font-semibold text-gray-800 dark:text-gray-100';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

    {{-- Name --}}
    <div>
        <label class="{{ $label }}">Name</label>
        <input type="text" name="name"
               value="{{ old('name', $user->name ?? '') }}"
               class="{{ $input }}" required>
        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Email --}}
    <div>
        <label class="{{ $label }}">Email</label>
        <input type="email" name="email"
               value="{{ old('email', $user->email ?? '') }}"
               class="{{ $input }}">
        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Phone --}}
    <div>
        <label class="{{ $label }}">Phone (61XXXXXXXX)</label>
        <input type="text" name="phone"
               placeholder="6151234567"
               value="{{ old('phone', $user->phone ?? '') }}"
               class="{{ $input }}">
        @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Password --}}
    <div>
        <label class="{{ $label }}">
            Password
            @if($isEdit)
                <span class="text-xs text-gray-400">(leave empty to keep)</span>
            @endif
        </label>
        <input type="password" name="password"
               class="{{ $input }}" {{ $isEdit ? '' : 'required' }}>
        @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Role --}}
    <div>
        <label class="{{ $label }}">Role</label>
        <select name="role" class="{{ $input }}" required>
            @foreach($roles as $role)
                <option value="{{ $role }}"
                    {{ old('role', $currentRole ?? '') === $role ? 'selected' : '' }}>
                    {{ ucfirst($role) }}
                </option>
            @endforeach
        </select>
        @error('role') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Status --}}
    <div>
        <label class="{{ $label }}">Status</label>
        <select name="status" class="{{ $input }}">
            <option value="active"
                {{ old('status', $user->status ?? 'active') === 'active' ? 'selected' : '' }}>
                Active
            </option>
            <option value="inactive"
                {{ old('status', $user->status ?? '') === 'inactive' ? 'selected' : '' }}>
                Inactive
            </option>
        </select>
    </div>

    {{-- Locations --}}
    <div class="sm:col-span-2">
        <label class="{{ $label }}">Locations</label>

        @if($locations->count())
            <select id="locations" name="locations[]" multiple class="w-full">
                @foreach($locations as $loc)
                    <option value="{{ $loc->id }}"
                        @selected(in_array($loc->id, old('locations', $selectedLocations ?? [])))>
                        {{ $loc->name }}
                    </option>
                @endforeach
            </select>

            <p class="text-xs text-gray-500 mt-2">
                Choose where this user can access.
            </p>
        @else
            <p class="text-sm text-gray-500">
                No locations found.
                <a href="{{ route('admin.locations.create') }}"
                   class="underline" style="color:#ff4b2b;">
                    Create locations first
                </a>
            </p>
        @endif
    </div>

</div>

{{-- Submit --}}
<div class="mt-8 flex justify-end">
    <button type="submit"
            class="rounded-xl px-7 py-3 text-sm font-semibold text-white
                   hover:opacity-95"
            style="background:#ff4b2b;">
        {{ $isEdit ? 'Update User' : 'Create User' }}
    </button>
</div>
