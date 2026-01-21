<x-admin-layout>
    <x-slot name="title">Routers</x-slot>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">
            Routers
        </h1>

        <a href="{{ route('admin.routers.create') }}"
           class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            + Add Router
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto bg-white rounded-xl shadow">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">IP</th>
                    <th class="px-4 py-3 text-left">Location</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($routers as $router)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $router->name }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $router->ip_address }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $router->location->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($router->is_active)
                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                    Active
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center space-x-2">
                            <a href="{{ route('admin.routers.edit', $router) }}"
                               class="text-indigo-600 hover:underline">
                                Edit
                            </a>

                            <form action="{{ route('admin.routers.destroy', $router) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('Delete this router?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                            No routers found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $routers->links() }}
    </div>
</x-admin-layout>
