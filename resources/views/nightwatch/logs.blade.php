<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Application Logs</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f4f6f9;
        }

        .main-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .log-message {
            max-width: 700px;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .filter-card {
            border: none;
            border-radius: 15px;
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
            href="{{ route('nightwatch.dashboard') }}"
            class="btn btn-outline-light"
        >
            📊 Dashboard
        </a>

    </div>

</nav>

<div class="container py-4">

    <div class="mb-4">

        <h2 class="fw-bold">
            🔎 Application Log Viewer
        </h2>

        <p class="text-muted">
            Search and filter Laravel application logs.
        </p>

    </div>

    <!-- Filters -->

    <div class="card filter-card mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="{{ route('nightwatch.logs') }}"
            >

                <div class="row g-3">

                    <div class="col-md-5">

                        <label class="form-label fw-semibold">
                            Search Message
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            value="{{ $search }}"
                            placeholder="Search logs..."
                        >

                    </div>

                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Log Level
                        </label>

                        <select
                            name="level"
                            class="form-select"
                        >

                            <option value="ALL">
                                All Levels
                            </option>

                            @foreach([
                                'DEBUG',
                                'INFO',
                                'NOTICE',
                                'WARNING',
                                'ERROR',
                                'CRITICAL',
                                'ALERT',
                                'EMERGENCY'
                            ] as $logLevel)

                                <option
                                    value="{{ $logLevel }}"
                                    {{ $level === $logLevel ? 'selected' : '' }}
                                >
                                    {{ $logLevel }}
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
                            class="form-control"
                            value="{{ $date }}"
                        >

                    </div>

                    <div class="col-md-2 d-flex align-items-end">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            🔎 Search
                        </button>

                    </div>

                </div>

            </form>

            <div class="mt-3">

                <a
                    href="{{ route('nightwatch.logs') }}"
                    class="btn btn-sm btn-outline-secondary"
                >
                    Reset Filters
                </a>

                <span class="text-muted ms-2">
                    {{ $totalLogs }} log(s) found
                </span>

            </div>

        </div>

    </div>

    <!-- Logs -->

    <div class="card main-card">

        <div class="card-header bg-white">

            <h5 class="fw-bold mb-0">
                Application Logs
            </h5>

        </div>

        <div class="card-body p-0">

            @if($logs->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-dark">

                            <tr>

                                <th>#</th>

                                <th>Date & Time</th>

                                <th>Level</th>

                                <th>Message</th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($logs as $index => $log)

                                <tr>

                                    <td>
                                        {{ $index + 1 }}
                                    </td>

                                    <td class="text-nowrap">

                                        {{ $log['datetime'] }}

                                    </td>

                                    <td>

                                        @php

                                            $badge = match($log['level']) {

                                                'ERROR',
                                                'CRITICAL',
                                                'ALERT',
                                                'EMERGENCY'
                                                    => 'danger',

                                                'WARNING'
                                                    => 'warning',

                                                'INFO'
                                                    => 'primary',

                                                'DEBUG'
                                                    => 'secondary',

                                                default
                                                    => 'dark',

                                            };

                                        @endphp

                                        <span
                                            class="badge bg-{{ $badge }}"
                                        >
                                            {{ $log['level'] }}
                                        </span>

                                    </td>

                                    <td class="log-message">

                                        {{ $log['message'] }}

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="p-5 text-center">

                    <div class="display-5">
                        🔍
                    </div>

                    <h5 class="mt-3">
                        No logs found
                    </h5>

                    <p class="text-muted">
                        Try changing your search or filter criteria.
                    </p>

                </div>

            @endif

        </div>

    </div>

</div>

</body>

</html>