<x-admin-layout>
<div
    x-data="{
        tab: localStorage.getItem('customer_show_tab') || 'subs',
        openSubscribe: false,
        openPassword: false,
        openSpeed: false,
        refreshTimer: null,

        setTab(value) {
            this.tab = value;
            localStorage.setItem('customer_show_tab', value);
        },

        startDevicesRefresh() {
            if (this.refreshTimer) {
                clearInterval(this.refreshTimer);
            }

            this.refreshTimer = setInterval(() => {
                if (this.tab === 'devices') {
                    localStorage.setItem('customer_show_tab', 'devices');
                    window.location.reload();
                }
            }, 10000);
        }
    }"
    x-init="startDevicesRefresh()"
    class="mx-auto max-w-7xl px-4 py-6 space-y-6 overflow-x-hidden"
>

@php
    $activePlanSubscription = $subscriptions->first(function ($sub) {
        return $sub->status === 'active' && (!$sub->expires_at || $sub->expires_at->isFuture());
    });

    $planModel = $activePlanSubscription?->plan;

    $displayDownloadSpeed = null;
    $displayDownloadUnit = null;
    $displayUploadSpeed = null;
    $displayUploadUnit = null;
    $speedSource = 'plan';

    if ($customer->speed_override_enabled && !empty($customer->download_speed)) {
        $displayDownloadSpeed = $customer->download_speed;
        $displayDownloadUnit = $customer->download_unit ?: 'Mbps';
        $displayUploadSpeed = $customer->upload_speed ?: $customer->download_speed;
        $displayUploadUnit = $customer->upload_unit ?: $displayDownloadUnit;
        $speedSource = 'override';
    } elseif ($planModel) {
        $displayDownloadSpeed = $planModel->download_speed ?? null;
        $displayDownloadUnit = $planModel->download_unit ?? 'Mbps';
        $displayUploadSpeed = $planModel->upload_speed ?? $displayDownloadSpeed;
        $displayUploadUnit = $planModel->upload_unit ?? $displayDownloadUnit;
        $speedSource = 'plan';
    }

    $devices = \Illuminate\Support\Facades\DB::connection('radius')
        ->table('radacct as r1')
        ->select('r1.*')
        ->where('r1.username', $customer->username)
        ->whereNotNull('r1.callingstationid')
        ->whereRaw('r1.radacctid = (
            SELECT MAX(r2.radacctid)
            FROM radacct r2
            WHERE r2.username = r1.username
              AND r2.callingstationid = r1.callingstationid
        )')
        ->orderByDesc('r1.radacctid')
        ->get();

    $allCustomerInvoices = \Illuminate\Support\Facades\DB::table('invoices')
        ->where('customer_id', $customer->id)
        ->orderByDesc('id')
        ->get();

    $invoiceMap = $allCustomerInvoices->keyBy('id');
@endphp

{{-- ================= PROFILE ================= --}}
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

    <div class="flex flex-col gap-5 px-6 py-6 md:flex-row md:items-start md:justify-between">
        <div class="flex items-center gap-4 min-w-0">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#ff5437] text-xl font-semibold text-white shadow-sm">
                {{ strtoupper(substr($customer->full_name ?? $customer->name, 0, 1)) }}
            </div>

            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100 break-words">
                    {{ $customer->full_name ?? $customer->name }}
                </h2>

                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium
                        {{ $customer->status === 'active'
                            ? 'bg-green-100 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-900/30 dark:text-green-300 dark:ring-green-800'
                            : 'bg-red-100 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-900/30 dark:text-red-300 dark:ring-red-800' }}">
                        {{ ucfirst($customer->status) }}
                    </span>

                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">
                        {{ ucfirst($customer->type ?? '—') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if(Route::has('admin.customers.edit'))
                <a
                    href="{{ route('admin.customers.edit', $customer) }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-blue-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-blue-400 dark:hover:bg-slate-800"
                    title="Edit Customer"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M17.414 2.586a2 2 0 010 2.828l-8.5 8.5A2 2 0 017.5 14.5H5a1 1 0 01-1-1V11a2 2 0 01.586-1.414l8.5-8.5a2 2 0 012.828 0z" />
                        <path d="M5 15a1 1 0 100 2h10a1 1 0 100-2H5z" />
                    </svg>
                    Edit
                </a>
            @endif

            <button
                type="button"
                @click="openSpeed = true"
                class="inline-flex items-center gap-2 rounded-xl border border-purple-300 bg-purple-50 px-4 py-2.5 text-sm font-medium text-purple-700 transition hover:bg-purple-100 dark:border-purple-800 dark:bg-purple-900/20 dark:text-purple-300 dark:hover:bg-purple-900/30"
                title="Speed Override"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M11.3 1.046A1 1 0 0010 2v5H7a1 1 0 00-.8 1.6l4 5A1 1 0 0012 13v-5h3a1 1 0 00.8-1.6l-4-5a1 1 0 00-.5-.354z" />
                    <path d="M4 15a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1z" />
                </svg>
                Speed Override
            </button>

            <button
                type="button"
                @click="openPassword = true"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                title="Change Password"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 8a6 6 0 10-11.659 2H5a2 2 0 00-2 2v3a2 2 0 002 2h8a2 2 0 002-2v-3a2 2 0 00-2-2h-1.341A6.002 6.002 0 0018 8zM8 8a4 4 0 118 0v2H8V8z" clip-rule="evenodd" />
                </svg>
                Change Password
            </button>

            <form method="POST" action="{{ route('admin.customers.clear-stale-sessions', $customer) }}" class="inline" onsubmit="return confirm('Clear stale sessions for this customer?')">
                @csrf
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl border border-amber-300 bg-amber-50 px-4 py-2.5 text-sm font-medium text-amber-700 transition hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300 dark:hover:bg-amber-900/30"
                    title="Clear Stale Sessions"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.172 7.707 8.879a1 1 0 10-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Clear Sessions
                </button>
            </form>

            <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="{{ $customer->type }}">
                <input type="hidden" name="full_name" value="{{ $customer->full_name }}">
                <input type="hidden" name="phone" value="{{ $customer->phone }}">
                <input type="hidden" name="location_id" value="{{ $customer->location_id }}">
                <input type="hidden" name="device_limit" value="{{ $customer->device_limit ?? 1 }}">
                <input type="hidden" name="status" value="inactive">
                <input type="hidden" name="speed_override_enabled" value="{{ $customer->speed_override_enabled ? 1 : 0 }}">
                <input type="hidden" name="download_speed" value="{{ $customer->download_speed }}">
                <input type="hidden" name="download_unit" value="{{ $customer->download_unit }}">
                <input type="hidden" name="upload_speed" value="{{ $customer->upload_speed }}">
                <input type="hidden" name="upload_unit" value="{{ $customer->upload_unit }}">
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl border border-yellow-300 bg-yellow-50 px-4 py-2.5 text-sm font-medium text-yellow-700 transition hover:bg-yellow-100 dark:border-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300 dark:hover:bg-yellow-900/30"
                    title="Disable Customer"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.536-10.95a.75.75 0 00-1.061-1.06L7.99 10.475a.75.75 0 101.06 1.06l4.486-4.485z" clip-rule="evenodd" />
                    </svg>
                    Disable
                </button>
            </form>

            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" class="inline" onsubmit="return confirm('Delete this customer?')">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl border border-red-300 bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 transition hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
                    title="Delete Customer"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.5 2a1 1 0 00-.894.553L7.382 3H5a1 1 0 000 2h.293l.853 10.236A2 2 0 008.14 17h3.72a2 2 0 001.994-1.764L14.707 5H15a1 1 0 100-2h-2.382l-.224-.447A1 1 0 0011.5 2h-3zM8 7a1 1 0 012 0v6a1 1 0 11-2 0V7zm4-1a1 1 0 00-1 1v6a1 1 0 102 0V7a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 border-t border-slate-200 px-6 py-5 text-sm dark:border-slate-800 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">ID</p>
            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $customer->id }}</p>
        </div>

        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Speed</p>
            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100 break-words">
                @if($displayDownloadSpeed)
                    ↓ {{ $displayDownloadSpeed }} {{ $displayDownloadUnit }}
                    /
                    ↑ {{ $displayUploadSpeed }} {{ $displayUploadUnit }}
                @else
                    Plan Default
                @endif
            </p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {{ $speedSource === 'override' ? 'Customer Override' : 'Subscription Plan' }}
            </p>
        </div>

        <div x-data="{ show:false }" class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Password</p>

            <div class="mt-1 flex items-center gap-2">
                <input
                    :type="show ? 'text' : 'password'"
                    value="Not viewable"
                    class="w-full bg-transparent font-semibold text-slate-900 dark:text-slate-100 outline-none"
                    readonly
                >

                <button
                    type="button"
                    @click="show = !show"
                    class="text-xs font-medium text-blue-500 hover:text-blue-600"
                >
                    <span x-text="show ? 'Hide' : 'Show'"></span>
                </button>
            </div>

            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Original password lama akhrin karo. Isticmaal Change Password.
            </p>
        </div>

        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Location</p>
            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $customer->location->name ?? 'No location' }}</p>
        </div>

        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Phone</p>
            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $customer->phone }}</p>
        </div>

        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Created</p>
            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $customer->created_at->format('d M Y') }}</p>
        </div>

        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Type</p>
            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ ucfirst($customer->type ?? '—') }}</p>
        </div>

        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Created by</p>
            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ auth()->user()->name }}</p>
        </div>

        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Username</p>
            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $customer->username ?? '—' }}</p>
        </div>

        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Device Limit</p>
            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $customer->device_limit ?? 1 }}</p>
        </div>
    </div>
</div>

{{-- ================= TABS ================= --}}
<div>
    <div class="flex gap-6 overflow-x-auto border-b border-slate-200 text-sm dark:border-slate-800">
        <button
            @click="setTab('subs')"
            :class="tab==='subs'
                ? 'border-b-2 border-[#ff5437] text-[#ff5437]'
                : 'text-slate-500 dark:text-slate-400'"
            class="pb-3 font-medium transition whitespace-nowrap"
        >
            Subscriptions
        </button>

        <button
            @click="setTab('devices')"
            :class="tab==='devices'
                ? 'border-b-2 border-[#ff5437] text-[#ff5437]'
                : 'text-slate-500 dark:text-slate-400'"
            class="pb-3 font-medium transition whitespace-nowrap"
        >
            Devices
        </button>
    </div>

    {{-- ================= SUBSCRIPTIONS TAB ================= --}}
    <div
        x-show="tab==='subs'"
        x-transition.opacity.duration.200ms
        class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
        <div class="flex flex-col gap-3 border-b border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
            <h3 class="font-semibold text-slate-800 dark:text-slate-100">Subscriptions</h3>

            @if(Route::has('admin.customers.subscribe.store'))
                @if(($plans ?? collect())->count() > 0)
                    <button
                        type="button"
                        @click="openSubscribe = true"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#ff5437] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#e94b32]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add Subscription
                    </button>
                @else
                    <button
                        type="button"
                        disabled
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-400 cursor-not-allowed dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500"
                        title="No plans available for this location"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add Subscription
                    </button>
                @endif
            @endif
        </div>

        <div class="overflow-hidden">
            <div class="w-full overflow-x-auto overflow-y-auto max-h-[520px] overscroll-contain">
                <div class="min-w-[1200px]">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 z-10 bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Invoice ID</th>
                                <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Plan</th>
                                <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Released By</th>
                                <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Price</th>
                                <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Paid At</th>
                                <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Start</th>
                                <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Expire</th>
                                <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Remaining</th>
                                <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Status</th>
                                <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 text-slate-700 dark:divide-slate-800 dark:text-slate-300">
                        @forelse($subscriptions as $sub)
                            @php
                                $isExpiredNow = $sub->expires_at && $sub->expires_at->isPast();
                                $displayStatus = $isExpiredNow && $sub->status === 'active' ? 'expired' : $sub->status;
                                $releasedBy = $sub->creator->name
                                    ?? $sub->user->name
                                    ?? $sub->createdBy->name
                                    ?? $sub->admin->name
                                    ?? 'System';

                                $invoiceRecord = null;

                                if (!empty($sub->invoice_id) && $invoiceMap->has($sub->invoice_id)) {
                                    $invoiceRecord = $invoiceMap->get($sub->invoice_id);
                                }

                                if (!$invoiceRecord) {
                                    $invoiceRecord = $allCustomerInvoices
                                        ->filter(function ($invoice) use ($sub) {
                                            $priceMatches = round((float) ($invoice->amount ?? 0), 2) === round((float) ($sub->calculated_price ?? 0), 2);

                                            if (!$priceMatches) {
                                                return false;
                                            }

                                            if ($sub->starts_at && !empty($invoice->created_at)) {
                                                try {
                                                    return \Carbon\Carbon::parse($invoice->created_at)->isSameDay($sub->starts_at);
                                                } catch (\Throwable $e) {
                                                    return false;
                                                }
                                            }

                                            return true;
                                        })
                                        ->sortByDesc('id')
                                        ->first();
                                }

                                if (!$invoiceRecord) {
                                    $invoiceRecord = $allCustomerInvoices
                                        ->filter(function ($invoice) use ($sub) {
                                            return round((float) ($invoice->amount ?? 0), 2) === round((float) ($sub->calculated_price ?? 0), 2);
                                        })
                                        ->sortByDesc('id')
                                        ->first();
                                }

                                $invoiceCode = $invoiceRecord?->id
                                    ? 'INV-' . str_pad($invoiceRecord->id, 5, '0', STR_PAD_LEFT)
                                    : ($sub->invoice_id ? 'INV-' . str_pad($sub->invoice_id, 5, '0', STR_PAD_LEFT) : '—');

                                $paidAtValue = $invoiceRecord?->paid_at ?? $sub->paid_at ?? null;
                            @endphp
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/60 {{ $displayStatus === 'active' ? 'bg-green-50/60 dark:bg-green-950/10' : '' }}">
                                <td class="px-4 py-3 font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ $invoiceCode }}
                                </td>

                                <td class="px-4 py-3 font-medium whitespace-nowrap">
                                    {{ $sub->plan->name ?? '—' }}
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">
                                        {{ $releasedBy }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-center font-semibold text-green-600 dark:text-green-400 whitespace-nowrap">
                                    ${{ number_format($sub->calculated_price ?? $sub->price ?? 0, 2) }}
                                </td>

                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    {{ $paidAtValue ? \Carbon\Carbon::parse($paidAtValue)->format('d M Y H:i') : '—' }}
                                </td>

                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    {{ $sub->starts_at?->format('d M Y') ?? '—' }}
                                </td>

                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    {{ $sub->expires_at?->format('d M Y') ?? '—' }}
                                </td>

                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @if(method_exists($sub, 'remainingLabel'))
                                        {{ $sub->remainingLabel() }}
                                    @elseif($sub->expires_at)
                                        {{ now()->diffForHumans($sub->expires_at, ['parts' => 2, 'short' => true]) }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium
                                        {{ $displayStatus === 'active'
                                            ? 'bg-green-100 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-900/30 dark:text-green-300 dark:ring-green-800'
                                            : ($displayStatus === 'expired'
                                                ? 'bg-red-100 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-900/30 dark:text-red-300 dark:ring-red-800'
                                                : ($displayStatus === 'paused'
                                                    ? 'bg-yellow-100 text-yellow-700 ring-1 ring-inset ring-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300 dark:ring-yellow-800'
                                                    : 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700')) }}">
                                        {{ ucfirst($displayStatus) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        @if(Route::has('admin.subs.extend'))
                                            <a
                                                href="{{ route('admin.subs.extend', $sub) }}"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 text-blue-600 transition hover:bg-blue-100 dark:border-blue-900 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/30"
                                                title="Extend"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v3H6a1 1 0 100 2h3v3a1 1 0 102 0v-3h3a1 1 0 100-2h-3V6z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        @else
                                            <span
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 text-blue-400 dark:border-blue-900 dark:bg-blue-900/20 dark:text-blue-500"
                                                title="Extend"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v3H6a1 1 0 100 2h3v3a1 1 0 102 0v-3h3a1 1 0 100-2h-3V6z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        @endif

                                        @if($sub->status === 'active' && Route::has('admin.subs.pause'))
                                            <form method="POST" action="{{ route('admin.subs.pause', $sub) }}" class="inline">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-yellow-200 bg-yellow-50 text-yellow-600 transition hover:bg-yellow-100 dark:border-yellow-900 dark:bg-yellow-900/20 dark:text-yellow-300 dark:hover:bg-yellow-900/30"
                                                    title="Pause"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M5.75 4.75A.75.75 0 016.5 4h1a.75.75 0 01.75.75v10a.75.75 0 01-.75.75h-1a.75.75 0 01-.75-.75v-10zm6 0A.75.75 0 0112.5 4h1a.75.75 0 01.75.75v10a.75.75 0 01-.75.75h-1a.75.75 0 01-.75-.75v-10z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @elseif($sub->status === 'active')
                                            <span
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-yellow-200 bg-yellow-50 text-yellow-400 dark:border-yellow-900 dark:bg-yellow-900/20 dark:text-yellow-500"
                                                title="Pause"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M5.75 4.75A.75.75 0 016.5 4h1a.75.75 0 01.75.75v10a.75.75 0 01-.75.75h-1a.75.75 0 01-.75-.75v-10zm6 0A.75.75 0 0112.5 4h1a.75.75 0 01.75.75v10a.75.75 0 01-.75.75h-1a.75.75 0 01-.75-.75v-10z" />
                                                </svg>
                                            </span>
                                        @endif

                                        @if($sub->status === 'paused' && Route::has('admin.subs.resume'))
                                            <form method="POST" action="{{ route('admin.subs.resume', $sub) }}" class="inline">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-green-200 bg-green-50 text-green-600 transition hover:bg-green-100 dark:border-green-900 dark:bg-green-900/20 dark:text-green-300 dark:hover:bg-green-900/30"
                                                    title="Resume"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M6.5 5.5a1 1 0 011.53-.848l6 3.75a1 1 0 010 1.696l-6 3.75A1 1 0 016.5 13.25v-7.75z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @elseif($sub->status === 'paused')
                                            <span
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-green-200 bg-green-50 text-green-400 dark:border-green-900 dark:bg-green-900/20 dark:text-green-500"
                                                title="Resume"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M6.5 5.5a1 1 0 011.53-.848l6 3.75a1 1 0 010 1.696l-6 3.75A1 1 0 016.5 13.25v-7.75z" />
                                                </svg>
                                            </span>
                                        @endif

                                        @if(Route::has('admin.subs.cancel'))
                                            <form method="POST" action="{{ route('admin.subs.cancel', $sub) }}" class="inline">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100 dark:border-red-900 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
                                                    title="Delete / Cancel"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M8.5 2a1 1 0 00-.894.553L7.382 3H5a1 1 0 000 2h.293l.853 10.236A2 2 0 008.14 17h3.72a2 2 0 001.994-1.764L14.707 5H15a1 1 0 100-2h-2.382l-.224-.447A1 1 0 0011.5 2h-3zM8 7a1 1 0 012 0v6a1 1 0 11-2 0V7zm4-1a1 1 0 00-1 1v6a1 1 0 102 0V7a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @else
                                            <span
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-400 dark:border-red-900 dark:bg-red-900/20 dark:text-red-500"
                                                title="Delete / Cancel"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M8.5 2a1 1 0 00-.894.553L7.382 3H5a1 1 0 000 2h.293l.853 10.236A2 2 0 008.14 17h3.72a2 2 0 001.994-1.764L14.707 5H15a1 1 0 100-2h-2.382l-.224-.447A1 1 0 0011.5 2h-3zM8 7a1 1 0 012 0v6a1 1 0 11-2 0V7zm4-1a1 1 0 00-1 1v6a1 1 0 102 0V7a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-10 text-center text-gray-400 dark:text-slate-500">
                                    No subscriptions found
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-6 py-3 text-center text-xs text-slate-500 dark:text-slate-400 sm:hidden">
                Swipe left/right inside the card to see full table
            </div>
        </div>
    </div>

    {{-- ================= DEVICES TAB ================= --}}
    <div
        x-show="tab==='devices'"
        x-transition.opacity.duration.200ms
        class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800">
            <h3 class="font-semibold text-slate-800 dark:text-slate-100">Connected Devices</h3>
            <span class="text-xs text-slate-400 dark:text-slate-500">Auto refresh: 10s</span>
        </div>

        <div class="overflow-hidden">
            <div class="w-full overflow-x-auto overscroll-contain">
                <div class="min-w-[920px]">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Device</th>
                                <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">IP</th>
                                <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Uptime</th>
                                <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Usage</th>
                                <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Last Online</th>
                                <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Status</th>
                                <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($devices as $d)
                            @php
                                $isOnline = is_null($d->acctstoptime);

                                $uptime = $isOnline
                                    ? \Carbon\Carbon::parse($d->acctstarttime)->diffForHumans(now(), [
                                        'parts' => 2,
                                        'short' => true,
                                        'syntax' => \Carbon\Carbon::DIFF_ABSOLUTE,
                                    ])
                                    : \Carbon\Carbon::parse($d->acctstarttime)->diffForHumans($d->acctstoptime, [
                                        'parts' => 2,
                                        'short' => true,
                                        'syntax' => \Carbon\Carbon::DIFF_ABSOLUTE,
                                    ]);

                                $usageBytes = (int) ($d->acctinputoctets ?? 0) + (int) ($d->acctoutputoctets ?? 0);
                                $usageMb = $usageBytes / (1024 * 1024);

                                $mac = strtoupper(trim((string) ($d->callingstationid ?? '')));
                                $prefix = substr(str_replace([':', '-'], '', $mac), 0, 6);

                                $deviceName = 'Device';

                                $applePrefixes = ['A4B1C1', 'F0D1A9', '3C2EFF', 'D89695', '2CF0A2', 'F4F15A'];
                                $samsungPrefixes = ['8C8590', '5CF370', 'D857EF', 'E8E5D6', 'CCB0DA', '4C3C16', '46B218'];
                                $xiaomiPrefixes = ['64CC2E', '28E347', 'F85B3B'];
                                $huaweiPrefixes = ['E89526', 'C85B76', 'A4DB30'];

                                if (in_array($prefix, $applePrefixes, true)) {
                                    $deviceName = 'iPhone';
                                } elseif (in_array($prefix, $samsungPrefixes, true)) {
                                    $deviceName = 'Samsung';
                                } elseif (in_array($prefix, $xiaomiPrefixes, true)) {
                                    $deviceName = 'Xiaomi';
                                } elseif (in_array($prefix, $huaweiPrefixes, true)) {
                                    $deviceName = 'Huawei';
                                } elseif (!empty($mac)) {
                                    $deviceName = 'Device ' . substr(str_replace([':', '-'], '', $mac), -4);
                                }
                            @endphp

                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                <td class="px-4 py-3">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-800 dark:text-slate-100">
                                            {{ $deviceName }}
                                        </span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $mac ?: 'No MAC' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                    {{ $d->framedipaddress ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-center text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                    {{ $uptime ?: '-' }}
                                </td>

                                <td class="px-4 py-3 text-center font-medium text-green-600 dark:text-green-400 whitespace-nowrap">
                                    {{ number_format($usageMb, 2) }} MB
                                </td>

                                <td class="px-4 py-3 text-center text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                    {{ $d->acctstoptime ? \Carbon\Carbon::parse($d->acctstoptime)->format('d M Y H:i') : \Carbon\Carbon::parse($d->acctstarttime)->format('d M Y H:i') }}
                                </td>

                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium
                                        {{ $isOnline
                                            ? 'bg-green-100 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-900/30 dark:text-green-300 dark:ring-green-800'
                                            : 'bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700' }}">
                                        {{ $isOnline ? 'Connected' : 'Offline' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @if($isOnline && Route::has('admin.devices.disconnect') && !empty($d->framedipaddress))
                                        <form method="POST" action="{{ route('admin.devices.disconnect') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="username" value="{{ $customer->username }}">
                                            <input type="hidden" name="ip" value="{{ $d->framedipaddress }}">

                                            <button
                                                type="submit"
                                                class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100 dark:border-red-900 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
                                                title="Disconnect Device"
                                            >
                                                Disconnect
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400 dark:text-slate-500">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-400 dark:text-slate-500">
                                    No devices found
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-6 py-3 text-center text-xs text-slate-500 dark:text-slate-400 sm:hidden">
                Swipe left/right inside the card to see full table
            </div>
        </div>
    </div>

    {{-- ================= ADD SUBSCRIPTION POPUP ================= --}}
    @if(Route::has('admin.customers.subscribe.store'))
        <div
            x-show="openSubscribe"
            x-cloak
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm"
        >
            <div
                @click.away="openSubscribe=false"
                class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            Add Subscription
                        </h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Assign a plan to {{ $customer->full_name ?? $customer->name }}
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="openSubscribe=false"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.47 4.47a.75.75 0 011.06 0L10 8.94l4.47-4.47a.75.75 0 111.06 1.06L11.06 10l4.47 4.47a.75.75 0 01-1.06 1.06L10 11.06l-4.47 4.47a.75.75 0 01-1.06-1.06L8.94 10 4.47 5.53a.75.75 0 010-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.customers.subscribe.store', $customer) }}" class="space-y-5 px-6 py-6">
                    @csrf

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Plan
                        </label>
                        <select
                            name="plan_id"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            {{ ($plans ?? collect())->count() === 0 ? 'disabled' : '' }}
                        >
                            @forelse($plans ?? collect() as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} – ${{ $plan->price }}</option>
                            @empty
                                <option value="">No plans available for this location</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Type
                            </label>
                            <select
                                name="type"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            >
                                <option value="days">Days</option>
                                <option value="hours">Hours</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Duration
                            </label>
                            <input
                                type="number"
                                name="value"
                                min="1"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                placeholder="Duration"
                            >
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                        <button
                            type="button"
                            @click="openSubscribe=false"
                            class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#ff5437] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#e94b32] {{ ($plans ?? collect())->count() === 0 ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Add Subscription
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ================= CHANGE PASSWORD POPUP ================= --}}
    @if(Route::has('admin.customers.password.update'))
        <div
            x-show="openPassword"
            x-cloak
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm"
        >
            <div
                @click.away="openPassword=false"
                class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            Change Password
                        </h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Set a new password for {{ $customer->full_name ?? $customer->name }}
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="openPassword=false"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.47 4.47a.75.75 0 011.06 0L10 8.94l4.47-4.47a.75.75 0 111.06 1.06L11.06 10l4.47 4.47a.75.75 0 01-1.06 1.06L10 11.06l-4.47 4.47a.75.75 0 01-1.06-1.06L8.94 10 4.47 5.53a.75.75 0 010-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.customers.password.update', $customer) }}" class="space-y-5 px-6 py-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            New Password
                        </label>
                        <input
                            type="password"
                            name="password"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            placeholder="Enter new password"
                            required
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Confirm Password
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            placeholder="Confirm new password"
                            required
                        >
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                        <button
                            type="button"
                            @click="openPassword=false"
                            class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#ff5437] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#e94b32]"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M17 8V7a5 5 0 00-10 0v1H6a2 2 0 00-2 2v5a2 2 0 002 2h8a2 2 0 002-2v-5a2 2 0 00-2-2h-1zm-8 0V7a3 3 0 116 0v1H9z" clip-rule="evenodd" />
                            </svg>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ================= SPEED OVERRIDE POPUP ================= --}}
    <div
        x-show="openSpeed"
        x-cloak
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm"
    >
        <div
            @click.away="openSpeed=false"
            class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
        >
            <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Speed Override
                    </h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Update custom customer speed for {{ $customer->full_name ?? $customer->name }}
                    </p>
                </div>

                <button
                    type="button"
                    @click="openSpeed=false"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.47 4.47a.75.75 0 011.06 0L10 8.94l4.47-4.47a.75.75 0 111.06 1.06L11.06 10l4.47 4.47a.75.75 0 01-1.06 1.06L10 11.06l-4.47 4.47a.75.75 0 01-1.06-1.06L8.94 10 4.47 5.53a.75.75 0 010-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="space-y-5 px-6 py-6">
                @csrf
                @method('PUT')

                <input type="hidden" name="type" value="{{ $customer->type }}">
                <input type="hidden" name="full_name" value="{{ $customer->full_name }}">
                <input type="hidden" name="phone" value="{{ $customer->phone }}">
                <input type="hidden" name="location_id" value="{{ $customer->location_id }}">
                <input type="hidden" name="device_limit" value="{{ $customer->device_limit ?? 1 }}">
                <input type="hidden" name="status" value="{{ $customer->status }}">
                <input type="hidden" name="speed_override_enabled" value="0">

                <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/60">
                    <input
                        id="speed_override_enabled"
                        type="checkbox"
                        name="speed_override_enabled"
                        value="1"
                        {{ $customer->speed_override_enabled ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-slate-300 text-[#ff5437] focus:ring-[#ff5437] dark:border-slate-600 dark:bg-slate-800"
                    >
                    <label for="speed_override_enabled" class="text-sm font-medium text-slate-700 dark:text-slate-300">
                        Enable Custom Speed Override
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Download Speed
                        </label>
                        <input
                            type="number"
                            name="download_speed"
                            min="1"
                            value="{{ $customer->download_speed }}"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            placeholder="e.g. 10"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Download Unit
                        </label>
                        <select
                            name="download_unit"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        >
                            <option value="Kbps" {{ ($customer->download_unit ?? 'Mbps') === 'Kbps' ? 'selected' : '' }}>Kbps</option>
                            <option value="Mbps" {{ ($customer->download_unit ?? 'Mbps') === 'Mbps' ? 'selected' : '' }}>Mbps</option>
                            <option value="Gbps" {{ ($customer->download_unit ?? 'Mbps') === 'Gbps' ? 'selected' : '' }}>Gbps</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Upload Speed
                        </label>
                        <input
                            type="number"
                            name="upload_speed"
                            min="1"
                            value="{{ $customer->upload_speed }}"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            placeholder="e.g. 5"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Upload Unit
                        </label>
                        <select
                            name="upload_unit"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#ff5437] focus:ring-2 focus:ring-[#ff5437]/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        >
                            <option value="Kbps" {{ ($customer->upload_unit ?? 'Mbps') === 'Kbps' ? 'selected' : '' }}>Kbps</option>
                            <option value="Mbps" {{ ($customer->upload_unit ?? 'Mbps') === 'Mbps' ? 'selected' : '' }}>Mbps</option>
                            <option value="Gbps" {{ ($customer->upload_unit ?? 'Mbps') === 'Gbps' ? 'selected' : '' }}>Gbps</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                    <button
                        type="button"
                        @click="openSpeed=false"
                        class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#ff5437] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#e94b32]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.2 7.2a1 1 0 01-1.414 0l-3-3a1 1 0 111.414-1.42l2.293 2.29 6.493-6.49a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Save Speed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-admin-layout>