<x-app-layout>
    <x-slot name="header">
        Admin User Details
    </x-slot>

    <x-slot name="subheader">
        View account information, access status, and password reset options for {{ $adminUser->name }}.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-person-vcard-fill"></i>
    </x-slot>

    <x-slot name="headerActions">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a
                href="{{ route('admin-users.index') }}"
                class="btn btn-light border d-inline-flex align-items-center gap-2"
            >
                <i class="bi bi-arrow-left"></i>
                Back to Admin Users
            </a>

            <a
                href="{{ route('admin-users.edit', $adminUser) }}"
                class="btn btn-primary d-inline-flex align-items-center gap-2"
            >
                <i class="bi bi-pencil-square"></i>
                Edit Account
            </a>
        </div>
    </x-slot>

    <div class="cyb-page cyb-stack">

        {{-- Profile Summary --}}
        <section class="cyb-card">
            <div class="admin-user-profile">
                <div class="admin-user-profile-main">
                    <x-avatar
                        :name="$adminUser->name"
                        size="52"
                    />

                    <div class="admin-user-profile-content">
                        <h2 class="admin-user-profile-name">
                            {{ $adminUser->name }}
                        </h2>

                        <p class="admin-user-profile-email">
                            {{ $adminUser->email }}
                        </p>

                        <div class="d-flex flex-wrap gap-2">
                            @if ($adminUser->active)
                                <span class="cyb-pill cyb-pill-success">
                                    <span class="cyb-status-dot cyb-status-dot-success"></span>
                                    Active
                                </span>
                            @else
                                <span class="cyb-pill cyb-pill-danger">
                                    <span class="cyb-status-dot cyb-status-dot-danger"></span>
                                    Inactive
                                </span>
                            @endif

                            @if ($adminUser->must_change_password)
                                <span class="cyb-pill cyb-pill-warning">
                                    <i class="bi bi-key"></i>
                                    Must Change Password
                                </span>
                            @else
                                <span class="cyb-pill cyb-pill-neutral">
                                    <i class="bi bi-check2"></i>
                                    Password Changed
                                </span>
                            @endif

                            @if ($adminUser->role)
                                <span class="cyb-pill cyb-pill-primary">
                                    <i class="bi bi-person-badge"></i>
                                    {{ $adminUser->role->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-4">

            {{-- Account Information --}}
            <div class="col-12 col-xl-8">
                <section class="cyb-card h-100">
                    <div class="cyb-card-header">
                        <div>
                            <h2 class="cyb-section-title">
                                Account Information
                            </h2>

                            <p class="cyb-section-description">
                                Basic details and system access information.
                            </p>
                        </div>
                    </div>

                    <div class="cyb-card-body">
                        <dl class="admin-user-info-grid mb-0">
                            <div class="admin-user-info-item">
                                <dt class="admin-user-info-label">
                                    Name
                                </dt>

                                <dd class="admin-user-info-value">
                                    {{ $adminUser->name }}
                                </dd>
                            </div>

                            <div class="admin-user-info-item">
                                <dt class="admin-user-info-label">
                                    Email
                                </dt>

                                <dd class="admin-user-info-value">
                                    {{ $adminUser->email }}
                                </dd>
                            </div>

                            <div class="admin-user-info-item">
                                <dt class="admin-user-info-label">
                                    Username
                                </dt>

                                <dd class="admin-user-info-value">
                                    @if ($adminUser->username)
                                        <span class="cyb-code">
                                            {{ $adminUser->username }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>

                            <div class="admin-user-info-item">
                                <dt class="admin-user-info-label">
                                    Role
                                </dt>

                                <dd class="admin-user-info-value">
                                    @if ($adminUser->role)
                                        {{ $adminUser->role->name }}
                                    @else
                                        <span class="text-danger">
                                            No role assigned
                                        </span>
                                    @endif
                                </dd>
                            </div>

                            <div class="admin-user-info-item">
                                <dt class="admin-user-info-label">
                                    Account Type
                                </dt>

                                <dd class="admin-user-info-value">
                                    {{ ucfirst($adminUser->type ?? 'N/A') }}
                                </dd>
                            </div>

                            <div class="admin-user-info-item">
                                <dt class="admin-user-info-label">
                                    Last Login
                                </dt>

                                <dd class="admin-user-info-value">
                                    {{ $adminUser->last_login_at?->format('M d, Y h:i A') ?? 'Never' }}
                                </dd>
                            </div>

                            <div class="admin-user-info-item">
                                <dt class="admin-user-info-label">
                                    Temporary Password Expires
                                </dt>

                                <dd class="admin-user-info-value">
                                    {{ $adminUser->temporary_password_expires_at?->format('M d, Y h:i A') ?? 'N/A' }}
                                </dd>
                            </div>

                            <div class="admin-user-info-item">
                                <dt class="admin-user-info-label">
                                    Created At
                                </dt>

                                <dd class="admin-user-info-value">
                                    {{ $adminUser->created_at?->format('M d, Y h:i A') ?? 'N/A' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </section>
            </div>

            {{-- Password Access --}}
            <div class="col-12 col-xl-4">
                <section class="cyb-card h-100">
                    <div class="cyb-card-header">
                        <div>
                            <h2 class="cyb-section-title">
                                Password Access
                            </h2>

                            <p class="cyb-section-description">
                                Reset this administrator's password when necessary.
                            </p>
                        </div>
                    </div>

                    <div class="cyb-card-body">
                        <div class="admin-password-panel">
                            <div class="admin-password-icon">
                                <i class="bi bi-key-fill"></i>
                            </div>

                            <div>
                                <div class="admin-password-title">
                                    Send Temporary Password
                                </div>

                                <p class="admin-password-description">
                                    A new temporary password will be emailed to this user.
                                    They will be required to change it after signing in.
                                </p>
                            </div>
                        </div>

                        @php
                            $confirmMessage = $adminUser->must_change_password
                                ? 'Send a new temporary password to this user?'
                                : 'This user has already changed their password. Sending a new temporary password will reset their current password and require them to change it again. Continue?';
                        @endphp

                        <form
                            method="POST"
                            action="{{ route('admin-users.resend-temporary-password', $adminUser) }}"
                            onsubmit="return confirm({{ \Illuminate\Support\Js::from($confirmMessage) }});"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="btn btn-warning w-100 d-inline-flex align-items-center justify-content-center gap-2"
                            >
                                <i class="bi bi-envelope-arrow-up"></i>
                                Resend Temporary Password
                            </button>
                        </form>

                        <div class="cyb-notice cyb-notice-warning admin-password-warning mt-3">
                            <i class="bi bi-info-circle"></i>

                            <div>
                                This resets the current password and marks the account
                                as requiring a password change.
                            </div>
                        </div>
                    </div>
                </section>
            </div>

        </div>
    </div>

    @push('styles')
        <style>
            /*
             * Admin User Show specific styling only.
             * Shared card/pill/code/notice styles come from
             * components.css.
             */

            .admin-user-profile {
                padding: 1.25rem;
            }

            .admin-user-profile-main {
                display: flex;
                align-items: flex-start;
                gap: 1rem;
            }

            .admin-user-profile-content {
                min-width: 0;
            }

            .admin-user-profile-name {
                margin: 0;
                color: var(--cyb-text, #212529);
                font-size: 1.25rem;
                font-weight: 600;
                letter-spacing: -0.015em;
            }

            .admin-user-profile-email {
                margin: 0.25rem 0 0.8rem;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.86rem;
            }

            .admin-user-info-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1.5rem 2rem;
            }

            .admin-user-info-item {
                min-width: 0;
            }

            .admin-user-info-label {
                margin-bottom: 0.3rem;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.72rem;
                font-weight: 650;
                letter-spacing: 0.035em;
                text-transform: uppercase;
            }

            .admin-user-info-value {
                margin: 0;
                color: #292d32;
                font-size: 0.88rem;
                font-weight: 500;
                line-height: 1.45;
                word-break: break-word;
            }

            .admin-password-panel {
                display: flex;
                align-items: flex-start;
                gap: 0.8rem;
                margin-bottom: 1.25rem;
            }

            .admin-password-icon {
                display: flex;
                width: 38px;
                height: 38px;
                flex: 0 0 38px;
                align-items: center;
                justify-content: center;
                border-radius: 0.6rem;
                background: #fff4d8;
                color: #87620f;
                font-size: 0.95rem;
            }

            .admin-password-title {
                color: #292d32;
                font-size: 0.88rem;
                font-weight: 600;
            }

            .admin-password-description {
                margin: 0.25rem 0 0;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.78rem;
                line-height: 1.5;
            }

            .admin-password-warning {
                border: 1px solid #f2dfaa;
                border-radius: 0.65rem;
            }

            @media (max-width: 767.98px) {
                .admin-user-info-grid {
                    grid-template-columns: 1fr;
                    gap: 1.25rem;
                }

                .admin-user-profile-main {
                    flex-direction: column;
                }
            }
        </style>
    @endpush
</x-app-layout>