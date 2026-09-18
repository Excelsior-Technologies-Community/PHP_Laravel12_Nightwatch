<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nightwatch Security Gate • Unauthorized</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .lock-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(56, 189, 248, 0.25);
            border-radius: 20px;
            padding: 40px 30px;
            max-width: 440px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }
        .lock-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="lock-card">
        <div class="lock-icon">🔒</div>
        <h4 class="fw-bold mb-2">Nightwatch Access Protected</h4>
        <p class="text-white-50 small mb-4">
            This monitoring dashboard is guarded by security gates. Please enter your valid Access Key to inspect live telemetry.
        </p>
        <form method="GET" action="{{ route('nightwatch.dashboard') }}">
            <div class="mb-3">
                <input type="password" name="key" class="form-control bg-dark text-white border-secondary py-2 text-center" placeholder="Enter Access Key..." required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                Unlock Telemetry Dashboard
            </button>
        </form>
    </div>
</body>
</html>
