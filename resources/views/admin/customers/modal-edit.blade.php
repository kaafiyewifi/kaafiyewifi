{{-- ================= EDIT CUSTOMER MODAL ================= --}}
<div
    x-show="showEdit"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
>
    <form
        method="POST"
        :action="`/admin/customers/${editCustomer.id}`"
        class="bg-white w-full max-w-md rounded-xl shadow-lg p-6 space-y-4"
    >
        @csrf
        @method('PUT')

        <h2 class="text-lg font-semibold text-center">
            Edit Customer
        </h2>

        {{-- NAME --}}
        <div>
            <label class="text-sm">Name</label>
            <input
                name="name"
                x-model="editCustomer.name"
                class="w-full border rounded-lg px-3 py-2"
                required
            >
        </div>

        {{-- PHONE --}}
        <div>
            <label class="text-sm">Phone</label>
            <input
                name="phone"
                x-model="editCustomer.phone"
                class="w-full border rounded-lg px-3 py-2"
                required
            >
        </div>

        {{-- ADDRESS --}}
        <div>
            <label class="text-sm">Address</label>
            <input
                name="address"
                x-model="editCustomer.address"
                class="w-full border rounded-lg px-3 py-2"
            >
        </div>

        {{-- STATUS --}}
        <div>
            <label class="text-sm">Status</label>
            <select
                name="status"
                x-model="editCustomer.status"
                class="w-full border rounded-lg px-3 py-2"
            >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        {{-- LOCATIONS MULTI SELECT --}}
        <div>
            <label class="text-sm">Locations</label>
            <select
                name="location_id[]"
                multiple
                class="w-full border rounded-lg px-3 py-2"
            >
                @foreach($locations as $loc)
                    <option
                        value="{{ $loc->id }}"
                        :selected="editCustomer.locations?.some(l => l.id === {{ $loc->id }})"
                    >
                        {{ $loc->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ACTIONS --}}
        <div class="flex gap-3 pt-4">
            <button
                type="button"
                @click="showEdit=false"
                class="flex-1 border rounded-lg py-2"
            >
                Cancel
            </button>

            <button
                type="submit"
                class="flex-1 bg-indigo-600 text-white rounded-lg py-2"
            >
                Update
            </button>
        </div>
    </form>
</div>
