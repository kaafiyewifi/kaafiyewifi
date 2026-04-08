{{-- ================= EDIT CUSTOMER MODAL ================= --}}
<div
    x-show="showEdit"
    x-cloak
    x-transition.opacity.duration.200ms
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm"
>
    <div
        @click.away="showEdit=false"
        class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
    >
        <form
            method="POST"
            :action="`/admin/customers/${editCustomer.id}`"
            class="space-y-4"
        >
            @csrf
            @method('PUT')

            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Edit Customer
                    </h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Update customer information
                    </p>
                </div>

                <button
                    type="button"
                    @click="showEdit=false"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                >
                    ✕
                </button>
            </div>

            {{-- TYPE --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Type</label>
                <select
                    name="type"
                    x-model="editCustomer.type"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                    required
                >
                    <option value="hotspot">Hotspot</option>
                    <option value="pppoe">PPPoE</option>
                </select>
            </div>

            {{-- NAME --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Name</label>
                <input
                    name="full_name"
                    x-model="editCustomer.full_name"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                    required
                >
            </div>

            {{-- PHONE --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Phone</label>
                <input
                    name="phone"
                    x-model="editCustomer.phone"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                    required
                >
            </div>

            {{-- LOCATION --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Location</label>
                <select
                    name="location_id"
                    x-model="editCustomer.location_id"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
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
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Device</label>
                <input
                    type="number"
                    name="device_limit"
                    x-model="editCustomer.device_limit"
                    min="1"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
                >
            </div>

            {{-- STATUS --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Status</label>
                <select
                    name="status"
                    x-model="editCustomer.status"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-orange-900/30"
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
                    class="flex-1 rounded-xl border border-slate-300 bg-white py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="flex-1 rounded-xl bg-orange-500 py-2.5 text-sm font-medium text-white transition hover:bg-orange-600"
                >
                    Update
                </button>
            </div>
        </form>
    </div>
</div>