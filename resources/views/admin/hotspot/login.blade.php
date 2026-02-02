<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Kaafiye WiFi Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Ubuntu; background: #f4f6fb; }
        .container { max-width: 420px; margin: 60px auto; background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,.08); }
        h1 { margin: 0 0 6px; font-size: 24px; text-align: center; }
        .subtitle { text-align: center; color: #6b7280; font-size: 13px; margin-bottom: 20px; }
        .tabs { display: flex; gap: 10px; margin-bottom: 16px; }
        .tab { flex: 1; text-align: center; padding: 10px; border-radius: 10px; border: 1px solid #e5e7eb; cursor: pointer; font-weight: 600; color: #374151; }
        .tab.active { background: #111827; color: #fff; border-color: #111827; }
        label { font-size: 13px; color: #6b7280; margin-top: 14px; display: block; }
        input { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #d1d5db; margin-top: 6px; font-size: 14px; }
        button { width: 100%; margin-top: 20px; padding: 14px; border-radius: 12px; border: none; background: #111827; color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; }
        .footer { text-align: center; font-size: 12px; color: #6b7280; margin-top: 18px; }
        .error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 10px; font-size: 13px; margin-bottom: 12px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Kaafiye WiFi</h1>
    <div class="subtitle">
        {{ $router->name ?? 'Hotspot' }} • {{ $router->identity ?? '' }}
    </div>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <div class="tabs">
        <div class="tab active" id="tab-voucher" onclick="switchMode('voucher')">Voucher</div>
        <div class="tab" id="tab-account" onclick="switchMode('account')">Account</div>
    </div>

    <form method="POST" action="{{ route('portal.login', $router) }}">
        @csrf

        {{-- MikroTik variables --}}
        <input type="hidden" name="dst" value="$(link-orig-esc)">
        <input type="hidden" name="popup" value="$(popup)">
        <input type="hidden" name="mac" value="$(mac)">
        <input type="hidden" name="ip" value="$(ip)">
        <input type="hidden" name="chap_id" value="$(chap-id)">
        <input type="hidden" name="chap_challenge" value="$(chap-challenge)">
        <input type="hidden" name="link_login_only" value="$(link-login-only)">
        <input type="hidden" name="mode" id="mode" value="voucher">

        {{-- Voucher --}}
        <div id="voucher-box">
            <label>Voucher Code</label>
            <input type="text" name="voucher" placeholder="Enter voucher code" autocomplete="off">
        </div>

        {{-- Account --}}
        <div id="account-box" style="display:none">
            <label>Username</label>
            <input type="text" name="username" placeholder="Username" autocomplete="username">

            <label>Password</label>
            <input type="password" name="password" placeholder="Password" autocomplete="current-password">
        </div>

        <button type="submit">Connect to Internet</button>
    </form>

    <div class="footer">Powered by Kaafiye • Secure WiFi Access</div>
</div>

<script>
    function switchMode(mode) {
        document.getElementById('mode').value = mode;
        document.getElementById('voucher-box').style.display = (mode === 'voucher') ? 'block' : 'none';
        document.getElementById('account-box').style.display = (mode === 'account') ? 'block' : 'none';
        document.getElementById('tab-voucher').classList.toggle('active', mode === 'voucher');
        document.getElementById('tab-account').classList.toggle('active', mode === 'account');
    }
</script>

</body>
</html>
