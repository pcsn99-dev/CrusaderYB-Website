<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <x-slot name="subheader">
        Overview of current CYB student and pictorial activity.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-grid-1x2-fill"></i>
    </x-slot>

    @php
        $user = Auth::user();

        $canViewStudents = $user?->hasPermission('view-student-accounts');
        $canViewWriteups = $user?->hasPermission('view-writeups');
        $canManageRoles = $user?->hasPermission('manage-roles');
        $canManageAdminUsers = $user?->hasPermission('manage-admin-users');
    @endphp

    <div class="dashboard-page">

        {{-- Welcome --}}
        <section class="dashboard-welcome mb-4">
            <div>
                <div class="dashboard-eyebrow">
                    @if ($activeYear)
                        CYB {{ $activeYear->year }}
                    @else
                        CYB Admin
                    @endif
                </div>

                <h2 class="dashboard-welcome-title">
                    Welcome back, {{ $user?->name ?? 'Admin' }}
                </h2>

                @if ($activeYear)
                    <p class="dashboard-welcome-text">
                        You're viewing the active CYB cycle for
                        <strong>{{ $activeYear->year }}</strong>.

                        @if ($activeYear->theme)
                            The current yearbook theme is
                            <strong>{{ $activeYear->theme }}</strong>.
                        @endif
                    </p>
                @else
                    <p class="dashboard-welcome-text">
                        No active CYB year is currently configured.
                        Dashboard statistics will remain empty until a year is activated.
                    </p>
                @endif
            </div>
        </section>

        @if ($activeYear)
            <section class="active-year-panel mb-4">
                <div class="active-year-main">
                    <div class="active-year-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>

                    <div>
                        <div class="active-year-label">
                            Active Yearbook Cycle
                        </div>

                        <div class="active-year-value">
                            {{ $activeYear->year }}
                        </div>
                    </div>
                </div>

                <div class="active-year-details">
                    @if ($activeYear->theme)
                        <div>
                            <span class="active-year-detail-label">
                                Theme
                            </span>

                            <span class="active-year-detail-value">
                                {{ $activeYear->theme }}
                            </span>
                        </div>
                    @endif

                    @if ($activeYear->subscription_end)
                        <div>
                            <span class="active-year-detail-label">
                                Subscription Deadline
                            </span>

                            <span class="active-year-detail-value">
                                {{ \Carbon\Carbon::parse($activeYear->subscription_end)->format('M j, Y') }}
                            </span>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- Statistics --}}
        <section class="row g-3 mb-4">

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-people"></i>
                    </div>

                    <div>
                        <div class="dashboard-stat-value">
                            {{ number_format($stats['student_accounts']) }}
                        </div>

                        <div class="dashboard-stat-label">
                            Student Accounts
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-person-check"></i>
                    </div>

                    <div>
                        <div class="dashboard-stat-value">
                            {{ number_format($stats['subscribed_students']) }}
                        </div>

                        <div class="dashboard-stat-label">
                            Subscribed Students
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-image"></i>
                    </div>

                    <div>
                        <div class="dashboard-stat-value">
                            {{ number_format($stats['third_party_students']) }}
                        </div>

                        <div class="dashboard-stat-label">
                            Third-Party Photos
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>

                    <div>
                        <div class="dashboard-stat-value">
                            {{ number_format($stats['reservations']) }}
                        </div>

                        <div class="dashboard-stat-label">
                            Reservations
                        </div>
                    </div>
                </div>
            </div>

        </section>

        <div class="row g-4">

            {{-- Quick Access --}}
            <div class="col-12 col-xl-7">
                <section class="card dashboard-card h-100">
                    <div class="card-header dashboard-card-header">
                        <div>
                            <h3 class="dashboard-section-title">
                                Quick Access
                            </h3>

                            <p class="dashboard-section-description">
                                Open the areas you use most often.
                            </p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            @if ($canViewStudents)
                                <div class="col-12 col-md-6">
                                    <a
                                        href="{{ route('student-accounts.index') }}"
                                        class="dashboard-module-link"
                                    >
                                        <div class="dashboard-module-icon">
                                            <i class="bi bi-mortarboard"></i>
                                        </div>

                                        <div class="dashboard-module-content">
                                            <div class="dashboard-module-title">
                                                Student Accounts
                                            </div>

                                            <div class="dashboard-module-description">
                                                Search students and review their
                                                CYB information and pictorial records.
                                            </div>
                                        </div>

                                        <i class="bi bi-arrow-right dashboard-module-arrow"></i>
                                    </a>
                                </div>
                            @endif

                            @if ($canViewWriteups)
                                <div class="col-12 col-md-6">
                                    <a
                                        href="{{ route('writeups.review.index') }}"
                                        class="dashboard-module-link"
                                    >
                                        <div class="dashboard-module-icon">
                                            <i class="bi bi-pencil-square"></i>
                                        </div>

                                        <div class="dashboard-module-content">
                                            <div class="dashboard-module-title">
                                                Review Writeups
                                            </div>

                                            <div class="dashboard-module-description">
                                                Review and manage submitted
                                                student yearbook writeups.
                                            </div>
                                        </div>

                                        <i class="bi bi-arrow-right dashboard-module-arrow"></i>
                                    </a>
                                </div>

                                <div class="col-12 col-md-6">
                                    <a
                                        href="{{ route('writeups.generic.index') }}"
                                        class="dashboard-module-link"
                                    >
                                        <div class="dashboard-module-icon">
                                            <i class="bi bi-card-text"></i>
                                        </div>

                                        <div class="dashboard-module-content">
                                            <div class="dashboard-module-title">
                                                Generic Writeups
                                            </div>

                                            <div class="dashboard-module-description">
                                                Maintain reusable writeups
                                                for eligible students.
                                            </div>
                                        </div>

                                        <i class="bi bi-arrow-right dashboard-module-arrow"></i>
                                    </a>
                                </div>
                            @endif

                            @if ($canManageAdminUsers)
                                <div class="col-12 col-md-6">
                                    <a
                                        href="{{ route('admin-users.index') }}"
                                        class="dashboard-module-link"
                                    >
                                        <div class="dashboard-module-icon">
                                            <i class="bi bi-person-gear"></i>
                                        </div>

                                        <div class="dashboard-module-content">
                                            <div class="dashboard-module-title">
                                                Admin Users
                                            </div>

                                            <div class="dashboard-module-description">
                                                Manage administrator accounts
                                                and access.
                                            </div>
                                        </div>

                                        <i class="bi bi-arrow-right dashboard-module-arrow"></i>
                                    </a>
                                </div>
                            @endif

                            @if ($canManageRoles)
                                <div class="col-12 col-md-6">
                                    <a
                                        href="{{ route('roles.index') }}"
                                        class="dashboard-module-link"
                                    >
                                        <div class="dashboard-module-icon">
                                            <i class="bi bi-person-badge"></i>
                                        </div>

                                        <div class="dashboard-module-content">
                                            <div class="dashboard-module-title">
                                                Roles & Permissions
                                            </div>

                                            <div class="dashboard-module-description">
                                                Control administrator roles
                                                and module permissions.
                                            </div>
                                        </div>

                                        <i class="bi bi-arrow-right dashboard-module-arrow"></i>
                                    </a>
                                </div>
                            @endif

                        </div>
                    </div>
                </section>
            </div>

            {{-- Current Focus --}}
            <div class="col-12 col-xl-5">
                <section class="card dashboard-card h-100">
                    <div class="card-header dashboard-card-header">
                        <div>
                            <h3 class="dashboard-section-title">
                                CYB Operations
                            </h3>

                            <p class="dashboard-section-description">
                                Current administrative workflow.
                            </p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="dashboard-operation-list">

                            <div class="dashboard-operation-item">
                                <div class="dashboard-operation-marker"></div>

                                <div>
                                    <div class="dashboard-operation-title">
                                        Student Accounts
                                    </div>

                                    <div class="dashboard-operation-text">
                                        Search student records, review
                                        subscription information, and manage
                                        photo-source status.
                                    </div>
                                </div>
                            </div>

                            <div class="dashboard-operation-item">
                                <div class="dashboard-operation-marker"></div>

                                <div>
                                    <div class="dashboard-operation-title">
                                        Writeup Management
                                    </div>

                                    <div class="dashboard-operation-text">
                                        Review student submissions and
                                        maintain generic and bulk writeups.
                                    </div>
                                </div>
                            </div>

                            <div class="dashboard-operation-item">
                                <div class="dashboard-operation-marker"></div>

                                <div>
                                    <div class="dashboard-operation-title">
                                        Pictorial Scheduling
                                    </div>

                                    <div class="dashboard-operation-text">
                                        Schedule management and admin booking
                                        tools will be available here once the
                                        pictorial module is completed.
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </section>
            </div>

        </div>
    </div>

    @push('styles')
        <style>
            .dashboard-page {
                padding-bottom: 2rem;
            }

            .dashboard-welcome {
                padding: 1.1rem 0 0.35rem;
            }

            .dashboard-eyebrow {
                margin-bottom: 0.35rem;
                color: #6c757d;
                font-size: 0.73rem;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .dashboard-welcome-title {
                margin: 0;
                color: #212529;
                font-size: 1.4rem;
                font-weight: 600;
                letter-spacing: -0.02em;
            }

            .dashboard-welcome-text {
                max-width: 680px;
                margin: 0.45rem 0 0;
                color: #6c757d;
                font-size: 0.9rem;
                line-height: 1.55;
            }

            .dashboard-stat-card {
                display: flex;
                min-height: 106px;
                align-items: center;
                gap: 1rem;
                padding: 1.15rem;
                border: 1px solid #e7eaed;
                border-radius: 0.75rem;
                background: #fff;
                box-shadow: 0 0.125rem 0.4rem rgba(0, 0, 0, 0.03);
            }

            .dashboard-stat-icon {
                display: flex;
                width: 44px;
                height: 44px;
                flex: 0 0 44px;
                align-items: center;
                justify-content: center;
                border-radius: 0.65rem;
                background: #f1f4f7;
                color: #495057;
                font-size: 1.15rem;
            }

            .dashboard-stat-value {
                color: #212529;
                font-size: 1.45rem;
                font-weight: 650;
                line-height: 1.1;
            }

            .dashboard-stat-label {
                margin-top: 0.25rem;
                color: #6c757d;
                font-size: 0.8rem;
            }

            .dashboard-card {
                overflow: hidden;
                border: 1px solid #e7eaed;
                border-radius: 0.75rem;
                box-shadow: 0 0.125rem 0.4rem rgba(0, 0, 0, 0.03);
            }

            .dashboard-card-header {
                padding: 1rem 1.2rem;
                border-bottom: 1px solid #edf0f2;
                background: #fff;
            }

            .dashboard-section-title {
                margin: 0;
                color: #212529;
                font-size: 1rem;
                font-weight: 600;
            }

            .dashboard-section-description {
                margin: 0.2rem 0 0;
                color: #6c757d;
                font-size: 0.79rem;
            }

            .dashboard-module-link {
                display: flex;
                min-height: 108px;
                align-items: flex-start;
                gap: 0.85rem;
                padding: 1rem;
                border: 1px solid #e7eaed;
                border-radius: 0.65rem;
                background: #fff;
                color: inherit;
                text-decoration: none;
                transition:
                    border-color 0.15s ease,
                    box-shadow 0.15s ease,
                    transform 0.15s ease;
            }

            .dashboard-module-link:hover {
                border-color: #cfd6dd;
                box-shadow: 0 0.2rem 0.6rem rgba(0, 0, 0, 0.045);
                color: inherit;
                transform: translateY(-1px);
            }

            .dashboard-module-icon {
                display: flex;
                width: 38px;
                height: 38px;
                flex: 0 0 38px;
                align-items: center;
                justify-content: center;
                border-radius: 0.55rem;
                background: #f1f4f7;
                color: #495057;
                font-size: 1rem;
            }

            .dashboard-module-content {
                min-width: 0;
                flex: 1;
            }

            .dashboard-module-title {
                color: #212529;
                font-size: 0.9rem;
                font-weight: 600;
            }

            .dashboard-module-description {
                margin-top: 0.25rem;
                color: #6c757d;
                font-size: 0.77rem;
                line-height: 1.45;
            }

            .dashboard-module-arrow {
                margin-top: 0.2rem;
                color: #adb5bd;
                font-size: 0.85rem;
            }

            .dashboard-operation-list {
                display: flex;
                flex-direction: column;
            }

            .dashboard-operation-item {
                display: flex;
                gap: 0.85rem;
                padding: 1rem 0;
                border-bottom: 1px solid #edf0f2;
            }

            .dashboard-operation-item:first-child {
                padding-top: 0;
            }

            .dashboard-operation-item:last-child {
                padding-bottom: 0;
                border-bottom: 0;
            }

            .dashboard-operation-marker {
                width: 8px;
                height: 8px;
                flex: 0 0 8px;
                margin-top: 0.42rem;
                border-radius: 50%;
                background: #6c757d;
            }

            .dashboard-operation-title {
                color: #212529;
                font-size: 0.86rem;
                font-weight: 600;
            }

            .dashboard-operation-text {
                margin-top: 0.25rem;
                color: #6c757d;
                font-size: 0.78rem;
                line-height: 1.5;
            }

            .active-year-panel {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1.5rem;
                padding: 1rem 1.15rem;
                border: 1px solid #e7eaed;
                border-radius: 0.75rem;
                background: #fff;
            }

            .active-year-main {
                display: flex;
                align-items: center;
                gap: 0.85rem;
            }

            .active-year-icon {
                display: flex;
                width: 42px;
                height: 42px;
                align-items: center;
                justify-content: center;
                border-radius: 0.65rem;
                background: #eef3f8;
                color: #495057;
                font-size: 1.05rem;
            }

            .active-year-label {
                color: #6c757d;
                font-size: 0.75rem;
                font-weight: 600;
            }

            .active-year-value {
                margin-top: 0.1rem;
                color: #212529;
                font-size: 1.25rem;
                font-weight: 650;
            }

            .active-year-details {
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                gap: 1.75rem;
            }

            .active-year-detail-label {
                display: block;
                color: #6c757d;
                font-size: 0.72rem;
            }

            .active-year-detail-value {
                display: block;
                margin-top: 0.15rem;
                color: #343a40;
                font-size: 0.86rem;
                font-weight: 550;
            }

            @media (max-width: 767.98px) {
                .active-year-panel {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .active-year-details {
                    justify-content: flex-start;
                }
            }
        </style>
    @endpush
</x-app-layout>