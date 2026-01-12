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

            {{-- NAME --}}
            <div>
                <label class="block text-sm mb-1">Name</label>
                <input
                    type="text"
                    name="name"
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

            {{-- ADDRESS --}}
            <div>
                <label class="block text-sm mb-1">Address</label>
                <input
                    type="text"
                    name="address"
                    class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-indigo-200"
                >
            </div>

            {{-- LOCATION (NEW ✅) --}}
            <div>
                <label class="block text-sm mb-1">Location</label>
                <select
                    name="location_id[]"
                    multiple
                    class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-indigo-200"
                >
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}">
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">
                    You can select multiple locations
                </p>
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
