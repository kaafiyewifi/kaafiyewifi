@php
    $isEdit = isset($customer) && $customer;
@endphp

{{-- Errors --}}
@if ($errors->any())
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <div class="font-bold mb-1">Please fix the following:</div>
        <ul class="list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

    {{-- Type --}}
    <div>
        <label class="block text-sm font-semibold mb-1">Type</label>
        @php $type = old('type', $customer->type ?? 'hotspot'); @endphp
        <select name="type"
                class="w-full rounded-xl px-4 py-3 text-sm
                       border border-gray-200 dark:border-gray-800
                       bg-gray-50 dark:bg-gray-900
                       text-gray-900 dark:text-gray-100
                       focus:outline-none focus:ring-2 focus:ring-[#ff4b2b]/30 focus:border-[#ff4b2b]">
            <option value="hotspot" {{ $type === 'hotspot' ? 'selected' : '' }}>Hotspot</option>
            <option value="pppoe" {{ $type === 'pppoe' ? 'selected' : '' }}>PPPoE</option>
        </select>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Select customer access type.
        </p>
    </div>

    {{-- Device Limit --}}
    <div>
        <label class="block text-sm font-semibold mb-1">Device Limit</label>
        <input type="number" name="device_limit" min="1"
               value="{{ old('device_limit', $customer->device_limit ?? 1) }}"
               placeholder="1"
               class="w-full rounded-xl px-4 py-3 text-sm
                      border border-gray-200 dark:border-gray-800
                      bg-gray-50 dark:bg-gray-900
                      text-gray-900 dark:text-gray-100
                      placeholder-gray-400 dark:placeholder-gray-500
                      focus:outline-none focus:ring-2 focus:ring-[#ff4b2b]/30 focus:border-[#ff4b2b]">
    </div>

    {{-- Full Name --}}
    <div class="sm:col-span-2">
        <label class="block text-sm font-semibold mb-1">Full Name</label>
        <input type="text" name="full_name"
               value="{{ old('full_name', $customer->full_name ?? '') }}"
               placeholder="Customer full name"
               class="w-full rounded-xl px-4 py-3 text-sm
                      border border-gray-200 dark:border-gray-800
                      bg-gray-50 dark:bg-gray-900
                      text-gray-900 dark:text-gray-100
                      placeholder-gray-400 dark:placeholder-gray-500
                      focus:outline-none focus:ring-2 focus:ring-[#ff4b2b]/30 focus:border-[#ff4b2b]">
    </div>

    {{-- Phone --}}
    <div>
        <label class="block text-sm font-semibold mb-1">Phone (61XXXXXXX)</label>
        <input type="text" name="phone"
               value="{{ old('phone', $customer->phone ?? '') }}"
               placeholder="6151234567"
               class="w-full rounded-xl px-4 py-3 text-sm
                      border border-gray-200 dark:border-gray-800
                      bg-gray-50 dark:bg-gray-900
                      text-gray-900 dark:text-gray-100
                      placeholder-gray-400 dark:placeholder-gray-500
                      focus:outline-none focus:ring-2 focus:ring-[#ff4b2b]/30 focus:border-[#ff4b2b]">
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Username will be auto-set to phone. Default password: 123456
        </p>
    </div>

    {{-- Location --}}
    <div>
        <label class="block text-sm font-semibold mb-1">Location</label>
        <select name="location_id"
                class="w-full rounded-xl px-4 py-3 text-sm
                       border border-gray-200 dark:border-gray-800
                       bg-gray-50 dark:bg-gray-900
                       text-gray-900 dark:text-gray-100
                       focus:outline-none focus:ring-2 focus:ring-[#ff4b2b]/30 focus:border-[#ff4b2b]">
            <option value="">-- Select Location --</option>
            @foreach(($locations ?? []) as $loc)
                <option value="{{ $loc->id }}"
                    {{ (string) old('location_id', $customer->location_id ?? '') === (string) $loc->id ? 'selected' : '' }}>
                    {{ $loc->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Status --}}
    <div>
        <label class="block text-sm font-semibold mb-1">Status</label>
        @php $status = old('status', $customer->status ?? 'active'); @endphp
        <select name="status"
                class="w-full rounded-xl px-4 py-3 text-sm
                       border border-gray-200 dark:border-gray-800
                       bg-gray-50 dark:bg-gray-900
                       text-gray-900 dark:text-gray-100
                       focus:outline-none focus:ring-2 focus:ring-[#ff4b2b]/30 focus:border-[#ff4b2b]">
            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>Suspended</option>
        </select>
    </div>

    {{-- Speed Override --}}
    <div class="sm:col-span-2">
        @php $speedOverrideEnabled = (int) old('speed_override_enabled', ($customer->speed_override_enabled ?? 0)) === 1; @endphp
        <div class="flex items-center gap-3 pt-2">
            <input type="hidden" name="speed_override_enabled" value="0">
            <input type="checkbox" name="speed_override_enabled" value="1" {{ $speedOverrideEnabled ? 'checked' : '' }}
                   class="h-5 w-5 rounded border-gray-300 text-[#ff4b2b] focus:ring-[#ff4b2b]/30">
            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Enable Speed Override</span>
        </div>
    </div>

    {{-- Download Speed --}}
    <div>
        <label class="block text-sm font-semibold mb-1">Download Speed</label>
        <input type="number" name="download_speed" min="1"
               value="{{ old('download_speed', $customer->download_speed ?? '') }}"
               placeholder="10"
               class="w-full rounded-xl px-4 py-3 text-sm
                      border border-gray-200 dark:border-gray-800
                      bg-gray-50 dark:bg-gray-900
                      text-gray-900 dark:text-gray-100
                      placeholder-gray-400 dark:placeholder-gray-500
                      focus:outline-none focus:ring-2 focus:ring-[#ff4b2b]/30 focus:border-[#ff4b2b]">
    </div>

    {{-- Download Unit --}}
    <div>
        <label class="block text-sm font-semibold mb-1">Download Unit</label>
        @php $downloadUnit = old('download_unit', $customer->download_unit ?? 'Mbps'); @endphp
        <select name="download_unit"
                class="w-full rounded-xl px-4 py-3 text-sm
                       border border-gray-200 dark:border-gray-800
                       bg-gray-50 dark:bg-gray-900
                       text-gray-900 dark:text-gray-100
                       focus:outline-none focus:ring-2 focus:ring-[#ff4b2b]/30 focus:border-[#ff4b2b]">
            <option value="Kbps" {{ $downloadUnit === 'Kbps' ? 'selected' : '' }}>Kbps</option>
            <option value="Mbps" {{ $downloadUnit === 'Mbps' ? 'selected' : '' }}>Mbps</option>
            <option value="Gbps" {{ $downloadUnit === 'Gbps' ? 'selected' : '' }}>Gbps</option>
        </select>
    </div>

    {{-- Upload Speed --}}
    <div>
        <label class="block text-sm font-semibold mb-1">Upload Speed</label>
        <input type="number" name="upload_speed" min="1"
               value="{{ old('upload_speed', $customer->upload_speed ?? '') }}"
               placeholder="10"
               class="w-full rounded-xl px-4 py-3 text-sm
                      border border-gray-200 dark:border-gray-800
                      bg-gray-50 dark:bg-gray-900
                      text-gray-900 dark:text-gray-100
                      placeholder-gray-400 dark:placeholder-gray-500
                      focus:outline-none focus:ring-2 focus:ring-[#ff4b2b]/30 focus:border-[#ff4b2b]">
    </div>

    {{-- Upload Unit --}}
    <div>
        <label class="block text-sm font-semibold mb-1">Upload Unit</label>
        @php $uploadUnit = old('upload_unit', $customer->upload_unit ?? 'Mbps'); @endphp
        <select name="upload_unit"
                class="w-full rounded-xl px-4 py-3 text-sm
                       border border-gray-200 dark:border-gray-800
                       bg-gray-50 dark:bg-gray-900
                       text-gray-900 dark:text-gray-100
                       focus:outline-none focus:ring-2 focus:ring-[#ff4b2b]/30 focus:border-[#ff4b2b]">
            <option value="Kbps" {{ $uploadUnit === 'Kbps' ? 'selected' : '' }}>Kbps</option>
            <option value="Mbps" {{ $uploadUnit === 'Mbps' ? 'selected' : '' }}>Mbps</option>
            <option value="Gbps" {{ $uploadUnit === 'Gbps' ? 'selected' : '' }}>Gbps</option>
        </select>
    </div>
</div>

{{-- Actions --}}
<div class="mt-8 flex items-center justify-end gap-3">
    <a href="{{ route('admin.customers.index') }}"
       class="rounded-xl px-5 py-2.5 text-sm font-semibold
              border border-gray-200 dark:border-gray-800
              bg-white dark:bg-gray-950 hover:bg-gray-50 dark:hover:bg-gray-900">
        Cancel
    </a>

    <button type="submit"
            class="rounded-xl px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-95"
            style="background:#ff4b2b;">
        {{ $isEdit ? 'Save Changes' : 'Create Customer' }}
    </button>
</div>