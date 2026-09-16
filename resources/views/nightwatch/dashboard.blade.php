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
            background: #f5f7fb;
        }

        .navbar-brand {
            font-weight: 700;
        }

        .stat-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            transition: 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .section-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .health-item {
            border-radius: 12px;
            padding: 15px;
            background: #f8f9fa;
        }

        .status-badge {
            min-width: 55px;
            display: inline-block;
            text-align: center;
        }

        .metric-number {
            font-size: 28px;
            font-weight: 700;
        }

        .table th {
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .url-cell {
            max-width: 350px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .refresh-select {
            width: 125px;
        }

        .progress {
            height: 10px;
            border-radius: 20px;
        }

        .page-title {
            font-weight: 700;
        }

        .small-muted {
            color: #6c757d;
            font-size: 13px;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg bg-dark navbar-dark shadow-sm">
    <div class="container-fluid px-4">

        <a
            class="navbar-brand"
            href="{{ route('nightwatch.dashboard') }}"
        >
            🌙 Nightwatch
        </a>

        <div class="d-flex align-items-center gap-2">

            <a
                href="{{ route('nightwatch.dashboard') }}"
                class="btn btn-light btn-sm"
            >
                Dashboard
            </a>

            <a
                href="{{ route('nightwatch.logs') }}"
                class="btn btn-outline-light btn-sm"
            >
                Logs
            </a>

            <a
                href="{{ route('nightwatch.performance') }}"
                class="btn btn-outline-light btn-sm"
            >
                Performance
            </a>

            <select
                id="autoRefresh"
                class="form-select form-select-sm refresh-select"
                title="Dashboard auto refresh"
            >
                <option value="0">Refresh: Off</option>
                <option value="30">Refresh: 30s</option>
                <option value="60">Refresh: 60s</option>
                <option value="120">Refresh: 2m</option>
            </select>

        </div>

    </div>
</nav>


<div class="container-fluid px-4 py-4">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">

        <div>
            <h2 class="page-title mb-1">
                Monitoring Dashboard
            </h2>

            <p class="text-muted mb-0">
                Monitor Laravel application logs, health and performance.
            </p>
        </div>

        {{-- Date Range --}}
        <form
            method="GET"
            action="{{ route('nightwatch.dashboard') }}"
            class="d-flex align-items-center gap-2"
        >

            <label
                for="range"
                class="fw-semibold text-nowrap"
            >
                Date Range:
            </label>

            <select
                name="range"
                id="range"
                class="form-select"
                onchange="this.form.submit()"
            >
                <option
                    value="today"
                    {{ ($range ?? 'all') === 'today' ? 'selected' : '' }}
                >
                    Today
                </option>

                <option
                    value="7"
                    {{ ($range ?? 'all') === '7' ? 'selected' : '' }}
                >
                    Last 7 Days
                </option>

                <option
                    value="30"
                    {{ ($range ?? 'all') === '30' ? 'selected' : '' }}
                >
                    Last 30 Days
                </option>

                <option
                    value="all"
                    {{ ($range ?? 'all') === 'all' ? 'selected' : '' }}
                >
                    All
                </option>
            </select>

        </form>

    </div>


    {{-- Selected Range --}}
    <div class="alert alert-light border mb-4">

        <strong>Showing data for:</strong>

        @if (($range ?? 'all') === 'today')
            Today
        @elseif (($range ?? 'all') === '7')
            Last 7 Days
        @elseif (($range ?? 'all') === '30')
            Last 30 Days
        @else
            All Available Data
        @endif

    </div>


    {{-- Log Statistics --}}
    <div class="row g-4 mb-4">

        {{-- Total Logs --}}
        <div class="col-xl-3 col-md-6">

            <div class="card stat-card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <div class="text-muted mb-2">
                                Total Logs
                            </div>

                            <div class="metric-number">
                                {{ number_format($totalLogs) }}
                            </div>
                        </div>

                        <div class="stat-icon bg-primary-subtle">
                            📋
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Errors --}}
        <div class="col-xl-3 col-md-6">

            <div class="card stat-card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <div class="text-muted mb-2">
                                Errors
                            </div>

                            <div class="metric-number text-danger">
                                {{ number_format($errorLogs) }}
                            </div>
                        </div>

                        <div class="stat-icon bg-danger-subtle">
                            ❌
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Warnings --}}
        <div class="col-xl-3 col-md-6">

            <div class="card stat-card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <div class="text-muted mb-2">
                                Warnings
                            </div>

                            <div class="metric-number text-warning">
                                {{ number_format($warningLogs) }}
                            </div>
                        </div>

                        <div class="stat-icon bg-warning-subtle">
                            ⚠️
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Info --}}
        <div class="col-xl-3 col-md-6">

            <div class="card stat-card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <div class="text-muted mb-2">
                                Info
                            </div>

                            <div class="metric-number text-info">
                                {{ number_format($infoLogs) }}
                            </div>
                        </div>

                        <div class="stat-icon bg-info-subtle">
                            ℹ️
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Health Section --}}
    <div class="card section-card mb-4">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h5 class="mb-1">
                        🩺 Application Health
                    </h5>

                    <div class="small-muted">
                        Current application infrastructure status
                    </div>
                </div>

                <span
                    class="badge
                    {{ $healthCount === $healthTotal ? 'text-bg-success' : 'text-bg-warning' }}
                    fs-6"
                >
                    {{ $healthCount }}/{{ $healthTotal }} Healthy
                </span>

            </div>


            <div class="row g-3">

                {{-- Application --}}
                <div class="col-xl-3 col-md-6">

                    <div class="health-item">

                        <div class="d-flex justify-content-between">

                            <strong>
                                Application
                            </strong>

                            @if ($health['application'])
                                <span class="text-success">
                                    ✓
                                </span>
                            @else
                                <span class="text-danger">
                                    ✕
                                </span>
                            @endif

                        </div>

                        <small class="text-muted">
                            Laravel application
                        </small>

                    </div>

                </div>


                {{-- Database --}}
                <div class="col-xl-3 col-md-6">

                    <div class="health-item">

                        <div class="d-flex justify-content-between">

                            <strong>
                                Database
                            </strong>

                            @if ($health['database'])
                                <span class="text-success">
                                    ✓
                                </span>
                            @else
                                <span class="text-danger">
                                    ✕
                                </span>
                            @endif

                        </div>

                        <small class="text-muted">
                            Database connection
                        </small>

                    </div>

                </div>


                {{-- Cache --}}
                <div class="col-xl-3 col-md-6">

                    <div class="health-item">

                        <div class="d-flex justify-content-between">

                            <strong>
                                Cache
                            </strong>

                            @if ($health['cache'])
                                <span class="text-success">
                                    ✓
                                </span>
                            @else
                                <span class="text-danger">
                                    ✕
                                </span>
                            @endif

                        </div>

                        <small class="text-muted">
                            Cache connection
                        </small>

                    </div>

                </div>


                {{-- Storage --}}
                <div class="col-xl-3 col-md-6">

                    <div class="health-item">

                        <div class="d-flex justify-content-between">

                            <strong>
                                Storage
                            </strong>

                            @if ($health['storage'])
                                <span class="text-success">
                                    ✓
                                </span>
                            @else
                                <span class="text-danger">
                                    ✕
                                </span>
                            @endif

                        </div>

                        <small class="text-muted">
                            Storage writable
                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Performance Statistics --}}
    <div class="card section-card mb-4">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h5 class="mb-1">
                        ⚡ Performance Overview
                    </h5>

                    <div class="small-muted">
                        Request performance for the selected date range
                    </div>
                </div>

                <a
                    href="{{ route('nightwatch.performance', ['range' => $range ?? 'all']) }}"
                    class="btn btn-primary btn-sm"
                >
                    View Performance →
                </a>

            </div>


            <div class="row g-4">

                {{-- Total Requests --}}
                <div class="col-xl-3 col-md-6">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="text-muted">
                            Total Requests
                        </div>

                        <div class="metric-number">
                            {{ number_format($performanceTotal) }}
                        </div>

                    </div>

                </div>


                {{-- Average --}}
                <div class="col-xl-3 col-md-6">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="text-muted">
                            Average Duration
                        </div>

                        <div class="metric-number">
                            {{ number_format($performanceAverage, 2) }} ms
                        </div>

                    </div>

                </div>


                {{-- Slow --}}
                <div class="col-xl-3 col-md-6">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="text-muted">
                            Slow Requests
                        </div>

                        <div class="metric-number text-warning">
                            {{ number_format($slowRequests) }}
                        </div>

                    </div>

                </div>


                {{-- Critical --}}
                <div class="col-xl-3 col-md-6">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="text-muted">
                            Critical Requests
                        </div>

                        <div class="metric-number text-danger">
                            {{ number_format($criticalRequests) }}
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Top Requested URLs + Status Distribution --}}
    <div class="row g-4 mb-4">

        {{-- Top Requested URLs --}}
        <div class="col-xl-7">

            <div class="card section-card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>
                            <h5 class="mb-1">
                                🔗 Top Requested URLs
                            </h5>

                            <div class="small-muted">
                                Most frequently requested application paths
                            </div>
                        </div>

                    </div>


                    @if ($topUrls->count())

                        <div class="table-responsive">

                            <table class="table table-hover align-middle mb-0">

                                <thead class="table-light">

                                    <tr>
                                        <th>#</th>
                                        <th>URL / Path</th>
                                        <th>Requests</th>
                                        <th>Avg Duration</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach ($topUrls as $index => $url)

                                        <tr>

                                            <td>
                                                <strong>
                                                    {{ $index + 1 }}
                                                </strong>
                                            </td>

                                            <td>

                                                <div
                                                    class="url-cell"
                                                    title="{{ $url->path }}"
                                                >
                                                    <code>
                                                        {{ $url->path }}
                                                    </code>
                                                </div>

                                            </td>

                                            <td>
                                                <span class="badge text-bg-primary">
                                                    {{ number_format($url->requests) }}
                                                </span>
                                            </td>

                                            <td>
                                                {{ number_format((float) $url->avg_duration, 2) }}
                                                ms
                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        <div class="text-center text-muted py-5">
                            No URL request data available for this range.
                        </div>

                    @endif

                </div>

            </div>

        </div>


        {{-- HTTP Status Distribution --}}
        <div class="col-xl-5">

            <div class="card section-card h-100">

                <div class="card-body">

                    <h5 class="mb-1">
                        📊 HTTP Status Distribution
                    </h5>

                    <div class="small-muted mb-4">
                        Requests grouped by HTTP status class
                    </div>


                    @php
                        $statusLabels = [
                            '2xx' => '2xx Success',
                            '3xx' => '3xx Redirect',
                            '4xx' => '4xx Client Error',
                            '5xx' => '5xx Server Error',
                        ];

                        $statusClasses = [
                            '2xx' => 'bg-success',
                            '3xx' => 'bg-info',
                            '4xx' => 'bg-warning',
                            '5xx' => 'bg-danger',
                        ];

                        $statusTotalSafe = max(1, $statusTotal);
                    @endphp


                    @foreach ($statusDistribution as $statusGroup => $count)

                        @php
                            $percentage = ($count / $statusTotalSafe) * 100;
                        @endphp

                        <div class="mb-4">

                            <div class="d-flex justify-content-between mb-2">

                                <span>
                                    {{ $statusLabels[$statusGroup] ?? $statusGroup }}
                                </span>

                                <strong>
                                    {{ number_format($count) }}
                                    ({{ number_format($percentage, 1) }}%)
                                </strong>

                            </div>

                            <div class="progress">

                                <div
                                    class="progress-bar {{ $statusClasses[$statusGroup] ?? 'bg-secondary' }}"
                                    role="progressbar"
                                    style="width: {{ $percentage }}%"
                                    aria-valuenow="{{ $percentage }}"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                ></div>

                            </div>

                        </div>

                    @endforeach


                    @if ($statusTotal === 0)

                        <div class="text-center text-muted py-3">
                            No HTTP status data available.
                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- Recent + Slowest Requests --}}
    <div class="row g-4 mb-4">

        {{-- Recent Performance --}}
        <div class="col-xl-6">

            <div class="card section-card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>

                            <h5 class="mb-1">
                                🕒 Recent Requests
                            </h5>

                            <div class="small-muted">
                                Latest monitored requests
                            </div>

                        </div>

                        <a
                            href="{{ route('nightwatch.performance', ['range' => $range ?? 'all']) }}"
                            class="btn btn-outline-primary btn-sm"
                        >
                            View All
                        </a>

                    </div>


                    @if ($recentPerformance->count())

                        <div class="table-responsive">

                            <table class="table table-hover align-middle mb-0">

                                <thead class="table-light">

                                    <tr>
                                        <th>Request</th>
                                        <th>Status</th>
                                        <th>Duration</th>
                                        <th>Category</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach ($recentPerformance as $metric)

                                        <tr>

                                            <td>

                                                <div>
                                                    <span class="badge text-bg-secondary">
                                                        {{ $metric->method }}
                                                    </span>
                                                </div>

                                                <div
                                                    class="url-cell mt-1"
                                                    title="{{ $metric->path }}"
                                                >
                                                    <code>
                                                        {{ $metric->path }}
                                                    </code>
                                                </div>

                                            </td>


                                            <td>

                                                @if ($metric->status_code >= 500)

                                                    <span class="badge text-bg-danger status-badge">
                                                        {{ $metric->status_code }}
                                                    </span>

                                                @elseif ($metric->status_code >= 400)

                                                    <span class="badge text-bg-warning status-badge">
                                                        {{ $metric->status_code }}
                                                    </span>

                                                @elseif ($metric->status_code >= 300)

                                                    <span class="badge text-bg-info status-badge">
                                                        {{ $metric->status_code }}
                                                    </span>

                                                @else

                                                    <span class="badge text-bg-success status-badge">
                                                        {{ $metric->status_code }}
                                                    </span>

                                                @endif

                                            </td>


                                            <td>
                                                <strong>
                                                    {{ number_format($metric->duration_ms, 2) }}
                                                </strong>
                                                ms
                                            </td>


                                            <td>

                                                @if ($metric->category === 'CRITICAL')

                                                    <span class="badge text-bg-danger">
                                                        CRITICAL
                                                    </span>

                                                @elseif ($metric->category === 'SLOW')

                                                    <span class="badge text-bg-warning">
                                                        SLOW
                                                    </span>

                                                @else

                                                    <span class="badge text-bg-success">
                                                        FAST
                                                    </span>

                                                @endif

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        <div class="text-center text-muted py-5">
                            No performance metrics available.
                        </div>

                    @endif

                </div>

            </div>

        </div>


        {{-- Slowest Requests --}}
        <div class="col-xl-6">

            <div class="card section-card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>

                            <h5 class="mb-1">
                                🐢 Slowest Requests
                            </h5>

                            <div class="small-muted">
                                Requests with the highest response duration
                            </div>

                        </div>

                        <a
                            href="{{ route('nightwatch.performance', [
                                'range' => $range ?? 'all',
                                'sort' => 'duration_desc'
                            ]) }}"
                            class="btn btn-outline-danger btn-sm"
                        >
                            View Slowest
                        </a>

                    </div>


                    @if ($slowestRequests->count())

                        <div class="table-responsive">

                            <table class="table table-hover align-middle mb-0">

                                <thead class="table-light">

                                    <tr>
                                        <th>#</th>
                                        <th>Request</th>
                                        <th>Status</th>
                                        <th>Duration</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach ($slowestRequests as $index => $metric)

                                        <tr>

                                            <td>
                                                <strong>
                                                    {{ $index + 1 }}
                                                </strong>
                                            </td>


                                            <td>

                                                <span class="badge text-bg-secondary">
                                                    {{ $metric->method }}
                                                </span>

                                                <div
                                                    class="url-cell mt-1"
                                                    title="{{ $metric->path }}"
                                                >
                                                    <code>
                                                        {{ $metric->path }}
                                                    </code>
                                                </div>

                                            </td>


                                            <td>

                                                @if ($metric->status_code >= 500)

                                                    <span class="badge text-bg-danger">
                                                        {{ $metric->status_code }}
                                                    </span>

                                                @elseif ($metric->status_code >= 400)

                                                    <span class="badge text-bg-warning">
                                                        {{ $metric->status_code }}
                                                    </span>

                                                @else

                                                    <span class="badge text-bg-success">
                                                        {{ $metric->status_code }}
                                                    </span>

                                                @endif

                                            </td>


                                            <td>

                                                <strong class="text-danger">
                                                    {{ number_format($metric->duration_ms, 2) }}
                                                    ms
                                                </strong>

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        <div class="text-center text-muted py-5">
                            No performance metrics available.
                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- Monitoring Test Tools --}}
    <div class="card section-card mb-4">

        <div class="card-body">

            <h5 class="mb-1">
                🧪 Monitoring Test Tools
            </h5>

            <p class="text-muted mb-4">
                Generate test logs and performance metrics to verify Nightwatch monitoring.
            </p>


            <div class="d-flex flex-wrap gap-2">

                {{-- Info --}}
                <form
                    method="POST"
                    action="{{ route('nightwatch.test.info') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-info"
                    >
                        ℹ️ Generate Info
                    </button>
                </form>


                {{-- Warning --}}
                <form
                    method="POST"
                    action="{{ route('nightwatch.test.warning') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-warning"
                    >
                        ⚠️ Generate Warning
                    </button>
                </form>


                {{-- Exception --}}
                <form
                    method="POST"
                    action="{{ route('nightwatch.test.exception') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-danger"
                    >
                        ❌ Generate Exception
                    </button>
                </form>


                {{-- All Logs --}}
                <form
                    method="POST"
                    action="{{ route('nightwatch.test.all') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-dark"
                    >
                        🚀 Generate All Test Logs
                    </button>
                </form>


                {{-- Slow Request --}}
                <a
                    href="{{ route('nightwatch.performance.test-slow') }}"
                    class="btn btn-outline-warning"
                >
                    🐢 Test Slow Request
                </a>


                {{-- Critical Request --}}
                <a
                    href="{{ route('nightwatch.performance.test-critical') }}"
                    class="btn btn-outline-danger"
                >
                    🔥 Test Critical Request
                </a>

            </div>

        </div>

    </div>


    {{-- Recent Errors --}}
    <div class="card section-card mb-4">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>

                    <h5 class="mb-1">
                        🚨 Recent Errors
                    </h5>

                    <div class="small-muted">
                        Latest application errors in the selected range
                    </div>

                </div>

                <a
                    href="{{ route('nightwatch.logs', [
                        'level' => 'error',
                        'range' => $range ?? 'all'
                    ]) }}"
                    class="btn btn-outline-danger btn-sm"
                >
                    View Error Logs
                </a>

            </div>


            @if ($recentErrors->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>
                                <th>Date & Time</th>
                                <th>Level</th>
                                <th>Message</th>
                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($recentErrors as $error)

                                <tr>

                                    <td class="text-nowrap">
                                        {{ $error['datetime'] ?? $error['date'] ?? '-' }}
                                    </td>


                                    <td>

                                        <span class="badge text-bg-danger">
                                            {{ strtoupper($error['level'] ?? 'ERROR') }}
                                        </span>

                                    </td>


                                    <td>
                                        {{ $error['message'] ?? '-' }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="text-center text-muted py-5">
                    🎉 No errors found for the selected date range.
                </div>

            @endif

        </div>

    </div>


    {{-- Footer --}}
    <div class="text-center text-muted py-3">

        <small>
            Laravel Nightwatch Monitoring Dashboard
            &middot;
            Auto-refresh:
            <span id="refreshStatus">
                Off
            </span>
        </small>

    </div>

</div>


<script>

    /*
    |--------------------------------------------------------------------------
    | Dashboard Auto Refresh
    |--------------------------------------------------------------------------
    */

    const autoRefreshSelect = document.getElementById('autoRefresh');
    const refreshStatus = document.getElementById('refreshStatus');

    const refreshStorageKey = 'nightwatch_auto_refresh_seconds';

    const savedRefresh = localStorage.getItem(refreshStorageKey);

    if (savedRefresh !== null) {
        autoRefreshSelect.value = savedRefresh;
    }

    function updateRefreshStatus() {

        const seconds = parseInt(autoRefreshSelect.value, 10);

        if (seconds === 0) {

            refreshStatus.textContent = 'Off';

            return;
        }

        if (seconds < 60) {

            refreshStatus.textContent = seconds + ' seconds';

        } else {

            refreshStatus.textContent = (seconds / 60) + ' minute(s)';

        }

    }

    autoRefreshSelect.addEventListener('change', function () {

        const seconds = parseInt(this.value, 10);

        localStorage.setItem(
            refreshStorageKey,
            seconds
        );

        updateRefreshStatus();

        if (seconds > 0) {

            window.location.reload();

        }

    });

    updateRefreshStatus();


    /*
    |--------------------------------------------------------------------------
    | Auto Reload
    |--------------------------------------------------------------------------
    */

    const refreshSeconds = parseInt(
        autoRefreshSelect.value,
        10
    );

    if (refreshSeconds > 0) {

        setTimeout(function () {

            window.location.reload();

        }, refreshSeconds * 1000);

    }

</script>

</body>
</html>