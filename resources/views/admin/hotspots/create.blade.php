<x-admin-layout>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div x-data="wizard()" class="max-w-4xl mx-auto py-10">

<div class="bg-white dark:bg-slate-800 rounded-xl shadow p-8">

<!-- STEPS -->
<div class="flex gap-6 mb-8 text-sm">
    <div :class="step==1?'text-blue-600 font-semibold':'text-gray-400'">1 Initial settings</div>
    <div :class="step==2?'text-blue-600 font-semibold':'text-gray-400'">2 Configuration</div>
    <div :class="step==3?'text-blue-600 font-semibold':'text-gray-400'">3 Result</div>
</div>

<form method="POST" action="{{ route('admin.hotspots.store',$location) }}">
@csrf

<!-- STEP 1 -->
<div x-show="step==1" class="space-y-4">

<input name="title" class="input" placeholder="* Title" required>

<select name="nas_type" class="input">
<option value="mikrotik">MikroTik</option>
<option>Cambium</option>
<option>Tanaza</option>
<option>Teltonika</option>
<option>Cudy</option>
<option>Ruckus</option>
<option>Alcatel</option>
<option>Grandstream</option>
</select>

<input name="physical_address" class="input" placeholder="Physical address">

<div class="text-right">
<button type="button" @click="step=2" class="btn">Next</button>
</div>

</div>

<!-- STEP 2 -->
<div x-show="step==2" class="space-y-4">

<input name="router_ip" class="input" placeholder="Router IP">

<input name="api_port" class="input" value="8728">

<input name="api_user" class="input" placeholder="API Username">

<input name="api_pass" class="input" placeholder="API Password">

<div class="flex justify-between">
<button type="button" @click="step=1" class="btn-secondary">Back</button>
<button type="button" @click="step=3" class="btn">Next</button>
</div>

</div>

<!-- STEP 3 -->
<div x-show="step==3" class="text-center space-y-6">

<p class="text-green-600 font-semibold">Configuration ready</p>

<div class="flex justify-between">
<button type="button" @click="step=2" class="btn-secondary">Back</button>
<button class="btn">Finish</button>
</div>

</div>

</form>

</div>
</div>

<style>
.input{ @apply w-full border rounded-lg px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-white;}
.btn{ @apply bg-blue-600 text-white px-6 py-2 rounded-lg;}
.btn-secondary{ @apply bg-gray-200 px-6 py-2 rounded-lg;}
</style>

<script>
function wizard(){
    return { step:1 }
}
</script>

</x-admin-layout>
