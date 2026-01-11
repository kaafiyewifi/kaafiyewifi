<div x-show="showEdit" x-cloak
     class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

    <form method="POST"
          :action="`/admin/customers/${editCustomer.id}`"
          class="bg-white w-full max-w-md p-6 rounded-xl space-y-4">
        @csrf
        @method('PUT')

        <h2 class="text-lg font-semibold text-center">Edit Customer</h2>

        <input name="name" x-model="editCustomer.name"
               class="w-full border p-2 rounded">

        <input name="phone" x-model="editCustomer.phone"
               class="w-full border p-2 rounded">

        <input name="address" x-model="editCustomer.address"
               class="w-full border p-2 rounded">

        <select name="location_id[]" multiple
                class="w-full border p-2 rounded">
            @foreach($locations as $location)
                <option
                    value="{{ $location->id }}"
                    :selected="editCustomer.locations.includes({{ $location->id }})">
                    {{ $location->name }}
                </option>
            @endforeach
        </select>

        <select name="status"
                class="w-full border p-2 rounded">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>

        <div class="flex gap-3 pt-4">
            <button type="button"
                    @click="showEdit=false"
                    class="flex-1 border rounded py-2">
                Cancel
            </button>

            <button type="submit"
                    class="flex-1 bg-[#5b146b] text-white rounded py-2">
                Update
            </button>
        </div>
    </form>
</div>
