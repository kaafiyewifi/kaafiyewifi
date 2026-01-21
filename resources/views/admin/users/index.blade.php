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

<div class="flex items-center justify-between gap-3 mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Users</h1>

   @can('manage users')
    <a href="{{ route('admin.users.create') }}"
       class="inline-flex items-center gap-2 rounded-lg px-4 py-2
              text-sm font-semibold text-white
              transition hover:opacity-90"
       style="background-color:#ff4b2b;">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="2" stroke-linecap="round" d="M12 5v14M5 12h14"/>
        </svg>
        Create User
    </a>
@endcan

</div>

{{-- Flash --}}
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

{{-- Search --}}
<form method="GET" action="{{ route('admin.users.index') }}" class="mb-4 flex flex-col sm:flex-row gap-3">
    <input
        type="text"
        name="q"
        value="{{ request('q') }}"
        placeholder="Search name or email..."
        class="w-full rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900
               px-4 py-2 text-sm text-gray-900 dark:text-gray-100 outline-none
               focus:ring-2 focus:ring-white/10 focus:border-gray-300 dark:focus:border-gray-700" />

    <div class="flex gap-2">
        <button type="submit"
                class="rounded-lg px-4 py-2 text-sm font-semibold {{ $btnSoft }} text-gray-900 dark:text-gray-100">
            Search
        </button>

        @if(request('q'))
            <a href="{{ route('admin.users.index') }}"
               class="rounded-lg px-4 py-2 text-sm font-semibold {{ $btnSoft }} text-gray-900 dark:text-gray-100">
                Clear
            </a>
        @endif
    </div>
</form>

<div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Name</th>
                    <th class="px-4 py-3 text-left font-semibold">Email</th>
                    <th class="px-4 py-3 text-left font-semibold">Role</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-left font-semibold">Created</th>
                    <th class="px-4 py-3 text-left font-semibold">Locations</th>
                    <th class="px-4 py-3 text-right font-semibold">Actions</th>
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

                <tr class="text-gray-900 dark:text-gray-100">
                    <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $user->email }}</td>

                    <td class="px-4 py-3">
                        @if($role)
                            <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-xs font-semibold">
                                {{ $role }}
                            </span>
                        @else
                            <span class="text-gray-500 dark:text-gray-400">-</span>
                        @endif
                    </td>

                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                            {{ $isActive ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-200' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-200' }}">
                            {{ ucfirst($status) }}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                        {{ optional($user->created_at)->format('Y-m-d') }}
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ optional($user->created_at)->format('h:i A') }}
                        </div>
                    </td>

                    <td class="px-4 py-3">
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
                                {{-- Edit icon --}}
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg {{ $btnSoft }}"
                                   title="Edit">
                                    <svg class="w-4 h-4 text-gray-700 dark:text-gray-200" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 20h9"/>
                                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                              d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/>
                                    </svg>
                                </a>

                                {{-- Delete icon --}}
                                @if(!$isSelf && !$isSuper)
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg {{ $secondary }}"
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
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-200 dark:bg-gray-800 text-gray-500 cursor-not-allowed"
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
                    <td colspan="7" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">
                        No users found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-gray-200 dark:border-gray-800 px-4 py-3">
        {{ $users->appends(request()->query())->links() }}
    </div>
</div>
@endsection
