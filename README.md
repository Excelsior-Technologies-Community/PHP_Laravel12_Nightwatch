# PHP_Laravel12_Nightwatch

## Overview

**PHP_Laravel12_Nightwatch** is a Laravel 12 application integrated with **Laravel Nightwatch**, the official monitoring and observability platform by Laravel. This project demonstrates a complete, production-ready setup for tracking application logs, exceptions, commands, jobs, and performance metrics using Nightwatch.

Nightwatch is designed primarily for **production environments** and real server traffic. While it can be installed locally for testing logs and exceptions, request metrics are best observed after deployment to a public server.

---

## Features

* Laravel 12 application setup
* Laravel Nightwatch integration
* Centralized log monitoring
* Exception and error tracking
* Background agent-based data ingestion
* Configurable sampling rates
* Secure token-based environment configuration
* Production-ready logging setup
* Works with Nginx / Apache / VPS / Docker

---

## Folder Structure

```
PHP_Laravel12_Nightwatch/
├── app/                # Application core logic
├── bootstrap/          # Framework bootstrapping
├── config/             # Configuration files (nightwatch.php, logging.php, etc.)
├── database/           # Migrations, factories, seeders
├── public/             # Public entry point (index.php)
├── resources/          # Views, assets, frontend files
├── routes/             # Web and API routes
├── storage/            # Logs, cache, compiled views
├── tests/              # Application tests
├── vendor/             # Composer dependencies
├── .env                # Environment configuration
├── artisan             # Artisan CLI
└── composer.json       # Project dependencies
```

---

## System Requirements

* PHP 8.2 or higher
* Composer (latest version)
* MySQL / MariaDB
* Node.js (optional but recommended)
* Apache / Nginx / XAMPP (for local)
* Internet connection

---

## STEP 1: Create a New Laravel 12 Project

Run the following command:

```bash
composer create-project laravel/laravel laravel-nightwatch
```

---

## STEP 2: Environment Configuration (.env)

Open the `.env` file and set basic app values:

```env
APP_NAME=Laravel
APP_ENV=production
APP_KEY=Your_Key
APP_DEBUG=false
APP_URL=http://localhost
```

Generate application key:

```bash
php artisan key:generate
```

---

## STEP 3: Database Configuration

Update database credentials in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:

```bash
php artisan migrate
```

---

## STEP 4: Run Laravel Development Server

```bash
php artisan serve
```

Open in browser:

```
http://127.0.0.1:8000
```

---

## STEP 5: Create Nightwatch Account & Application

1. Visit [https://nightwatch.laravel.com](https://nightwatch.laravel.com)

2. Login / Sign up

3. Create Organization

   * Name: Laravel Ecommerce
   * Type: Individual

     <img width="620" height="759" alt="Screenshot 2026-01-23 123156" src="https://github.com/user-attachments/assets/17bca71c-7906-4832-ae96-525f56f8bfd2" />


4. Create Application

   * Application Name: Laravel Project
   * Storage Region: Europe / US (Asia not required)

5. Create Environment

   * Name: Production
   * Environment URL: [http://localhost](http://localhost) (optional)

     <img width="634" height="828" alt="Screenshot 2026-01-23 123703" src="https://github.com/user-attachments/assets/c0c9c95d-ca60-47de-8c67-01d1f9acd117" />

     <img width="629" height="692" alt="Screenshot 2026-01-23 123730" src="https://github.com/user-attachments/assets/95794e3b-af9c-41f4-9b0e-c6bc69efbfa4" />

     <img width="637" height="601" alt="Screenshot 2026-01-23 123828" src="https://github.com/user-attachments/assets/620ee879-ef98-4ecf-806b-caf362d09796" />

     <img width="618" height="503" alt="Screenshot 2026-01-23 123916" src="https://github.com/user-attachments/assets/e4d47efe-18e5-43cf-b567-083ccb10775c" />

     Dashboard:-
     
     <img width="1919" height="908" alt="Screenshot 2026-01-23 134729" src="https://github.com/user-attachments/assets/c1d5965e-afe7-4e9e-8313-9ef12e59ced7" />



---

## STEP 6: Install Nightwatch Package

Install the official package:

```bash
composer require laravel/nightwatch
```

Verify installation:

```bash
composer show laravel/nightwatch
```

---

## STEP 7: Add Nightwatch Token

Copy the Environment Token from Nightwatch dashboard and add it to `.env`:

```env
NIGHTWATCH_TOKEN=aoYTc1WFwsGfOkdeGijBYRIo4VeBXCzf3phMt3Xf7zSA
NIGHTWATCH_ENABLED=true
NIGHTWATCH_ENV=local
NIGHTWATCH_REQUEST_SAMPLE_RATE=0.1
```

---

## STEP 8: Logging Configuration (IMPORTANT)

Nightwatch requires proper log configuration.

Add this to `.env`:

```env
LOG_LEVEL=debug
LOG_CHANNEL=stack
LOG_STACK=single,nightwatch
```

This ensures:

• Logs are stored locally
• Logs are also sent to Nightwatch

---

## STEP 9: Nightwatch Configuration File

Open `config/nightwatch.php` and ensure Nightwatch is enabled:

```php
<?php

return [
    'enabled' => env('NIGHTWATCH_ENABLED', true),
    'token' => env('NIGHTWATCH_TOKEN'),
    'deployment' => env('NIGHTWATCH_DEPLOY'),
    'server' => env('NIGHTWATCH_SERVER', (string) gethostname()),
    'capture_exception_source_code' => env('NIGHTWATCH_CAPTURE_EXCEPTION_SOURCE_CODE', true),
    'capture_request_payload' => env('NIGHTWATCH_CAPTURE_REQUEST_PAYLOAD', false),
    'redact_payload_fields' => explode(',', env('NIGHTWATCH_REDACT_PAYLOAD_FIELDS', '_token,password,password_confirmation')),
    'redact_headers' => explode(',', env('NIGHTWATCH_REDACT_HEADERS', 'Authorization,Cookie,Proxy-Authorization,X-XSRF-TOKEN')),

    'sampling' => [
        'requests' => env('NIGHTWATCH_REQUEST_SAMPLE_RATE', 1.0),
        'commands' => env('NIGHTWATCH_COMMAND_SAMPLE_RATE', 1.0),
        'exceptions' => env('NIGHTWATCH_EXCEPTION_SAMPLE_RATE', 1.0),
        'scheduled_tasks' => env('NIGHTWATCH_SCHEDULED_TASK_SAMPLE_RATE', 1.0),
    ],

    'filtering' => [
        'ignore_cache_events' => env('NIGHTWATCH_IGNORE_CACHE_EVENTS', false),
        'ignore_mail' => env('NIGHTWATCH_IGNORE_MAIL', false),
        'ignore_notifications' => env('NIGHTWATCH_IGNORE_NOTIFICATIONS', false),
        'ignore_outgoing_requests' => env('NIGHTWATCH_IGNORE_OUTGOING_REQUESTS', false),
        'ignore_queries' => env('NIGHTWATCH_IGNORE_QUERIES', false),
        'log_level' => env('NIGHTWATCH_LOG_LEVEL', env('LOG_LEVEL', 'debug')),
    ],

    'ingest' => [
        'uri' => env('NIGHTWATCH_INGEST_URI', '127.0.0.1:2407'),
        'timeout' => env('NIGHTWATCH_INGEST_TIMEOUT', 0.5),
        'connection_timeout' => env('NIGHTWATCH_INGEST_CONNECTION_TIMEOUT', 0.5),
        'event_buffer' => env('NIGHTWATCH_INGEST_EVENT_BUFFER', 500),
    ],
];
```

---

## STEP 10: Run Nightwatch Agent

This must run in a separate terminal:

```bash
php artisan nightwatch:agent
```

Expected output:

```
Nightwatch agent initiated
Authentication successful
```

---

## STEP 11: Test Request Monitoring (Local)

Add this route in `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-request', function () {
    sleep(1);
    return 'Request OK';
});
```

Open in browser:

```
http://127.0.0.1:8000/test-request
```

 Nightwatch Dashboard → Exceptions / Logs
The event will appear within **10–30 seconds**

---

## STEP 12: Why Requests Are Not Visible Locally (IMPORTANT)

 On Localhost:

* HTTP Requests will NOT appear
* This is expected behavior

Nightwatch is designed for:

*   Production servers
*	VPS / Cloud server
*	Nginx + PHP-FPM
*	Laravel Forge / Vapor
*	Public traffic


 Requests appear only when:

* App is deployed on a real server
* Public traffic is received

For local request debugging, **Laravel Telescope** is recommended.

---

## STEP 13: Production Deployment (Short Overview)

To see request metrics:

1. Deploy project to a VPS (AWS / DigitalOcean / etc.)
2. Use Nginx + PHP-FPM
3. Update `.env`:

```env
APP_ENV=production
APP_DEBUG=false
```

4. Run Nightwatch agent in background
5. Requests will start appearing in the dashboard

---

