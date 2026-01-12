<x-admin-layout>
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow p-6">

    <h1 class="text-xl font-semibold mb-6">
        Create Subscription Plan
    </h1>

    <form method="POST"
          action="{{ route('admin.subscription-plans.store') }}"
          class="space-y-4">
        @csrf

        {{-- NAME --}}
        <input name="name" placeholder="Plan Name"
               class="w-full border rounded-lg px-4 py-2" required>

        {{-- PRICE --}}
        <input name="price" type="number" step="0.01"
               placeholder="Price"
               class="w-full border rounded-lg px-4 py-2" required>

        {{-- DOWNLOAD --}}
        <div class="grid grid-cols-3 gap-3">
            <input name="download_speed" type="number"
                   placeholder="Download"
                   class="border rounded-lg px-4 py-2">
            <select name="download_unit" class="border rounded-lg px-3">
                <option>Mbps</option>
                <option>Kbps</option>
                <option>Gbps</option>
            </select>
        </div>

        {{-- UPLOAD --}}
        <div class="grid grid-cols-3 gap-3">
            <input name="upload_speed" type="number"
                   placeholder="Upload"
                   class="border rounded-lg px-4 py-2">
            <select name="upload_unit" class="border rounded-lg px-3">
                <option>Mbps</option>
                <option>Kbps</option>
                <option>Gbps</option>
            </select>
        </div>

        {{-- DATA --}}
        <div class="grid grid-cols-3 gap-3">
            <select name="data_type" class="border rounded-lg px-3">
                <option value="unlimited">Unlimited</option>
                <option value="limited">Limited</option>
            </select>

            <input name="data_limit" type="number"
                   placeholder="Limit"
                   class="border rounded-lg px-4 py-2">

            <select name="data_unit" class="border rounded-lg px-3">
                <option>GB</option>
                <option>MB</option>
            </select>
        </div>

        {{-- DEVICES --}}
        <input name="devices" type="number" min="1"
               placeholder="Devices"
               class="w-full border rounded-lg px-4 py-2" required>

        {{-- STATUS --}}
        <select name="status" class="w-full border rounded-lg px-4 py-2">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>

        {{-- ACTIONS --}}
        <div class="flex gap-3 pt-6">
            <a href="{{ route('admin.subscription-plans.index') }}"
               class="flex-1 text-center border rounded-lg py-2">
                Cancel
            </a>

            <button class="flex-1 bg-[#5b146b] text-white rounded-lg py-2">
                Save Plan
            </button>
        </div>

    </form>
</div>
</x-admin-layout>
