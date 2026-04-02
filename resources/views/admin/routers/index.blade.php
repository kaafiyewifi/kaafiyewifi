@extends('layouts.admin')

@section('content')
<div class="px-6 py-6">

    {{-- Page Title --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">MikroTik Routers</h1>
            <p class="mt-1 text-sm text-gray-500">Manage your MikroTik routers on this page</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="#"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/>
                </svg>
                Tutorial
            </a>

            <a href="{{ route('admin.routers.wizard.stage1') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Link a MikroTik
            </a>
        </div>
    </div>

    @php
        $activeStatus = request('status'); // null|connected|offline
        $q = request('q');
        $perPage = (int) request('per_page', 10);

        $tabBase = ['q' => $q, 'per_page' => $perPage];
        $isAll = empty($activeStatus);
        $isOnlineTab = ($activeStatus === 'connected');
        $isOfflineTab = ($activeStatus === 'offline');

        // ✅ Online window: 3 minutes (same as Controller)
        $onlineCutoff = now()->subMinutes(3);
    @endphp

    {{-- Tabs + counters --}}
    <div class="mt-6 flex items-center gap-6 border-b border-gray-200">

        {{-- All --}}
        <a href="{{ route('admin.routers.index', array_filter($tabBase)) }}"
           class="relative inline-flex items-center gap-2 pb-3 text-sm {{ $isAll ? 'font-semibold text-gray-900' : 'font-medium text-gray-600 hover:text-gray-900' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $isAll ? 'text-orange-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            All
            <span class="rounded-md {{ $isAll ? 'bg-orange-50 text-orange-600' : 'bg-gray-100 text-gray-600' }} px-2 py-0.5 text-xs font-semibold">
                {{ $total ?? 0 }}
            </span>
            @if($isAll)
                <span class="absolute -bottom-[1px] left-0 h-[2px] w-full bg-orange-500"></span>
            @endif
        </a>

        {{-- Online --}}
        <a href="{{ route('admin.routers.index', array_filter(['status'=>'connected'] + $tabBase)) }}"
           class="relative inline-flex items-center gap-2 pb-3 text-sm {{ $isOnlineTab ? 'font-semibold text-gray-900' : 'font-medium text-gray-600 hover:text-gray-900' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $isOnlineTab ? 'text-orange-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.53 16.11a6 6 0 0 1 6.94 0M5.07 12.66a11 11 0 0 1 13.86 0M1.64 9.2a16 16 0 0 1 20.72 0"/>
            </svg>
            Online
            <span class="rounded-md bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-600">
                {{ $onlineCount ?? 0 }}
            </span>
            @if($isOnlineTab)
                <span class="absolute -bottom-[1px] left-0 h-[2px] w-full bg-orange-500"></span>
            @endif
        </a>

        {{-- Offline --}}
        <a href="{{ route('admin.routers.index', array_filter(['status'=>'offline'] + $tabBase)) }}"
           class="relative inline-flex items-center gap-2 pb-3 text-sm {{ $isOfflineTab ? 'font-semibold text-gray-900' : 'font-medium text-gray-600 hover:text-gray-900' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $isOfflineTab ? 'text-orange-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.36 5.64L5.64 18.36M6.5 6.5a16 16 0 0 1 11 0M9.2 9.2a11 11 0 0 1 5.6 0M11.5 11.5a6 6 0 0 1 1 0"/>
            </svg>
            Offline
            <span class="rounded-md bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-600">
                {{ $offlineCount ?? 0 }}
            </span>
            @if($isOfflineTab)
                <span class="absolute -bottom-[1px] left-0 h-[2px] w-full bg-orange-500"></span>
            @endif
        </a>
    </div>

    {{-- Card --}}
    <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm">

        {{-- Card header with Search --}}
        <div class="flex items-center justify-end px-6 py-4">
            <form method="GET" class="relative w-[340px]">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1 0 5.25 5.25a7.5 7.5 0 0 0 11.4 11.4z"/>
                    </svg>
                </span>

                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search"
                    class="w-full rounded-lg border border-gray-200 bg-white py-2 pl-10 pr-3 text-sm text-gray-700 placeholder:text-gray-400 focus:border-orange-300 focus:ring-2 focus:ring-orange-100"
                />
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-t border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600">
                        <th class="px-6 py-3">Board Name</th>
                        <th class="px-6 py-3">Provisioning</th>
                        <th class="px-6 py-3">CPU</th>
                        <th class="px-6 py-3">Memory</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Remote Winbox</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($routers as $router)
                        @php
                            $status = is_object($router->status) && property_exists($router->status, 'value')
                                ? $router->status->value
                                : (string) $router->status;

                            // Provisioning label (keep by status)
                            $provLabel = match ($status) {
                                'connected', 'online' => 'Completed',
                                'services_pending' => 'Services Pending',
                                'pending', 'provisioning' => 'Command Pending',
                                'error' => 'Failed',
                                default => 'Unknown',
                            };

                            $m = $router->latestMetric ?? null;
                            $cpu = $m?->cpu_load;

                            $memUsedMB = null;
                            if ($m?->total_memory && $m?->free_memory !== null) {
                                $memUsedMB = ($m->total_memory - $m->free_memory) / 1024 / 1024;
                            }

                            // ✅ Online/Offline by last_seen_at freshness (NOT by status)
                            $isOnline = false;
                            if (!empty($router->last_seen_at)) {
                                try {
                                    $seenAt = $router->last_seen_at instanceof \Illuminate\Support\Carbon
                                        ? $router->last_seen_at
                                        : \Illuminate\Support\Carbon::parse($router->last_seen_at);

                                    $isOnline = $seenAt->gte($onlineCutoff);
                                } catch (\Throwable $e) {
                                    $isOnline = false;
                                }
                            }

                            // Querystring keep
                            $qs = array_filter([
                                'status' => request('status'),
                                'q' => request('q'),
                                'per_page' => request('per_page', 10),
                            ]);
                        @endphp

                        <tr class="hover:bg-gray-50/70">
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ $router->name }}
                                <div class="mt-1 text-xs font-medium text-gray-500">
                                    {{ $router->identity ?? $router->mgmt_host }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-md border border-orange-200 bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700">
                                    {{ $provLabel }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                @if($cpu !== null)
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-semibold text-green-700">
                                        {{ $cpu }}%
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">%</span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                @if($memUsedMB !== null)
                                    <span class="inline-flex items-center rounded-md border border-orange-200 bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700">
                                        {{ number_format($memUsedMB, 2) }} MB
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md border border-orange-200 bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700">
                                        0.00 MB
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                @if($isOnline)
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                                        Online
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-red-50 px-3 py-1 text-xs font-semibold text-red-600">
                                        Offline
                                    </span>
                                @endif

                                {{-- Optional: show last seen --}}
                                <div class="mt-1 text-xs text-gray-400">
                                    last_seen: {{ $router->last_seen_at ? $router->last_seen_at : '-' }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                @if($router->mgmt_host)
                                    <div class="flex items-center gap-3 text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17h4.5M4.5 6.75h15v9h-15z"/>
                                        </svg>

                                        <a class="text-sm font-semibold text-orange-600 hover:underline"
                                           href="winbox://{{ $router->mgmt_host }}"
                                           title="Open Winbox">
                                            Open
                                        </a>

                                        <button type="button"
                                                class="text-xs text-gray-500 hover:text-gray-900"
                                                onclick="navigator.clipboard.writeText(@js($router->mgmt_host))">
                                            Copy IP
                                        </button>
                                    </div>
                                @else
                                    <div class="flex items-center gap-3 text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17h4.5M4.5 6.75h15v9h-15z"/>
                                        </svg>
                                        <span class="text-red-500 font-semibold">-</span>
                                    </div>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right">
                                <div class="relative inline-block text-left">
                                    <button
                                        type="button"
                                        class="js-menu-btn inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-200"
                                        aria-haspopup="true"
                                        aria-expanded="false"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>

                                    <div
                                        class="js-menu hidden absolute right-0 z-20 mt-2 w-52 origin-top-right rounded-xl border border-gray-200 bg-white shadow-lg"
                                        role="menu"
                                        aria-orientation="vertical"
                                    >
                                        <div class="py-2 text-sm text-gray-700">
                                            <a href="{{ route('admin.routers.show', $router) }}"
                                               class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50"
                                               role="menuitem">
                                                View
                                            </a>

                                            <a href="{{ route('admin.routers.winbox.regenerate', $router) }}"
                                               class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50"
                                               role="menuitem">
                                                Regenerate winbox
                                            </a>

                                            <a href="{{ route('admin.routers.reprovision', $router) }}"
                                               class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50"
                                               role="menuitem">
                                                Reprovision
                                            </a>

                                            <div class="my-2 border-t border-gray-100"></div>

                                            <a href="{{ route('admin.routers.hotspot.sync', $router) }}"
                                               class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50"
                                               role="menuitem">
                                                Sync hotspot files
                                            </a>

                                            <a href="{{ route('admin.routers.time.sync', $router) }}"
                                               class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50"
                                               role="menuitem">
                                                Sync Router Time
                                            </a>

                                            <div class="my-2 border-t border-gray-100"></div>

                                            <form method="POST" action="{{ route('admin.routers.destroy', $router) }}"
                                                  onsubmit="return confirm('Delete this router?');">
                                                @csrf
                                                @method('DELETE')
                                                @foreach($qs as $k => $v)
                                                    <input type="hidden" name="redirect[{{ $k }}]" value="{{ $v }}">
                                                @endforeach

                                                <button type="submit"
                                                        class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50"
                                                        role="menuitem">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-sm text-gray-500">
                                No MikroTik routers
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-6 py-4">
            <p class="text-sm text-gray-500">
                Showing {{ $routers->firstItem() ?? 0 }} to {{ $routers->lastItem() ?? 0 }} of {{ $routers->total() ?? 0 }} results
            </p>

            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">Per page</span>

                <form method="GET">
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">

                    <select name="per_page"
                            onchange="this.form.submit()"
                            class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-orange-300 focus:ring-2 focus:ring-orange-100">
                        @foreach([10,25,50] as $n)
                            <option value="{{ $n }}" @selected((int)request('per_page',10)===$n)>{{ $n }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="px-6 pb-6">
            {{ $routers->links() }}
        </div>
    </div>

</div>

{{-- Dropdown behavior (no Alpine needed) --}}
<script>
(function () {
  function closeAllMenus() {
    document.querySelectorAll('.js-menu').forEach(m => m.classList.add('hidden'));
    document.querySelectorAll('.js-menu-btn').forEach(b => b.setAttribute('aria-expanded', 'false'));
  }

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.js-menu-btn');
    const menu = e.target.closest('.js-menu');

    if (menu) return;

    if (btn) {
      const wrapper = btn.parentElement;
      const m = wrapper.querySelector('.js-menu');
      const isHidden = m.classList.contains('hidden');

      closeAllMenus();
      if (isHidden) {
        m.classList.remove('hidden');
        btn.setAttribute('aria-expanded', 'true');
      }
      return;
    }

    closeAllMenus();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAllMenus();
  });
})();
</script>
@endsection