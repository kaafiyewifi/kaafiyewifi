@extends('layouts.admin')

@section('title','Edit Location')
@section('header','Edit Location')

@section('content')
<div class="mb-6 flex items-start justify-between gap-4">
  <div>
    <h1 class="text-2xl font-semibold">Edit Location</h1>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
      Code: <span class="font-mono">{{ $location->code }}</span>
    </p>
  </div>

  <a href="{{ route('admin.locations.index') }}" class="btn-outline">
    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12l7.5-7.5M3 12h18"/>
    </svg>
    Back
  </a>
</div>

@if ($errors->any())
  <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800
              dark:border-red-900/40 dark:bg-red-950/40 dark:text-red-200">
    <div class="font-semibold mb-1">Update failed. Please fix:</div>
    <ul class="list-disc pl-5 space-y-1">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="w-full max-w-3xl mx-auto">
  <div class="card card-pad">
    <form method="POST" action="{{ route('admin.locations.update', $location->id) }}" class="space-y-5">
      @csrf
      @method('PUT')

      @include('admin.locations.partials.form', ['location' => $location])

      <div class="pt-2 flex items-center justify-between">
        <a href="{{ route('admin.locations.show', $location->id) }}"
           class="text-sm text-gray-700 hover:underline dark:text-gray-200">
          View profile
        </a>

        <button type="submit" class="btn-primary">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l1.5 1.5L15 9.75"/>
            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h9A2.25 2.25 0 0 1 18.75 6v12A2.25 2.25 0 0 1 16.5 20.25h-9A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z"/>
          </svg>
          Update Location
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
