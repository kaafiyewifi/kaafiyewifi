<x-admin-layout>

<div
x-data="{
    showCreate:false,
    showEdit:false,
    editPlan:{},

    openEdit(plan){
        this.editPlan = plan
        this.showEdit = true
    }
}"
class="space-y-6"
>

{{-- HEADER --}}
<div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold">
        Subscription Plans
    </h1>

    <button
        @click="showCreate=true"
        class="bg-[#ff5b39] hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm shadow">
        + Create Plan
    </button>
</div>

{{-- TABLE --}}
<div class="bg-white rounded-xl shadow border overflow-hidden">

<table class="w-full text-sm">
<thead class="bg-slate-100 text-gray-600">
<tr>
    <th class="px-4 py-3 text-left">Name</th>
    <th class="px-4 py-3 text-left">Price</th>
    <th class="px-4 py-3 text-left">Speed</th>
    <th class="px-4 py-3 text-left">Data</th>
    <th class="px-4 py-3 text-left">Devices</th>
    <th class="px-4 py-3 text-right">Action</th>
</tr>
</thead>

<tbody class="divide-y">

@forelse($plans as $plan)
<tr class="hover:bg-slate-50">

    <td class="px-4 py-3 font-medium">{{ $plan->name }}</td>

    <td class="px-4 py-3 text-green-600">
        ${{ number_format($plan->price,2) }}
    </td>

    <td class="px-4 py-3">
        ↓ {{ $plan->download_speed }} {{ $plan->download_unit }}
        /
        ↑ {{ $plan->upload_speed }} {{ $plan->upload_unit }}
    </td>

    <td class="px-4 py-3">
        @if($plan->data_type === 'unlimited')
            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                Unlimited
            </span>
        @else
            {{ $plan->data_limit }} {{ $plan->data_unit }}
        @endif
    </td>

    <td class="px-4 py-3 text-blue-600">
        {{ $plan->devices }}
    </td>

    <td class="px-4 py-3 text-right space-x-3">

        <button
            @click='openEdit(@js($plan))'
            class="text-orange-500">
            ✏️
        </button>

        <form method="POST"
              action="{{ route('admin.subscription-plans.destroy',$plan) }}"
              class="inline">
            @csrf
            @method('DELETE')
            <button
                onclick="return confirm('Delete this plan?')"
                class="text-red-500">
                🗑
            </button>
        </form>

    </td>

</tr>
@empty
<tr>
    <td colspan="6" class="px-6 py-10 text-center text-gray-400">
        No subscription plans found
    </td>
</tr>
@endforelse

</tbody>
</table>
</div>

{{-- ================= CREATE MODAL ================= --}}
<div x-show="showCreate" x-cloak
class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">

<form method="POST"
action="{{ route('admin.subscription-plans.store') }}"
class="bg-white w-full max-w-md p-6 rounded-xl space-y-3">

@csrf

<h2 class="text-lg font-semibold text-center text-[#5b146b]">
Create Subscription Plan
</h2>

<input name="name" class="w-full border p-2 rounded" placeholder="Plan name" required>
<input name="price" type="number" step="0.01" class="w-full border p-2 rounded" placeholder="Price" required>

<div class="flex gap-2">
    <input name="download_speed" class="w-full border p-2 rounded" placeholder="Download speed">
    <select name="download_unit" class="border p-2 rounded">
        <option>Mbps</option>
        <option>Kbps</option>
    </select>
</div>

<div class="flex gap-2">
    <input name="upload_speed" class="w-full border p-2 rounded" placeholder="Upload speed">
    <select name="upload_unit" class="border p-2 rounded">
        <option>Mbps</option>
        <option>Kbps</option>
    </select>
</div>

<select name="data_type" class="w-full border p-2 rounded">
    <option value="unlimited">Unlimited</option>
    <option value="limited">Limited</option>
</select>

<div class="flex gap-2">
    <input name="data_limit" class="w-full border p-2 rounded" placeholder="Data limit">
    <select name="data_unit" class="border p-2 rounded">
        <option>GB</option>
        <option>MB</option>
    </select>
</div>

<input name="devices" type="number" class="w-full border p-2 rounded" placeholder="Devices" required>

<div class="flex gap-3 pt-3">
    <button type="button" @click="showCreate=false" class="flex-1 border py-2 rounded">
        Cancel
    </button>
    <button class="flex-1 bg-[#5b146b] text-white py-2 rounded">
        Save
    </button>
</div>

</form>
</div>

{{-- ================= EDIT MODAL ================= --}}
<div x-show="showEdit" x-cloak
class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">

<form method="POST"
:action="`/admin/subscription-plans/${editPlan.id}`"
class="bg-white w-full max-w-md p-6 rounded-xl space-y-3">

@csrf
@method('PUT')

<h2 class="text-lg font-semibold text-center text-[#5b146b]">
Edit Subscription Plan
</h2>

<input name="name" x-model="editPlan.name" class="w-full border p-2 rounded">
<input name="price" type="number" step="0.01" x-model="editPlan.price" class="w-full border p-2 rounded">

<div class="flex gap-2">
    <input name="download_speed" x-model="editPlan.download_speed" class="w-full border p-2 rounded">
    <select name="download_unit" x-model="editPlan.download_unit" class="border p-2 rounded">
        <option>Mbps</option>
        <option>Kbps</option>
    </select>
</div>

<div class="flex gap-2">
    <input name="upload_speed" x-model="editPlan.upload_speed" class="w-full border p-2 rounded">
    <select name="upload_unit" x-model="editPlan.upload_unit" class="border p-2 rounded">
        <option>Mbps</option>
        <option>Kbps</option>
    </select>
</div>

<select name="data_type" x-model="editPlan.data_type" class="w-full border p-2 rounded">
    <option value="unlimited">Unlimited</option>
    <option value="limited">Limited</option>
</select>

<div class="flex gap-2">
    <input name="data_limit" x-model="editPlan.data_limit" class="w-full border p-2 rounded">
    <select name="data_unit" x-model="editPlan.data_unit" class="border p-2 rounded">
        <option>GB</option>
        <option>MB</option>
    </select>
</div>

<input name="devices" type="number" x-model="editPlan.devices" class="w-full border p-2 rounded">

<div class="flex gap-3 pt-3">
    <button type="button" @click="showEdit=false" class="flex-1 border py-2 rounded">
        Cancel
    </button>
    <button class="flex-1 bg-[#5b146b] text-white py-2 rounded">
        Update
    </button>
</div>

</form>
</div>

</div>

</x-admin-layout>
