<x-admin-layout>
<div class="min-h-screen flex items-center justify-center px-4 py-10">

    <div class="w-full max-w-lg bg-white dark:bg-slate-900 shadow-xl rounded-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-6">

        {{-- TITLE --}}
        <div class="text-center">
            <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-100">
                Extend Subscription
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                {{ $customer->full_name ?? $customer->name }}
            </p>
        </div>

        {{-- PLAN INFO --}}
        <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-xl text-sm">
            <p><b>Plan:</b> {{ $plan->name }}</p>
            <p><b>Current Expiry:</b> {{ $subscription->expires_at?->format('d M Y') ?? '—' }}</p>
        </div>

        {{-- FORM --}}
        <form method="POST" action="{{ route('admin.subs.extend.post', $subscription) }}" class="space-y-4">
            @csrf

            <select name="type"
                class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2 text-slate-800 dark:text-slate-100">
                <option value="days">Days</option>
                <option value="hours">Hours</option>
            </select>

            <input
                type="number"
                name="value"
                min="1"
                required
                placeholder="Enter duration"
                class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2 text-slate-800 dark:text-slate-100"
            >

            <div class="flex justify-between items-center pt-4">

                {{-- BACK --}}
                <a href="{{ route('admin.customers.show', $customer) }}"
                   class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                    ← Back
                </a>

                {{-- SUBMIT --}}
                <button
                    class="bg-[#ff5437] hover:bg-[#e94b32] text-white px-5 py-2 rounded-xl text-sm font-medium"
                >
                    Extend
                </button>
            </div>
        </form>

    </div>

</div>
</x-admin-layout>