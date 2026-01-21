@extends('layouts.admin')

@section('title', 'Admin Home')
@section('header', 'Admin Home')

@section('content')
    <div class="space-y-3">
        <h1 class="text-2xl font-bold">Welcome</h1>
        <p class="opacity-80">Core setup is working: auth + roles + admin layout.</p>

        <div class="mt-4">
            <a class="inline-block px-4 py-2 rounded bg-gray-900 text-white"
               href="{{ route('dashboard') }}">
                Back to Dashboard
            </a>
        </div>
    </div>
@endsection
