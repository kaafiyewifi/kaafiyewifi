@extends('layouts.admin')

@section('title','Profile')
@section('header','Profile')

@section('content')
<div class="mb-6 flex items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Profile</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Manage your account settings.</p>
    </div>

    <a href="{{ route('admin.home') }}"
       class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2
              text-sm font-semibold text-gray-700 hover:bg-gray-50 transition
              dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Dashboard
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    {{-- Profile --}}
    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden
                    dark:border-gray-700 dark:bg-gray-800">
        <div class="px-5 py-4 border-b border-gray-200 bg-gray-50
                    dark:border-gray-700 dark:bg-gray-900">
            <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100">Profile Information</h2>
            <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-300">
                Update your account's profile information and email address.
            </p>
        </div>

        <div class="p-5">
            @include('profile.partials.update-profile-information-form')
        </div>
    </section>

    {{-- Password --}}
    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden
                    dark:border-gray-700 dark:bg-gray-800">
        <div class="px-5 py-4 border-b border-gray-200 bg-gray-50
                    dark:border-gray-700 dark:bg-gray-900">
            <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100">Update Password</h2>
            <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-300">
                Use a long, random password to stay secure.
            </p>
        </div>

        <div class="p-5">
            @include('profile.partials.update-password-form')
        </div>
    </section>
</div>
@endsection
