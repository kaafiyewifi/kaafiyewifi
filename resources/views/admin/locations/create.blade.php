@extends('layouts.admin')

@section('title','Create Location')
@section('header','Create Location')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            Create Location
        </h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
            Add a new location profile.
        </p>
    </div>

    {{-- BACK BUTTON --}}
    <a href="{{ route('admin.locations.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border border-gray-300
              px-4 py-2 text-sm font-medium text-gray-700
              hover:bg-gray-100 transition
              dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 19l-7-7 7-7"/>
        </svg>
        Back
    </a>
</div>

{{-- ERRORS --}}
@if ($errors->any())
  <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800
              dark:border-red-900/40 dark:bg-red-950/40 dark:text-red-200">
    <div class="font-semibold mb-1">Create failed. Please fix:</div>
    <ul class="list-disc pl-5 space-y-1">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

{{-- FORM CARD --}}
<div class="w-full max-w-3xl mx-auto">
  <div class="card card-pad">
    <form method="POST"
          action="{{ route('admin.locations.store') }}"
          class="space-y-5">
      @csrf

      @include('admin.locations.partials.form', ['location' => null])

      {{-- ACTIONS --}}
      <div class="pt-4 flex items-center justify-between">

        {{-- BACK (BOTTOM) --}}
        <a href="{{ route('admin.locations.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700
                  dark:text-gray-400 dark:hover:text-gray-200">
            ← Back to Locations
        </a>

        {{-- SAVE --}}
        <button type="submit" class="btn-primary">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  d="M12 6v12m6-6H6"/>
          </svg>
          Save Location
        </button>
      </div>

    </form>
  </div>
</div>

@endsection
