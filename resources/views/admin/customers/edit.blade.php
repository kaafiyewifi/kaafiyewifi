<div
    x-show="showEditCustomer"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
>

<form
    method="POST"
    :action="`/admin/customers/${editCustomer.id}`"
    class="bg-white w-full max-w-md rounded-2xl p-6 space-y-4"
>
    @csrf
    @method('PUT')

    <h2 class="text-lg font-semibold text-center">
        Edit Customer
    </h2>

    <input
        name="name"
        x-model="editCustomer.name"
        class="w-full border rounded-lg px-4 py-2"
        required
    >

    <input
        name="phone"
        x-model="editCustomer.phone"
        class="w-full border rounded-lg px-4 py-2"
        required
    >

    <input
        name="address"
        x-model="editCustomer.address"
        class="w-full border rounded-lg px-4 py-2"
    >

    <select name="status"
            x-model="editCustomer.status"
            class="w-full border rounded-lg px-4 py-2">
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select>

    <div class="flex gap-3 pt-4">
        <button
            type="button"
            @click="showEditCustomer=false"
            class="flex-1 border rounded-lg py-2"
        >
            Cancel
        </button>

        <button
            type="submit"
            class="flex-1 bg-[#5b146b] text-white rounded-lg py-2"
        >
            Update
        </button>
    </div>

</form>
</div>
