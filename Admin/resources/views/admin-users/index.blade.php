<x-app-layout>
    <x-slot name="header">
        Admin Users
    </x-slot>

    <x-slot name="subheader">
        Manage staff access, assigned roles, account status, and temporary password resets.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-people-fill"></i>
    </x-slot>

    <x-slot name="headerActions">
        <a
            href="{{ route('admin-users.create') }}"
            class="btn btn-primary d-inline-flex align-items-center gap-2"
        >
            <i class="bi bi-person-plus"></i>
            Create Admin User
        </a>
    </x-slot>

    <div class="cyb-page">

        {{-- Alerts --}}
        @if (session('success'))
            <div class="alert alert-success d-flex align-items-start gap-2 mb-4">
                <i class="bi bi-check-circle-fill mt-1"></i>

                <div>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger d-flex align-items-start gap-2 mb-4">
                <i class="bi bi-exclamation-triangle-fill mt-1"></i>

                <div>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        {{-- Main Card --}}
        <div class="cyb-card">
            <div class="cyb-toolbar">
                <div>
                    <h2 class="cyb-section-title">
                        Administrator Accounts
                    </h2>

                    <p class="cyb-section-description">
                        Staff accounts with administrative access to CYB.
                    </p>
                </div>

                <span class="cyb-pill cyb-pill-neutral">
                    <i class="bi bi-shield-lock"></i>

                    {{ $adminUsers->total() }}
                    {{ $adminUsers->total() === 1 ? 'admin user' : 'admin users' }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table cyb-table admin-users-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Password</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($adminUsers as $adminUser)
                            <tr>
                                <td>
                                    <div class="cyb-table-identity">
                                        <x-avatar
                                            :name="$adminUser->name"
                                            size="36"
                                        />

                                        <div class="cyb-table-identity-content">
                                            <div class="cyb-table-primary">
                                                {{ $adminUser->name }}
                                            </div>

                                            <div class="cyb-table-secondary">
                                                {{ $adminUser->email ?: 'No email' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if ($adminUser->username)
                                        <span class="cyb-code">
                                            {{ $adminUser->username }}
                                        </span>
                                    @else
                                        <span class="text-muted">
                                            —
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($adminUser->role)
                                        <span class="cyb-pill cyb-pill-primary">
                                            <i class="bi bi-person-badge"></i>
                                            {{ $adminUser->role->name }}
                                        </span>
                                    @else
                                        <span class="cyb-pill cyb-pill-danger">
                                            <i class="bi bi-exclamation-circle"></i>
                                            No Role
                                        </span>
                                    @endif
                                </td>

                                <td>
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
                                </td>

                                <td>
                                    @if ($adminUser->must_change_password)
                                        <span class="cyb-pill cyb-pill-warning">
                                            <i class="bi bi-key"></i>
                                            Must Change
                                        </span>
                                    @else
                                        <span class="cyb-pill cyb-pill-neutral">
                                            <i class="bi bi-check2"></i>
                                            Set
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <div class="dropdown">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-light border cyb-table-action-button"
                                            data-bs-toggle="dropdown"
                                            data-bs-boundary="viewport"
                                            aria-expanded="false"
                                            aria-label="Open admin user actions"
                                        >
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a
                                                    href="{{ route('admin-users.show', $adminUser) }}"
                                                    class="dropdown-item d-flex align-items-center gap-2"
                                                >
                                                    <i class="bi bi-eye"></i>
                                                    View Details
                                                </a>
                                            </li>

                                            <li>
                                                <a
                                                    href="{{ route('admin-users.edit', $adminUser) }}"
                                                    class="dropdown-item d-flex align-items-center gap-2"
                                                >
                                                    <i class="bi bi-pencil-square"></i>
                                                    Edit Account
                                                </a>
                                            </li>

                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>

                                            @if ($adminUser->active)
                                                <li>
                                                    <form
                                                        method="POST"
                                                        action="{{ route('admin-users.deactivate', $adminUser) }}"
                                                        onsubmit="return confirm('Deactivate this admin account?');"
                                                    >
                                                        @csrf
                                                        @method('PATCH')

                                                        <button
                                                            type="submit"
                                                            class="dropdown-item d-flex align-items-center gap-2 text-warning"
                                                        >
                                                            <i class="bi bi-pause-circle"></i>
                                                            Deactivate
                                                        </button>
                                                    </form>
                                                </li>
                                            @else
                                                <li>
                                                    <form
                                                        method="POST"
                                                        action="{{ route('admin-users.activate', $adminUser) }}"
                                                        onsubmit="return confirm('Activate this admin account?');"
                                                    >
                                                        @csrf
                                                        @method('PATCH')

                                                        <button
                                                            type="submit"
                                                            class="dropdown-item d-flex align-items-center gap-2 text-success"
                                                        >
                                                            <i class="bi bi-play-circle"></i>
                                                            Activate
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif

                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>

                                            <li>
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin-users.destroy', $adminUser) }}"
                                                    onsubmit="return confirm('Are you sure you want to delete this admin user?');"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                                    >
                                                        <i class="bi bi-trash"></i>
                                                        Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="p-0"
                                >
                                    <div class="cyb-empty-state">
                                        <div class="cyb-empty-state-icon">
                                            <i class="bi bi-people"></i>
                                        </div>

                                        <h3 class="cyb-empty-state-title">
                                            No admin users found
                                        </h3>

                                        <p class="cyb-empty-state-text mb-3">
                                            Create an administrator account to start
                                            assigning system access.
                                        </p>

                                        <a
                                            href="{{ route('admin-users.create') }}"
                                            class="btn btn-primary"
                                        >
                                            <i class="bi bi-person-plus me-1"></i>
                                            Create Admin User
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="cyb-pagination-footer">
                <div class="cyb-pagination-summary">
                    @if ($adminUsers->total() > 0)
                        Showing
                        <strong>{{ $adminUsers->firstItem() }}</strong>
                        to
                        <strong>{{ $adminUsers->lastItem() }}</strong>
                        of
                        <strong>{{ $adminUsers->total() }}</strong>
                        admin users
                    @else
                        No records to display
                    @endif
                </div>

                <div>
                    {{ $adminUsers->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /*
             * Page-specific overrides only.
             * Shared card/table/pill/empty-state styles live in
             * components.css and tables.css.
             */

            .admin-users-table {
                min-width: 900px;
            }

            .admin-users-table td:nth-child(1) {
                min-width: 230px;
            }

            .admin-users-table td:nth-child(2) {
                min-width: 130px;
            }

            .admin-users-table td:nth-child(3) {
                min-width: 150px;
            }

            .admin-users-table td:nth-child(4),
            .admin-users-table td:nth-child(5) {
                min-width: 120px;
            }

            .admin-users-table td:last-child {
                width: 80px;
            }
        </style>
    @endpush
</x-app-layout>