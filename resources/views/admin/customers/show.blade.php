<x-admin-layout>

<div 
    x-data="{ 
        tab:'payment', 
        showSubscribe:false 
    }"
    class="max-w-7xl mx-auto w-full space-y-6"
>

    {{-- ================= PROFILE HEADER ================= --}}
    <div class="bg-blue-100 rounded-lg px-6 py-5">

        <h2 class="text-xl font-semibold text-[#5b146b] mb-3">
            {{ $customer->name }}
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-2 gap-x-10 text-sm">
            <p><b>Customer ID:</b> {{ $customer->id }}</p>

            <p>
                <b>Locations:</b>
                @forelse($customer->locations as $location)
                    <span class="bg-white px-2 py-0.5 rounded text-xs mr-1">
                        {{ $location->name }}
                    </span>
                @empty
                    <span class="text-slate-500">No location</span>
                @endforelse
            </p>

            <p><b>Phone:</b> {{ $customer->phone }}</p>
            <p><b>Created:</b> {{ $customer->created_at->format('d M Y') }}</p>
            <p><b>Address:</b> {{ $customer->address ?? '-' }}</p>
            <p><b>Created by:</b> System</p>
        </div>
    </div>

    {{-- ================= TABS ================= --}}
    <div>

        <div class="border-b mb-4 flex gap-8 text-sm">
            <button
                @click="tab='payment'"
                :class="tab==='payment' ? 'text-[#5b146b] border-b-2 border-[#5b146b]' : 'text-slate-500'"
                class="pb-2 font-medium">
                Payment
            </button>

            <button
                @click="tab='devices'"
                :class="tab==='devices' ? 'text-[#5b146b] border-b-2 border-[#5b146b]' : 'text-slate-500'"
                class="pb-2 font-medium">
                Devices
            </button>
        </div>

        {{-- ================= PAYMENT TAB ================= --}}
        <div x-show="tab==='payment'">

            {{-- ADD SUBSCRIPTION BUTTON (POPUP) --}}
            <div class="flex justify-end mb-4">
                <button
                    @click="showSubscribe=true"
                    class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg font-medium">
                    + Add Subscription
                </button>
            </div>

            {{-- SUBSCRIPTIONS TABLE --}}
            <div class="bg-white rounded-xl shadow border overflow-x-auto">

                <table class="w-full text-sm">
                    <thead class="bg-indigo-50 text-indigo-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Plan</th>
                            <th class="px-4 py-3 text-left">Price</th>
                            <th class="px-4 py-3">Start</th>
                            <th class="px-4 py-3">Expire</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">

                        @forelse($customer->subscriptions as $sub)

                            @php
                                $now = now();
                                if ($now < $sub->starts_at) {
                                    $status = 'upcoming';
                                } elseif ($now > $sub->expires_at) {
                                    $status = 'expired';
                                } else {
                                    $status = $sub->status ?? 'active';
                                }
                            @endphp

                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium">
                                    {{ $sub->plan->name ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-green-600 font-semibold">
                                    ${{ number_format($sub->price,2) }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $sub->starts_at->format('d M Y') }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $sub->expires_at->format('d M Y') }}
                                </td>

                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded
                                        {{ $status=='active' ? 'bg-green-100 text-green-700' :
                                           ($status=='paused' ? 'bg-yellow-100 text-yellow-700' :
                                           ($status=='expired' ? 'bg-red-100 text-red-700' :
                                           'bg-blue-100 text-blue-700')) }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-gray-400">
                                    No subscriptions found
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>

        {{-- ================= DEVICES TAB ================= --}}
        <div x-show="tab==='devices'" class="bg-white border rounded-lg p-4">
            Devices section…
        </div>
    </div>

    {{-- ================= ADD SUBSCRIPTION POPUP ================= --}}
    <div
        x-show="showSubscribe"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    >
        <form
            method="POST"
            action="{{ route('admin.customers.subscribe.store', $customer) }}"
            class="bg-white w-full max-w-md rounded-xl p-6 space-y-4"
        >
            @csrf

            <h2 class="text-lg font-semibold text-center">
                Add Subscription
            </h2>

            {{-- PLAN --}}
            <select name="plan_id" required class="w-full border rounded-lg px-3 py-2">
                <option value="">Select Plan</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}">
                        {{ $plan->name }} — ${{ $plan->price }}
                    </option>
                @endforeach
            </select>

            {{-- START DATE --}}
            <input
                type="date"
                name="starts_at"
                value="{{ now()->toDateString() }}"
                class="w-full border rounded-lg px-3 py-2"
                required
            >

            {{-- DAYS --}}
            <input
                type="number"
                name="days"
                placeholder="Days (e.g 30)"
                class="w-full border rounded-lg px-3 py-2"
                required
            >

            {{-- ACTIONS --}}
            <div class="flex gap-3 pt-3">
                <button
                    type="button"
                    @click="showSubscribe=false"
                    class="flex-1 border rounded-lg py-2">
                    Cancel
                </button>

                <button
                    type="submit"
                    class="flex-1 bg-indigo-600 text-white rounded-lg py-2">
                    Add
                </button>
            </div>
        </form>
    </div>

</div>

</x-admin-layout>
