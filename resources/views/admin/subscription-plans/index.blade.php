<x-admin-layout>

<div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

    {{-- HEADER --}}
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-[#ff5437]">
            Subscription Plans
        </h1>

        <a
            href="{{ route('admin.subscription-plans.create') }}"
            class="bg-[#ff5437] hover:bg-[#e94b32] text-white px-4 py-2 rounded-lg text-sm font-medium"
        >
            + Create Plan
        </a>
    </div>

    {{-- CARD --}}
    <div class="bg-white dark:bg-darkCard rounded-xl shadow border dark:border-darkBorder overflow-hidden">

        <table class="w-full text-sm">
            <thead class="bg-[#fee8de] dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-center">Price</th>
                    <th class="px-4 py-3 text-center">Speed</th>
                    <th class="px-4 py-3 text-center">Devices</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y dark:divide-darkBorder">
                @forelse($plans as $i => $plan)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800">

                    <td class="px-4 py-3">
                        {{ $plans->firstItem() + $i }}
                    </td>

                    <td class="px-4 py-3 font-medium">
                        {{ $plan->name }}
                    </td>

                    <td class="px-4 py-3 text-center font-semibold text-green-600">
                        ${{ number_format($plan->price, 2) }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        @if($plan->download_speed)
                            ↓ {{ $plan->download_speed }} {{ $plan->download_unit }}
                        @else
                            —
                        @endif
                    </td>

                    <td class="px-4 py-3 text-center">
                        {{ $plan->devices }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 text-xs rounded
                            {{ $plan->status
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700' }}">
                            {{ $plan->status ? 'Active' : 'Inactive' }}
                        </span>
                    </td>

                    {{-- ACTIONS --}}
                    <td class="px-4 py-3 text-right space-x-2">

                        <a
                            href="{{ route('admin.subscription-plans.edit', $plan) }}"
                            class="text-blue-600 hover:text-blue-800"
                        >✏️</a>

                        <form
                            method="POST"
                            action="{{ route('admin.subscription-plans.toggle', $plan) }}"
                            class="inline"
                        >
                            @csrf
                            <button
                                type="submit"
                                class="{{ $plan->status ? 'text-yellow-600' : 'text-green-600' }}"
                            >
                                {{ $plan->status ? '⛔' : '✅' }}
                            </button>
                        </form>

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-10 text-gray-400">
                        No subscription plans found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- PAGINATION --}}
        <div class="p-4">
            {{ $plans->links() }}
        </div>

    </div>
</div>

</x-admin-layout>
