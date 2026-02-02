<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Router Provisioning Console</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            background: #0f172a;
            font-family: monospace;
            color: #e5e7eb;
            margin: 0;
            padding: 20px;
        }

        .console {
            background: #020617;
            border: 1px solid #1e293b;
            border-radius: 6px;
            padding: 16px;
            height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
            font-size: 13px;
        }

        .log-info { color: #22c55e; }
        .log-warning { color: #eab308; }
        .log-error { color: #ef4444; }

        .header {
            margin-bottom: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="header">
    ▶ Router Provisioning Progress
</div>

<div id="console" class="console"></div>

<script>
    const routerId = {{ $router->id }};
    const consoleEl = document.getElementById('console');

    let lastLogId = 0;

    async function fetchLogs() {
        try {
            const response = await fetch(`/routers/${routerId}/logs?after=${lastLogId}`);

            if (!response.ok) {
                throw new Error('Failed to fetch logs');
            }

            const logs = await response.json();

            logs.forEach(log => {
                const line = document.createElement('div');
                line.classList.add(`log-${log.level}`);
                line.textContent = `[${log.created_at}] ${log.message}`;

                consoleEl.appendChild(line);

                lastLogId = log.id;
            });

            if (logs.length > 0) {
                consoleEl.scrollTop = consoleEl.scrollHeight;
            }

        } catch (error) {
            const errLine = document.createElement('div');
            errLine.classList.add('log-error');
            errLine.textContent = `[ERROR] ${error.message}`;
            consoleEl.appendChild(errLine);
        }
    }

    // Initial load
    fetchLogs();

    // Poll every 2 seconds
    setInterval(fetchLogs, 2000);
</script>

</body>
</html>
