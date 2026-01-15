<x-admin-layout>

<div class="max-w-7xl mx-auto px-6 py-6">

<div class="bg-white dark:bg-slate-800 rounded-xl shadow p-6">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-4">
        <h2 class="font-semibold text-lg">Hotspots</h2>

        <span class="text-sm text-gray-500">
            Create hotspots inside locations
        </span>
    </div>

    {{-- SEARCH --}}
    <div class="flex justify-end mb-3">
        <input placeholder="Search"
               class="border rounded-lg px-3 py-2 text-sm w-48">
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
    <table class="w-full text-sm text-left">
        <thead class="bg-slate-100 dark:bg-slate-700">
            <tr>
                <th class="px-3 py-2">ID</th>
                <th class="px-3 py-2">Name</th>
                <th class="px-3 py-2">Location</th>
                <th class="px-3 py-2">SSID</th>
                <th class="px-3 py-2">Speed</th>
             
            </tr>
        </thead>

        <tbody class="divide-y">
        @forelse($hotspots as $hotspot)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                <td class="px-3 py-2">{{ $hotspot->id }}</td>
                <td class="px-3 py-2 font-medium">{{ $hotspot->name }}</td>
                <td class="px-3 py-2 text-blue-600">
                    <a href="{{ route('admin.locations.show',$hotspot->location) }}">
                        {{ $hotspot->location->name }}
                    </a>
                </td>
                <td class="px-3 py-2">{{ $hotspot->ssid }}</td>
                <td class="px-3 py-2">
                    {{ $hotspot->download_speed }}/{{ $hotspot->upload_speed }}
                    {{ $hotspot->speed_unit }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-10 text-gray-400">
                    No hotspots found
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>

</div>

</div>

</x-admin-layout>
