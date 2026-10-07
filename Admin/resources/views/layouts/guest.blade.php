<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap"
        rel="stylesheet"
    >

    <!-- App -->
    @vite([
        'resources/css/guest.css',
        'resources/js/app.js',
    ])

    @stack('styles')
</head>

<body class="guest-body">
    <main class="guest-shell">
        <div class="guest-container">

            <div class="guest-brand">
                <a
                    href="{{ url('/') }}"
                    class="guest-brand-link"
                >
                    <x-application-logo class="guest-logo" />
                </a>
            </div>

            <div class="guest-card">
                {{ $slot }}
            </div>

        </div>
    </main>

    <style>
        body.guest-body {
            margin: 0;
            min-height: 100vh;
            background: #f4f6f8;
            color: #212529;
            font-family:
                'Figtree',
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                'Segoe UI',
                sans-serif;
        }

        .guest-shell {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .guest-container {
            width: 100%;
            max-width: 430px;
        }

        .guest-brand {
            margin-bottom: 1.25rem;
            text-align: center;
        }

        .guest-brand-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: inherit;
            text-decoration: none;
        }

        .guest-logo {
            width: 80px;
            height: 80px;
            color: #6c757d;
        }

        .guest-card {
            overflow: hidden;
            padding: 1.5rem;
            border: 1px solid var(--cyb-border, #e7eaed);
            border-radius: 0.85rem;
            background: #fff;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.06);
        }

        @media (max-width: 575.98px) {
            .guest-shell {
                align-items: flex-start;
                padding-top: 2rem;
            }

            .guest-card {
                padding: 1.2rem;
            }
        }
    </style>
</body>
</html>