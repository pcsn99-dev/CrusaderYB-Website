<x-app-layout>
    @php
        $rolesCount = method_exists($roles, 'total')
            ? $roles->total()
            : $roles->count();

        $totalPermissions = $roles->sum('permissions_count');
        $totalAssignedUsers = $roles->sum('users_count');
    @endphp

    <x-slot name="header">
        Role Management
    </x-slot>

    <x-slot name="subheader">
        Manage administrator roles and control access to CYB features.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-person-badge-fill"></i>
    </x-slot>

    <x-slot name="headerActions">
        <a
            href="{{ route('roles.create') }}"
            class="btn btn-primary d-inline-flex align-items-center gap-2"
        >
            <i class="bi bi-plus-lg"></i>
            Create Role
        </a>
    </x-slot>

    <div class="roles-page">

        {{-- Summary --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="roles-summary-card">
                    <div class="roles-summary-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>

                    <div>
                        <div class="roles-summary-value">
                            {{ number_format($rolesCount) }}
                        </div>

                        <div class="roles-summary-label">
                            Roles
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="roles-summary-card">
                    <div class="roles-summary-icon">
                        <i class="bi bi-key"></i>
                    </div>

                    <div>
                        <div class="roles-summary-value">
                            {{ number_format($totalPermissions) }}
                        </div>

                        <div class="roles-summary-label">
                            Assigned Permissions
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="roles-summary-card">
                    <div class="roles-summary-icon">
                        <i class="bi bi-people"></i>
                    </div>

                    <div>
                        <div class="roles-summary-value">
                            {{ number_format($totalAssignedUsers) }}
                        </div>

                        <div class="roles-summary-label">
                            Assigned Users
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main card --}}
        <div class="card roles-card">
            <div class="card-header roles-toolbar">
                <div>
                    <h2 class="roles-section-title">
                        Roles
                    </h2>

                    <p class="roles-section-description">
                        Define access groups and assign permissions to administrators.
                    </p>
                </div>

                <div class="roles-search">
                    <label
                        for="roles-search"
                        class="visually-hidden"
                    >
                        Search roles
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>

                        <input
                            id="roles-search"
                            type="search"
                            class="form-control"
                            placeholder="Search role name, slug, or count"
                            autocomplete="off"
                        >
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table roles-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Slug</th>
                                <th class="text-center">
                                    Permissions
                                </th>
                                <th class="text-center">
                                    Users
                                </th>
                                <th class="text-end">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody id="roles-table-body">
                            @forelse ($roles as $role)
                                @php
                                    $roleSlug = $role->slug
                                        ?? \Illuminate\Support\Str::slug($role->name);

                                    $permissionsCount =
                                        $role->permissions_count ?? 0;

                                    $usersCount =
                                        $role->users_count ?? 0;
                                @endphp

                                <tr
                                    data-role-row
                                    data-search="{{ strtolower(
                                        $role->name . ' ' .
                                        $roleSlug . ' ' .
                                        $permissionsCount . ' permissions ' .
                                        $usersCount . ' users'
                                    ) }}"
                                >
                                    <td>
                                        <div class="role-name-wrap">
                                            <div class="role-icon">
                                                <i class="bi bi-person-badge"></i>
                                            </div>

                                            <div class="role-text">
                                                <div class="role-name">
                                                    {{ $role->name }}
                                                </div>

                                                <div class="role-description">
                                                    Access group for administrator permissions
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="role-slug">
                                            {{ $roleSlug }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <span class="count-badge count-badge-warning">
                                            {{ $permissionsCount }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <span class="count-badge count-badge-info">
                                            {{ $usersCount }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-light border role-actions-button"
                                                data-bs-toggle="dropdown"
                                                data-bs-boundary="viewport"
                                                aria-expanded="false"
                                                aria-label="Open role actions"
                                            >
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <a
                                                        href="{{ route('roles.show', $role) }}"
                                                        class="dropdown-item d-flex align-items-center gap-2"
                                                    >
                                                        <i class="bi bi-eye"></i>
                                                        View details
                                                    </a>
                                                </li>

                                                <li>
                                                    <a
                                                        href="{{ route('roles.edit', $role) }}"
                                                        class="dropdown-item d-flex align-items-center gap-2"
                                                    >
                                                        <i class="bi bi-pencil-square"></i>
                                                        Edit role
                                                    </a>
                                                </li>

                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>

                                                <li>
                                                    <form
                                                        method="POST"
                                                        action="{{ route('roles.destroy', $role) }}"
                                                        onsubmit="return confirm('Are you sure you want to delete this role?');"
                                                    >
                                                        @csrf
                                                        @method('DELETE')

                                                        <button
                                                            type="submit"
                                                            class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                                        >
                                                            <i class="bi bi-trash"></i>
                                                            Delete role
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
                                        colspan="5"
                                        class="text-center py-5"
                                    >
                                        <div class="roles-empty-state">
                                            <div class="roles-empty-icon">
                                                <i class="bi bi-person-badge"></i>
                                            </div>

                                            <h3 class="roles-empty-title">
                                                No roles found
                                            </h3>

                                            <p class="roles-empty-text">
                                                Create roles to organize administrator
                                                access and permissions.
                                            </p>

                                            <a
                                                href="{{ route('roles.create') }}"
                                                class="btn btn-primary"
                                            >
                                                <i class="bi bi-plus-lg me-1"></i>
                                                Create Role
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse

                            <tr
                                id="roles-no-results-row"
                                class="d-none"
                            >
                                <td
                                    colspan="5"
                                    class="text-center py-5"
                                >
                                    <div class="roles-no-results">
                                        <i class="bi bi-search"></i>
                                        No roles match your search.
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @if (method_exists($roles, 'links'))
                <div class="card-footer roles-footer">
                    <div class="roles-pagination-summary">
                        @if ($roles->total() > 0)
                            Showing
                            <strong>{{ $roles->firstItem() }}</strong>
                            to
                            <strong>{{ $roles->lastItem() }}</strong>
                            of
                            <strong>{{ $roles->total() }}</strong>
                            roles
                        @else
                            No records to display
                        @endif
                    </div>

                    <div>
                        {{ $roles->withQueryString()->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            .roles-page {
                padding-bottom: 2rem;
            }

            .roles-summary-card {
                display: flex;
                min-height: 92px;
                align-items: center;
                gap: 0.9rem;
                padding: 1rem 1.1rem;
                border: 1px solid #e7eaed;
                border-radius: 0.75rem;
                background: #fff;
                box-shadow: 0 0.125rem 0.4rem rgba(0, 0, 0, 0.03);
            }

            .roles-summary-icon {
                display: flex;
                width: 42px;
                height: 42px;
                flex: 0 0 42px;
                align-items: center;
                justify-content: center;
                border-radius: 0.65rem;
                background: #f1f4f7;
                color: #495057;
                font-size: 1.05rem;
            }

            .roles-summary-value {
                color: #212529;
                font-size: 1.35rem;
                font-weight: 650;
                line-height: 1.1;
            }

            .roles-summary-label {
                margin-top: 0.2rem;
                color: #6c757d;
                font-size: 0.78rem;
            }

            .roles-card {
                overflow: hidden;
                border: 1px solid #e7eaed;
                border-radius: 0.75rem;
                box-shadow: 0 0.125rem 0.4rem rgba(0, 0, 0, 0.03);
            }

            .roles-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 1rem 1.2rem;
                border-bottom: 1px solid #edf0f2;
                background: #fff;
            }

            .roles-section-title {
                margin: 0;
                color: #212529;
                font-size: 1rem;
                font-weight: 600;
            }

            .roles-section-description {
                margin: 0.2rem 0 0;
                color: #6c757d;
                font-size: 0.79rem;
            }

            .roles-search {
                width: min(100%, 340px);
            }

            .roles-search .input-group-text {
                background: #f8f9fa;
                color: #6c757d;
            }

            .roles-search .form-control,
            .roles-search .input-group-text {
                min-height: 40px;
            }

            .roles-table thead th {
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

            .roles-table tbody td {
                padding: 1rem;
                border-color: #edf0f2;
            }

            .roles-table tbody tr:hover {
                background: #fafbfc;
            }

            .role-name-wrap {
                display: flex;
                align-items: center;
                gap: 0.8rem;
            }

            .role-icon {
                display: flex;
                width: 38px;
                height: 38px;
                flex: 0 0 38px;
                align-items: center;
                justify-content: center;
                border-radius: 0.6rem;
                background: #eef2f6;
                color: #495057;
                font-size: 0.95rem;
            }

            .role-text {
                min-width: 0;
            }

            .role-name {
                color: #212529;
                font-size: 0.9rem;
                font-weight: 600;
            }

            .role-description {
                margin-top: 0.2rem;
                color: #6c757d;
                font-size: 0.75rem;
            }

            .role-slug {
                display: inline-block;
                padding: 0.25rem 0.5rem;
                border-radius: 0.4rem;
                background: #f1f3f5;
                color: #5b636b;
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                font-size: 0.75rem;
            }

            .count-badge {
                display: inline-flex;
                min-width: 34px;
                height: 26px;
                align-items: center;
                justify-content: center;
                padding: 0 0.55rem;
                border-radius: 999px;
                font-size: 0.75rem;
                font-weight: 600;
            }

            .count-badge-warning {
                background: #fff4d8;
                color: #87620f;
            }

            .count-badge-info {
                background: #e8f3f8;
                color: #24657b;
            }

            .role-actions-button {
                width: 36px;
                height: 36px;
                padding: 0;
            }

            .roles-empty-state {
                max-width: 420px;
                margin: 0 auto;
            }

            .roles-empty-icon {
                display: flex;
                width: 52px;
                height: 52px;
                margin: 0 auto;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: #f1f3f5;
                color: #6c757d;
                font-size: 1.25rem;
            }

            .roles-empty-title {
                margin: 1rem 0 0.3rem;
                color: #343a40;
                font-size: 1rem;
                font-weight: 600;
            }

            .roles-empty-text {
                margin: 0 0 1rem;
                color: #6c757d;
                font-size: 0.84rem;
            }

            .roles-no-results {
                color: #6c757d;
                font-size: 0.85rem;
            }

            .roles-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 0.85rem 1.2rem;
                background: #fff;
            }

            .roles-pagination-summary {
                color: #6c757d;
                font-size: 0.8rem;
            }

            @media (max-width: 767.98px) {
                .roles-toolbar {
                    align-items: stretch;
                    flex-direction: column;
                }

                .roles-search {
                    width: 100%;
                }

                .roles-footer {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .roles-table {
                    min-width: 760px;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const searchInput =
                    document.getElementById('roles-search');

                const rows = Array.from(
                    document.querySelectorAll('[data-role-row]')
                );

                const noResultsRow =
                    document.getElementById(
                        'roles-no-results-row'
                    );

                if (!searchInput || !noResultsRow) {
                    return;
                }

                searchInput.addEventListener('input', () => {
                    const value =
                        searchInput.value
                            .trim()
                            .toLowerCase();

                    let visibleCount = 0;

                    rows.forEach((row) => {
                        const searchValue =
                            row.dataset.search ?? '';

                        const matches =
                            searchValue.includes(value);

                        row.classList.toggle(
                            'd-none',
                            !matches
                        );

                        if (matches) {
                            visibleCount++;
                        }
                    });

                    noResultsRow.classList.toggle(
                        'd-none',
                        visibleCount !== 0
                    );
                });
            });
        </script>
    @endpush
</x-app-layout>