@if(session('toast'))
<div
    x-data="{ show:true }"
    x-init="setTimeout(() => show = false, 3500)"
    x-show="show"
    x-transition:enter="transform ease-out duration-300"
    x-transition:enter-start="translate-x-full opacity-0"
    x-transition:enter-end="translate-x-0 opacity-100"
    x-transition:leave="transform ease-in duration-200"
    x-transition:leave-start="translate-x-0 opacity-100"
    x-transition:leave-end="translate-x-full opacity-0"
    class="fixed top-6 right-6 z-50 min-w-[280px] rounded-xl shadow-lg text-white px-4 py-3 flex items-center gap-3
        {{ session('toast.type') === 'success' ? 'bg-green-600' : '' }}
        {{ session('toast.type') === 'error' ? 'bg-red-600' : '' }}
        {{ session('toast.type') === 'warning' ? 'bg-yellow-500 text-black' : '' }}
        {{ session('toast.type') === 'info' ? 'bg-blue-600' : '' }}
    "
>
    {{-- ICON --}}
    <span class="text-xl">
        @if(session('toast.type') === 'success') ✅
        @elseif(session('toast.type') === 'error') ❌
        @elseif(session('toast.type') === 'warning') ⚠
        @else ℹ
        @endif
    </span>

    {{-- MESSAGE --}}
    <span class="text-sm font-medium flex-1">
        {{ session('toast.message') }}
    </span>

    {{-- CLOSE --}}
    <button @click="show=false" class="text-white/80 hover:text-white">
        ✕
    </button>
</div>
@endif
