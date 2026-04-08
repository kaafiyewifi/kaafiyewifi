@extends('layouts.admin')

@section('content')
<div class="min-h-[calc(100vh-120px)] bg-gray-50 dark:bg-slate-950">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">

        {{-- Title --}}
        <div class="text-center">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
                Add Mikrotik Device
            </h1>
            <p class="mt-4 text-slate-500 dark:text-slate-400 leading-relaxed">
                To proceed with the onboarding, connect your Mikrotik router to enable automated
                provisioning and management.
            </p>
        </div>

        {{-- Card --}}
        <div class="mt-10 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">

            {{-- Card header --}}
            <div class="px-6 sm:px-10 py-8 border-b border-slate-200 dark:border-slate-800">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Device Configuration</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Follow these steps to connect your Mikrotik device to our billing system.
                </p>
            </div>

            {{-- Stepper band --}}
            <div class="px-6 sm:px-10 py-8 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40">
                <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr_auto_1fr] items-center gap-6">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-12 h-12 rounded-full bg-orange-500 text-white flex items-center justify-center font-extrabold">1</div>
                        <div>
                            <div class="font-bold">Connection</div>
                            <div class="text-sm text-slate-500">Basic device information</div>
                        </div>
                    </div>

                    <div class="hidden md:block w-24 lg:w-40 h-px bg-slate-200 dark:bg-slate-700"></div>

                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-orange-100 text-orange-600 border flex items-center justify-center font-extrabold">2</div>
                        <div>
                            <div class="font-bold">Device Details</div>
                            <div class="text-sm text-slate-500">Provisioning command</div>
                        </div>
                    </div>

                    <div class="hidden md:block w-24 lg:w-40 h-px bg-slate-200 dark:bg-slate-700"></div>

                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-orange-100 text-orange-600 border flex items-center justify-center font-extrabold">3</div>
                        <div>
                            <div class="font-bold">Service Setup</div>
                            <div class="text-sm text-slate-500">Configure PPPoE and Hotspot</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('admin.routers.wizard.stage1.store') }}" class="px-6 sm:px-10 py-10">
                @csrf

                <div class="max-w-4xl space-y-8">

                    {{-- Identity --}}
                    <div>
                        <label class="block text-sm font-bold">
                            Mikrotik Identity <span class="text-orange-600">*</span>
                        </label>

                        <input
                            name="identity"
                            value="{{ old('identity') }}"
                            required
                            placeholder="MikroTik10"
                            class="mt-3 w-full rounded-xl border px-4 py-3 pl-4"
                        >
                    </div>

                    {{-- ✅ LOCATION ADDED --}}
                    <div>
                        <label class="block text-sm font-bold">
                            Location
                        </label>

                        <select
                            name="location_id"
                            class="mt-3 w-full rounded-xl border px-4 py-3"
                        >
                            <option value="">Select location</option>

                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Button --}}
                    <div class="flex justify-end pt-6">
                        <button
                            type="submit"
                            class="bg-slate-900 text-white px-10 py-4 rounded-xl font-bold"
                        >
                            Next Step →
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>
</div>
@endsection