<div x-show="showCreateCustomer" x-cloak
     class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

<form method="POST"
      action="{{ route('admin.customers.store') }}"
      class="bg-white w-full max-w-md p-6 rounded-xl space-y-4">

@csrf

<h2 class="text-lg font-semibold text-center">Create Customer</h2>

<input name="name" required
       class="w-full border p-2 rounded"
       placeholder="Name">

<input name="phone" required
       class="w-full border p-2 rounded"
       placeholder="Phone">

<input name="address"
       class="w-full border p-2 rounded"
       placeholder="Address">

{{-- LOCATION MULTI SELECT --}}
<select id="locationSelect"
        name="location_id[]"
        multiple
        class="w-full border p-2 rounded">
    @foreach($locations as $location)
        <option value="{{ $location->id }}">
            {{ $location->name }}
        </option>
    @endforeach
</select>

<p class="text-xs text-center text-gray-500">
    Default password: <b>123456</b>
</p>

<div class="flex gap-3 pt-3">
    <button type="button"
            @click="showCreateCustomer=false"
            class="flex-1 border rounded py-2">
        Cancel
    </button>

    <button type="submit"
            class="flex-1 bg-[#5b146b] text-white rounded py-2">
        Save
    </button>
</div>

</form>
</div>
