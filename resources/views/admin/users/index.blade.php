@extends('layouts.admin')

@section('title', 'Users')
@section('page_title', 'Users')

@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold">Users</h1>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th class="text-right">Change Role</th>
                </tr>
            </thead>

            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td class="muted">{{ $u->id }}</td>
                        <td class="font-semibold">{{ $u->name }}</td>
                        <td class="muted">{{ $u->email }}</td>
                        <td class="muted">{{ $u->roles->first()->name ?? '-' }}</td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('sa.users.setRole', $u) }}" class="inline-flex gap-2">
                                @csrf
                                <select name="role" class="select w-44">
                                    @foreach($roles as $r)
                                        <option value="{{ $r }}" @selected(($u->roles->first()->name ?? '') === $r)>
                                            {{ $r }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn-outline">Save</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

</div>
@endsection
