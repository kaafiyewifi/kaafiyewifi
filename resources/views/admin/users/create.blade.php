@extends('layouts.admin')
@section('title','Create User')

@section('content')
<h1 class="text-xl font-semibold mb-6">Create User</h1>

<form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4 max-w-xl">
  @csrf
  @include('admin.users.partials.form', ['user' => null, 'currentRole' => null, 'selectedLocations' => []])
  <button class="px-4 py-2 rounded bg-gray-900 text-white">Save</button>
</form>
@endsection
