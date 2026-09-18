<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nightwatch Monitoring & APM Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background: #f1f5f9;
            color: #1e293b;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .stat-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            background: #ffffff;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .section-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            background: #ffffff;
        }

        .telemetry-card {
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 16px;
            transition: all 0.2s;
        }

        .telemetry-card:hover {
            background: #ffffff;
            border-color: #cbd5e1;
        }

        .metric-number {
            font-size: 26px;
            font-weight: 800;
        }

        .progress-thin {
            height: 8px;
            border-radius: 20px;
        }

        .badge-pulse {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            margin-right: 4px;
            animation: blink 1.4s infinite;
        }

        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.3; }
            100% { opacity: 1; }
        }

        .threat-item {
            border-left: 3px solid #ef4444;
            background: #fff1f2;
            border-radius: 8px;
            padding: 10px 14px;
        }

        .extension-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .url-cell {
            max-width: 320px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>

<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg bg-dark navbar-dark shadow-sm sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('nightwatch.dashboard') }}">
            <span>🌙</span>
            <span>Nightwatch APM</span>
            <span class="badge bg-primary text-white ms-1" style="font-size: 10px;">v12 Telemetry</span>
        </a>

        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('nightwatch.dashboard') }}" class="btn btn-light btn-sm fw-semibold">
                Dashboard
            </a>

            <a href="{{ route('nightwatch.live-stream') }}" class="btn btn-outline-info btn-sm d-flex align-items-center gap-1">
                <span class="badge-pulse"></span>
                <span>Live Stream</span>
            </a>

            <a href="{{ route('nightwatch.logs') }}" class="btn btn-outline-light btn-sm">
                Logs
            </a>

            <a href="{{ route('nightwatch.performance') }}" class="btn btn-outline-light btn-sm">
                Performance
            </a>

            <!-- Auto-Refresh Toggle -->
            <div class="input-group input-group-sm ms-2" style="width: 170px;">
                <span class="input-group-text bg-secondary border-secondary text-white small" id="refreshTimer">Off</span>
                <select id="autoRefresh" class="form-select bg-dark text-white border-secondary" title="Auto Refresh Frequency">
                    <option value="0">Off</option>
                    <option value="5">5s Refresh</option>
                    <option value="10">10s Refresh</option>
                    <option value="30">30s Refresh</option>
                </select>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 py-4">

    <!-- Header Title & Range Filter -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1">Application Observability & APM</h3>
            <p class="text-muted small mb-0">
                Continuous performance tracing, system resource telemetry, and real-time security monitoring.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <form method="GET" action="{{ route('nightwatch.dashboard') }}" class="d-flex gap-2">
                <select name="range" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="today" {{ $range === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="7" {{ $range === '7' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30" {{ $range === '30' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="all" {{ $range === 'all' ? 'selected' : '' }}>All Time</option>
                </select>
            </form>

            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    ⚡ Generate Test Events
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li>
                        <form method="POST" action="{{ route('nightwatch.test.info') }}">
                            @csrf
                            <button type="submit" class="dropdown-item small">Info Log with Masking</button>
                        </form>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('nightwatch.test.warning') }}">
                            @csrf
                            <button type="submit" class="dropdown-item small">Warning Log</button>
                        </form>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('nightwatch.test.exception') }}">
                            @csrf
                            <button type="submit" class="dropdown-item small text-danger">Test Exception</button>
                        </form>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a href="{{ route('nightwatch.performance.test-slow') }}" class="dropdown-item small text-warning">
                            Slow Request (2s)
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('nightwatch.performance.test-critical') }}" class="dropdown-item small text-danger">
                            Critical Request (4s)
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show small" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- SECTION 1: Server & System Resource Telemetry -->
    <div class="row g-3 mb-4">
        <!-- CPU Usage Gauge -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">CPU UTILIZATION</span>
                    <span class="badge {{ $telemetry['cpu']['percentage'] > 80 ? 'bg-danger' : ($telemetry['cpu']['percentage'] > 60 ? 'bg-warning text-dark' : 'bg-success') }} rounded-pill px-2 py-1" style="font-size: 10px;">
                        {{ $telemetry['cpu']['status'] }}
                    </span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <span class="metric-number text-dark" id="cpuVal">{{ $telemetry['cpu']['percentage'] }}%</span>
                    <span class="text-muted small">({{ $telemetry['cpu']['cores'] }} Cores)</span>
                </div>
                <div class="progress progress-thin bg-light">
                    <div id="cpuBar" class="progress-bar {{ $telemetry['cpu']['percentage'] > 80 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ $telemetry['cpu']['percentage'] }}%"></div>
                </div>
                <div class="text-muted small mt-2" style="font-size: 11px;">Active OS Processor load</div>
            </div>
        </div>

        <!-- RAM Usage -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">RAM / MEMORY USAGE</span>
                    <span class="badge {{ $telemetry['ram']['percentage'] > 85 ? 'bg-danger' : 'bg-info' }} rounded-pill px-2 py-1" style="font-size: 10px;">
                        {{ $telemetry['ram']['percentage'] }}%
                    </span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <span class="metric-number text-dark" id="ramVal">{{ $telemetry['ram']['used_gb'] }} GB</span>
                    <span class="text-muted small">/ {{ $telemetry['ram']['total_gb'] }} GB</span>
                </div>
                <div class="progress progress-thin bg-light">
                    <div id="ramBar" class="progress-bar bg-info" style="width: {{ $telemetry['ram']['percentage'] }}%"></div>
                </div>
                <div class="text-muted small mt-2" style="font-size: 11px;">Free: {{ $telemetry['ram']['free_gb'] }} GB physical RAM</div>
            </div>
        </div>

        <!-- Disk Storage & Inodes -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">STORAGE DISK CAPACITY</span>
                    <span class="badge {{ $telemetry['disk']['percentage'] > 85 ? 'bg-danger' : 'bg-primary' }} rounded-pill px-2 py-1" style="font-size: 10px;">
                        {{ $telemetry['disk']['percentage'] }}% Used
                    </span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <span class="metric-number text-dark" id="diskVal">{{ $telemetry['disk']['used_gb'] }} GB</span>
                    <span class="text-muted small">/ {{ $telemetry['disk']['total_gb'] }} GB</span>
                </div>
                <div class="progress progress-thin bg-light">
                    <div id="diskBar" class="progress-bar bg-primary" style="width: {{ $telemetry['disk']['percentage'] }}%"></div>
                </div>
                <div class="text-muted small mt-2" style="font-size: 11px;">Free Disk Space: {{ $telemetry['disk']['free_gb'] }} GB</div>
            </div>
        </div>

        <!-- PHP OPcache & Extensions Health -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">PHP & OPCACHE HEALTH</span>
                    <span class="badge {{ $telemetry['opcache']['enabled'] ? 'bg-success' : 'bg-secondary' }} rounded-pill px-2 py-1" style="font-size: 10px;">
                        {{ $telemetry['opcache']['enabled'] ? 'OPcache Active' : 'OPcache Off' }}
                    </span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <span class="metric-number text-dark">{{ $telemetry['opcache']['hit_rate'] }}%</span>
                    <span class="text-muted small">Cache Hit Rate</span>
                </div>
                <div class="d-flex flex-wrap gap-1 mt-2">
                    <span class="badge bg-light text-dark border" style="font-size: 10px;">PHP {{ $telemetry['environment']['php_version'] }}</span>
                    <span class="badge bg-light text-dark border" style="font-size: 10px;">Limit {{ $telemetry['environment']['memory_limit'] }}</span>
                    <span class="badge bg-light text-success border border-success" style="font-size: 10px;">{{ $telemetry['extensions']['loaded_count'] }}/{{ $telemetry['extensions']['total_count'] }} Exts OK</span>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: Core KPI Metrics (Logs & Latency) -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold">TOTAL REQUESTS</div>
                    <div class="metric-number text-dark">{{ number_format($performanceTotal) }}</div>
                    <div class="small text-muted">{{ $fastRequests }} Fast (<500ms)</div>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">⚡</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold">AVERAGE LATENCY</div>
                    <div class="metric-number text-dark">{{ number_format($performanceAverage, 1) }} ms</div>
                    <div class="small text-muted">Max: {{ number_format($performanceMax, 1) }} ms</div>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">⏱️</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold">SLOW & CRITICAL</div>
                    <div class="metric-number text-warning">{{ $slowRequests + $criticalRequests }}</div>
                    <div class="small text-muted">{{ $criticalRequests }} Critical (>3000ms)</div>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">⚠️</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold">APPLICATION ERRORS</div>
                    <div class="metric-number text-danger">{{ $errorLogs }}</div>
                    <div class="small text-muted">{{ $totalLogs }} Total Log Entries</div>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">🚨</div>
            </div>
        </div>
    </div>

    <!-- SECTION 3: Chart.js Interactive Visual Analytics -->
    <div class="row g-4 mb-4">
        <!-- 24-Hour Request Volume & Latency Trend -->
        <div class="col-12 col-xl-8">
            <div class="section-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0">Hourly Request Volume & Latency Trend (24h)</h5>
                        <div class="text-muted small">Dual-axis correlation of traffic throughput vs response speed</div>
                    </div>
                    <span class="badge bg-light text-dark border">Chart.js Live</span>
                </div>
                <div style="height: 280px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- HTTP Status Code Breakdown -->
        <div class="col-12 col-xl-4">
            <div class="section-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0">HTTP Status Codes</h5>
                        <div class="text-muted small">Distribution of response classes</div>
                    </div>
                    <span class="badge bg-light text-dark border">{{ $statusTotal }} Total</span>
                </div>
                <div style="height: 240px; position: relative;" class="d-flex align-items-center justify-content-center">
                    <canvas id="statusDonutChart"></canvas>
                </div>
                <div class="d-flex justify-content-around text-center mt-3 pt-2 border-top small">
                    <div><strong class="text-success">{{ $statusDistribution['2xx'] }}</strong><br><span class="text-muted">2xx OK</span></div>
                    <div><strong class="text-info">{{ $statusDistribution['3xx'] }}</strong><br><span class="text-muted">3xx Redir</span></div>
                    <div><strong class="text-warning">{{ $statusDistribution['4xx'] }}</strong><br><span class="text-muted">4xx Client</span></div>
                    <div><strong class="text-danger">{{ $statusDistribution['5xx'] }}</strong><br><span class="text-muted">5xx Server</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 4: Top 10 Slowest Routes & Security Threat Detector -->
    <div class="row g-4 mb-4">
        <!-- Top 10 Slowest Routes Bar Chart -->
        <div class="col-12 col-xl-6">
            <div class="section-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0">Top Slowest Endpoints</h5>
                        <div class="text-muted small">Ranked by average latency duration (ms)</div>
                    </div>
                    <a href="{{ route('nightwatch.performance') }}" class="btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div style="height: 280px;">
                    <canvas id="slowestRoutesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Security & 404/403 Brute-Force Scanner Detector -->
        <div class="col-12 col-xl-6">
            <div class="section-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <span>🛡️ 404/403 Threat & Exploit Scanner</span>
                            @if($totalSecurityEvents > 0)
                                <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 10px;">{{ $totalSecurityEvents }} Events</span>
                            @else
                                <span class="badge bg-success rounded-pill px-2 py-1" style="font-size: 10px;">Secure</span>
                            @endif
                        </h5>
                        <div class="text-muted small">Identifies suspicious client IPs and malicious probe attempts</div>
                    </div>
                </div>

                @if($threatIps->isEmpty() && $suspiciousPaths->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <div style="font-size: 32px;">✅</div>
                        <div class="fw-semibold mt-2">No suspicious 4xx/5xx security anomalies detected</div>
                        <div class="small">All client traffic aligns with standard application routes.</div>
                    </div>
                @else
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6 class="fw-bold small text-muted text-uppercase mb-2">Top Suspicious IPs</h6>
                            <div class="d-flex flex-column gap-2">
                                @foreach($threatIps as $threat)
                                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border">
                                        <div>
                                            <span class="fw-bold font-monospace small">{{ $threat->ip_address }}</span>
                                            <span class="badge bg-danger bg-opacity-10 text-danger ms-1" style="font-size: 9px;">HTTP {{ $threat->last_status }}</span>
                                        </div>
                                        <span class="badge bg-dark text-white rounded-pill">{{ $threat->total_anomalies }} hits</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="fw-bold small text-muted text-uppercase mb-2">Top Scanned Probe Paths</h6>
                            <div class="d-flex flex-column gap-2">
                                @foreach($suspiciousPaths as $pathItem)
                                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border">
                                        <span class="text-truncate font-monospace small" style="max-width: 140px;" title="{{ $pathItem->path }}">
                                            {{ $pathItem->path }}
                                        </span>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge bg-warning text-dark" style="font-size: 10px;">{{ $pathItem->status_code }}</span>
                                            <span class="badge bg-secondary rounded-pill">{{ $pathItem->attempts }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-3 pt-2 border-top text-muted small d-flex justify-content-between align-items-center">
                    <span>Sensitive Data Masking is active on all logs</span>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success">Masking: ON</span>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 5: Recent Errors & Infrastructure Health -->
    <div class="row g-4">
        <!-- Recent Errors with Sensitive Data Masking -->
        <div class="col-12 col-xl-8">
            <div class="section-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0">Recent Exception Logs (Masked)</h5>
                        <div class="text-muted small">Passwords, Bearer tokens, and secrets are auto-redacted</div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('nightwatch.live-stream') }}" class="btn btn-sm btn-outline-info">⚡ Live Stream</a>
                        <a href="{{ route('nightwatch.logs') }}" class="btn btn-sm btn-outline-secondary">View All Logs</a>
                    </div>
                </div>

                @if($recentErrors->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <div style="font-size: 32px;">🎉</div>
                        <div class="fw-semibold mt-2">Zero Application Errors Logged</div>
                        <div class="small">The application is running smoothly with no active exceptions.</div>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Level</th>
                                    <th>Masked Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentErrors as $err)
                                    <tr>
                                        <td class="text-muted font-monospace text-nowrap">{{ $err['datetime'] }}</td>
                                        <td><span class="badge bg-danger">ERROR</span></td>
                                        <td class="text-break">{{ $err['message'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Infrastructure & PHP Extension Status -->
        <div class="col-12 col-xl-4">
            <div class="section-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">PHP Extension Matrix</h5>
                    <span class="badge bg-light text-dark border">Laravel 12 Specs</span>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    @foreach($telemetry['extensions']['list'] as $ext)
                        <span class="extension-chip {{ $ext['loaded'] ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' }}">
                            <span>{{ $ext['loaded'] ? '✓' : '✗' }}</span>
                            <span>{{ $ext['name'] }}</span>
                        </span>
                    @endforeach
                </div>

                <h6 class="fw-bold small text-muted text-uppercase mb-2">Core Service Checks</h6>
                <div class="d-flex flex-column gap-2 small">
                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light">
                        <span>Database PDO Connection</span>
                        <span class="badge {{ $health['database'] ? 'bg-success' : 'bg-danger' }}">{{ $health['database'] ? 'CONNECTED' : 'DISCONNECTED' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light">
                        <span>Cache Read/Write Driver</span>
                        <span class="badge {{ $health['cache'] ? 'bg-success' : 'bg-danger' }}">{{ $health['cache'] ? 'OPERATIONAL' : 'FAILED' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light">
                        <span>Local Storage Disk</span>
                        <span class="badge {{ $health['storage'] ? 'bg-success' : 'bg-danger' }}">{{ $health['storage'] ? 'WRITABLE' : 'UNWRITABLE' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js Setup & Auto-Refresh Controller -->
<script>
    // 1. Dual-Axis 24-Hour Hourly Trend Chart
    const hourlyData = @json($hourlyData);
    const ctxTrend = document.getElementById('trendChart').getContext('2d');
    const trendChart = new Chart(ctxTrend, {
        type: 'bar',
        data: {
            labels: hourlyData.labels,
            datasets: [
                {
                    label: 'Request Volume',
                    data: hourlyData.volume,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    yAxisID: 'yVolume',
                    borderRadius: 4,
                },
                {
                    label: 'Avg Latency (ms)',
                    data: hourlyData.latency,
                    type: 'line',
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'yLatency',
                    pointRadius: 3,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                yVolume: {
                    type: 'linear',
                    position: 'left',
                    title: { display: true, text: 'Requests', font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    beginAtZero: true
                },
                yLatency: {
                    type: 'linear',
                    position: 'right',
                    title: { display: true, text: 'Latency (ms)', font: { size: 11 } },
                    grid: { drawOnChartArea: false },
                    beginAtZero: true
                }
            },
            plugins: {
                legend: { position: 'top', labels: { font: { size: 12 } } }
            }
        }
    });

    // 2. HTTP Status Code Doughnut Chart
    const statusDist = @json($statusDistribution);
    const ctxStatus = document.getElementById('statusDonutChart').getContext('2d');
    const statusChart = new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['2xx OK', '3xx Redir', '4xx Client', '5xx Server'],
            datasets: [{
                data: [statusDist['2xx'], statusDist['3xx'], statusDist['4xx'], statusDist['5xx']],
                backgroundColor: ['#10b981', '#0ea5e9', '#f59e0b', '#ef4444'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false }
            }
        }
    });

    // 3. Top 10 Slowest Routes Bar Chart
    const slowestData = @json($top10SlowestRoutes);
    const ctxSlow = document.getElementById('slowestRoutesChart').getContext('2d');
    const slowChart = new Chart(ctxSlow, {
        type: 'bar',
        data: {
            labels: slowestData.labels.length ? slowestData.labels : ['No requests yet'],
            datasets: [{
                label: 'Avg Duration (ms)',
                data: slowestData.avg_duration.length ? slowestData.avg_duration : [0],
                backgroundColor: '#ef4444',
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { beginAtZero: true, title: { display: true, text: 'Duration (ms)', font: { size: 11 } } },
                y: { ticks: { font: { size: 11 } } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

    // 4. Auto-Refresh Controller
    let refreshTimerInterval = null;
    let secondsLeft = 0;
    const selectElem = document.getElementById('autoRefresh');
    const timerBadge = document.getElementById('refreshTimer');

    function applyAutoRefresh(seconds) {
        if (refreshTimerInterval) clearInterval(refreshTimerInterval);
        if (seconds <= 0) {
            timerBadge.innerText = 'Off';
            return;
        }

        secondsLeft = seconds;
        timerBadge.innerText = secondsLeft + 's';

        refreshTimerInterval = setInterval(() => {
            secondsLeft--;
            timerBadge.innerText = secondsLeft + 's';

            if (secondsLeft <= 0) {
                // Poll live telemetry API or reload
                window.location.reload();
            }
        }, 1000);
    }

    selectElem.addEventListener('change', (e) => {
        const val = parseInt(e.target.value);
        localStorage.setItem('nightwatch_refresh_interval', val);
        applyAutoRefresh(val);
    });

    // Restore saved refresh preference
    const savedRefresh = parseInt(localStorage.getItem('nightwatch_refresh_interval') || 0);
    selectElem.value = savedRefresh;
    applyAutoRefresh(savedRefresh);
</script>
</body>
</html>