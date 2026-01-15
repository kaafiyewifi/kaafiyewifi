<x-admin-layout>
<div class="max-w-md mx-auto mt-10 bg-white rounded-xl shadow p-6 space-y-4">

<h2 class="text-lg font-semibold text-center">
    Add Subscription 
</h2>



<form method="POST" action="{{ route('admin.customers.subscribe.store', $customer) }}" class="space-y-4">
@csrf

<select name="plan_id" required class="w-full border rounded px-3 py-2">
@foreach($plans as $plan)
<option value="{{ $plan->id }}">
    {{ $plan->name }} — ${{ $plan->price }}
</option>
@endforeach
</select>

<select name="unit" class="w-full border rounded px-3 py-2">
    <option value="hours">Hours</option>
    <option value="days" selected>Days</option>
</select>

<input type="number" name="value" min="1" required
       class="w-full border rounded px-3 py-2"
       placeholder="Enter number">

<div class="flex gap-2">
    <a href="{{ route('admin.customers.show', $customer) }}"
       class="w-1/2 text-center border rounded py-2">
        Cancel
    </a>

    <button type="submit"
        class="w-1/2 bg-[#ff5437] text-white rounded py-2">
        Add
    </button>
</div>

</form>
</div>
</x-admin-layout>
