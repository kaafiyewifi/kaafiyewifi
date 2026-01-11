<div
x-show="showEdit"
x-cloak
class="fixed inset-0 bg-black/60 flex items-center justify-center z-50"
>

<form
method="POST"
:action="`/admin/subscription-plans/${editPlan.id}`"
class="bg-[#0f0f14] text-white w-full max-w-md p-6 rounded-xl space-y-3 border border-white/10"
>
@csrf
@method('PUT')

<h2 class="text-lg font-semibold text-center text-purple-400">
Edit Subscription Plan
</h2>

<input name="name" x-model="editPlan.name"
class="w-full bg-[#161622] border border-white/10 p-2 rounded"
placeholder="Plan name">

<input name="price" type="number" step="0.01"
x-model="editPlan.price"
class="w-full bg-[#161622] border border-white/10 p-2 rounded"
placeholder="Price">

<div class="flex gap-2">
<input name="download_speed" x-model="editPlan.download_speed"
class="w-full bg-[#161622] border border-white/10 p-2 rounded"
placeholder="Download speed">

<select name="download_unit"
x-model="editPlan.download_unit"
class="bg-[#161622] border border-white/10 p-2 rounded">
<option value="Mbps">Mbps</option>
<option value="Kbps">Kbps</option>
</select>
</div>

<div class="flex gap-2">
<input name="upload_speed" x-model="editPlan.upload_speed"
class="w-full bg-[#161622] border border-white/10 p-2 rounded"
placeholder="Upload speed">

<select name="upload_unit"
x-model="editPlan.upload_unit"
class="bg-[#161622] border border-white/10 p-2 rounded">
<option value="Mbps">Mbps</option>
<option value="Kbps">Kbps</option>
</select>
</div>

<select name="data_type"
x-model="editPlan.data_type"
class="w-full bg-[#161622] border border-white/10 p-2 rounded">
<option value="unlimited">Unlimited</option>
<option value="limited">Limited</option>
</select>

<div class="flex gap-2">
<input name="data_limit" x-model="editPlan.data_limit"
class="w-full bg-[#161622] border border-white/10 p-2 rounded"
placeholder="Data limit">

<select name="data_unit"
x-model="editPlan.data_unit"
class="bg-[#161622] border border-white/10 p-2 rounded">
<option value="GB">GB</option>
<option value="MB">MB</option>
</select>
</div>

<input name="devices" type="number"
x-model="editPlan.devices"
class="w-full bg-[#161622] border border-white/10 p-2 rounded"
placeholder="Devices">

<div class="flex gap-3 pt-3">
<button type="button"
@click="showEdit=false"
class="flex-1 border border-white/10 py-2 rounded">
Cancel
</button>

<button class="flex-1 bg-purple-700 text-white py-2 rounded">
Update
</button>
</div>

</form>
</div>
