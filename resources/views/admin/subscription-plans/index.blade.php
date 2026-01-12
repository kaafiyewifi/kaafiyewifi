<x-admin-layout>

<div class="w-full flex justify-center px-4 py-10">

    {{-- FIXED WIDTH --}}
    <div class="w-full max-w-[1100px] space-y-6">

        {{-- HEADER --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-white">
                Subscription Plans
            </h1>

            <a
                href="{{ route('admin.subscription-plans.create') }}"
                class="bg-indigo-600 hover:bg-indigo-700
                       text-white text-sm font-medium
                       px-5 py-2 rounded-lg transition"
            >
                + Create Plan
            </a>
        </div>

        {{-- CARD (LINE BORDER + FIXED HEIGHT) --}}
        <div
            class="bg-white dark:bg-slate-900
                   border border-slate-300 dark:border-slate-700
                   rounded-xl
                   h-[520px]
                   flex flex-col"
        >

            {{-- TABLE SCROLL AREA --}}
            <div class="flex-1 overflow-y-auto">

                <table class="w-full text-sm">

                    {{-- TABLE HEAD --}}
                    <thead class="sticky top-0 z-10
                                  bg-slate-100 dark:bg-slate-800
                                  border-b border-slate-300 dark:border-slate-700">
                        <tr class="text-slate-700 dark:text-slate-300">
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-center">Price</th>
                            <th class="px-5 py-3 text-center">Download</th>
                            <th class="px-5 py-3 text-center">Devices</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-center">Actions</th>
                        </tr>
                    </thead>

                    {{-- TABLE BODY --}}
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">

                        @forelse($plans as $plan)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800">

                            <td class="px-5 py-3 font-medium text-slate-800 dark:text-white">
                                {{ $plan->name }}
                            </td>

                            <td class="px-5 py-3 text-center text-slate-700 dark:text-slate-300">
                                ${{ number_format($plan->price, 2) }}
                            </td>

                            <td class="px-5 py-3 text-center text-slate-700 dark:text-slate-300">
                                {{ $plan->download_speed ?? '-' }}
                                {{ $plan->download_unit ?? '' }}
                            </td>

                            <td class="px-5 py-3 text-center">
                                {{ $plan->devices }}
                            </td>

                            <td class="px-5 py-3 text-center">
                                <span
                                    class="px-3 py-1 text-xs rounded-full font-semibold
                                    {{ $plan->status
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                        : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'
                                    }}"
                                >
                                    {{ $plan->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            {{-- ACTION ICONS --}}
                            <td class="px-5 py-3 text-center space-x-4">

                                {{-- EDIT --}}
                                <a
                                    href="{{ route('admin.subscription-plans.edit', $plan) }}"
                                    class="inline-flex items-center justify-center
                                           w-8 h-8 rounded-full
                                           hover:bg-indigo-100 dark:hover:bg-indigo-900
                                           text-indigo-600"
                                    title="Edit"
                                >
                                    ✏️
                                </a>

                                {{-- DELETE --}}
                                <form
                                    method="POST"
                                    action="{{ route('admin.subscription-plans.destroy', $plan) }}"
                                    class="inline"
                                    onsubmit="return confirm('Delete this plan?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center
                                               w-8 h-8 rounded-full
                                               hover:bg-red-100 dark:hover:bg-red-900
                                               text-red-600"
                                        title="Delete"
                                    >
                                        🗑
                                    </button>
                                </form>

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6"
                                class="py-20 text-center text-slate-500 dark:text-slate-400">
                                No subscription plans found
                            </td>
                        </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if($plans->hasPages())
            <div
                class="px-5 py-4
                       border-t border-slate-300 dark:border-slate-700
                       bg-slate-100 dark:bg-slate-800"
            >
                {{ $plans->links() }}
            </div>
            @endif

        </div>
    </div>
</div>

</x-admin-layout>
