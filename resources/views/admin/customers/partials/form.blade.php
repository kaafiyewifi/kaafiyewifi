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
        </select>
    </div>

    {{-- Enabled --}}
    <div class="flex items-center gap-3 pt-7">
        @php $enabled = (bool) old('is_active', $customer->is_active ?? true); @endphp
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" {{ $enabled ? 'checked' : '' }}
               class="h-5 w-5 rounded border-gray-300 text-[#ff4b2b] focus:ring-[#ff4b2b]/30">
        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Account Enabled</span>
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
