<x-admin-layout>

<div
    x-data="{
        showCreate:false,
        showEdit:false,
        editCustomer:null
    }"
    class="max-w-7xl mx-auto px-4 py-6"
>

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">
            Customers
        </h1>

        {{-- ✅ CREATE CUSTOMER BUTTON (FIXED) --}}
        <button
            @click="showCreate = true"
            class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-lg text-sm font-medium shadow"
        >
            + Create Customer
        </button>
    </div>

    {{-- CARD --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow border dark:border-slate-700">

        {{-- SEARCH --}}
        <div class="p-4 border-b dark:border-slate-700 flex justify-end">
            <form method="GET" class="w-full max-w-sm">
                <input
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search customer..."
                    class="w-full px-4 py-2 rounded-lg border
                           bg-white dark:bg-slate-800
                           dark:border-slate-700
                           text-sm"
                >
            </form>
        </div>

        {{-- TABLE SCROLL --}}
        <div class="max-h-[420px] overflow-y-auto">

            <table class="w-full text-sm">

                {{-- STICKY HEADER --}}
                <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 z-10">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Address</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y dark:divide-slate-700">

                    @forelse($customers as $customer)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800">

                        <td class="px-4 py-3 text-slate-500">
                            #{{ $customer->id }}
                        </td>

                        <td class="px-4 py-3 font-medium">
                            {{ $customer->name }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $customer->phone }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $customer->address ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs
                                {{ $customer->status=='active'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($customer->status) }}
                            </span>
                        </td>

                        {{-- ✅ ACTION ICONS (FIXED) --}}
                        <td class="px-4 py-3 text-center space-x-3">

                            {{-- VIEW --}}
                            <a href="{{ route('admin.customers.show',$customer) }}"
                               class="text-blue-600"
                               title="View">
                                👁
                            </a>

                            {{-- EDIT --}}
                            <button
                                @click="
                                    showEdit=true;
                                    editCustomer = {{ Js::from($customer) }}
                                "
                                class="text-orange-500"
                                title="Edit">
                                ✏️
                            </button>

                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-slate-400">
                            No customers found
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>

        </div>
    </div>

    {{-- ✅ PAGINATION (PAGE NUMBERS) --}}
    <div class="mt-4">
        {{ $customers->links() }}
    </div>

    {{-- CREATE MODAL --}}
    @include('admin.customers.modal-create')

    {{-- EDIT MODAL --}}
    @include('admin.customers.modal-edit')

</div>

</x-admin-layout>
