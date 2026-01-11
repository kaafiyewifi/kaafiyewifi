<x-admin-layout>

<link rel="stylesheet"
 href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>

<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div x-data="customerPage()" x-init="initChoices()" class="space-y-6">

{{-- HEADER --}}
<div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold">Customers</h1>

    <button
        @click="showCreate=true"
        class="bg-[#ff5b39] hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm">
        + Create Customer
    </button>
</div>

{{-- CARD --}}
<div class="bg-white dark:bg-[#0f0f14] rounded-xl shadow border overflow-hidden">

{{-- SEARCH --}}
<div class="flex justify-end px-4 py-3 border-b">
<form method="GET" class="relative">
<input name="q" value="{{ request('q') }}"
class="w-64 pl-9 pr-3 py-2 border rounded-lg text-sm"
placeholder="Search customer...">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
</form>
</div>

<table class="w-full text-sm">
<thead class="bg-slate-50 dark:bg-[#161622]">
<tr>
<th class="px-4 py-3 text-left">ID</th>
<th class="px-4 py-3 text-left">Customer</th>
<th class="px-4 py-3 text-left">Phone</th>
<th class="px-4 py-3 text-left">Address</th>
<th class="px-4 py-3 text-center">Status</th>
<th class="px-4 py-3 text-center">Action</th>
</tr>
</thead>

<tbody class="divide-y">
@foreach($customers as $c)
<tr class="hover:bg-slate-50 dark:hover:bg-white/5">
<td class="px-4 py-3 text-gray-500">#{{ $c->id }}</td>
<td class="px-4 py-3 font-medium">{{ $c->name }}</td>
<td class="px-4 py-3">{{ $c->phone }}</td>
<td class="px-4 py-3">{{ $c->address ?? '-' }}</td>
<td class="px-4 py-3 text-center">
<span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
{{ ucfirst($c->status) }}
</span>
</td>
<td class="px-4 py-3 text-center space-x-3">
<a href="{{ route('admin.customers.show',$c) }}">👁</a>
</td>
</tr>
@endforeach
</tbody>
</table>

<div class="px-4 py-3 border-t">
{{ $customers->links() }}
</div>

</div>
</div>

<script>
function customerPage(){
return{
showCreate:false,
showEdit:false,

initChoices(){
 new Choices('#createLocationSelect',{
 removeItemButton:true,searchEnabled:true,shouldSort:false,itemSelectText:''
 })
}
}
}
</script>

</x-admin-layout>
