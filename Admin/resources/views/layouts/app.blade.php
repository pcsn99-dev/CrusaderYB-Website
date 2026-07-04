<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Vendor CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tabulator-tables@6.4.0/dist/css/tabulator_bootstrap5.min.css">

    {{-- App CSS / JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @stack('styles')
</head>

<body class="layout-fixed sidebar-expand-lg">
    <div class="app-wrapper">

        {{-- Top Navigation --}}
        @include('layouts.navbar')

        {{-- Sidebar --}}
        @include('layouts.sidebar')

        <main class="app-main">
            <x-alert />

            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row align-items-center g-2">
                        <div class="col-sm-6">
                            @isset($header)
                                <h1 class="h3 mb-1 fw-semibold text-primary">
                                    {{ $header }}
                                </h1>
                            @endisset

                            @isset($subheader)
                                <p class="mb-0 text-secondary">
                                    {{ $subheader }}
                                </p>
                            @endisset
                        </div>

                        @isset($breadcrumbs)
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-end mb-0">
                                    {{ $breadcrumbs }}
                                </ol>
                            </div>
                        @endisset
                    </div>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    {{ $slot }}
                </div>
            </div>
        </main>

    </div>

    {{-- Vendor JS --}}
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tabulator-tables@6.4.0/dist/js/tabulator.min.js"></script>

    @livewireScripts
    @stack('scripts')
</body>
</html>