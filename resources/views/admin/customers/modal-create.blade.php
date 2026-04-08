<div
    x-show="showCreate"
    x-cloak
    x-transition.opacity.duration.200ms
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm"
>
    <div
        @click.away="showCreate=false"
        class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
    >

        {{-- HEADER --}}
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Create Customer</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Add a new customer account</p>
            </div>

            <button
                type="button"
                @click="showCreate=false"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
            >
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
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Type</label>
                <select
                    name="type"
                    required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                >
                    <option value="hotspot" @selected(old('type') === 'hotspot')>Hotspot</option>
                    <option value="pppoe" @selected(old('type') === 'pppoe')>PPPoE</option>
                </select>
            </div>

            {{-- NAME --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Name</label>
                <input
                    type="text"
                    name="full_name"
                    value="{{ old('full_name') }}"
                    required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                >
            </div>

            {{-- PHONE --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Phone</label>
                <input
                    type="text"
                    name="phone"
                    value="{{ old('phone') }}"
                    required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                >
            </div>

            {{-- LOCATION --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Location</label>
                <select
                    name="location_id"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                >
                    <option value="">Select location</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" @selected(old('location_id') == $location->id)>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- DEVICE LIMIT --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Device</label>
                <input
                    type="number"
                    name="device_limit"
                    value="{{ old('device_limit', 1) }}"
                    min="1"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                >
            </div>

            {{-- STATUS --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Status</label>
                <select
                    name="status"
                    required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                >
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    <option value="suspended" @selected(old('status') === 'suspended')>Suspended</option>
                </select>
            </div>

            {{-- ACTIONS --}}
            <div class="flex justify-end gap-3 pt-3">
                <button
                    type="button"
                    @click="showCreate=false"
                    class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="inline-flex items-center rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-orange-600"
                >
                    Save
                </button>
            </div>
        </form>

    </div>
</div>