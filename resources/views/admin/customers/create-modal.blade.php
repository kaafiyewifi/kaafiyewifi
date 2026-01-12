<div
    x-show="showCreate"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
>

    <div class="bg-white w-full max-w-lg rounded-xl shadow p-6">

        <h2 class="text-xl font-semibold mb-4">
            Create Customer
        </h2>

        {{-- ERRORS --}}
        @if ($errors->any())
            <div class="bg-red-100 text-red-700 text-sm rounded p-3 mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('admin.customers.store') }}"
            class="space-y-4"
        >
            @csrf

            <input
                name="name"
                value="{{ old('name') }}"
                placeholder="Customer name"
                class="w-full border rounded px-4 py-2"
                required>

            <input
                name="phone"
                value="{{ old('phone') }}"
                placeholder="Phone"
                class="w-full border rounded px-4 py-2"
                required>

            <input
                name="address"
                value="{{ old('address') }}"
                placeholder="Address"
                class="w-full border rounded px-4 py-2">

            <select
                name="location_id[]"
                multiple
                class="w-full border rounded px-4 py-2"
            >
                @foreach($locations as $loc)
                    <option value="{{ $loc->id }}">
                        {{ $loc->name }}
                    </option>
                @endforeach
            </select>

            <div class="flex justify-end gap-3 pt-4">
                <button
                    type="button"
                    @click="showCreate=false"
                    class="px-4 py-2 border rounded"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="bg-[#ff5b39] hover:bg-orange-600 text-white px-4 py-2 rounded"
                >
                    Save Customer
                </button>
            </div>
        </form>
    </div>
</div>
