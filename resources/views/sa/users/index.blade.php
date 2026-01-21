@extends('layouts.admin')

@section('title', 'Users')
@section('header', 'User Management (Super Admin)')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Users</h1>

    <div class="overflow-x-auto border border-gray-200 rounded">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
            <tr>
                <th class="text-left p-3">ID</th>
                <th class="text-left p-3">Name</th>
                <th class="text-left p-3">Email</th>
                <th class="text-left p-3">Role</th>
                <th class="text-left p-3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $u)
                <tr class="border-t border-gray-200">
                    <td class="p-3">{{ $u->id }}</td>
                    <td class="p-3">{{ $u->name }}</td>
                    <td class="p-3">{{ $u->email }}</td>
                    <td class="p-3">
                        {{ $u->getRoleNames()->first() ?? 'none' }}
                    </td>
                    <td class="p-3">
                        <form method="POST" action="{{ route('sa.users.setRole', $u) }}" class="flex gap-2 items-center">
                            @csrf
                            <select name="role" class="border rounded px-2 py-1">
                                @foreach($roles as $r)
                                    <option value="{{ $r }}" @selected(($u->getRoleNames()->first() ?? '') === $r)>
                                        {{ $r }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="px-3 py-1 rounded bg-gray-900 text-white">
                                Save
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
