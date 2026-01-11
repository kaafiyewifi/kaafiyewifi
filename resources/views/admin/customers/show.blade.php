<x-admin-layout>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div
x-data="{
    tab:'payment',
    showSubscribe:false,
    price:0,
    selectedPlan:null,

    calc(){
        if(this.selectedPlan){
            this.price = this.selectedPlan.price * this.$refs.duration.value
        }
    }
}"
class="space-y-6"
>

{{-- ================= PROFILE HEADER ================= --}}
<div class="bg-blue-100 rounded-md px-6 py-5">

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
        <p><b>Created by:</b> {{ optional($customer->creator)->name ?? 'System' }}</p>

    </div>
</div>

{{-- ================= TABS ================= --}}
<div>

    <div class="border-b mb-3 flex gap-8 text-sm">
        <button @click="tab='payment'"
            :class="tab==='payment' ? 'text-[#5b146b] border-b border-[#5b146b]' : 'text-slate-500'"
            class="pb-2">Payment</button>

        <button @click="tab='devices'"
            :class="tab==='devices' ? 'text-[#5b146b] border-b border-[#5b146b]' : 'text-slate-500'"
            class="pb-2">Devices</button>
    </div>

    {{-- ================= PAYMENT TAB ================= --}}
    <div x-show="tab==='payment'" x-cloak>

        <div class="flex justify-end mb-3">
            <button
                @click="showSubscribe=true"
                class="bg-green-600 text-white text-xs px-3 py-1.5 rounded">
                + Add Subscription
            </button>
        </div>

        <div class="bg-white rounded-xl shadow border overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-indigo-50 text-indigo-700">
                <tr>
                    <th class="px-4 py-3">Plan</th>
                    <th class="px-4 py-3">Price</th>
                    <th class="px-4 py-3">Start</th>
                    <th class="px-4 py-3">Expire</th>
                    <th class="px-4 py-3">Countdown</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
                </thead>

                <tbody class="divide-y">

                @forelse($customer->subscriptions as $sub)

                @php
                    $now = now();
                    if($now < $sub->starts_at){
                        $status = 'upcoming';
                    }elseif($now > $sub->expires_at){
                        $status = 'expired';
                    }else{
                        $status = $sub->status ?? 'active';
                    }
                @endphp

                <tr class="hover:bg-slate-50">

                    <td class="px-4 py-3 font-medium">{{ $sub->plan->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-green-600 font-semibold">${{ number_format($sub->price,2) }}</td>
                    <td class="px-4 py-3">{{ $sub->starts_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3">{{ $sub->expires_at->format('d M Y H:i') }}</td>

                    <td class="px-4 py-3">
                        @if($status=='expired')
                            <span class="text-red-600 font-semibold">Expired</span>
                        @else
                            <span
                                x-data="countdownTimer('{{ $sub->expires_at }}')"
                                x-text="timeLeft"
                                class="text-indigo-600 font-semibold">
                            </span>
                        @endif
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

                    <td class="px-4 py-3 text-center">
                        <form method="POST" action="{{ route('admin.subs.cancel',$sub) }}">
                            @csrf
                            <button class="text-red-600">🗑</button>
                        </form>
                    </td>

                </tr>

                @empty
                <tr>
                    <td colspan="7" class="text-center py-6 text-gray-400">
                        No subscriptions found
                    </td>
                </tr>
                @endforelse

                </tbody>
            </table>
        </div>
    </div>

    {{-- ================= DEVICES TAB ================= --}}
    <div x-show="tab==='devices'" x-cloak class="bg-white border rounded p-4">
        Devices section...
    </div>

</div>

</div>

</x-admin-layout>
