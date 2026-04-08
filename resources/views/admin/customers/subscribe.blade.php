<x-admin-layout>
<div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-10">
    <div class="mx-auto flex max-w-md justify-center px-4">
        <div class="w-full rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

            {{-- HEADER --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                        Add Subscription
                    </h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Assign plan to customer
                    </p>
                </div>

                {{-- BACK BUTTON --}}
                <a
                    href="{{ route('admin.customers.show', $customer) }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    ← Back
                </a>
            </div>

            {{-- FORM --}}
            <form method="POST" action="{{ route('admin.customers.subscribe.store', $customer) }}" class="space-y-5">
                @csrf

                {{-- PLAN --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Select Plan
                    </label>
                    <select
                        name="plan_id"
                        required
                        {{ ($plans ?? collect())->count() === 0 ? 'disabled' : '' }}
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                    >
                        @forelse($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                                {{ $plan->name }} — ${{ $plan->price }}
                            </option>
                        @empty
                            <option value="">No plans available for this location</option>
                        @endforelse
                    </select>

                    @if(($plans ?? collect())->count() === 0)
                        <p class="mt-2 text-xs text-red-500 dark:text-red-400">
                            This customer location has no active subscription plans.
                        </p>
                    @endif
                </div>

                {{-- TYPE --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Duration Type
                    </label>
                    <select
                        name="type"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                    >
                        <option value="hours" @selected(old('type') === 'hours')>Hours</option>
                        <option value="days" @selected(old('type', 'days') === 'days')>Days</option>
                    </select>
                </div>

                {{-- VALUE --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Duration Value
                    </label>
                    <input
                        type="number"
                        name="value"
                        min="1"
                        required
                        value="{{ old('value') }}"
                        placeholder="Enter number"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                    >
                </div>

                {{-- ACTIONS --}}
                <div class="flex items-center gap-3 pt-2">
                    <a
                        href="{{ route('admin.customers.show', $customer) }}"
                        class="w-1/2 text-center rounded-lg border border-slate-300 bg-white py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        {{ ($plans ?? collect())->count() === 0 ? 'disabled' : '' }}
                        class="w-1/2 rounded-lg bg-orange-500 py-2 text-sm font-medium text-white transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Add
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
</x-admin-layout>