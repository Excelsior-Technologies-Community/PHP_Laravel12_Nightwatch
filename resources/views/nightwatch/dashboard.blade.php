<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Nightwatch Monitoring Dashboard</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f4f6f9;
        }

        .navbar-brand {
            font-weight: 700;
        }

        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .stat-number {
            font-size: 30px;
            font-weight: 700;
        }

        .health-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .health-icon {
            font-size: 25px;
        }

        .error-box {
            border-left: 5px solid #dc3545;
            background: #fff;
        }

        .status-dot {
            width: 11px;
            height: 11px;
            display: inline-block;
            border-radius: 50%;
            margin-right: 7px;
        }

        .online {
            background: #198754;
        }

        .offline {
            background: #dc3545;
        }

        .table-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container">

        <a
            href="{{ route('nightwatch.dashboard') }}"
            class="navbar-brand"
        >
            🌙 Laravel Nightwatch
        </a>

        <a
            href="{{ route('nightwatch.logs') }}"
            class="btn btn-outline-light"
        >
            🔎 View Logs
        </a>

    </div>
</nav>

<div class="container py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    @endif

    <div class="mb-4">

        <h2 class="fw-bold">
            📊 Monitoring Dashboard
        </h2>

        <p class="text-muted">
            Laravel application health and Nightwatch monitoring overview.
        </p>

    </div>

    <!-- Statistics -->

    <div class="row g-4 mb-4">

        <div class="col-md-3">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Total Logs
                </div>

                <div class="stat-number">
                    {{ $totalLogs }}
                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Errors
                </div>

                <div class="stat-number text-danger">
                    {{ $errorLogs }}
                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Warnings
                </div>

                <div class="stat-number text-warning">
                    {{ $warningLogs }}
                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Info Logs
                </div>

                <div class="stat-number text-primary">
                    {{ $infoLogs }}
                </div>

            </div>

        </div>

    </div>

    <!-- Health -->

    <div class="card health-card mb-4">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>

                    <h5 class="fw-bold mb-1">
                        ❤️ Application Health
                    </h5>

                    <small class="text-muted">
                        {{ $healthCount }} / {{ $healthTotal }} services healthy
                    </small>

                </div>

                <span
                    class="badge {{ $healthCount === $healthTotal ? 'bg-success' : 'bg-warning' }}"
                >
                    {{ $healthCount === $healthTotal ? 'Healthy' : 'Attention Required' }}
                </span>

            </div>

            <div class="row g-3">

                @foreach($health as $service => $status)

                    <div class="col-md-4 col-lg">

                        <div class="border rounded p-3">

                            <span
                                class="status-dot {{ $status ? 'online' : 'offline' }}"
                            ></span>

                            <strong>
                                {{ ucfirst($service) }}
                            </strong>

                            <div class="small text-muted mt-1">

                                {{ $status ? 'Operational' : 'Unavailable' }}

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        </div>

    </div>

    <!-- Performance Monitoring -->

<div class="card health-card mb-4">

    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <div>

                <h5 class="fw-bold mb-1">
                    ⏱️ Performance Monitoring
                </h5>

                <small class="text-muted">
                    Request timing and performance overview
                </small>

            </div>

            <a
                href="{{ route('nightwatch.performance') }}"
                class="btn btn-primary"
            >
                View Performance
            </a>

        </div>

        <div class="row g-3">

            <div class="col-md-3">

                <div class="border rounded p-3">

                    <div class="text-muted small">
                        Total Requests
                    </div>

                    <div class="fs-4 fw-bold">
                        {{ $performanceTotal }}
                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div class="border rounded p-3">

                    <div class="text-muted small">
                        Average Time
                    </div>

                    <div class="fs-4 fw-bold">
                        {{ number_format($performanceAverage, 2) }} ms
                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div class="border rounded p-3">

                    <div class="text-muted small">
                        Slow Requests
                    </div>

                    <div class="fs-4 fw-bold text-warning">
                        {{ $performanceSlow }}
                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div class="border rounded p-3">

                    <div class="text-muted small">
                        Critical Requests
                    </div>

                    <div class="fs-4 fw-bold text-danger">
                        {{ $performanceCritical }}
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- Recent Performance -->

<div class="card table-card mb-4">

    <div class="card-header bg-white">

        <h5 class="fw-bold mb-0">
            📈 Recent Performance Activity
        </h5>

    </div>

    <div class="card-body p-0">

        @if($recentPerformance->count())

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>Request</th>
                            <th>Method</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Category</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($recentPerformance as $performance)

                            <tr>

                                <td>
                                    {{ $performance->path }}
                                </td>

                                <td>

                                    <span class="badge bg-dark">
                                        {{ $performance->method }}
                                    </span>

                                </td>

                                <td class="fw-semibold">

                                    {{ number_format($performance->duration_ms, 2) }}
                                    ms

                                </td>

                                <td>
                                    {{ $performance->status_code }}
                                </td>

                                <td>

                                    @php

                                        $badge = match($performance->category) {
                                            'CRITICAL' => 'danger',
                                            'SLOW' => 'warning',
                                            default => 'success',
                                        };

                                    @endphp

                                    <span
                                        class="badge bg-{{ $badge }} {{ $performance->category === 'SLOW' ? 'text-dark' : '' }}"
                                    >
                                        {{ $performance->category }}
                                    </span>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="p-4 text-center text-muted">
                No performance records available yet.
            </div>

        @endif

    </div>

</div>

    <!-- Test Monitoring -->

    <div class="card table-card mb-4">

        <div class="card-body">

            <h5 class="fw-bold mb-1">
                🧪 Monitoring Test Tools
            </h5>

            <p class="text-muted">
                Generate test events and verify that Laravel logs and Nightwatch receive monitoring data.
            </p>

            <div class="d-flex flex-wrap gap-2">

                <form method="POST" action="{{ route('nightwatch.test.info') }}">
                    @csrf

                    <button class="btn btn-primary">
                        ℹ️ Generate Info
                    </button>
                </form>

                <form method="POST" action="{{ route('nightwatch.test.warning') }}">
                    @csrf

                    <button class="btn btn-warning">
                        ⚠️ Generate Warning
                    </button>
                </form>

                <form method="POST" action="{{ route('nightwatch.test.exception') }}">
                    @csrf

                    <button class="btn btn-danger">
                        🚨 Generate Exception
                    </button>
                </form>

                <form method="POST" action="{{ route('nightwatch.test.all') }}">
                    @csrf

                    <button class="btn btn-dark">
                        ⚡ Generate All Events
                    </button>
                </form>

            </div>

        </div>

    </div>

    <!-- Recent Errors -->

    <div class="card table-card">

        <div class="card-header bg-white">

            <h5 class="fw-bold mb-0">
                🚨 Recent Application Errors
            </h5>

        </div>

        <div class="card-body p-0">

            @if($recentErrors->count())

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead class="table-light">

                            <tr>

                                <th>Date & Time</th>

                                <th>Level</th>

                                <th>Message</th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($recentErrors as $error)

                                <tr>

                                    <td class="text-nowrap">
                                        {{ $error['datetime'] }}
                                    </td>

                                    <td>

                                        <span class="badge bg-danger">
                                            {{ $error['level'] }}
                                        </span>

                                    </td>

                                    <td>
                                        {{ \Illuminate\Support\Str::limit($error['message'], 180) }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="p-4 text-center text-muted">

                    No application errors detected yet.

                </div>

            @endif

        </div>

    </div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>