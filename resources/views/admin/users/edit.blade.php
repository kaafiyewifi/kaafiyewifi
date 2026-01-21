@extends('layouts.admin')
@section('title','Edit User')

@section('content')
<h1 class="text-xl font-semibold mb-6">Edit User</h1>

<form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4 max-w-xl">
  @csrf @method('PUT')
  @include('admin.users.partials.form')
  <button class="px-4 py-2 rounded bg-gray-900 text-white">Update</button>
</form>
@endsection
