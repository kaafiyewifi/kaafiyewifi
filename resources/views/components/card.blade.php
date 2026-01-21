@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'bg-white border border-gray-200 rounded-2xl shadow-sm']) }}>
    <div class="p-5 sm:p-6">
        @if($title)
            <div class="mb-4">
                <h2 class="text-lg font-semibold">{{ $title }}</h2>
                @if($subtitle)
                    <p class="mt-1 text-sm text-gray-600">{{ $subtitle }}</p>
                @endif
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
