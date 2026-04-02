<x-admin-layout>
<div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-8">
    <div class="mx-auto flex max-w-2xl justify-center px-4">
        <div class="w-full rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

            {{-- HEADER --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-800 dark:text-slate-100">
                        Edit Subscription
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Update subscription plan details
                    </p>
                </div>

                {{-- BACK BUTTON --}}
                <a
                    href="{{ route('admin.subscriptions.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    ← Back
                </a>
            </div>

            {{-- FORM --}}
            <form method="POST" action="{{ route('admin.subscriptions.update', $subscription) }}" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- NAME --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Name
                    </label>
                    <input
                        name="name"
                        value="{{ old('name', $subscription->name) }}"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                    >
                </div>

                {{-- PRICE + DAYS --}}
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Price
                        </label>
                        <input
                            name="price"
                            type="number"
                            step="0.01"
                            value="{{ old('price', $subscription->price) }}"
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
                            value="{{ old('base_days', $subscription->base_days) }}"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                        >
                    </div>
                </div>

                {{-- UPLOAD --}}
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Upload Speed
                        </label>
                        <input
                            name="upload_speed"
                            type="number"
                            value="{{ old('upload_speed', $subscription->upload_speed) }}"
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
                            <option value="Mbps" @selected(old('upload_unit', $subscription->upload_unit) == 'Mbps')>Mbps</option>
                            <option value="Kbps" @selected(old('upload_unit', $subscription->upload_unit) == 'Kbps')>Kbps</option>
                        </select>
                    </div>
                </div>

                {{-- DOWNLOAD --}}
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Download Speed
                        </label>
                        <input
                            name="download_speed"
                            type="number"
                            value="{{ old('download_speed', $subscription->download_speed) }}"
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
                            <option value="Mbps" @selected(old('download_unit', $subscription->download_unit) == 'Mbps')>Mbps</option>
                            <option value="Kbps" @selected(old('download_unit', $subscription->download_unit) == 'Kbps')>Kbps</option>
                        </select>
                    </div>
                </div>

                {{-- STATUS --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Status
                    </label>
                    <select
                        name="status"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                    >
                        <option value="active" @selected(old('status', $subscription->status) == 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $subscription->status) == 'inactive')>Inactive</option>
                    </select>
                </div>

                {{-- DESCRIPTION --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Description
                    </label>
                    <textarea
                        name="description"
                        rows="4"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                    >{{ old('description', $subscription->description) }}</textarea>
                </div>

                {{-- ACTIONS --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a
                        href="{{ route('admin.subscriptions.index') }}"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Cancel
                    </a>

                    <button class="inline-flex items-center rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-orange-600">
                        Update
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
</x-admin-layout>