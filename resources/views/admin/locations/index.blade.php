<x-admin-layout>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div
    x-data="locationPage()"
    class="min-h-[calc(100vh-4rem)] flex justify-center px-6 py-10
           bg-slate-100 dark:bg-slate-900"
>
    <div class="w-full max-w-6xl space-y-6">

        {{-- ================= HEADER ================= --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-white">
                Locations
            </h1>

            <button
                @click="openCreate"
                class="px-4 py-2 rounded-lg text-sm font-medium text-white shadow"
                style="background:#ff5437"
            >
                + Add Location
            </button>
        </div>

        {{-- ================= CARD ================= --}}
        <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow
                   border border-slate-200 dark:border-slate-700
                   flex flex-col overflow-hidden"
        >

            {{-- SEARCH --}}
            <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                <input
                    x-model="search"
                    type="text"
                    placeholder="Search location..."
                    class="w-64 px-3 py-2 rounded-lg border text-sm
                           bg-white dark:bg-slate-900
                           border-slate-300 dark:border-slate-700
                           text-slate-700 dark:text-white"
                >
            </div>

            {{-- TABLE --}}
            <div class="overflow-y-auto max-h-[420px]">
                <table class="w-full text-sm">
                    <thead
                        class="sticky top-0 z-10
                               bg-slate-50 dark:bg-slate-700
                               text-slate-600 dark:text-slate-200"
                    >
                        <tr>
                            <th class="px-4 py-3 text-left w-16">ID</th>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-right w-32">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($locations as $location)
                        <tr
                            x-show="search === '' || '{{ strtolower($location->name) }}'.includes(search.toLowerCase())"
                            class="hover:bg-slate-50 dark:hover:bg-slate-700/40"
                        >
                            <td class="px-4 py-3 text-slate-500">
                                {{ $location->id }}
                            </td>

                            {{-- 🔥 AUTO HOTSPOT TAB LINK --}}
                            <td class="px-4 py-3 font-medium text-blue-600">
                                <a
                                  href="{{ route('admin.locations.show',$location) }}?tab=hotspots"
                                  class="hover:underline"
                                >
                                    {{ $location->name }}
                                </a>
                            </td>

                            <td class="px-4 py-3 text-right space-x-3">

                                {{-- VIEW --}}
                                <a
                                    href="{{ route('admin.locations.show',$location) }}?tab=hotspots"
                                    class="text-indigo-600"
                                    title="View"
                                >
                                    👁
                                </a>

                                {{-- EDIT --}}
                                <button
                                    @click="openEdit({{ $location->id }}, '{{ $location->name }}')"
                                    class="text-orange-500"
                                    title="Edit"
                                >
                                    ✏️
                                </button>

                                {{-- DELETE --}}
                                <form
                                    method="POST"
                                    action="{{ route('admin.locations.destroy',$location) }}"
                                    class="inline"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        onclick="return confirm('Delete location?')"
                                        class="text-red-600"
                                        title="Delete"
                                    >
                                        🗑
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-16 text-center text-slate-400">
                                No locations found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                {{ $locations->links() }}
            </div>
        </div>
    </div>

    {{-- ================= CREATE MODAL ================= --}}
    <div
        x-show="showCreate"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
    >
        <form
            @submit.prevent="saveLocation"
            class="bg-white dark:bg-slate-800
                   w-full max-w-md rounded-xl p-6 space-y-4 shadow-xl"
        >
            <h2 class="text-lg font-semibold text-center text-slate-800 dark:text-white">
                Add Location
            </h2>

            <input
                x-model="form.name"
                class="w-full px-3 py-2 rounded-lg border
                       border-slate-300 dark:border-slate-700
                       dark:bg-slate-900 dark:text-white"
                placeholder="Location name"
                required
            >

            <div class="flex gap-3 pt-4">
                <button
                    type="button"
                    @click="closeAll"
                    class="flex-1 border rounded-lg py-2
                           border-slate-300 dark:border-slate-700
                           dark:text-white"
                >
                    Cancel
                </button>

                <button
                    class="flex-1 rounded-lg py-2 text-white font-medium"
                    style="background:#ff5437"
                >
                    Save
                </button>
            </div>
        </form>
    </div>

    {{-- ================= EDIT MODAL ================= --}}
    <div
        x-show="showEdit"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
    >
        <form
            @submit.prevent="updateLocation"
            class="bg-white dark:bg-slate-800
                   w-full max-w-md rounded-xl p-6 space-y-4 shadow-xl"
        >
            <h2 class="text-lg font-semibold text-center text-slate-800 dark:text-white">
                Edit Location
            </h2>

            <input
                x-model="edit.name"
                class="w-full px-3 py-2 rounded-lg border
                       border-slate-300 dark:border-slate-700
                       dark:bg-slate-900 dark:text-white"
                required
            >

            <div class="flex gap-3 pt-4">
                <button
                    type="button"
                    @click="closeAll"
                    class="flex-1 border rounded-lg py-2
                           border-slate-300 dark:border-slate-700
                           dark:text-white"
                >
                    Cancel
                </button>

                <button
                    class="flex-1 rounded-lg py-2 text-white font-medium"
                    style="background:#ff5437"
                >
                    Update
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function locationPage(){
    return {
        search: '',
        showCreate: false,
        showEdit: false,
        form: { name: '' },
        edit: { id: null, name: '' },

        openCreate(){
            this.form.name = '';
            this.showCreate = true;
        },

        openEdit(id, name){
            this.edit.id = id;
            this.edit.name = name;
            this.showEdit = true;
        },

        closeAll(){
            this.showCreate = false;
            this.showEdit = false;
        },

        async saveLocation(){
            await fetch("{{ route('admin.locations.store') }}",{
                method: 'POST',
                headers:{
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':'{{ csrf_token() }}'
                },
                body: JSON.stringify(this.form)
            });
            location.reload();
        },

        async updateLocation(){
            await fetch(`/admin/locations/${this.edit.id}`,{
                method:'PUT',
                headers:{
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':'{{ csrf_token() }}'
                },
                body: JSON.stringify({ name: this.edit.name })
            });
            location.reload();
        }
    }
}
</script>

</x-admin-layout>
