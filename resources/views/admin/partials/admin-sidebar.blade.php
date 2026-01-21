<nav class="space-y-1">

    <a href="{{ route('admin.home') }}"
       class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100">
        <x-icon.dashboard class="w-5 h-5" />
        <span>Admin Home</span>
    </a>

    @role('super_admin')
    <a href="{{ route('sa.users.index') }}"
       class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100">
        <x-icon.users class="w-5 h-5" />
        <span>Users</span>
    </a>
    @endrole

    {{-- Phase 2 (Locations & Hotspots) --}}
    <a href="{{ route('admin.locations.index') }}"
       class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100">
        <x-icon.locations class="w-5 h-5" />
        <span>Locations</span>
    </a>

    <a href="{{ route('admin.hotspots.index') }}"
       class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100">
        <x-icon.hotspots class="w-5 h-5" />
        <span>Hotspots</span>
    </a>

    {{-- Reports (later) --}}
    <a href="{{ route('admin.reports.index') }}"
       class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100">
        <x-icon.reports class="w-5 h-5" />
        <span>Reports</span>
    </a>

    {{-- Audit (optional) --}}
    <a href="{{ route('admin.audit.index') }}"
       class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100">
        <x-icon.audit class="w-5 h-5" />
        <span>Audit Logs</span>
    </a>

</nav>
