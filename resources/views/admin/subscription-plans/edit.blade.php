<x-admin-layout>

<div class="max-w-xl mx-auto bg-white rounded-xl shadow p-6">

    <h2 class="text-xl font-semibold mb-6 text-center text-slate-800">
        Edit Subscription Plan
    </h2>

    <form
        method="POST"
        action="{{ route('admin.subscription-plans.update', $plan) }}"
        class="space-y-4"
    >
        @csrf
        @method('PUT')

        <input name="name" value="{{ $plan->name }}" required
            class="w-full border rounded px-3 py-2">

        <input name="price" type="number" step="0.01" value="{{ $plan->price }}" required
            class="w-full border rounded px-3 py-2">

        <div class="grid grid-cols-2 gap-3">
            <input name="download_speed" value="{{ $plan->download_speed }}"
                class="border rounded px-3 py-2">
            <select name="download_unit" class="border rounded px-3 py-2">
                <option value="Mbps" @selected($plan->download_unit=='Mbps')>Mbps</option>
                <option value="Kbps" @selected($plan->download_unit=='Kbps')>Kbps</option>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <input name="upload_speed" value="{{ $plan->upload_speed }}"
                class="border rounded px-3 py-2">
            <select name="upload_unit" class="border rounded px-3 py-2">
                <option value="Mbps" @selected($plan->upload_unit=='Mbps')>Mbps</option>
                <option value="Kbps" @selected($plan->upload_unit=='Kbps')>Kbps</option>
            </select>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <select name="data_type" class="border rounded px-3 py-2">
                <option value="unlimited" @selected($plan->data_type=='unlimited')>Unlimited</option>
                <option value="limited" @selected($plan->data_type=='limited')>Limited</option>
            </select>

            <input name="data_limit" value="{{ $plan->data_limit }}"
                class="border rounded px-3 py-2">

            <select name="data_unit" class="border rounded px-3 py-2">
                <option value="GB" @selected($plan->data_unit=='GB')>GB</option>
                <option value="MB" @selected($plan->data_unit=='MB')>MB</option>
            </select>
        </div>

        <input name="devices" type="number" value="{{ $plan->devices }}" required
            class="w-full border rounded px-3 py-2">

        <select name="status" class="w-full border rounded px-3 py-2">
            <option value="1" @selected($plan->status)>Active</option>
            <option value="0" @selected(!$plan->status)>Inactive</option>
        </select>

        <div class="flex gap-3 pt-4">
            <a href="{{ route('admin.subscription-plans.index') }}"
               class="flex-1 border rounded py-2 text-center">
                Cancel
            </a>

            <button class="flex-1 bg-[#ff5437] text-white rounded py-2">
                Update Plan
            </button>
        </div>

    </form>
</div>

</x-admin-layout>
