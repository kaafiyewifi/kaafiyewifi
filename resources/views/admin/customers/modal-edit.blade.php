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

        {{-- TYPE --}}
        <div>
            <label class="text-sm">Type</label>
            <select
                name="type"
                x-model="editCustomer.type"
                class="w-full border rounded-lg px-3 py-2"
                required
            >
                <option value="hotspot">Hotspot</option>
                <option value="pppoe">PPPoE</option>
            </select>
        </div>

        {{-- NAME --}}
        <div>
            <label class="text-sm">Name</label>
            <input
                name="full_name"
                x-model="editCustomer.full_name"
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

        {{-- LOCATION --}}
        <div>
            <label class="text-sm">Location</label>
            <select
                name="location_id"
                x-model="editCustomer.location_id"
                class="w-full border rounded-lg px-3 py-2"
            >
                <option value="">Select location</option>
                @foreach($locations as $loc)
                    <option value="{{ $loc->id }}">
                        {{ $loc->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- DEVICE --}}
        <div>
            <label class="text-sm">Device</label>
            <input
                type="number"
                name="device_limit"
                x-model="editCustomer.device_limit"
                min="1"
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
                <option value="suspended">Suspended</option>
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