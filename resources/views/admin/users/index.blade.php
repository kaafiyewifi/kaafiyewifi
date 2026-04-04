{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Users')
@section('page_title', 'Users')

@section('content')
@php
    $primary   = 'bg-[#5a116a] hover:bg-[#4a0e59] text-white';
    $secondary = 'bg-[#ff5938] hover:bg-[#e94f31] text-white';
    $btnSoft   = 'border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800';
@endphp

<div class="max-w-7xl mx-auto px-4 py-6 overflow-x-hidden">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Users</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage system users
            </p>
        </div>

        @can('manage users')
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 shrink-0"
               style="background-color:#ff4b2b;">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2" stroke-linecap="round" d="M12 5v14M5 12h14"/>
                </svg>
                Create User
            </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">

        <div class="border-b border-gray-200 p-5 dark:border-gray-800">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Users List
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        View and manage all registered users
                    </p>
                </div>

                <form method="GET" action="{{ route('admin.users.index') }}" class="w-full xl:w-auto">
                    <div class="flex flex-col items-stretch gap-3 sm:flex-row xl:justify-end">
                        <div class="relative w-full sm:w-80 min-w-0">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 014.391 8.813l3.648 3.648a.75.75 0 11-1.06 1.06l-3.648-3.647A5.5 5.5 0 118.5 3zm-4 5.5a4 4 0 118 0 4 4 0 01-8 0z" clip-rule="evenodd" />
                                </svg>
                            </span>

                            <input
                                type="text"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="Search name or email..."
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900
                                       py-2.5 pl-10 pr-4 text-sm text-gray-900 dark:text-gray-100 outline-none
                                       focus:ring-2 focus:ring-white/10 focus:border-gray-300 dark:focus:border-gray-700"
                            >
                        </div>

                        <button type="submit"
                                class="rounded-xl px-4 py-2.5 text-sm font-semibold {{ $btnSoft }} text-gray-900 dark:text-gray-100 shrink-0">
                            Search
                        </button>

                        @if(request('q'))
                            <a href="{{ route('admin.users.index') }}"
                               class="rounded-xl px-4 py-2.5 text-sm font-semibold {{ $btnSoft }} text-gray-900 dark:text-gray-100 shrink-0 text-center">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="p-5">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="w-full overflow-x-auto overflow-y-auto max-h-[520px] overscroll-contain">
                    <div class="min-w-[1150px]">
                        <table class="w-full text-sm">
                            <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Name</th>
                                    <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Email</th>
                                    <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Role</th>
                                    <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Status</th>
                                    <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Created</th>
                                    <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Locations</th>
                                    <th class="px-4 py-3 text-right font-semibold whitespace-nowrap">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($users as $user)
                                @php
                                    $role = $user->roles->pluck('name')->first();
                                    $status = $user->status ?? 'active';
                                    $isActive = $status === 'active';
                                    $isSelf = auth()->id() === $user->id;
                                    $isSuper = $user->hasRole('super_admin');

                                    $locationNames = method_exists($user, 'locations')
                                        ? $user->locations->pluck('name')->filter()->values()
                                        : collect();
                                @endphp

                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60 text-gray-900 dark:text-gray-100">
                                    <td class="px-4 py-3 font-medium whitespace-nowrap">
                                        {{ $user->name }}
                                    </td>

                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                        {{ $user->email }}
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($role)
                                            <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-xs font-semibold">
                                                {{ $role }}
                                            </span>
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                                            {{ $isActive ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-200' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-200' }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                        {{ optional($user->created_at)->format('Y-m-d') }}
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ optional($user->created_at)->format('h:i A') }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 min-w-[180px]">
                                        @if($locationNames->count())
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($locationNames as $ln)
                                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700
                                                                dark:bg-blue-900/20 dark:text-blue-200">
                                                        {{ $ln }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">

                                            @can('manage users')
                                                <a href="{{ route('admin.users.edit', $user) }}"
                                                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg {{ $btnSoft }} shrink-0"
                                                   title="Edit">
                                                    <svg class="w-4 h-4 text-gray-700 dark:text-gray-200" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                              d="M12 20h9"/>
                                                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                              d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/>
                                                    </svg>
                                                </a>

                                                @if(!$isSelf && !$isSuper)
                                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                          onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg {{ $secondary }} shrink-0"
                                                                title="Delete">
                                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M3 6h18"/>
                                                                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M8 6V4h8v2"/>
                                                                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M19 6l-1 14H6L5 6"/>
                                                                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M10 11v6M14 11v6"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" disabled
                                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-200 dark:bg-gray-800 text-gray-500 cursor-not-allowed shrink-0"
                                                            title="{{ $isSelf ? 'Cannot delete yourself' : 'Cannot delete super_admin' }}">
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M3 6h18"/>
                                                            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M8 6V4h8v2"/>
                                                            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M19 6l-1 14H6L5 6"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            @endcan

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                        No users found.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400 sm:hidden">
                Swipe left/right inside the card to see full table
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-800 px-4 py-4 overflow-x-auto">
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection