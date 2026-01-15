<x-admin-layout>

<div class="max-w-7xl mx-auto px-6 py-6 space-y-6">

    {{-- TOP BAR --}}
    <div class="flex justify-between items-center">
        <a href="{{ route('admin.locations.index') }}"
           class="text-sm font-medium px-4 py-2 rounded-lg text-white"
           style="background:#ff5d39">
            ← Back to Locations
        </a>

        <a href="{{ route('admin.hotspots.wizard.step1',$location) }}"
           class="text-sm font-medium px-4 py-2 rounded-lg text-white"
           style="background:#ff5d39">
            + Add Hotspot
        </a>
    </div>

    {{-- LOCATION HEADER --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow p-6">
        <h1 class="text-xl font-semibold text-slate-800 dark:text-white">
            {{ $location->name }}
        </h1>
        <p class="text-sm text-slate-500">
            Created: {{ $location->created_at->format('d M Y') }}
        </p>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow p-5">
            <p class="text-sm text-slate-500">Total Hotspots</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-white">
                {{ $location->hotspots->count() }}
            </p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow p-5">
            <p class="text-sm text-slate-500">Status</p>
            <p class="text-lg font-semibold text-green-600">Active</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow p-5">
            <p class="text-sm text-slate-500">Location ID</p>
            <p class="text-lg font-semibold text-slate-800 dark:text-white">
                #{{ $location->id }}
            </p>
        </div>

    </div>

    {{-- HOTSPOTS LIST --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow p-6">

        <h2 class="font-semibold text-lg mb-4 text-slate-800 dark:text-white">
            Hotspots
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">

                <thead class="border-b border-slate-200 dark:border-slate-700">
                    <tr class="text-slate-600 dark:text-slate-300">
                        <th class="px-3 py-2">#</th>
                        <th class="px-3 py-2">Name</th>
                        <th class="px-3 py-2">SSID</th>
                        <th class="px-3 py-2">Speed</th>
                        <th class="px-3 py-2 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">

                    @forelse($location->hotspots as $i => $hotspot)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <td class="px-3 py-2">{{ $i+1 }}</td>
                        <td class="px-3 py-2 font-medium">{{ $hotspot->name }}</td>
                        <td class="px-3 py-2">{{ $hotspot->ssid }}</td>
                        <td class="px-3 py-2">
                            {{ $hotspot->download_speed }}/{{ $hotspot->upload_speed }}
                            {{ $hotspot->speed_unit }}
                        </td>
                        <td class="px-3 py-2 text-right space-x-2">

    {{-- EDIT --}}
   <td class="px-3 py-2 text-right space-x-2">

    <a href="{{ route('admin.hotspots.wizard.step2', [$location,$hotspot]) }}"
       class="text-blue-500 hover:text-blue-700">
        ✏️
    </a>

    <form action="{{ route('admin.hotspots.destroy',$hotspot) }}"
          method="POST"
          class="inline"
          onsubmit="return confirm('Delete this hotspot?')">
        @csrf
        @method('DELETE')
        <button class="text-red-500 hover:text-red-700">🗑</button>
    </form>

</td>


                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-slate-400">
                            No hotspots for this location
                        </td>
                    </tr>
                    @endforelse

                </tbody>

            </table>
        </div>

    </div>

</div>

</x-admin-layout>
