<x-admin-layout>

<div
    x-data="{ dark: document.documentElement.classList.contains('dark') }"
    class="min-h-[80vh] flex items-center justify-center px-4"
>

    <div
        class="w-full max-w-xl bg-white dark:bg-slate-900
               rounded-2xl shadow-lg border
               border-slate-200 dark:border-slate-700 p-6"
    >

        {{-- TITLE --}}
        <h2 class="text-xl font-semibold text-center text-slate-800 dark:text-white mb-6">
            Create Subscription Plan
        </h2>

        {{-- FORM --}}
        <form
            method="POST"
            action="{{ route('admin.subscription-plans.store') }}"
            class="space-y-4"
        >
            @csrf

            {{-- PLAN NAME --}}
            <input
                name="name"
                placeholder="Plan Name (e.g Basic, Pro)"
                required
                class="w-full rounded-lg border px-4 py-2
                       bg-white dark:bg-slate-800
                       border-slate-300 dark:border-slate-600
                       text-slate-800 dark:text-white
                       focus:ring-2 focus:ring-indigo-500"
            >

            {{-- PRICE --}}
            <input
                name="price"
                type="number"
                step="0.01"
                required
                placeholder="Price (USD)"
                class="w-full rounded-lg border px-4 py-2
                       bg-white dark:bg-slate-800
                       border-slate-300 dark:border-slate-600
                       text-slate-800 dark:text-white"
            >

            {{-- DOWNLOAD --}}
            <div class="grid grid-cols-2 gap-3">
                <input
                    name="download_speed"
                    type="number"
                    placeholder="Download Speed"
                    class="rounded-lg border px-4 py-2
                           bg-white dark:bg-slate-800
                           border-slate-300 dark:border-slate-600
                           text-slate-800 dark:text-white"
                >

                <select
                    name="download_unit"
                    class="rounded-lg border px-3 py-2
                           bg-white dark:bg-slate-800
                           border-slate-300 dark:border-slate-600
                           text-slate-800 dark:text-white"
                >
                    <option>Mbps</option>
                    <option>Kbps</option>
                    <option>Gbps</option>
                </select>
            </div>

            {{-- UPLOAD --}}
            <div class="grid grid-cols-2 gap-3">
                <input
                    name="upload_speed"
                    type="number"
                    placeholder="Upload Speed"
                    class="rounded-lg border px-4 py-2
                           bg-white dark:bg-slate-800
                           border-slate-300 dark:border-slate-600
                           text-slate-800 dark:text-white"
                >

                <select
                    name="upload_unit"
                    class="rounded-lg border px-3 py-2
                           bg-white dark:bg-slate-800
                           border-slate-300 dark:border-slate-600
                           text-slate-800 dark:text-white"
                >
                    <option>Mbps</option>
                    <option>Kbps</option>
                    <option>Gbps</option>
                </select>
            </div>

            {{-- DATA --}}
            <div class="grid grid-cols-3 gap-3">
                <select
                    name="data_type"
                    class="rounded-lg border px-3 py-2
                           bg-white dark:bg-slate-800
                           border-slate-300 dark:border-slate-600
                           text-slate-800 dark:text-white"
                >
                    <option value="unlimited">Unlimited</option>
                    <option value="limited">Limited</option>
                </select>

                <input
                    name="data_limit"
                    type="number"
                    placeholder="Limit"
                    class="rounded-lg border px-4 py-2
                           bg-white dark:bg-slate-800
                           border-slate-300 dark:border-slate-600
                           text-slate-800 dark:text-white"
                >

                <select
                    name="data_unit"
                    class="rounded-lg border px-3 py-2
                           bg-white dark:bg-slate-800
                           border-slate-300 dark:border-slate-600
                           text-slate-800 dark:text-white"
                >
                    <option>GB</option>
                    <option>MB</option>
                </select>
            </div>

            {{-- DEVICES --}}
            <input
                name="devices"
                type="number"
                min="1"
                required
                placeholder="Simultaneous Devices"
                class="w-full rounded-lg border px-4 py-2
                       bg-white dark:bg-slate-800
                       border-slate-300 dark:border-slate-600
                       text-slate-800 dark:text-white"
            >

            {{-- STATUS --}}
            <select
                name="status"
                class="w-full rounded-lg border px-4 py-2
                       bg-white dark:bg-slate-800
                       border-slate-300 dark:border-slate-600
                       text-slate-800 dark:text-white"
            >
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>

            {{-- ACTIONS --}}
            <div class="flex gap-3 pt-4">
                <a
                    href="{{ route('admin.subscription-plans.index') }}"
                    class="flex-1 text-center border rounded-lg py-2
                           text-slate-700 dark:text-slate-300
                           border-slate-300 dark:border-slate-600"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="flex-1 bg-indigo-600 hover:bg-indigo-700
                           text-white rounded-lg py-2 font-medium"
                >
                    Save Plan
                </button>
            </div>

        </form>
    </div>
</div>

</x-admin-layout>
