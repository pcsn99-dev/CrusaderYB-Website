<x-app-layout>
    @php
        $permissionsCount = $role->permissions->count();
        $usersCount = $role->users->count();

        $systemPermissions = $role->permissions->filter(function ($permission) {
            return str_contains($permission->slug, 'dashboard')
                || str_contains($permission->slug, 'roles')
                || str_contains($permission->slug, 'admin-users');
        });

        $studentPermissions = $role->permissions->filter(function ($permission) {
            return str_contains($permission->slug, 'student');
        });

        $writeupPermissions = $role->permissions->filter(function ($permission) {
            return str_contains($permission->slug, 'writeups');
        });

        $otherPermissions = $role->permissions->reject(function ($permission) use (
            $systemPermissions,
            $studentPermissions,
            $writeupPermissions
        ) {
            return $systemPermissions->contains('id', $permission->id)
                || $studentPermissions->contains('id', $permission->id)
                || $writeupPermissions->contains('id', $permission->id);
        });

        $permissionGroups = [
            'System Management' => [
                'permissions' => $systemPermissions,
                'icon' => 'bi-shield-lock',
                'description' => 'Dashboard, roles, and administrator account access.',
            ],

            'Student Accounts' => [
                'permissions' => $studentPermissions,
                'icon' => 'bi-mortarboard',
                'description' => 'Student account access and administrative student actions.',
            ],

            'Writeup Workflow' => [
                'permissions' => $writeupPermissions,
                'icon' => 'bi-pencil-square',
                'description' => 'Writeup review, creation, and management access.',
            ],

            'Other Permissions' => [
                'permissions' => $otherPermissions,
                'icon' => 'bi-three-dots',
                'description' => 'Additional permissions that do not belong to the main modules above.',
            ],
        ];
    @endphp

    <x-slot name="header">
        Role Details
    </x-slot>

    <x-slot name="subheader">
        View {{ $role->name }} permissions and assigned administrator accounts.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-person-badge-fill"></i>
    </x-slot>

    <x-slot name="headerActions">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a
                href="{{ route('roles.index') }}"
                class="btn btn-light border d-inline-flex align-items-center gap-2"
            >
                <i class="bi bi-arrow-left"></i>
                Back to Roles
            </a>

            <a
                href="{{ route('roles.edit', $role) }}"
                class="btn btn-primary d-inline-flex align-items-center gap-2"
            >
                <i class="bi bi-pencil-square"></i>
                Edit Role
            </a>
        </div>
    </x-slot>

    <div class="role-details-page">

        {{-- Role Summary --}}
        <section class="card role-details-card mb-4">
            <div class="card-body role-summary">
                <div class="role-summary-main">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="role-summary-badge">
                            <i class="bi bi-person-badge"></i>
                            {{ $role->name }}
                        </span>

                        @if ($role->is_protected)
                            <span class="role-protected-badge">
                                <i class="bi bi-lock-fill"></i>
                                Protected
                            </span>
                        @endif
                    </div>

                    <p class="role-description">
                        {{ $role->description ?: 'No description provided.' }}
                    </p>

                    <div class="role-slug">
                        <span class="role-meta-label">
                            Slug
                        </span>

                        <code>{{ $role->slug }}</code>
                    </div>
                </div>

                <div class="role-summary-stats">
                    <div class="role-stat">
                        <div class="role-stat-icon">
                            <i class="bi bi-key"></i>
                        </div>

                        <div>
                            <div class="role-stat-value">
                                {{ $permissionsCount }}
                            </div>

                            <div class="role-stat-label">
                                Permissions
                            </div>
                        </div>
                    </div>

                    <div class="role-stat">
                        <div class="role-stat-icon">
                            <i class="bi bi-people"></i>
                        </div>

                        <div>
                            <div class="role-stat-value">
                                {{ $usersCount }}
                            </div>

                            <div class="role-stat-label">
                                Assigned Users
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($role->is_protected)
                <div class="protected-role-banner">
                    <div class="protected-role-banner-icon">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>

                    <div>
                        <div class="protected-role-banner-title">
                            Protected system role
                        </div>

                        <div class="protected-role-banner-text">
                            This role is protected to prevent accidental system
                            lockout. Its name and permissions are preserved.
                        </div>
                    </div>
                </div>
            @endif
        </section>

        {{-- Permissions --}}
        <section class="card role-details-card mb-4">
            <div class="card-header role-section-header">
                <div>
                    <h2 class="role-section-title">
                        Permissions
                    </h2>

                    <p class="role-section-description">
                        Access granted to this role.
                    </p>
                </div>

                <span class="role-count">
                    {{ $permissionsCount }}
                    {{ $permissionsCount === 1 ? 'permission' : 'permissions' }}
                </span>
            </div>

            <div class="card-body">
                @if ($permissionsCount)
                    <div class="permission-groups">
                        @foreach ($permissionGroups as $groupName => $group)
                            @php
                                $groupPermissions = $group['permissions'];
                            @endphp

                            @if ($groupPermissions->count())
                                <div class="permission-group">
                                    <div class="permission-group-header">
                                        <div class="permission-group-heading">
                                            <div class="permission-group-icon">
                                                <i class="bi {{ $group['icon'] }}"></i>
                                            </div>

                                            <div>
                                                <div class="permission-group-title-row">
                                                    <h3 class="permission-group-title">
                                                        {{ $groupName }}
                                                    </h3>

                                                    <span class="permission-count">
                                                        {{ $groupPermissions->count() }}
                                                        {{ $groupPermissions->count() === 1
                                                            ? 'permission'
                                                            : 'permissions' }}
                                                    </span>
                                                </div>

                                                <p class="permission-group-description">
                                                    {{ $group['description'] }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="permission-view-list">
                                        @foreach ($groupPermissions as $permission)
                                            <div class="permission-view-item">
                                                <div class="permission-view-icon">
                                                    <i class="bi bi-check2"></i>
                                                </div>

                                                <div class="permission-view-content">
                                                    <div class="permission-view-title-row">
                                                        <span class="permission-view-name">
                                                            {{ $permission->name }}
                                                        </span>

                                                        <code class="permission-view-slug">
                                                            {{ $permission->slug }}
                                                        </code>
                                                    </div>

                                                    @if ($permission->description)
                                                        <p class="permission-view-description">
                                                            {{ $permission->description }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="role-empty-state">
                        <div class="role-empty-icon">
                            <i class="bi bi-key"></i>
                        </div>

                        <h3 class="role-empty-title">
                            No permissions assigned
                        </h3>

                        <p class="role-empty-text">
                            This role currently does not grant access to any module.
                        </p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Assigned Users --}}
        <section class="card role-details-card">
            <div class="card-header role-section-header">
                <div>
                    <h2 class="role-section-title">
                        Assigned Users
                    </h2>

                    <p class="role-section-description">
                        Administrator accounts currently using this role.
                    </p>
                </div>

                <span class="role-count">
                    {{ $usersCount }}
                    {{ $usersCount === 1 ? 'user' : 'users' }}
                </span>
            </div>

            @if ($usersCount)
                <div class="table-responsive">
                    <table class="table role-users-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Type</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($role->users as $user)
                                <tr>
                                    <td>
                                        <div class="role-user">
                                            <x-avatar
                                                :name="$user->name"
                                                size="34"
                                            />

                                            <div>
                                                <div class="role-user-name">
                                                    {{ $user->name }}
                                                </div>

                                                @if (!empty($user->username))
                                                    <div class="role-user-username">
                                                        {{ $user->username }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="role-user-email">
                                            {{ $user->email ?: '—' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="role-user-type">
                                            {{ ucfirst($user->type ?? 'N/A') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="role-empty-state">
                    <div class="role-empty-icon">
                        <i class="bi bi-person-x"></i>
                    </div>

                    <h3 class="role-empty-title">
                        No assigned users
                    </h3>

                    <p class="role-empty-text">
                        No administrator accounts are currently assigned to this role.
                    </p>
                </div>
            @endif
        </section>

    </div>

    @push('styles')
        <style>
            .role-details-page {
                padding-bottom: 2rem;
            }

            .role-details-card {
                overflow: hidden;
                border: 1px solid #e7eaed;
                border-radius: 0.75rem;
                box-shadow: 0 0.125rem 0.4rem rgba(0, 0, 0, 0.03);
            }

            .role-summary {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 2rem;
                padding: 1.25rem;
            }

            .role-summary-main {
                min-width: 0;
                flex: 1;
            }

            .role-summary-badge,
            .role-protected-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                min-height: 28px;
                padding: 0.25rem 0.6rem;
                border-radius: 999px;
                font-size: 0.74rem;
                font-weight: 600;
            }

            .role-summary-badge {
                background: #eef3f8;
                color: #495057;
            }

            .role-protected-badge {
                background: #e8f0fe;
                color: #315ca8;
            }

            .role-description {
                max-width: 760px;
                margin: 0 0 1rem;
                color: #5f666d;
                font-size: 0.9rem;
                line-height: 1.55;
            }

            .role-slug {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .role-meta-label {
                color: #6c757d;
                font-size: 0.74rem;
                font-weight: 600;
            }

            .role-slug code {
                padding: 0.2rem 0.45rem;
                border-radius: 0.35rem;
                background: #f1f3f5;
                color: #5b636b;
                font-size: 0.72rem;
            }

            .role-summary-stats {
                display: grid;
                grid-template-columns: repeat(2, minmax(145px, 1fr));
                gap: 0.75rem;
                flex: 0 0 auto;
            }

            .role-stat {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                min-width: 145px;
                padding: 0.85rem 1rem;
                border: 1px solid #e7eaed;
                border-radius: 0.65rem;
                background: #fafbfc;
            }

            .role-stat-icon {
                display: flex;
                width: 36px;
                height: 36px;
                flex: 0 0 36px;
                align-items: center;
                justify-content: center;
                border-radius: 0.55rem;
                background: #eef2f6;
                color: #495057;
                font-size: 0.9rem;
            }

            .role-stat-value {
                color: #212529;
                font-size: 1.15rem;
                font-weight: 650;
                line-height: 1;
            }

            .role-stat-label {
                margin-top: 0.2rem;
                color: #6c757d;
                font-size: 0.72rem;
            }

            .protected-role-banner {
                display: flex;
                align-items: flex-start;
                gap: 0.8rem;
                padding: 1rem 1.2rem;
                border-top: 1px solid #d9e4f5;
                background: #f4f8fd;
                color: #355779;
            }

            .protected-role-banner-icon {
                flex: 0 0 auto;
                margin-top: 0.05rem;
            }

            .protected-role-banner-title {
                font-size: 0.84rem;
                font-weight: 600;
            }

            .protected-role-banner-text {
                margin-top: 0.2rem;
                max-width: 740px;
                font-size: 0.77rem;
                line-height: 1.5;
            }

            .role-section-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 1rem 1.2rem;
                border-bottom: 1px solid #edf0f2;
                background: #fff;
            }

            .role-section-title {
                margin: 0;
                color: #212529;
                font-size: 1rem;
                font-weight: 600;
            }

            .role-section-description {
                margin: 0.2rem 0 0;
                color: #6c757d;
                font-size: 0.79rem;
            }

            .role-count,
            .permission-count {
                display: inline-flex;
                align-items: center;
                min-height: 24px;
                padding: 0.15rem 0.5rem;
                border-radius: 999px;
                background: #f1f3f5;
                color: #687078;
                font-size: 0.7rem;
                font-weight: 600;
                white-space: nowrap;
            }

            .permission-groups {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .permission-group {
                overflow: hidden;
                border: 1px solid #e7eaed;
                border-radius: 0.65rem;
                background: #fff;
            }

            .permission-group-header {
                padding: 0.85rem 1rem;
                border-bottom: 1px solid #edf0f2;
                background: #f8f9fa;
            }

            .permission-group-heading {
                display: flex;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .permission-group-icon {
                display: flex;
                width: 34px;
                height: 34px;
                flex: 0 0 34px;
                align-items: center;
                justify-content: center;
                border-radius: 0.55rem;
                background: #e9edf1;
                color: #495057;
                font-size: 0.9rem;
            }

            .permission-group-title-row {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.5rem;
            }

            .permission-group-title {
                margin: 0;
                color: #292d32;
                font-size: 0.88rem;
                font-weight: 600;
            }

            .permission-group-description {
                margin: 0.15rem 0 0;
                color: #6c757d;
                font-size: 0.76rem;
                line-height: 1.4;
            }

            .permission-view-list {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .permission-view-item {
                display: flex;
                gap: 0.75rem;
                padding: 0.9rem 1rem;
                border-bottom: 1px solid #edf0f2;
            }

            .permission-view-item:nth-child(odd) {
                border-right: 1px solid #edf0f2;
            }

            .permission-view-item:nth-last-child(-n + 2) {
                border-bottom: 0;
            }

            .permission-view-icon {
                display: flex;
                width: 24px;
                height: 24px;
                flex: 0 0 24px;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: #e8f5ee;
                color: #197149;
                font-size: 0.75rem;
            }

            .permission-view-content {
                min-width: 0;
                flex: 1;
            }

            .permission-view-title-row {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.45rem;
            }

            .permission-view-name {
                color: #292d32;
                font-size: 0.84rem;
                font-weight: 600;
            }

            .permission-view-slug {
                padding: 0.15rem 0.4rem;
                border-radius: 0.35rem;
                background: #f1f3f5;
                color: #687078;
                font-size: 0.67rem;
                font-weight: 500;
            }

            .permission-view-description {
                margin: 0.2rem 0 0;
                color: #6c757d;
                font-size: 0.75rem;
                line-height: 1.4;
            }

            .role-users-table thead th {
                padding: 0.85rem 1rem;
                border-bottom-width: 1px;
                background: #f8f9fa;
                color: #6c757d;
                font-size: 0.74rem;
                font-weight: 650;
                letter-spacing: 0.03em;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .role-users-table tbody td {
                padding: 1rem;
                border-color: #edf0f2;
            }

            .role-users-table tbody tr:hover {
                background: #fafbfc;
            }

            .role-user {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .role-user-name {
                color: #212529;
                font-size: 0.86rem;
                font-weight: 600;
            }

            .role-user-username {
                margin-top: 0.1rem;
                color: #6c757d;
                font-size: 0.72rem;
            }

            .role-user-email {
                color: #5f666d;
                font-size: 0.82rem;
            }

            .role-user-type {
                display: inline-flex;
                align-items: center;
                min-height: 24px;
                padding: 0.15rem 0.5rem;
                border-radius: 999px;
                background: #f1f3f5;
                color: #687078;
                font-size: 0.7rem;
                font-weight: 600;
            }

            .role-empty-state {
                max-width: 440px;
                margin: 0 auto;
                padding: 3.5rem 1.5rem;
                text-align: center;
            }

            .role-empty-icon {
                display: flex;
                width: 50px;
                height: 50px;
                margin: 0 auto;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: #f1f3f5;
                color: #6c757d;
                font-size: 1.2rem;
            }

            .role-empty-title {
                margin: 0.9rem 0 0.3rem;
                color: #343a40;
                font-size: 0.95rem;
                font-weight: 600;
            }

            .role-empty-text {
                margin: 0;
                color: #6c757d;
                font-size: 0.8rem;
            }

            @media (max-width: 991.98px) {
                .role-summary {
                    flex-direction: column;
                }

                .role-summary-stats {
                    width: 100%;
                }
            }

            @media (max-width: 767.98px) {
                .role-summary-stats {
                    grid-template-columns: 1fr;
                }

                .role-section-header {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .permission-view-list {
                    grid-template-columns: 1fr;
                }

                .permission-view-item {
                    border-right: 0 !important;
                }

                .permission-view-item:nth-last-child(-n + 2) {
                    border-bottom: 1px solid #edf0f2;
                }

                .permission-view-item:last-child {
                    border-bottom: 0;
                }

                .role-users-table {
                    min-width: 650px;
                }
            }
        </style>
    @endpush
</x-app-layout>