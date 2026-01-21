<div
    x-data="{ show:true }"
    x-init="setTimeout(()=>show=true,50)"
    x-show="show"
    x-transition:enter="transition ease-out duration-500"
    x-transition:enter-start="opacity-0 translate-y-6"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-6"
>


<x-admin-layout>

<div class="min-h-[calc(100vh-5rem)] flex items-center justify-center bg-slate-100 px-4">

    <div class="bg-white
                w-full max-w-5xl
                rounded-2xl shadow-lg
                p-10
                transition-all duration-300">

        {{-- WIZARD HEADER --}}
        <div class="flex gap-10 mb-8">

            <div class="flex flex-col items-center text-sm">
                <div class="w-7 h-7 rounded-full bg-[#ff5d39] text-white flex items-center justify-center">1</div>
                <span class="mt-2 font-medium text-slate-800">Initial settings</span>
            </div>

            <div class="flex flex-col items-center text-sm opacity-40">
                <div class="w-7 h-7 rounded-full bg-slate-300 text-white flex items-center justify-center">2</div>
                <span class="mt-2">Configuration</span>
            </div>

            <div class="flex flex-col items-center text-sm opacity-40">
                <div class="w-7 h-7 rounded-full bg-slate-300 text-white flex items-center justify-center">3</div>
                <span class="mt-2">Result</span>
            </div>

        </div>

        {{-- FORM --}}
        <form method="POST"
              action="{{ route('admin.hotspots.wizard.store.step1',$location) }}"
              class="space-y-6">

            @csrf

            <div class="grid md:grid-cols-2 gap-6">

                {{-- TITLE --}}
                <div class="md:col-span-2">
                    <label class="text-sm text-slate-600">Title *</label>
                    <input name="title" required
                        class="w-full mt-1 px-4 py-3 rounded-xl border
                               border-slate-300
                               bg-white
                               text-slate-800
                               focus:ring-2 focus:ring-[#ff5d39]"
                        placeholder="Hotspot title">
                </div>

                {{-- NAS TYPE --}}
                <div>
                    <label class="text-sm text-slate-600">NAS type</label>
                    <select name="nas_type"
                        class="w-full mt-1 px-4 py-3 rounded-xl border
                               border-slate-300
                               bg-white
                               text-slate-800">
                        <option value="MikroTik">MikroTik</option>
                        <option value="Cambium">Cambium</option>
                        <option value="Ubiquiti">Ubiquiti</option>
                    </select>
                </div>

                {{-- PHYSICAL ADDRESS --}}
                <div>
                    <label class="text-sm text-slate-600">Physical address</label>
                    <input name="physical_address"
                        class="w-full mt-1 px-4 py-3 rounded-xl border
                               border-slate-300
                               bg-white
                               text-slate-800"
                        placeholder="Address">
                </div>

                {{-- SSID --}}
                <div class="md:col-span-2">
                    <label class="text-sm text-slate-600">SSID *</label>
                    <input name="ssid" required
                        class="w-full mt-1 px-4 py-3 rounded-xl border
                               border-slate-300
                               bg-white
                               text-slate-800
                               focus:ring-2 focus:ring-[#ff5d39]"
                        placeholder="WiFi name">
                </div>

            </div>

            {{-- SPEED --}}
            <div class="grid grid-cols-3 gap-4">

                <input name="download_speed" type="number"
                    class="px-4 py-3 rounded-xl border
                           border-slate-300
                           bg-white
                           text-slate-800"
                    placeholder="Download">

                <input name="upload_speed" type="number"
                    class="px-4 py-3 rounded-xl border
                           border-slate-300
                           bg-white
                           text-slate-800"
                    placeholder="Upload">

                <select name="speed_unit"
                    class="px-4 py-3 rounded-xl border
                           border-slate-300
                           bg-white
                           text-slate-800">
                    <option>Mbps</option>
                    <option>Kbps</option>
                    <option>Gbps</option>
                </select>

            </div>

            {{-- BUTTON --}}
            <div class="flex justify-end pt-6">

               <button
    type="submit"
    class="px-10 py-3 rounded-xl text-white font-medium shadow
           hover:opacity-90 transition flex items-center gap-2"
    style="background:#ff5d39">

    <span>Next</span>
    <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</button>


            </div>

        </form>

    </div>

</div>

</x-admin-layout>
