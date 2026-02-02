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

                    {{-- Step 1 active --}}
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-12 h-12 rounded-full bg-orange-500 text-white flex items-center justify-center font-extrabold">
                            1
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-slate-900 dark:text-slate-100">Connection</div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">Basic device information</div>
                        </div>
                    </div>

                    <div class="hidden md:block w-24 lg:w-40 h-px bg-slate-200 dark:bg-slate-700"></div>

                    {{-- Step 2 inactive --}}
                    <div class="flex items-center gap-4 min-w-0 md:justify-center">
                        <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-500/15 text-orange-600 border border-orange-200 dark:border-orange-500/30 flex items-center justify-center font-extrabold">
                            2
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-slate-900 dark:text-slate-100">Device Details</div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">Provisioning command</div>
                        </div>
                    </div>

                    <div class="hidden md:block w-24 lg:w-40 h-px bg-slate-200 dark:bg-slate-700"></div>

                    {{-- Step 3 inactive --}}
                    <div class="flex items-center gap-4 min-w-0 md:justify-end">
                        <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-500/15 text-orange-600 border border-orange-200 dark:border-orange-500/30 flex items-center justify-center font-extrabold">
                            3
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-slate-900 dark:text-slate-100">Service Setup</div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">Configure PPPoE and Hotspot</div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('admin.routers.wizard.stage1.store') }}" class="px-6 sm:px-10 py-10">
                @csrf

                <div class="max-w-4xl">
                    <label class="block text-sm font-bold text-slate-900 dark:text-slate-100">
                        Mikrotik Identity <span class="text-orange-600">*</span>
                    </label>

                    <div class="mt-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-3 flex items-center">
                                <div class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-400 dark:text-slate-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 6h16M7 10h10M10 14h4" />
                                    </svg>
                                </div>
                            </div>

                            <input
                                name="identity"
                                value="{{ old('identity') }}"
                                required
                                placeholder="MikroTik10"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900
                                       px-4 py-3 pl-16 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500
                                       focus:outline-none focus:ring-2 focus:ring-orange-200 dark:focus:ring-orange-500/30 focus:border-orange-300 dark:focus:border-orange-500/40"
                            />
                        </div>

                        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                            The identity name of your Mikrotik device (System → Identity)
                        </p>

                        @error('identity')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-14 flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-4 rounded-2xl bg-slate-900 hover:bg-slate-950
                                   dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white
                                   px-10 sm:px-14 py-4 sm:py-5 text-white font-bold
                                   focus:outline-none focus:ring-2 focus:ring-slate-200 dark:focus:ring-slate-700"
                        >
                            Next Step
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14" />
                                <path d="M13 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
