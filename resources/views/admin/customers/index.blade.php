{{-- resources/views/admin/customers/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Customers')

@section('content')
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8 py-6">

        {{-- Header --}}
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    Customers
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Manage customers by location and status.
                </p>
            </div>

            @can('manage customers')
 <a href="{{ route('admin.customers.create') }}"
   class="inline-flex items-center justify-center gap-2
          rounded-xl px-5 py-2.5 text-sm font-semibold text-white
          bg-[#ff5938]
          hover:bg-[#e94f32]
          active:bg-[#d8462c]
          focus:outline-none focus:ring-2 focus:ring-[#ff5938]/40
          transition">
    + Add Customer
</a>

@endcan

        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800
                        dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800
                        dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="mb-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm
                    dark:border-gray-800 dark:bg-gray-950">
            <form method="GET" action="{{ route('admin.customers.index') }}"
                  class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

                {{-- Search --}}
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-200">
                        Search
                    </label>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Name / Username / Phone"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900
                               placeholder:text-gray-400
                               focus:border-[#5a116a] focus:ring-2 focus:ring-[#5a116a]/20 focus:outline-none
                               dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500"
                    >
                </div>

                {{-- Location --}}
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-200">
                        Location
                    </label>
                    <select
                        name="location_id"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900
                               focus:border-[#5a116a] focus:ring-2 focus:ring-[#5a116a]/20 focus:outline-none
                               dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                        <option value="">All locations</option>
                        @foreach(($locations ?? collect()) as $loc)
                            <option value="{{ $loc->id }}" @selected((string)request('location_id') === (string)$loc->id)>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-200">
                        Status
                    </label>
                    <select
                        name="status"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900
                               focus:border-[#5a116a] focus:ring-2 focus:ring-[#5a116a]/20 focus:outline-none
                               dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                        <option value="">All</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="w-full rounded-xl px-4 py-2.5 text-sm font-semibold text-white
                               bg-[#ff5938] hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[#ff5938]/30">
                        Apply
                    </button>

                    <a href="{{ route('admin.customers.index') }}"
                       class="w-full rounded-xl px-4 py-2.5 text-center text-sm font-semibold
                              bg-gray-100 text-gray-900 hover:bg-gray-200
                              dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm
                    dark:border-gray-800 dark:bg-gray-950">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wide text-gray-600
                                  dark:bg-gray-900 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Username</th>
                            <th class="px-4 py-3">Phone</th>
                            <th class="px-4 py-3">Location</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($customers as $c)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/60">
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $c->full_name }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        #{{ $c->id }}
                                    </div>
                                </td>

                                <td class="px-4 py-3 font-mono text-xs text-gray-800 dark:text-gray-200">
                                    {{ $c->username }}
                                </td>

                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $c->phone ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $c->location?->name ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    @if($c->is_active)
                                        <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-800
                                                     dark:bg-green-900/20 dark:text-green-200">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700
                                                     dark:bg-gray-900 dark:text-gray-200">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        @can('manage customers')
                                            <a href="{{ route('admin.customers.edit', $c) }}"
                                               class="rounded-xl px-3 py-1.5 text-xs font-semibold
                                                      bg-gray-100 text-gray-900 hover:bg-gray-200
                                                      dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                                                Edit
                                            </a>

                                            <form method="POST"
                                                  action="{{ route('admin.customers.destroy', $c) }}"
                                                  onsubmit="return confirm('Delete this customer?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="rounded-xl px-3 py-1.5 text-xs font-semibold text-white
                                                               bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/30">
                                                    Delete
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No customers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
                {{ $customers->appends(request()->query())->links() }}
            </div>
        </div>

    </div>
@endsection
