@extends('layouts.admin')

@section('title', $location->name)

@section('content')
<div class="mb-6 flex items-start justify-between">
  <div>
    <div class="text-sm opacity-70">Location Profile</div>
    <h1 class="text-2xl font-semibold mt-1">{{ $location->name }}</h1>
    <div class="mt-2 text-sm opacity-80">
      Code: <span class="font-mono">{{ $location->code }}</span> •
      Status: <strong>{{ $location->status }}</strong>
    </div>
    <div class="mt-1 text-sm opacity-70">
      {{ $location->address ?? '—' }} • {{ $location->city ?? '—' }}
    </div>
  </div>

  <div class="flex gap-2">
    <a href="{{ route('admin.locations.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-200 hover:bg-gray-50">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12l7.5-7.5M3 12h18"/>
      </svg>
      Back
    </a>

    <a href="{{ route('admin.locations.edit', ['location' => $location->id]) }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-[#ff4b2b] hover:bg-[#e64326] text-white font-medium shadow-sm">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M4.5 19.5h3.75L19.5 8.25a1.875 1.875 0 00-2.652-2.652L5.598 16.848 4.5 19.5z"/>
      </svg>
      Edit
    </a>
  </div>
</div>

<div class="grid gap-4">
  <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
    <div class="font-semibold mb-2">Hotspots</div>
    <div class="text-sm opacity-80">
      Hotspots module wuxuu imanayaa xiga (CRUD under this location). Halkan waxaan gelin doonaa list + Create Hotspot button.
    </div>
  </div>
</div>
@endsection
