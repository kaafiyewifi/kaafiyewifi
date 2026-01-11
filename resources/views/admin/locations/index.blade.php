<x-admin-layout>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div x-data="{ showCreate:false, showEdit:false, editLocation:{} }" class="space-y-6">

{{-- HEADER --}}
<div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-purple-700">Locations</h1>

    <button @click="showCreate=true"
        class="bg-green-600 text-white px-4 py-2 rounded">
        + Add Location
    </button>
</div>

{{-- TABLE --}}
<div class="bg-white rounded-xl shadow border overflow-hidden">

<table class="w-full text-sm">
<thead class="bg-purple-50 text-purple-700">
<tr>
    <th class="px-4 py-2 text-left">Name</th>
    <th class="px-4 py-2 text-left">Created</th>
    <th class="px-4 py-2 text-right">Actions</th>
</tr>
</thead>

<tbody class="divide-y">

@forelse($locations as $location)
<tr class="hover:bg-slate-50">

<td class="px-4 py-2 font-medium">{{ $location->name }}</td>

<td class="px-4 py-2">{{ $location->created_at->format('d M Y') }}</td>

<td class="px-4 py-2 text-right flex justify-end gap-2">

<button
@click='showEdit=true; editLocation=@json($location)'
class="text-blue-600">✏️</button>

<form method="POST" action="{{ route('admin.locations.destroy',$location) }}">
@csrf
@method('DELETE')
<button onclick="return confirm('Delete location?')" class="text-red-600">🗑</button>
</form>

</td>

</tr>
@empty
<tr>
<td colspan="3" class="text-center py-6 text-gray-400">
No locations found
</td>
</tr>
@endforelse

</tbody>
</table>
</div>

{{-- ================= CREATE MODAL ================= --}}
<div x-show="showCreate" x-cloak
class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

<form method="POST"
action="{{ route('admin.locations.store') }}"
class="bg-white p-6 rounded-xl w-full max-w-md space-y-4">

@csrf

<h2 class="text-lg font-semibold text-purple-700 text-center">
Create Location
</h2>

<input name="name"
class="w-full border p-2 rounded"
placeholder="Location name" required>

<div class="flex gap-3">
<button type="button" @click="showCreate=false"
class="flex-1 border py-2 rounded">Cancel</button>

<button class="flex-1 bg-purple-700 text-white py-2 rounded">
Save
</button>
</div>

</form>
</div>

{{-- ================= EDIT MODAL ================= --}}
<div x-show="showEdit" x-cloak
class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

<form method="POST"
:action="`/admin/locations/${editLocation.id}`"
class="bg-white p-6 rounded-xl w-full max-w-md space-y-4">

@csrf
@method('PUT')

<h2 class="text-lg font-semibold text-purple-700 text-center">
Edit Location
</h2>

<input name="name"
x-model="editLocation.name"
class="w-full border p-2 rounded"
required>

<div class="flex gap-3">
<button type="button" @click="showEdit=false"
class="flex-1 border py-2 rounded">Cancel</button>

<button class="flex-1 bg-purple-700 text-white py-2 rounded">
Update
</button>
</div>

</form>
</div>

</div>

</x-admin-layout>
