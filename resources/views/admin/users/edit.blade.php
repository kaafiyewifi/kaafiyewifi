@extends('layouts.admin')
@section('page_title','Edit User')

@section('content')
<div class="min-h-[calc(100vh-160px)] px-4 py-8">

    {{-- Back --}}
    <div class="max-w-3xl mx-auto mb-4 flex justify-end">
        <a href="{{ route('admin.users.index') }}"
           class="rounded-xl px-4 py-2 text-sm font-semibold
                  border border-gray-200 dark:border-gray-800
                  bg-white dark:bg-gray-950 hover:bg-gray-50 dark:hover:bg-gray-900">
            ← Back
        </a>
    </div>

    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-950
                border border-gray-200 dark:border-gray-800
                rounded-2xl p-6 sm:p-8 shadow-sm">

        <h2 class="text-2xl font-bold mb-1">Edit User</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Update user information and access.
        </p>

        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PATCH')
            @include('admin.users.partials.form')
        </form>
    </div>
</div>
@endsection
