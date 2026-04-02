<x-admin-layout>
<div class="max-w-7xl mx-auto px-4 py-6">

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">
            Subscriptions
        </h1>

        @if(Route::has('admin.subscriptions.create'))
            <a
                href="{{ route('admin.subscriptions.create') }}"
                class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-lg text-sm font-medium shadow"
            >
                + Create Subscription
            </a>
        @endif
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow border border-slate-200 dark:border-slate-800">

        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex justify-end">
            <form method="GET" class="w-full max-w-sm">
                <input
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search subscription..."
                    class="w-full px-4 py-2 rounded-lg border bg-white dark:bg-slate-800 dark:border-slate-700 text-sm text-slate-800 dark:text-slate-100"
                >
            </form>
        </div>

        <div class="max-h-[420px] overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 z-10">
                    <tr class="text-slate-700 dark:text-slate-200">
                        <th class="px-4 py-3 text-left">ID</th>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Price</th>
                        <th class="px-4 py-3 text-left">Base Days</th>
                        <th class="px-4 py-3 text-left">Upload</th>
                        <th class="px-4 py-3 text-left">Download</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($subscriptions as $subscription)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-700 dark:text-slate-300">
                            <td class="px-4 py-3">#{{ $subscription->id }}</td>

                            <td class="px-4 py-3 font-medium">
                                {{ $subscription->name }}
                            </td>

                            <td class="px-4 py-3">
                                ${{ number_format($subscription->price, 2) }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $subscription->base_days }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $subscription->upload_speed ? $subscription->upload_speed . ' ' . $subscription->upload_unit : '—' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $subscription->download_speed ? $subscription->download_speed . ' ' . $subscription->download_unit : '—' }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs
                                    {{ $subscription->status === 'active'
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                        : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center space-x-3">
                                @if(Route::has('admin.subscriptions.show'))
                                    <a
                                        href="{{ route('admin.subscriptions.show', $subscription) }}"
                                        class="text-blue-600 dark:text-blue-400"
                                        title="View"
                                    >
                                        👁
                                    </a>
                                @endif

                                @if(Route::has('admin.subscriptions.edit'))
                                    <a
                                        href="{{ route('admin.subscriptions.edit', $subscription) }}"
                                        class="text-orange-500"
                                        title="Edit"
                                    >
                                        ✏️
                                    </a>
                                @endif

                                @if(Route::has('admin.subscriptions.destroy'))
                                    <form method="POST" action="{{ route('admin.subscriptions.destroy', $subscription) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="text-red-600 dark:text-red-400"
                                            title="Delete"
                                            onclick="return confirm('Delete this subscription?')"
                                        >
                                            🗑
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-slate-400 dark:text-slate-500">
                                No subscriptions found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $subscriptions->links() }}
    </div>

</div>
</x-admin-layout>
