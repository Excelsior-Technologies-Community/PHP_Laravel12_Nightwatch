<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Performance Monitoring</title>

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
            font-size: 28px;
            font-weight: 700;
        }

        .main-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .metric-path {
            max-width: 350px;
            word-break: break-word;
        }

        .category-badge {
            font-size: 12px;
            padding: 7px 10px;
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

        <div class="d-flex gap-2">

            <a
                href="{{ route('nightwatch.dashboard') }}"
                class="btn btn-outline-light"
            >
                📊 Dashboard
            </a>

            <a
                href="{{ route('nightwatch.logs') }}"
                class="btn btn-outline-light"
            >
                🔎 Logs
            </a>

        </div>

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
            ⏱️ Performance Monitoring
        </h2>

        <p class="text-muted mb-0">
            Monitor request execution time, memory usage, and slow application operations.
        </p>

    </div>

    <!-- Statistics -->

    <div class="row g-4 mb-4">

        <div class="col-md-4 col-lg-2">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Total
                </div>

                <div class="stat-number">
                    {{ $totalRequests }}
                </div>

                <small class="text-muted">
                    Requests
                </small>

            </div>

        </div>

        <div class="col-md-4 col-lg-2">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Average
                </div>

                <div class="stat-number">
                    {{ number_format($averageDuration, 2) }}
                </div>

                <small class="text-muted">
                    ms
                </small>

            </div>

        </div>

        <div class="col-md-4 col-lg-2">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Minimum
                </div>

                <div class="stat-number">
                    {{ number_format($minimumDuration, 2) }}
                </div>

                <small class="text-muted">
                    ms
                </small>

            </div>

        </div>

        <div class="col-md-4 col-lg-2">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Maximum
                </div>

                <div class="stat-number">
                    {{ number_format($maximumDuration, 2) }}
                </div>

                <small class="text-muted">
                    ms
                </small>

            </div>

        </div>

        <div class="col-md-4 col-lg-2">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Slow
                </div>

                <div class="stat-number text-warning">
                    {{ $slowRequests }}
                </div>

                <small class="text-muted">
                    Requests
                </small>

            </div>

        </div>

        <div class="col-md-4 col-lg-2">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Critical
                </div>

                <div class="stat-number text-danger">
                    {{ $criticalRequests }}
                </div>

                <small class="text-muted">
                    Requests
                </small>

            </div>

        </div>

    </div>

    <!-- Test Tools -->

    <div class="card main-card mb-4">

        <div class="card-body">

            <h5 class="fw-bold">
                🧪 Performance Test Tools
            </h5>

            <p class="text-muted">
                Generate slow and critical requests to verify performance monitoring.
            </p>

            <div class="d-flex flex-wrap gap-2">

                <a
                    href="{{ route('nightwatch.performance.test-slow', ['seconds' => 2]) }}"
                    class="btn btn-warning"
                >
                    🐢 Test Slow Request
                </a>

                <a
                    href="{{ route('nightwatch.performance.test-critical') }}"
                    class="btn btn-danger"
                >
                    🚨 Test Critical Request
                </a>

            </div>

            <div class="mt-3 small text-muted">

                Slow threshold:
                <strong>
                    {{ number_format((float) config('performance.slow_threshold_ms')) }} ms
                </strong>

                &nbsp; | &nbsp;

                Critical threshold:
                <strong>
                    {{ number_format((float) config('performance.critical_threshold_ms')) }} ms
                </strong>

            </div>

        </div>

    </div>

    <!-- Filters -->

    <div class="card main-card mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="{{ route('nightwatch.performance') }}"
            >

                <div class="row g-3">

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Search Path / Route
                        </label>

                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            class="form-control"
                            placeholder="/test-request"
                        >

                    </div>

                    <div class="col-md-2">

                        <label class="form-label fw-semibold">
                            Category
                        </label>

                        <select
                            name="category"
                            class="form-select"
                        >

                            <option value="ALL">
                                All
                            </option>

                            <option
                                value="FAST"
                                {{ $category === 'FAST' ? 'selected' : '' }}
                            >
                                FAST
                            </option>

                            <option
                                value="SLOW"
                                {{ $category === 'SLOW' ? 'selected' : '' }}
                            >
                                SLOW
                            </option>

                            <option
                                value="CRITICAL"
                                {{ $category === 'CRITICAL' ? 'selected' : '' }}
                            >
                                CRITICAL
                            </option>

                        </select>

                    </div>

                    <div class="col-md-2">

                        <label class="form-label fw-semibold">
                            Method
                        </label>

                        <select
                            name="method"
                            class="form-select"
                        >

                            <option value="ALL">
                                All
                            </option>

                            @foreach($methods as $httpMethod)

                                <option
                                    value="{{ $httpMethod }}"
                                    {{ $method === $httpMethod ? 'selected' : '' }}
                                >
                                    {{ $httpMethod }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-2">

                        <label class="form-label fw-semibold">
                            Date
                        </label>

                        <input
                            type="date"
                            name="date"
                            value="{{ $date }}"
                            class="form-control"
                        >

                    </div>

                    <div class="col-md-2 d-flex align-items-end">

                        <button
                            class="btn btn-primary w-100"
                            type="submit"
                        >
                            🔎 Filter
                        </button>

                    </div>

                </div>

            </form>

            <div class="mt-3">

                <a
                    href="{{ route('nightwatch.performance') }}"
                    class="btn btn-sm btn-outline-secondary"
                >
                    Reset
                </a>

                <span class="text-muted ms-2">

                    {{ $totalRequests }} record(s)

                </span>

            </div>

        </div>

    </div>

    <!-- Performance Table -->

    <div class="card main-card">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">

            <div>

                <h5 class="fw-bold mb-0">
                    📈 Request Performance
                </h5>

                <small class="text-muted">
                    Latest monitored application requests
                </small>

            </div>

            <form
                method="POST"
                action="{{ route('nightwatch.performance.clear') }}"
                onsubmit="return confirm('Clear all performance metrics?')"
            >

                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="btn btn-sm btn-outline-danger"
                >
                    🗑️ Clear
                </button>

            </form>

        </div>

        <div class="card-body p-0">

            @if($filteredRecords->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-dark">

                            <tr>

                                <th>#</th>

                                <th>Request</th>

                                <th>Route</th>

                                <th>Method</th>

                                <th>Status</th>

                                <th>Duration</th>

                                <th>Memory</th>

                                <th>Category</th>

                                <th>Time</th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($filteredRecords as $record)

                                <tr>

                                    <td>
                                        {{ $filteredRecords->firstItem() + $loop->index }}
                                    </td>

                                    <td class="metric-path">
                                        {{ $record->path }}
                                    </td>

                                    <td>
                                        {{ $record->route_name ?? 'N/A' }}
                                    </td>

                                    <td>
                                        <span class="badge bg-dark">
                                            {{ $record->method }}
                                        </span>
                                    </td>

                                    <td>

                                        @if($record->status_code >= 500)

                                            <span class="badge bg-danger">
                                                {{ $record->status_code }}
                                            </span>

                                        @elseif($record->status_code >= 400)

                                            <span class="badge bg-warning text-dark">
                                                {{ $record->status_code }}
                                            </span>

                                        @else

                                            <span class="badge bg-success">
                                                {{ $record->status_code }}
                                            </span>

                                        @endif

                                    </td>

                                    <td class="fw-semibold">

                                        {{ number_format($record->duration_ms, 2) }}
                                        ms

                                    </td>

                                    <td>

                                        {{ number_format($record->memory_mb, 2) }}
                                        MB

                                    </td>

                                    <td>

                                        @php

                                            $categoryBadge = match($record->category) {
                                                'CRITICAL' => 'danger',
                                                'SLOW' => 'warning',
                                                default => 'success',
                                            };

                                        @endphp

                                        <span
                                            class="badge bg-{{ $categoryBadge }} category-badge {{ $record->category === 'SLOW' ? 'text-dark' : '' }}"
                                        >
                                            {{ $record->category }}
                                        </span>

                                    </td>

                                    <td class="text-nowrap">

                                        {{ $record->created_at->format('d M Y H:i:s') }}

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                <div class="p-3">

                    {{ $filteredRecords->links('pagination::bootstrap-5') }}

                </div>

            @else

                <div class="p-5 text-center">

                    <div class="display-5">
                        ⏱️
                    </div>

                    <h5 class="mt-3">
                        No performance records found
                    </h5>

                    <p class="text-muted">
                        Open an application route or use one of the performance test buttons above.
                    </p>

                </div>

            @endif

        </div>

    </div>

</div>

</body>

</html>