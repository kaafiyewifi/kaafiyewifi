@extends('layouts.admin')
@section('page_title','Create Customer')

@section('content')
<div class="min-h-[calc(100vh-160px)] px-4 py-8">

    {{-- Back (outside card, top-right) --}}
    <div class="max-w-3xl mx-auto mb-4 flex justify-end">
        <a href="{{ route('admin.customers.index') }}"
           class="rounded-xl px-4 py-2 text-sm font-semibold
                  border border-gray-200 dark:border-gray-800
                  bg-white dark:bg-gray-950 hover:bg-gray-50 dark:hover:bg-gray-900">
            ← Back
        </a>
    </div>

    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-950
                border border-gray-200 dark:border-gray-800
                rounded-2xl p-6 sm:p-8 shadow-sm">

        <h2 class="text-2xl font-bold mb-1">Create Customer</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Add a new hotspot customer (username = phone, default password = 123456).
        </p>

        <form method="POST" action="{{ route('admin.customers.store') }}">
            @csrf
            @include('admin.customers.partials.form', ['customer' => null])
        </form>
    </div>
</div>
@endsection
