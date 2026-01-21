<x-admin-layout>
    <x-slot name="title">Edit Router</x-slot>

    <h1 class="text-2xl font-semibold mb-6 text-gray-800">
        Edit Router
    </h1>

    <form action="{{ route('admin.routers.update', $router) }}" method="POST"
          class="bg-white p-6 rounded-xl shadow max-w-xl">
        @csrf
        @method('PUT')

        @include('admin.routers.partials.form', ['router' => $router])

        <div class="mt-6 flex gap-3">
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">
                Update
            </button>
            <a href="{{ route('admin.routers.index') }}"
               class="px-4 py-2 bg-gray-200 rounded-lg">
                Cancel
            </a>
        </div>
    </form>
</x-admin-layout>
