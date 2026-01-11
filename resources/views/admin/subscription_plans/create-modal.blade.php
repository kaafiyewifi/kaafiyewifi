<div x-data="{showCreate:false}">
<div x-show="showCreate" class="fixed inset-0 bg-black/50 flex items-center justify-center">

<form method="POST" action="{{ route('admin.subscription-plans.store') }}"
class="bg-white p-6 rounded w-96 space-y-3">
@csrf

<h2 class="text-lg font-semibold text-center">Create Plan</h2>

<input name="name" placeholder="Plan name" class="w-full border p-2 rounded">
<input name="price" placeholder="Price" class="w-full border p-2 rounded">

<select name="billing_cycle" class="w-full border p-2 rounded">
<option value="day">Day</option>
<option value="hour">Hour</option>
<option value="month">Month</option>
</select>

<input name="cycle_length" placeholder="Cycle length (e.g 30)" class="w-full border p-2 rounded">

<input name="speed_limit" placeholder="Speed Mbps" class="w-full border p-2 rounded">
<input name="bandwidth_limit" placeholder="Bandwidth GB" class="w-full border p-2 rounded">

<select name="data_type" class="w-full border p-2 rounded">
<option value="limited">Limited</option>
<option value="unlimited">Unlimited</option>
</select>

<div class="flex gap-2 pt-3">
<button type="button" @click="showCreate=false" class="flex-1 border py-2 rounded">Cancel</button>
<button class="flex-1 bg-purple-700 text-white py-2 rounded">Save</button>
</div>

</form>
</div>
</div>
