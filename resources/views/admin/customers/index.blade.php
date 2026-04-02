<x-admin-layout>

@php
    $perPage = (int) request('per_page', method_exists($customers, 'perPage') ? $customers->perPage() : 10);
    $allowedPerPage = [10, 50, 100, 500];

    if (!in_array($perPage, $allowedPerPage, true)) {
        $perPage = 10;
    }
@endphp

<div class="min-h-screen bg-slate-50 py-6 dark:bg-slate-950">
    <div
        x-data="{
            showCreate: false,
            showEdit: false,
            editCustomer: null
        }"
        class="mx-auto flex w-full max-w-7xl flex-col px-4 sm:px-6 lg:px-8"
    >
        {{-- HEADER --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-center sm:text-left">
                <h1 class="text-2xl font-semibold tracking-tight text-slate-800 dark:text-slate-100">
                    Customers
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Manage customer accounts
                </p>
            </div>

            <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
                <a
                    href="{{ route('admin.home') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    ← Back
                </a>

                <button
                    type="button"
                    @click="showCreate = true"
                    class="inline-flex items-center justify-center rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-orange-600"
                >
                    + Create Customer
                </button>
            </div>
        </div>

        {{-- CARD --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            {{-- CARD HEADER / FILTERS --}}
            <div class="border-b border-slate-200 p-4 dark:border-slate-800">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div class="text-center xl:text-left">
                        <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                            Customers List
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            View and manage all registered customers
                        </p>
                    </div>

                    <form method="GET" class="w-full xl:w-auto">
                        <div class="flex flex-col items-stretch justify-center gap-3 sm:flex-row xl:justify-end">
                            <div class="relative w-full sm:w-80">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 014.391 8.813l3.648 3.648a.75.75 0 11-1.06 1.06l-3.648-3.647A5.5 5.5 0 118.5 3zm-4 5.5a4 4 0 118 0 4 4 0 01-8 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>

                                <input
                                    type="text"
                                    name="q"
                                    value="{{ request('q') }}"
                                    placeholder="Search customer..."
                                    class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-orange-900/30"
                                >
                            </div>

                            @if(request()->filled('q'))
                                <input type="hidden" name="per_page" value="{{ $perPage }}">

                                <a
                                    href="{{ route('admin.customers.index', ['per_page' => $perPage]) }}"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                                >
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- FIXED HEIGHT TABLE AREA --}}
            <div class="h-[70vh] min-h-[520px] max-h-[820px]">
                <div class="h-full overflow-auto">
                    <div class="min-w-full overflow-x-auto">
                        <table class="min-w-[980px] w-full text-sm">
                            <thead class="sticky top-0 z-20 bg-slate-50 dark:bg-slate-800">
                                <tr class="text-left text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    <th class="px-6 py-4 whitespace-nowrap">ID</th>
                                    <th class="px-6 py-4 whitespace-nowrap">Name</th>
                                    <th class="px-6 py-4 whitespace-nowrap">Phone</th>
                                    <th class="px-6 py-4 whitespace-nowrap">Type</th>
                                    <th class="px-6 py-4 whitespace-nowrap">Device</th>
                                    <th class="px-6 py-4 whitespace-nowrap">Status</th>
                                    <th class="px-6 py-4 text-center whitespace-nowrap">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse($customers as $customer)
                                    <tr class="align-middle text-slate-700 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800/60">
                                        <td class="px-6 py-4 font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                            #{{ $customer->id }}
                                        </td>

                                        <td class="px-6 py-4 font-medium text-slate-800 dark:text-slate-100 whitespace-nowrap">
                                            {{ $customer->full_name ?? $customer->name }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $customer->phone }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ ucfirst($customer->type ?? '-') }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $customer->device_limit ?? 1 }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium
                                                {{ $customer->status === 'active'
                                                    ? 'bg-green-100 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-900/30 dark:text-green-300 dark:ring-green-800'
                                                    : 'bg-red-100 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-900/30 dark:text-red-300 dark:ring-red-800' }}">
                                                {{ ucfirst($customer->status) }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                <a
                                                    href="{{ route('admin.customers.show', $customer) }}"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 text-blue-600 transition hover:bg-blue-100 dark:border-blue-900 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                    title="View"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M10 3C5 3 1.73 7.11.46 9.06a1.74 1.74 0 000 1.88C1.73 12.89 5 17 10 17s8.27-4.11 9.54-6.06a1.74 1.74 0 000-1.88C18.27 7.11 15 3 10 3zm0 11a4 4 0 110-8 4 4 0 010 8z" />
                                                        <path d="M10 8a2 2 0 100 4 2 2 0 000-4z" />
                                                    </svg>
                                                </a>

                                                <button
                                                    type="button"
                                                    @click='showEdit = true; editCustomer = @json($customer)'
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-orange-200 bg-orange-50 text-orange-500 transition hover:bg-orange-100 dark:border-orange-900 dark:bg-orange-900/20 dark:text-orange-400 dark:hover:bg-orange-900/30"
                                                    title="Edit"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M17.414 2.586a2 2 0 010 2.828l-8.5 8.5A2 2 0 017.5 14.5H5a1 1 0 01-1-1V11a2 2 0 01.586-1.414l8.5-8.5a2 2 0 012.828 0z" />
                                                        <path d="M5 15a1 1 0 100 2h10a1 1 0 100-2H5z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-16 text-center text-slate-400 dark:text-slate-500">
                                            No customers found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- CARD FOOTER --}}
            <div class="border-t border-slate-200 px-4 py-4 dark:border-slate-800">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <form method="GET" class="flex justify-center md:justify-start">
                        @if(request()->filled('q'))
                            <input type="hidden" name="q" value="{{ request('q') }}">
                        @endif

                        <select
                            name="per_page"
                            onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30 sm:w-40"
                        >
                            @foreach($allowedPerPage as $size)
                                <option value="{{ $size }}" {{ $perPage === $size ? 'selected' : '' }}>
                                    Per page: {{ $size }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    <div class="flex justify-center overflow-x-auto">
                        {{ $customers->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>

            {{-- MOBILE SCROLL HINT --}}
            <div class="border-t border-slate-200 px-4 py-3 text-center text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400 sm:hidden">
                Swipe left/right to view full table
            </div>
        </div>

        {{-- CREATE MODAL --}}
        @include('admin.customers.modal-create')

        {{-- EDIT MODAL --}}
        @include('admin.customers.modal-edit')
    </div>
</div>

</x-admin-layout>