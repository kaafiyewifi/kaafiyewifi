<input name="name"
x-model="editPlan.name"
class="w-full border p-2 rounded"
placeholder="Plan name"
required>

<input name="price" type="number" step="0.01"
x-model="editPlan.price"
class="w-full border p-2 rounded"
placeholder="Price"
required>

<div class="flex gap-2">
<input name="download_speed"
x-model="editPlan.download_speed"
class="w-full border p-2 rounded"
placeholder="Download speed">

<select name="download_unit"
x-model="editPlan.download_unit"
class="border p-2 rounded">
<option value="Mbps">Mbps</option>
<option value="Kbps">Kbps</option>
</select>
</div>

<div class="flex gap-2">
<input name="upload_speed"
x-model="editPlan.upload_speed"
class="w-full border p-2 rounded"
placeholder="Upload speed">

<select name="upload_unit"
x-model="editPlan.upload_unit"
class="border p-2 rounded">
<option value="Mbps">Mbps</option>
<option value="Kbps">Kbps</option>
</select>
</div>

<select name="data_type"
x-model="editPlan.data_type"
class="w-full border p-2 rounded">
<option value="unlimited">Unlimited</option>
<option value="limited">Limited</option>
</select>

<div class="flex gap-2">
<input name="data_limit"
x-model="editPlan.data_limit"
class="w-full border p-2 rounded"
placeholder="Data limit">

<select name="data_unit"
x-model="editPlan.data_unit"
class="border p-2 rounded">
<option value="GB">GB</option>
<option value="MB">MB</option>
</select>
</div>

<input name="devices" type="number"
x-model="editPlan.devices"
class="w-full border p-2 rounded"
placeholder="Simultaneous devices"
required>
