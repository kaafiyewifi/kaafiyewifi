@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold">Router Duplicates Cleanup</h1>
            <p class="text-sm text-gray-500">Preview duplicates and safely delete pending/unseen routers.</p>
        </div>

        <form method="POST" action="{{ route('admin.routers.cleanup.run') }}">
            @csrf
            <button type="submit"
                class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700"
                onclick="return confirm('Delete duplicate routers (pending + never seen)?')">
                Delete Duplicates
            </button>
        </form>
    </div>

    @if(session('status'))
        <div class="mb-4 p-3 rounded bg-green-50 text-green-700">
            {{ session('status') }}
        </div>
    @endif

    @if(empty($groups) || count($groups) === 0)
        <div class="p-4 rounded bg-white border">
            No duplicates found ✅
        </div>
    @else
        <div class="space-y-4">
            @foreach($groups as $g)
                <div class="p-4 rounded bg-white border">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold">Identity: {{ $g['identity'] }}</div>
                            <div class="text-sm text-gray-500">
                                Keep: #{{ $g['keep']->id }} (status={{ $g['keep']->status }}, last_seen={{ $g['keep']->last_seen_at ?? 'null' }})
                            </div>
                        </div>
                        <div class="text-sm">
                            <span class="px-2 py-1 rounded bg-orange-50 text-orange-700">
                                Will delete: {{ $g['delete']->count() }}
                            </span>
                        </div>
                    </div>

                    @if($g['delete']->count() > 0)
                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500">
                                        <th class="py-2 pr-4">ID</th>
                                        <th class="py-2 pr-4">Name</th>
                                        <th class="py-2 pr-4">Status</th>
                                        <th class="py-2 pr-4">Last Seen</th>
                                        <th class="py-2 pr-4">Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($g['delete'] as $r)
                                        <tr class="border-t">
                                            <td class="py-2 pr-4">#{{ $r->id }}</td>
                                            <td class="py-2 pr-4">{{ $r->name }}</td>
                                            <td class="py-2 pr-4">{{ $r->status }}</td>
                                            <td class="py-2 pr-4">{{ $r->last_seen_at ?? 'null' }}</td>
                                            <td class="py-2 pr-4">{{ $r->created_at }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="mt-3 text-sm text-gray-500">Nothing safe to delete in this group.</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
