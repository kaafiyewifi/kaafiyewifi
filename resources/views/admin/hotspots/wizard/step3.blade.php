<x-admin-layout>

<div class="min-h-[calc(100vh-5rem)] flex justify-center items-center bg-slate-100 px-4">

<div class="bg-white w-full max-w-4xl rounded-2xl shadow-xl p-10 space-y-8">

{{-- STEP INDICATOR --}}
<div class="flex items-center gap-6 text-sm font-medium">
    <div class="flex items-center gap-2 text-green-600">
        ✔ <span>Initial</span>
    </div>
    <div class="flex items-center gap-2 text-green-600">
        ✔ <span>Config</span>
    </div>
    <div class="flex items-center gap-2 text-indigo-600">
        3 <span>Result</span>
    </div>
</div>

{{-- GREEN INFO BOX --}}
<div class="bg-green-50 border border-green-300 rounded-xl p-6 text-sm text-slate-700 space-y-2">
    <p class="font-medium">Done! Copy this script and run it on your MikroTik router:</p>
    <ol class="list-decimal pl-5 space-y-1">
        <li>Copy the script below.</li>
        <li>Paste into MikroTik terminal.</li>
        <li>Execute it.</li>
        <li>Click "Check status".</li>
    </ol>
</div>

{{-- COPY BUTTON --}}
<div>
    <button onclick="copyScript()"
        class="px-4 py-2 rounded-lg border text-sm hover:bg-slate-100">
        Copy script
    </button>
</div>

{{-- SCRIPT BOX --}}
<textarea id="scriptBox"
    readonly
    class="w-full h-60 rounded-xl border p-4 font-mono text-xs
           bg-slate-50
           text-slate-700 resize-none">{{ $script }}</textarea>

{{-- ACTION BUTTONS --}}
<div class="flex justify-between items-center pt-4">

    <button onclick="checkRouterStatus()"
        class="px-5 py-2 rounded-lg border text-sm hover:bg-slate-100">
        Check status
    </button>

    <a href="{{ route('admin.hotspots.show', $hotspot) }}"
   class="px-6 py-2 rounded-lg text-white text-sm shadow"
   style="background:#ff5d39">
    Go hotspot
</a>


</div>

{{-- STATUS RESULT --}}
<div id="routerStatusBox"></div>

</div>
</div>

{{-- ================= JS ================= --}}
<script>
function copyScript(){
    let box = document.getElementById("scriptBox");
    box.select();
    document.execCommand("copy");
    alert("Script copied successfully!");
}

function checkRouterStatus(){
    fetch("{{ route('admin.hotspots.routerStatus',$hotspot) }}")
        .then(res => res.json())
        .then(data => {
            if(data.status === 'online'){
                document.getElementById("routerStatusBox").innerHTML = `
                <div class="bg-green-100 text-green-700 p-4 rounded-xl text-sm mt-4">
                    ✅ Router Online <br>
                    CPU: ${data.cpu}% <br>
                    Uptime: ${data.uptime}
                </div>`;
            } else {
                document.getElementById("routerStatusBox").innerHTML = `
                <div class="bg-red-100 text-red-700 p-4 rounded-xl text-sm mt-4">
                    ❌ Router Offline
                </div>`;
            }
        })
        .catch(()=>{
            document.getElementById("routerStatusBox").innerHTML = `
            <div class="bg-red-100 text-red-700 p-4 rounded-xl text-sm mt-4">
                ❌ Connection failed
            </div>`;
        });
}
</script>

</x-admin-layout>
