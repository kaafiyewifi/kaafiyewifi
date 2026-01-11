<x-admin-layout>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div x-data="{showCreate:false,showEdit:false,editHotspot:{}}" class="space-y-6">

    {{-- HEADER --}}
    <div class="flex justify-between">
        <h1 class="text-2xl font-semibold text-purple-700">Hotspots</h1>
        <button
            @click="showCreate=true"
            class="bg-green-600 text-white px-4 py-2 rounded">
            + Add Hotspot
        </button>
    </div>

    {{-- TABLE --}}
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-purple-50 text-purple-700">
                <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">SSID</th>
                    <th class="px-4 py-2">Location</th>
                    <th class="px-4 py-2">Speed</th>
                    <th class="px-4 py-2">Users</th>
                    <th class="px-4 py-2 text-right">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @foreach($hotspots as $h)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2 font-medium">{{ $h->name }}</td>
                    <td class="px-4 py-2">{{ $h->ssid }}</td>
                    <td class="px-4 py-2">{{ $h->location->name }}</td>
                    <td class="px-4 py-2">
                        {{ $h->download_speed }}/{{ $h->upload_speed }} {{ $h->speed_unit }}
                    </td>
                    <td class="px-4 py-2">{{ $h->max_users }}</td>

                    <td class="px-4 py-2 text-right flex justify-end gap-2">
                        <button
                            @click='showEdit=true; editHotspot=@json($h)'
                            class="text-blue-600">✏️</button>

                        <form method="POST" action="{{ route('admin.hotspots.destroy',$h) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600">🗑</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- CREATE MODAL --}}
    <div x-show="showCreate" x-cloak
         class="fixed inset-0 bg-black/50 flex justify-center items-center z-50">
        <form method="POST" action="{{ route('admin.hotspots.store') }}"
              class="bg-white p-6 rounded-xl w-full max-w-md space-y-3">
            @csrf

            <select name="location_id" class="w-full border p-2 rounded">
                @foreach($locations as $l)
                    <option value="{{ $l->id }}">{{ $l->name }}</option>
                @endforeach
            </select>

            <input name="name" placeholder="Hotspot Name" class="w-full border p-2 rounded">
            <input name="ssid" placeholder="SSID" class="w-full border p-2 rounded">

            <div class="flex gap-2">
                <input name="download_speed" placeholder="Download" class="w-full border p-2 rounded">
                <input name="upload_speed" placeholder="Upload" class="w-full border p-2 rounded">
            </div>

            <select name="speed_unit" class="w-full border p-2 rounded">
                <option>Mbps</option>
                <option>Kbps</option>
            </select>

            <input name="max_users" placeholder="Max Users" class="w-full border p-2 rounded">

            <div class="flex gap-2 pt-3">
                <button type="button" @click="showCreate=false"
                        class="flex-1 border py-2 rounded">Cancel</button>
                <button class="flex-1 bg-purple-700 text-white py-2 rounded">
                    Save
                </button>
            </div>
        </form>
    </div>

    {{-- EDIT MODAL --}}
    <div x-show="showEdit" x-cloak
         class="fixed inset-0 bg-black/50 flex justify-center items-center z-50">
        <form method="POST"
              :action="`/admin/hotspots/${editHotspot.id}`"
              class="bg-white p-6 rounded-xl w-full max-w-md space-y-3">
            @csrf
            @method('PUT')

            <input name="name" x-model="editHotspot.name" class="w-full border p-2 rounded">
            <input name="ssid" x-model="editHotspot.ssid" class="w-full border p-2 rounded">

            <div class="flex gap-2">
                <input name="download_speed" x-model="editHotspot.download_speed"
                       class="w-full border p-2 rounded">
                <input name="upload_speed" x-model="editHotspot.upload_speed"
                       class="w-full border p-2 rounded">
            </div>

            <select name="speed_unit" x-model="editHotspot.speed_unit"
                    class="w-full border p-2 rounded">
                <option>Mbps</option>
                <option>Kbps</option>
            </select>

            <input name="max_users" x-model="editHotspot.max_users"
                   class="w-full border p-2 rounded">

            <div class="flex gap-2 pt-3">
                <button type="button" @click="showEdit=false"
                        class="flex-1 border py-2 rounded">Cancel</button>
                <button class="flex-1 bg-purple-700 text-white py-2 rounded">
                    Update
                </button>
            </div>
        </form>
    </div>

</div>

</x-admin-layout>
