<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nightwatch Live Stream • Real-Time Terminal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #090d16;
            color: #e2e8f0;
            font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .stream-header {
            background: #0f172a;
            border-bottom: 1px solid rgba(56, 189, 248, 0.2);
            padding: 12px 24px;
        }
        .stream-terminal {
            flex: 1;
            padding: 20px 24px;
            overflow-y: auto;
            background: #040812;
            font-size: 13px;
            line-height: 1.7;
        }
        .log-row {
            padding: 4px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            display: flex;
            align-items: flex-start;
            gap: 12px;
            word-break: break-word;
        }
        .log-time {
            color: #64748b;
            white-space: nowrap;
            font-size: 12px;
        }
        .badge-error {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .badge-warning {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .badge-info {
            background: rgba(14, 165, 233, 0.2);
            color: #38bdf8;
            border: 1px solid rgba(14, 165, 233, 0.3);
        }
        .badge-debug {
            background: rgba(148, 163, 184, 0.2);
            color: #cbd5e1;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }
        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            display: inline-block;
            box-shadow: 0 0 10px #10b981;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
            100% { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="stream-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('nightwatch.dashboard') }}" class="btn btn-sm btn-outline-secondary text-white-50">
                ← Dashboard
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="pulse-dot" id="liveIndicator"></span>
                <span class="fw-bold text-white fs-6">Nightwatch Live Log Stream</span>
                <span class="badge bg-dark border border-secondary text-info ms-2" style="font-size: 10px;">Tail -f Active</span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="text-white-50 small me-2" id="lastUpdated">Syncing...</span>
            <button class="btn btn-sm btn-outline-warning" id="pauseBtn" onclick="togglePause()">
                ⏸ Pause
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="clearTerminal()">
                🗑 Clear
            </button>
            <label class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                <input type="checkbox" id="autoScroll" checked>
                <span>Auto-Scroll</span>
            </label>
        </div>
    </div>

    <div class="stream-terminal" id="terminalBox">
        <div class="text-muted small mb-3">Connecting to Nightwatch telemetry event bus... [Streaming active laravel.log with data masking]</div>
        <div id="logsContainer"></div>
    </div>

    <script>
        let isPaused = false;
        let knownLogKeys = new Set();
        const logsContainer = document.getElementById('logsContainer');
        const terminalBox = document.getElementById('terminalBox');
        const autoScrollCheck = document.getElementById('autoScroll');
        const lastUpdated = document.getElementById('lastUpdated');
        const liveIndicator = document.getElementById('liveIndicator');
        const pauseBtn = document.getElementById('pauseBtn');

        function togglePause() {
            isPaused = !isPaused;
            if (isPaused) {
                pauseBtn.innerText = '▶ Resume';
                pauseBtn.className = 'btn btn-sm btn-outline-success';
                liveIndicator.style.background = '#eab308';
                liveIndicator.style.boxShadow = 'none';
            } else {
                pauseBtn.innerText = '⏸ Pause';
                pauseBtn.className = 'btn btn-sm btn-outline-warning';
                liveIndicator.style.background = '#10b981';
                liveIndicator.style.boxShadow = '0 0 10px #10b981';
                fetchLogs();
            }
        }

        function clearTerminal() {
            logsContainer.innerHTML = '<div class="text-muted small py-2">-- Terminal buffer cleared --</div>';
            knownLogKeys.clear();
        }

        async function fetchLogs() {
            if (isPaused) return;

            try {
                const res = await fetch('{{ route("nightwatch.api.live-logs") }}?limit=50');
                const data = await res.json();

                if (data.success && data.logs) {
                    lastUpdated.innerText = 'Last Event: ' + data.timestamp;

                    // Display in chronological order for live stream
                    const reversedLogs = [...data.logs].reverse();

                    reversedLogs.forEach(log => {
                        const key = (log.datetime || '') + '_' + (log.message || '').substring(0, 40);
                        if (!knownLogKeys.has(key)) {
                            knownLogKeys.add(key);

                            const row = document.createElement('div');
                            row.className = 'log-row';

                            const level = (log.level || 'info').toLowerCase();
                            let badgeClass = 'badge-info';
                            if (level === 'error') badgeClass = 'badge-error';
                            else if (level === 'warning') badgeClass = 'badge-warning';
                            else if (level === 'debug') badgeClass = 'badge-debug';

                            row.innerHTML = `
                                <span class="log-time">[${log.datetime || ''}]</span>
                                <span class="badge ${badgeClass} px-2 py-1 rounded" style="font-size: 10px;">${level.toUpperCase()}</span>
                                <span class="text-light text-opacity-90 flex-grow-1">${escapeHtml(log.message)}</span>
                            `;
                            logsContainer.appendChild(row);

                            if (autoScrollCheck.checked) {
                                terminalBox.scrollTop = terminalBox.scrollHeight;
                            }
                        }
                    });
                }
            } catch (err) {
                console.error('Stream error:', err);
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Poll every 2.5 seconds
        setInterval(fetchLogs, 2500);
        fetchLogs();
    </script>
</body>
</html>
