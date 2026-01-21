@extends('layouts.admin')

@section('title','Locations')
@section('header','Locations')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-5 flex items-start justify-between">
    <div>
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">
            Locations
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Manage your branches and their profiles.
        </p>
    </div>

    <a href="{{ route('admin.locations.create') }}"
       class="btn-primary inline-flex items-center gap-2">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  d="M12 6v12m6-6H6"/>
        </svg>
        New Location
    </a>
</div>

{{-- CARD --}}
<div class="rounded-2xl border border-gray-200 bg-white shadow-sm
            dark:border-gray-700 dark:bg-gray-800">

    {{-- CARD HEADER --}}
    <div class="flex items-center justify-between px-5 py-4
                border-b border-gray-200 dark:border-gray-700
                bg-gray-50 dark:bg-gray-900 rounded-t-2xl">
        <div>
            <h2 class="text-sm font-semibold tracking-wide text-gray-700 dark:text-gray-300 uppercase">
                All Locations
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                List of locations in your system
            </p>
        </div>

        <span class="text-sm text-gray-500 dark:text-gray-400">
            Total: {{ $locations->count() }}
        </span>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs uppercase tracking-wide
                           text-gray-500 dark:text-gray-400
                           border-b border-gray-200 dark:border-gray-700">
                    <th class="px-5 py-3 text-left">Code</th>
                    <th class="px-5 py-3 text-left">Name</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">City</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($locations as $location)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40 transition">

                    {{-- Code --}}
                    <td class="px-5 py-3 font-mono text-gray-800 dark:text-gray-100">
                        {{ $location->code }}
                    </td>

                    {{-- Name --}}
                    <td class="px-5 py-3 font-medium text-gray-900 dark:text-gray-100">
                        {{ $location->name }}
                    </td>

                    {{-- Status --}}
                    <td class="px-5 py-3">
                        @if($location->status === 'active')
                            <span class="inline-flex items-center gap-1
                                         rounded-full bg-green-100 px-2.5 py-1
                                         text-xs font-semibold text-green-700
                                         dark:bg-green-900/40 dark:text-green-300">
                                ● Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1
                                         rounded-full bg-red-100 px-2.5 py-1
                                         text-xs font-semibold text-red-700
                                         dark:bg-red-900/40 dark:text-red-300">
                                ● Inactive
                            </span>
                        @endif
                    </td>

                    {{-- City --}}
                    <td class="px-5 py-3 text-gray-700 dark:text-gray-300">
                        {{ $location->city ?? '—' }}
                    </td>

                    {{-- Actions --}}
                    <td class="px-5 py-3">
                        <div class="flex justify-end items-center gap-2">

                            {{-- View --}}
                            <a href="{{ route('admin.locations.show', $location->id) }}"
                               title="View"
                               class="w-9 h-9 flex items-center justify-center rounded-lg
                                      text-blue-600 hover:bg-blue-50
                                      dark:text-blue-400 dark:hover:bg-blue-900/30">
                                👁️
                            </a>

                            {{-- Edit --}}
                            <a href="{{ route('admin.locations.edit', $location->id) }}"
                               title="Edit"
                               class="w-9 h-9 flex items-center justify-center rounded-lg
                                      text-orange-500 hover:bg-orange-50
                                      dark:text-orange-400 dark:hover:bg-orange-900/30">
                                ✏️
                            </a>

                            {{-- Delete --}}
                            <form method="POST"
                                  action="{{ route('admin.locations.destroy', $location->id) }}"
                                  onsubmit="return confirm('Delete this location?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        title="Delete"
                                        class="w-9 h-9 flex items-center justify-center rounded-lg
                                               text-red-600 hover:bg-red-50
                                               dark:text-red-400 dark:hover:bg-red-900/30">
                                    🗑️
                                </button>
                            </form>

                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                        No locations found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
