<x-admin-layout>
<div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-8">
    <div class="mx-auto flex max-w-2xl justify-center px-4">
        <div class="w-full rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-800 dark:text-slate-100">
                        Create Subscription
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Add a new subscription plan
                    </p>
                </div>

                <a
                    href="{{ route('admin.subscriptions.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    ← Back
                </a>
            </div>

            <form method="POST" action="{{ route('admin.subscriptions.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Name
                    </label>
                    <input
                        name="name"
                        placeholder="Name"
                        value="{{ old('name') }}"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                    >
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Price
                        </label>
                        <input
                            name="price"
                            type="number"
                            step="0.01"
                            placeholder="Price"
                            value="{{ old('price') }}"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Base Days
                        </label>
                        <input
                            name="base_days"
                            type="number"
                            placeholder="Base Days"
                            value="{{ old('base_days') }}"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Upload Speed
                        </label>
                        <input
                            name="upload_speed"
                            type="number"
                            placeholder="Upload Speed"
                            value="{{ old('upload_speed') }}"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Upload Unit
                        </label>
                        <select
                            name="upload_unit"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                        >
                            <option value="Mbps" @selected(old('upload_unit') === 'Mbps')>Mbps</option>
                            <option value="Kbps" @selected(old('upload_unit') === 'Kbps')>Kbps</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Download Speed
                        </label>
                        <input
                            name="download_speed"
                            type="number"
                            placeholder="Download Speed"
                            value="{{ old('download_speed') }}"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Download Unit
                        </label>
                        <select
                            name="download_unit"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                        >
                            <option value="Mbps" @selected(old('download_unit') === 'Mbps')>Mbps</option>
                            <option value="Kbps" @selected(old('download_unit') === 'Kbps')>Kbps</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Status
                    </label>
                    <select
                        name="status"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                    >
                        <option value="active" @selected(old('status') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Description
                    </label>
                    <textarea
                        name="description"
                        rows="4"
                        placeholder="Description"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                    >{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a
                        href="{{ route('admin.subscriptions.index') }}"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Cancel
                    </a>

                    <button class="inline-flex items-center rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-orange-600">
                        Save
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
</x-admin-layout>