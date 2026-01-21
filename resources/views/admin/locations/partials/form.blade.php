@php
  $name    = old('name', optional($location)->name);
  $status  = old('status', optional($location)->status ?? 'active');
  $address = old('address', optional($location)->address);
  $city    = old('city', optional($location)->city);
@endphp

<div class="grid grid-cols-1 gap-5">
  <div>
    <label class="label">Name <span class="text-red-500">*</span></label>
    <input name="name" value="{{ $name }}" required class="input" placeholder="Ceelasha" />
    @error('name') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="label">Status <span class="text-red-500">*</span></label>
    <select name="status" class="select">
      <option value="active"   @selected($status==='active')>Active</option>
      <option value="inactive" @selected($status==='inactive')>Inactive</option>
    </select>
    @error('status') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="label">Address</label>
    <input name="address" value="{{ $address }}" class="input" placeholder="30street" />
    @error('address') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="label">City</label>
    <input name="city" value="{{ $city }}" class="input" placeholder="Mogadishu" />
    @error('city') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
  </div>

  @if($location)
    <p class="text-sm text-gray-600 dark:text-gray-300">
      Code: <span class="font-mono">{{ $location->code }}</span> (auto)
    </p>
  @endif
</div>
