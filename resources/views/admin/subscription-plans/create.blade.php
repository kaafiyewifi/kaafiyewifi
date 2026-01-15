<x-admin-layout>

<div class="max-w-xl mx-auto bg-white dark:bg-slate-900 rounded-xl shadow p-6">

    <h2 class="text-xl font-semibold mb-6 text-center text-slate-800 dark:text-slate-100">
        Create Subscription Plan
    </h2>

    <form
        method="POST"
        action="{{ route('admin.subscription-plans.store') }}"
        class="space-y-4"
    >
        @csrf

        {{-- PLAN NAME --}}
        <input
            type="text"
            name="name"
            placeholder="Plan name (e.g Basic, Pro)"
            required
            class="w-full border rounded-lg px-3 py-2 dark:bg-slate-800 dark:border-slate-700"
        >

        {{-- PRICE --}}
        <input
            type="number"
            step="0.01"
            name="price"
            placeholder="Price (USD)"
            required
            class="w-full border rounded-lg px-3 py-2 dark:bg-slate-800 dark:border-slate-700"
        >

        {{-- DOWNLOAD SPEED --}}
        <div class="grid grid-cols-2 gap-3">
            <input
                type="number"
                name="download_speed"
                placeholder="Download speed"
                class="border rounded-lg px-3 py-2 dark:bg-slate-800 dark:border-slate-700"
            >

            <select
                name="download_unit"
                class="border rounded-lg px-3 py-2 dark:bg-slate-800 dark:border-slate-700"
            >
                <option value="Mbps">Mbps</option>
                <option value="Kbps">Kbps</option>
            </select>
        </div>

        {{-- UPLOAD SPEED --}}
        <div class="grid grid-cols-2 gap-3">
            <input
                type="number"
                name="upload_speed"
                placeholder="Upload speed"
                class="border rounded-lg px-3 py-2 dark:bg-slate-800 dark:border-slate-700"
            >

            <select
                name="upload_unit"
                class="border rounded-lg px-3 py-2 dark:bg-slate-800 dark:border-slate-700"
            >
                <option value="Mbps">Mbps</option>
                <option value="Kbps">Kbps</option>
            </select>
        </div>

        {{-- DATA --}}
        <div class="grid grid-cols-3 gap-3">
            <select
                name="data_type"
                class="border rounded-lg px-3 py-2 dark:bg-slate-800 dark:border-slate-700"
            >
                <option value="unlimited">Unlimited</option>
                <option value="limited">Limited</option>
            </select>

            <input
                type="number"
                name="data_limit"
                placeholder="Limit"
                class="border rounded-lg px-3 py-2 dark:bg-slate-800 dark:border-slate-700"
            >

            <select
                name="data_unit"
                class="border rounded-lg px-3 py-2 dark:bg-slate-800 dark:border-slate-700"
            >
                <option value="GB">GB</option>
                <option value="MB">MB</option>
            </select>
        </div>

        {{-- DEVICES --}}
        <input
            type="number"
            name="devices"
            placeholder="Simultaneous devices"
            value="1"
            min="1"
            required
            class="w-full border rounded-lg px-3 py-2 dark:bg-slate-800 dark:border-slate-700"
        >

        {{-- STATUS --}}
        <select
            name="status"
            class="w-full border rounded-lg px-3 py-2 dark:bg-slate-800 dark:border-slate-700"
        >
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>

        {{-- ACTIONS --}}
        <div class="flex gap-3 pt-4">
            <a
                href="{{ route('admin.subscription-plans.index') }}"
                class="flex-1 text-center border rounded-lg py-2 dark:border-slate-700"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="flex-1 bg-[#ff5437] text-white rounded-lg py-2"
            >
                Save Plan
            </button>
        </div>

    </form>
</div>

</x-admin-layout>
