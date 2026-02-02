@extends('layouts.admin')

@section('content')
<div class="min-h-[calc(100vh-120px)] bg-gray-50 dark:bg-gray-950 py-10">
    <div class="max-w-4xl mx-auto px-4">

        {{-- Header --}}
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Add MikroTik Device</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400 max-w-xl mx-auto">
                Connect your MikroTik router to enable automated provisioning and management.
            </p>
        </div>

        {{-- Card --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm">

            {{-- Steps --}}
            <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center justify-center font-semibold">1</span>
                    <span class="text-sm text-gray-500">Connection</span>
                </div>

                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-orange-500 text-white flex items-center justify-center font-semibold">2</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Provision</span>
                </div>

                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center justify-center font-semibold">3</span>
                    <span class="text-sm text-gray-500">Service Setup</span>
                </div>
            </div>

            <div class="px-8 py-8 space-y-6">

                {{-- Provision Command --}}
                <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-900 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-800">
                        <span class="text-xs uppercase tracking-wide text-gray-400">Provisioning Command</span>

                        <button id="copyBtn" type="button"
                            class="text-xs px-3 py-1.5 rounded-md bg-white/10 hover:bg-white/20 text-white">
                            <span id="copyText">Copy</span>
                        </button>
                    </div>

                    <pre id="cmdBox" class="text-sm text-emerald-200 font-mono p-5 whitespace-pre-wrap break-words">
{{ $command ?? '' }}
                    </pre>

                    @if(empty($command))
                        <div class="px-5 pb-5">
                            <div class="text-xs text-amber-200/90 bg-amber-500/10 border border-amber-500/20 rounded-lg p-3">
                                Command is empty. Ensure controller sends <code class="font-mono">$command</code>.
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Hint --}}
                <div class="rounded-lg bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800 p-4 text-sm text-sky-800 dark:text-sky-200">
                    If you see <b>“device mode not allowed”</b>, run:
                    <code class="px-2 py-1 bg-black/20 rounded">/system/device-mode update mode=advanced</code>
                    then power-cycle the router and retry.
                </div>

                {{-- Status --}}
                <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-950">
                    <div class="px-5 py-3 border-b border-gray-800 text-xs text-gray-400 font-mono">
                        Device Connection Status
                    </div>

                    <div class="px-5 py-5 space-y-2 font-mono text-sm">
                        <div id="line1" class="text-gray-200">• Waiting for command execution…</div>
                        <div id="line2" class="text-gray-400">Run the provisioning command on MikroTik.</div>
                        <div id="line3" class="text-gray-500">This page will auto-detect when device connects.</div>

                        <div class="pt-4 flex items-center justify-between">
                            <span id="statusMeta" class="text-xs text-gray-500">
                                status: {{ $router->status ?? 'pending' }}
                            </span>

                            <a id="continueBtn"
                               href="{{ $continueUrl ?? route('admin.routers.service-setup', $router) }}"
                               class="hidden px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-sm">
                                Continue →
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-between pt-2">
                    <a href="{{ route('admin.routers.wizard.stage1') }}"
                       class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        ← Previous Step
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const statusUrl = @json($statusUrl ?? route('admin.routers.wizard.status', $router));
    const continueBtn = document.getElementById('continueBtn');
    const line1 = document.getElementById('line1');
    const line2 = document.getElementById('line2');
    const line3 = document.getElementById('line3');
    const statusMeta = document.getElementById('statusMeta');

    // Copy
    const copyBtn = document.getElementById('copyBtn');
    const cmdBox  = document.getElementById('cmdBox');
    const copyText = document.getElementById('copyText');
    if (copyBtn && cmdBox) {
        copyBtn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(cmdBox.innerText.trim());
                if (copyText) copyText.textContent = 'Copied!';
                setTimeout(() => { if (copyText) copyText.textContent = 'Copy'; }, 1200);
            } catch (e) {
                if (copyText) copyText.textContent = 'Copy failed';
                setTimeout(() => { if (copyText) copyText.textContent = 'Copy'; }, 1200);
            }
        });
    }

    let timer = null;

    function setPending(msg1, msg2, msg3, meta) {
        if (continueBtn) continueBtn.classList.add('hidden');
        if (line1) { line1.className = "text-gray-200"; line1.textContent = "• " + (msg1 || "Waiting for command execution…"); }
        if (line2) { line2.className = "text-gray-400"; line2.textContent = msg2 || "Run the provisioning command on MikroTik."; }
        if (line3) { line3.className = "text-gray-500"; line3.textContent = msg3 || "This page will auto-detect when device connects."; }
        if (statusMeta) statusMeta.textContent = meta || "status: pending";
    }

    function setConnected(data) {
        if (line1) { line1.className = "text-emerald-300"; line1.textContent = "✓ " + (data.message || "Device Connected Successfully!"); }
        if (line2) { line2.className = "text-emerald-200/90"; line2.textContent = data.hint || "Device is online and ready."; }
        if (line3) { line3.className = "text-gray-400"; line3.textContent = "You can continue to Service Setup."; }

        const status = data.status ?? 'connected';
        const lastSeen = data.last_seen_at ? (' | last_seen: ' + data.last_seen_at) : '';
        if (statusMeta) statusMeta.textContent = 'status: ' + status + lastSeen;

        if (continueBtn) continueBtn.classList.remove('hidden');

        clearInterval(timer);
        timer = null;
    }

    async function checkStatus() {
        try {
            const res = await fetch(statusUrl, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });

            // If redirected to login, response will likely be HTML
            const ct = (res.headers.get('content-type') || '').toLowerCase();

            if (!res.ok) {
                setPending("Waiting…", "Server returned HTTP " + res.status, "Check auth/session or route.", "status: http " + res.status);
                return;
            }

            if (!ct.includes('application/json')) {
                setPending("Waiting…", "Status endpoint did not return JSON.", "If you are logged out, refresh page and login.", "status: non-json response");
                return;
            }

            const data = await res.json();

            if (data && data.connected) {
                setConnected(data);
                return;
            }

            const status = (data && data.status) ? data.status : 'pending';
            setPending(
                data.message || "Waiting for command execution…",
                data.hint || "Run the provisioning command on MikroTik.",
                "This page will auto-detect when device connects.",
                "status: " + status + (data.last_seen_at ? (" | last_seen: " + data.last_seen_at) : "")
            );
        } catch (e) {
            setPending("Waiting…", "Network error while polling status.", "Try refresh.", "status: error");
        }
    }

    checkStatus();
    timer = setInterval(checkStatus, 3000);
})();
</script>
@endsection
