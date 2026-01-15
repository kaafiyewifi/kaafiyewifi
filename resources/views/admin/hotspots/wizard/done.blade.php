<x-admin-layout>

<div class="max-w-5xl mx-auto px-4 py-10">

    {{-- STEPS --}}
    <div class="flex items-center justify-center mb-6 text-sm font-medium">
        <span class="flex items-center text-green-600">✔ Initial</span>
        <span class="mx-3 text-gray-400">—</span>
        <span class="flex items-center text-green-600">✔ Config</span>
        <span class="mx-3 text-gray-400">—</span>
        <span class="flex items-center text-blue-600 font-semibold">3 Result</span>
    </div>

    {{-- CARD --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6">

        {{-- INFO BOX --}}
        <div class="border border-green-300 bg-green-50 dark:bg-green-900/20 dark:border-green-700 rounded-xl p-5 text-sm text-slate-700 dark:text-slate-200 mb-6">
            <p class="font-semibold mb-2">Done! Copy this script and run it on your MikroTik router:</p>
            <ol class="list-decimal list-inside space-y-1">
                <li>Copy the script below.</li>
                <li>Paste into MikroTik terminal.</li>
                <li>Execute it.</li>
                <li>Click "Check status".</li>
            </ol>
        </div>

        {{-- COPY BUTTON --}}
        <div class="flex justify-between items-center mb-2">
            <button onclick="copyScript()"
                class="px-4 py-2 rounded-lg border text-sm hover:bg-slate-100 dark:hover:bg-slate-700">
                Copy script
            </button>
        </div>

        {{-- SCRIPT BOX --}}
        <textarea id="scriptBox"
            class="w-full h-72 rounded-xl border dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm font-mono p-4 text-slate-800 dark:text-slate-200 resize-none"
            readonly>{{ $script }}</textarea>

        {{-- ACTION BUTTONS --}}
        <div class="flex flex-col sm:flex-row justify-between gap-4 mt-6">

            <button onclick="checkVpnStatus()"
                class="px-5 py-2 rounded-lg border text-sm hover:bg-slate-100 dark:hover:bg-slate-700">
                Check VPN status
            </button>

            <div class="flex gap-3">
                <button onclick="autoPushScript()"
                    class="px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold">
                    Auto push to router
                </button>

                <a href="{{ route('admin.hotspots.index') }}"
                    class="px-5 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-semibold">
                    Go to hotspots
                </a>
            </div>
        </div>

        {{-- STATUS MESSAGE --}}
        <p id="pushStatus" class="mt-4 text-sm font-medium"></p>

    </div>

</div>

{{-- SCRIPT --}}
<script>
function copyScript(){
    const box = document.getElementById('scriptBox');
    box.select();
    document.execCommand("copy");
    alert("Script copied!");
}

/* ============================
   AUTO PUSH
============================ */
function autoPushScript(){
    document.getElementById('pushStatus').innerHTML =
        "<span class='text-blue-600'>Pushing script to router...</span>";

    fetch("{{ route('admin.hotspots.autoPush',[$location,$hotspot]) }}",{
        method:'POST',
        headers:{
            'X-CSRF-TOKEN':'{{ csrf_token() }}',
            'Accept':'application/json'
        }
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.status==='success'){
            document.getElementById('pushStatus').innerHTML =
              "<span class='text-green-600'>"+data.message+"</span>";
        }else{
            document.getElementById('pushStatus').innerHTML =
              "<span class='text-red-600'>"+data.message+"</span>";
        }
    })
    .catch(()=>{
        document.getElementById('pushStatus').innerHTML =
          "<span class='text-red-600'>Connection failed</span>";
    })
}

/* ============================
   VPN STATUS CHECK
============================ */
function checkVpnStatus(){
    document.getElementById('pushStatus').innerHTML =
      "<span class='text-blue-600'>Checking VPN status...</span>";

    fetch("{{ route('admin.hotspots.vpnStatus',[$location,$hotspot]) }}")
    .then(res=>res.json())
    .then(data=>{
        if(data.status === 'connected'){
            document.getElementById('pushStatus').innerHTML =
              "<span class='text-green-600'>VPN Connected</span>";
        }
        else if(data.status === 'not_configured'){
            document.getElementById('pushStatus').innerHTML =
              "<span class='text-yellow-500'>VPN Not Configured</span>";
        }
        else{
            document.getElementById('pushStatus').innerHTML =
              "<span class='text-red-600'>VPN Offline</span>";
        }
    })
    .catch(()=>{
        document.getElementById('pushStatus').innerHTML =
          "<span class='text-red-600'>Router not reachable</span>";
    });
}
</script>

</x-admin-layout>
