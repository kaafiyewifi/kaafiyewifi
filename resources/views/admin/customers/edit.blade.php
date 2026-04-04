@extends('layouts.admin')
@section('page_title','Edit Customer')

@section('content')
<div class="min-h-[calc(100vh-160px)] bg-slate-50 dark:bg-slate-950 px-4 py-8">
    <div class="mx-auto max-w-3xl">

        {{-- HEADER --}}
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                    Edit Customer
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Update customer details
                </p>
            </div>

            <a href="{{ route('admin.customers.index') }}"
               class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900">
                ← Back
            </a>
        </div>

        {{-- CARD --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950 sm:p-8">

            <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- TYPE --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Type</label>
                    <select name="type"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
                               dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        <option value="hotspot" @selected($customer->type=='hotspot')>Hotspot</option>
                        <option value="pppoe" @selected($customer->type=='pppoe')>PPPoE</option>
                    </select>
                </div>

                {{-- NAME --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Full Name</label>
                    <input type="text" name="full_name"
                        value="{{ $customer->full_name }}"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
                               dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>

                {{-- PHONE --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input type="text" name="phone"
                        value="{{ $customer->phone }}"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
                               dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>

                {{-- DEVICE LIMIT --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Device Limit</label>
                    <input type="number" name="device_limit" min="1"
                        value="{{ $customer->device_limit ?? 1 }}"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
                               dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>

                {{-- STATUS --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select name="status"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
                               dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        <option value="active" @selected($customer->status=='active')>Active</option>
                        <option value="inactive" @selected($customer->status=='inactive')>Inactive</option>
                        <option value="suspended" @selected($customer->status=='suspended')>Suspended</option>
                    </select>
                </div>

                {{-- SPEED OVERRIDE --}}
                <div class="flex items-center gap-3 pt-2">
                    <input type="hidden" name="speed_override_enabled" value="0">
                    <input type="checkbox" name="speed_override_enabled" value="1"
                        {{ $customer->speed_override_enabled ? 'checked' : '' }}
                        class="h-5 w-5">
                    <span class="text-sm">Enable Speed Override</span>
                </div>

                {{-- DOWNLOAD --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Download Speed</label>
                    <input type="number" name="download_speed"
                        value="{{ $customer->download_speed }}"
                        class="w-full rounded-lg border px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Download Unit</label>
                    <select name="download_unit" class="w-full rounded-lg border px-3 py-2 text-sm">
                        <option value="Kbps" @selected($customer->download_unit=='Kbps')>Kbps</option>
                        <option value="Mbps" @selected($customer->download_unit=='Mbps')>Mbps</option>
                        <option value="Gbps" @selected($customer->download_unit=='Gbps')>Gbps</option>
                    </select>
                </div>

                {{-- UPLOAD --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Upload Speed</label>
                    <input type="number" name="upload_speed"
                        value="{{ $customer->upload_speed }}"
                        class="w-full rounded-lg border px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Upload Unit</label>
                    <select name="upload_unit" class="w-full rounded-lg border px-3 py-2 text-sm">
                        <option value="Kbps" @selected($customer->upload_unit=='Kbps')>Kbps</option>
                        <option value="Mbps" @selected($customer->upload_unit=='Mbps')>Mbps</option>
                        <option value="Gbps" @selected($customer->upload_unit=='Gbps')>Gbps</option>
                    </select>
                </div>

                {{-- BUTTONS --}}
                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('admin.customers.index') }}"
                       class="px-4 py-2 rounded-lg border">
                        Cancel
                    </a>

                    <button type="submit"
                        class="px-5 py-2 rounded-lg bg-[#ff5437] text-white">
                        Update Customer
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection