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

<div class="min-h-[calc(100vh-5rem)] bg-slate-100 flex items-center justify-center px-4">

    <div class="bg-white
                w-full max-w-5xl
                rounded-2xl shadow-lg
                p-10 transition-all duration-300">

        {{-- STEPPER --}}
        <div class="flex gap-10 mb-10 text-sm text-slate-500">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-full bg-green-500 text-white flex items-center justify-center">✓</span>
                Initial settings
            </div>

            <div class="flex items-center gap-2 font-semibold text-blue-600">
                <span class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center">2</span>
                Configuration
            </div>

            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-full border flex items-center justify-center">3</span>
                Result
            </div>
        </div>

        <form method="POST"
              action="{{ route('admin.hotspots.wizard.store.step2', [$location,$hotspot]) }}"
              class="space-y-8">

            @csrf

            {{-- SETUP TYPE --}}
            <div>
                <label class="block text-sm font-medium mb-2 text-slate-700">
                    Setup type
                </label>
                <select id="setupType"
                        class="w-full px-4 py-3 rounded-lg border
                               border-slate-300
                               bg-white
                               text-slate-800">
                    <option value="simple">Simple</option>
                    <option value="advanced">Advanced</option>
                </select>
            </div>

            {{-- SIMPLE SETUP --}}
            <div id="simpleSetup">
                <label class="block text-sm font-medium mb-2 text-slate-700">
                    Setup
                </label>
                <select
                    class="w-full px-4 py-3 rounded-lg border
                           border-slate-300
                           bg-white
                           text-slate-800">
                    <option>Setup VPN (Wireguard)</option>
                    <option>Setup VPN (OpenVPN)</option>
                    <option selected>Full setup (with Wireguard)</option>
                    <option>Full setup (with OpenVPN)</option>
                </select>
            </div>

            {{-- ADVANCED FIELDS --}}
            <div id="advancedFields"
                 class="space-y-6 hidden transition-all duration-300">

                <div>
                    <label class="block text-sm font-medium mb-2 text-slate-700">
                        * Connection type
                    </label>
                    <select name="connection_type"
                            class="w-full px-4 py-3 rounded-lg border
                                   border-slate-300
                                   bg-white
                                   text-slate-800">
                        <option value="">Select</option>
                        <option value="vpn">VPN</option>
                        <option value="direct">Direct</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-slate-700">
                        * Radius secret
                    </label>
                    <input name="radius_secret"
                           class="w-full px-4 py-3 rounded-lg border
                                  border-slate-300
                                  bg-white
                                  text-slate-800"
                           placeholder="Radius secret">
                </div>

            </div>

            {{-- INFO BOX --}}
            <div class="bg-yellow-50 border border-yellow-300 rounded-xl p-6 text-sm text-yellow-800">
                If you click the "Next" button, this action will generate a script that you need to copy and paste into your router's console (terminal).
                This script will establish VPN, Radius and Hotspot configuration automatically.
            </div>

            {{-- BUTTONS --}}
            <div class="flex justify-end gap-4 pt-6">

                <a href="{{ route('admin.hotspots.wizard.step1',$location) }}"
                   class="px-6 py-3 rounded-lg border text-sm">
                    Back
                </a>

                <button
                    class="px-8 py-3 rounded-lg text-white font-medium shadow transition hover:opacity-90"
                    style="background:#ff5d39">
                    Next
                </button>

            </div>

        </form>

    </div>

</div>

{{-- TOGGLE LOGIC --}}
<script>
    const setupType = document.getElementById('setupType');
    const advancedFields = document.getElementById('advancedFields');
    const simpleSetup = document.getElementById('simpleSetup');

    setupType.addEventListener('change', function () {
        if (this.value === 'advanced') {
            advancedFields.classList.remove('hidden');
            simpleSetup.classList.add('hidden');
        } else {
            advancedFields.classList.add('hidden');
            simpleSetup.classList.remove('hidden');
        }
    });
</script>

</x-admin-layout>
