<x-admin-layout>
<div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

{{-- ================= PROFILE ================= --}}
<div class="bg-white dark:bg-slate-900 rounded-xl shadow px-6 py-5">

    {{-- TOP --}}
    <div class="flex items-center gap-4">

        {{-- AVATAR --}}
        <div class="w-14 h-14 rounded-full bg-[#ff5437] text-white
                    flex items-center justify-center text-xl font-semibold">
            {{ strtoupper(substr($customer->name,0,1)) }}
        </div>

        {{-- NAME + STATUS --}}
        <div class="flex-1">
            <h2 class="text-xl font-semibold text-[#ff5437]">
                {{ $customer->name }}
            </h2>

            <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded
                {{ $customer->status === 'active'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-red-100 text-red-700' }}">
                {{ ucfirst($customer->status) }}
            </span>
        </div>

        {{-- ACTIONS --}}
        <div class="flex items-center gap-3 text-sm">

            {{-- EDIT --}}
            <a href="{{ route('admin.customers.edit', $customer) }}"
               class="text-blue-600 hover:underline">
                ✏️ Edit
            </a>

            {{-- PASSWORD --}}
            <a href="#"
               title="Change Password"
               class="text-slate-500 hover:text-slate-700">
                🔒
            </a>
        </div>
    </div>

    {{-- INFO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-3 mt-4 text-sm text-slate-700 dark:text-slate-300">
        <p><b>ID:</b> {{ $customer->id }}</p>
        <p><b>Location:</b> {{ $customer->locations->pluck('name')->join(', ') ?: 'No location' }}</p>
        <p><b>Phone:</b> {{ $customer->phone }}</p>
        <p><b>Created:</b> {{ $customer->created_at->format('d M Y') }}</p>
        <p><b>Address:</b> {{ $customer->address ?? '—' }}</p>
        
        <p><b>Created by:</b> {{ auth()->user()->name }}</p>
    </div>

    {{-- META --}}
    <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs text-slate-500 mt-2">
        <span>
            
        </span>

        
    </div>
</div>

{{-- ================= TABS ================= --}}
<div x-data="{ tab: 'subs' }">

    <div class="flex gap-6 border-b dark:border-slate-700 text-sm">
        <button
            @click="tab='subs'"
            :class="tab==='subs'
                ? 'border-b-2 border-[#ff5437] text-[#ff5437]'
                : 'text-slate-500'"
            class="pb-2 font-medium">
            Subscriptions
        </button>

        <button
            @click="tab='devices'"
            :class="tab==='devices'
                ? 'border-b-2 border-[#ff5437] text-[#ff5437]'
                : 'text-slate-500'"
            class="pb-2 font-medium">
            Devices
        </button>
    </div>

    {{-- ================= SUBSCRIPTIONS TAB ================= --}}
    <div x-show="tab==='subs'" class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow overflow-hidden">

        <div class="flex justify-between items-center px-6 py-4 border-b dark:border-slate-700">
            <h3 class="font-semibold">Subscriptions</h3>

            <a href="{{ route('admin.customers.subscribe', $customer) }}"
               class="bg-[#ff5437] hover:bg-[#e94b32] text-white px-4 py-2 rounded-lg text-sm">
                + Add Subscription
            </a>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-slate-100 dark:bg-slate-800">
                <tr>
                    <th class="px-4 py-3 text-left">Plan</th>
                    <th class="px-4 py-3 text-center">Price</th>
                    <th class="px-4 py-3 text-center">Start</th>
                    <th class="px-4 py-3 text-center">Expire</th>
                    <th class="px-4 py-3 text-center">Remaining</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y dark:divide-slate-700">
            @forelse($subscriptions as $sub)
                <tr class="{{ $sub->status === 'active'
                        ? 'bg-green-50 dark:bg-green-900/20'
                        : '' }}">

                    <td class="px-4 py-3 font-medium">
                        {{ $sub->plan->name ?? '—' }}
                    </td>

                    <td class="px-4 py-3 text-center text-green-600 font-semibold">
                        ${{ number_format($sub->price,2) }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        {{ $sub->starts_at?->format('d M Y') }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        {{ $sub->expires_at?->format('d M Y') }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        {{ $sub->remainingLabel() }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 rounded text-xs
                            {{ $sub->status === 'active'
                                ? 'bg-green-200 text-green-800'
                                : ($sub->status === 'expired'
                                    ? 'bg-red-200 text-red-800'
                                    : 'bg-yellow-200 text-yellow-800') }}">
                            {{ ucfirst($sub->status) }}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-center space-x-2">
                        @if($sub->status === 'active')
                            <form method="POST" action="{{ route('admin.subs.pause',$sub) }}" class="inline">
                                @csrf
                                <button class="text-yellow-600">⏸</button>
                            </form>
                        @endif

                        @if($sub->status === 'paused')
                            <form method="POST" action="{{ route('admin.subs.resume',$sub) }}" class="inline">
                                @csrf
                                <button class="text-green-600">▶</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.subs.cancel',$sub) }}" class="inline">
                            @csrf
                            <button class="text-red-600">✖</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-8 text-gray-400">
                        No subscriptions found
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- ================= DEVICES TAB ================= --}}
    <div x-show="tab==='devices'" class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow p-6 text-sm text-slate-500">
        Devices connected (coming soon…)
    </div>

</div>
</div>


{{-- ================= ADD SUBSCRIPTION POPUP ================= --}}
<div x-show="openSubscribe" class="fixed inset-0 bg-black/50 flex items-center justify-center">
<div @click.away="openSubscribe=false"
     class="bg-white rounded-xl w-full max-w-md p-6">

<h3 class="font-semibold mb-4 text-[#ff5437]">
Add Subscription – {{ $customer->name }}
</h3>

<form method="POST" action="{{ route('admin.customers.subscribe.store',$customer) }}" class="space-y-4">
@csrf

<select name="plan_id" class="w-full border rounded">
@foreach($plans as $plan)
<option value="{{ $plan->id }}">{{ $plan->name }} – ${{ $plan->price }}</option>
@endforeach
</select>

<select name="type" class="w-full border rounded">
<option value="days">Days</option>
<option value="hours">Hours</option>
</select>

<input type="number" name="value" min="1" class="w-full border rounded" placeholder="Duration">

<div class="flex justify-end gap-2">
<button type="button" @click="openSubscribe=false">Cancel</button>
<button class="bg-[#ff5437] text-white px-4 py-2 rounded">Add</button>
</div>

</form>
</div>
</div>
</x-admin-layout>
