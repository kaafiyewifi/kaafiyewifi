<div
    x-show="showCreate"
    x-cloak
    x-transition.opacity.duration.200ms
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
>
    <div
        @click.away="showCreate=false"
        class="bg-white w-full max-w-md rounded-xl shadow-lg p-6"
    >

        {{-- HEADER --}}
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Create Customer</h2>
            <button
                @click="showCreate=false"
                class="text-gray-400 hover:text-gray-700 text-xl">
                ✕
            </button>
        </div>

        {{-- FORM --}}
        <form
            method="POST"
            action="{{ route('admin.customers.store') }}"
            class="space-y-4"
        >
            @csrf

            {{-- TYPE --}}
            <div>
                <label class="block text-sm mb-1">Type</label>
                <select
                    name="type"
                    required
                    class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-indigo-200"
                >
                    <option value="hotspot">Hotspot</option>
                    <option value="pppoe">PPPoE</option>
                </select>
            </div>

            {{-- NAME --}}
            <div>
                <label class="block text-sm mb-1">Name</label>
                <input
                    type="text"
                    name="full_name"
                    required
                    class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-indigo-200"
                >
            </div>

            {{-- PHONE --}}
            <div>
                <label class="block text-sm mb-1">Phone</label>
                <input
                    type="text"
                    name="phone"
                    required
                    class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-indigo-200"
                >
            </div>

            {{-- LOCATION --}}
            <div>
                <label class="block text-sm mb-1">Location</label>
                <select
                    name="location_id"
                    class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-indigo-200"
                >
                    <option value="">Select location</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}">
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- DEVICE LIMIT --}}
            <div>
                <label class="block text-sm mb-1">Device</label>
                <input
                    type="number"
                    name="device_limit"
                    value="1"
                    min="1"
                    class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-indigo-200"
                >
            </div>

            {{-- STATUS --}}
            <div>
                <label class="block text-sm mb-1">Status</label>
                <select
                    name="status"
                    required
                    class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-indigo-200"
                >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>

            {{-- ACTIONS --}}
            <div class="flex justify-end gap-2 pt-3">
                <button
                    type="button"
                    @click="showCreate=false"
                    class="px-4 py-2 border rounded-lg text-sm"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm"
                >
                    Save
                </button>
            </div>
        </form>

    </div>
</div>