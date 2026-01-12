<x-admin-layout>

<div
    x-data="{
        showCreate:false,
        showEdit:false,
        editCustomer:{},
        openEdit(customer){
            this.editCustomer = customer
            this.showEdit = true
        }
    }"
    class="w-full flex justify-center"
>


<div class="w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- HEADER --}}
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Customers</h1>

        <button
            @click="showCreate = true"
            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm transition">
            + Create Customer
        </button>
    </div>

    {{-- TABLE CARD --}}
    <div class="bg-white rounded-xl shadow border overflow-hidden">

        {{-- SEARCH --}}
        <div class="flex justify-end px-4 py-4 border-b">
            <form method="GET" class="relative w-full sm:w-72">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search customer..."
                    class="w-full pl-9 pr-3 py-2 border rounded-lg text-sm focus:ring focus:ring-indigo-200"
                >
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
            </form>
        </div>

        @php
            $sort = request('sort');
            $dir  = request('dir','desc');
            $nextDir = $dir === 'asc' ? 'desc' : 'asc';
        @endphp

        {{-- TABLE --}}
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <a href="?sort=id&dir={{ $sort==='id' ? $nextDir : 'asc' }}&q={{ request('q') }}"
                           class="flex items-center gap-1">
                            ID
                            @if($sort==='id') {{ $dir==='asc'?'↑':'↓' }} @endif
                        </a>
                    </th>

                    <th class="px-4 py-3 text-left">
                        <a href="?sort=name&dir={{ $sort==='name' ? $nextDir : 'asc' }}&q={{ request('q') }}"
                           class="flex items-center gap-1">
                            Name
                            @if($sort==='name') {{ $dir==='asc'?'↑':'↓' }} @endif
                        </a>
                    </th>

                    <th class="px-4 py-3 text-left">
                        <a href="?sort=phone&dir={{ $sort==='phone' ? $nextDir : 'asc' }}&q={{ request('q') }}"
                           class="flex items-center gap-1">
                            Phone
                            @if($sort==='phone') {{ $dir==='asc'?'↑':'↓' }} @endif
                        </a>
                    </th>

                    <th class="px-4 py-3 text-left">Address</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($customers as $c)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-gray-500">#{{ $c->id }}</td>
                    <td class="px-4 py-3 font-medium">{{ $c->name }}</td>
                    <td class="px-4 py-3">{{ $c->phone }}</td>
                    <td class="px-4 py-3">{{ $c->address ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 text-xs rounded
                            {{ $c->status==='active'
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($c->status) }}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-center space-x-2">
                        <a href="{{ route('admin.customers.show',$c) }}"
                           class="text-blue-600 hover:text-blue-800">👁</a>

                        <button
                            type="button"
                            @click="
                                editCustomer = {
                                    id: {{ $c->id }},
                                    name: '{{ $c->name }}',
                                    phone: '{{ $c->phone }}',
                                    address: '{{ $c->address }}',
                                    status: '{{ $c->status }}'
                                };
                                showEdit = true;
                            "
                            class="text-orange-500 hover:text-orange-700">✏️</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-10 text-center text-gray-400">
                        No customers found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        {{-- PAGINATION ONLY (TEXT REMOVED ✅) --}}
        <div class="flex justify-end px-4 py-3 border-t">
            {{ $customers->links() }}
        </div>

    </div>

    {{-- MODALS --}}
    @include('admin.customers.modal-create')
    @include('admin.customers.modal-edit')

</div>
</div>

</x-admin-layout>
