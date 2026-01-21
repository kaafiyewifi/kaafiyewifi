<div>
  <label class="block text-sm mb-1">Name</label>
  <input name="name" value="{{ old('name', $user->name ?? '') }}"
         class="w-full rounded border border-gray-200 bg-white px-3 py-2">
  @error('name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
</div>

<div>
  <label class="block text-sm mb-1">Email</label>
  <input name="email" value="{{ old('email', $user->email ?? '') }}"
         class="w-full rounded border border-gray-200 bg-white px-3 py-2">
  @error('email') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
</div>

<div>
  <label class="block text-sm mb-1">Password @if($user) <span class="opacity-70">(leave blank to keep)</span> @endif</label>
  <input type="password" name="password"
         class="w-full rounded border border-gray-200 bg-white px-3 py-2">
  @error('password') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
</div>

<div>
  <label class="block text-sm mb-1">Role</label>
  @php($val = old('role', $currentRole ?? null))
  <select name="role" class="w-full rounded border border-gray-200 bg-white px-3 py-2">
    <option value="" disabled @selected(!$val)>Select role</option>
    @foreach($roles as $r)
      <option value="{{ $r }}" @selected($val===$r)>{{ $r }}</option>
    @endforeach
  </select>
  @error('role') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
</div>

<div>
  <div class="text-sm mb-2">Locations</div>
  @php($selected = old('locations', $selectedLocations ?? []))
  <div class="space-y-2">
    @foreach($locations as $loc)
      <label class="flex items-center gap-2">
        <input type="checkbox" name="locations[]" value="{{ $loc->id }}"
               @checked(in_array($loc->id, $selected))>
        <span>{{ $loc->name }} <span class="opacity-70 font-mono">({{ $loc->code }})</span></span>
      </label>
    @endforeach
  </div>
  @error('locations') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
  @error('locations.*') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
</div>
