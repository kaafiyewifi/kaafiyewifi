<x-admin-layout>
<div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-10">
    <div class="mx-auto flex max-w-3xl justify-center px-4">
        <div class="w-full rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

            {{-- HEADER --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-orange-500">
                        {{ $subscription->name }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Subscription details
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

            {{-- DETAILS --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 text-sm text-slate-700 dark:text-slate-300">

                <div class="rounded-xl bg-slate-50 dark:bg-slate-800/40 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Price</p>
                    <p class="mt-1 font-semibold text-green-600">
                        ${{ number_format($subscription->price, 2) }}
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 dark:bg-slate-800/40 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Base Days</p>
                    <p class="mt-1 font-semibold">
                        {{ $subscription->base_days }}
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 dark:bg-slate-800/40 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Upload Speed</p>
                    <p class="mt-1 font-semibold">
                        {{ $subscription->upload_speed ?? '—' }} {{ $subscription->upload_unit ?? '' }}
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 dark:bg-slate-800/40 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Download Speed</p>
                    <p class="mt-1 font-semibold">
                        {{ $subscription->download_speed ?? '—' }} {{ $subscription->download_unit ?? '' }}
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 dark:bg-slate-800/40 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Status</p>
                    <span class="inline-block mt-1 px-2 py-1 rounded text-xs
                        {{ $subscription->status === 'active'
                            ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                            : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' }}">
                        {{ ucfirst($subscription->status) }}
                    </span>
                </div>

                <div class="md:col-span-2 rounded-xl bg-slate-50 dark:bg-slate-800/40 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Description</p>
                    <p class="mt-1">
                        {{ $subscription->description ?? '—' }}
                    </p>
                </div>

            </div>

            {{-- ACTIONS --}}
            <div class="mt-6 flex items-center justify-end gap-3">
                <a
                    href="{{ route('admin.subscriptions.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    Cancel
                </a>

                <a
                    href="{{ route('admin.subscriptions.edit', $subscription) }}"
                    class="inline-flex items-center rounded-lg bg-orange-500 px-5 py-2 text-sm font-medium text-white transition hover:bg-orange-600"
                >
                    ✏️ Edit
                </a>
            </div>

        </div>
    </div>
</div>
</x-admin-layout>