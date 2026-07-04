@php
    $user = Auth::user();

    $canViewDashboard = $user?->hasPermission('view-admin-dashboard');
    $canManageRoles = $user?->hasPermission('manage-roles');
    $canManageAdminUsers = $user?->hasPermission('manage-admin-users');
    $canViewWriteups = $user?->hasPermission('view-writeups');

    $canAccessUserManagement = $canManageRoles || $canManageAdminUsers;

    $userManagementActive = request()->routeIs('roles.*') ||
                            request()->routeIs('admin-users.*');

    $writeupsActive = request()->routeIs('writeups.*');
@endphp

<aside class="app-sidebar shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="brand-link cyb-brand-link">
            <span class="cyb-brand-logo">
                <i class="bi bi-journal-bookmark-fill"></i>
            </span>

            <span class="brand-text">
                CYB Admin Panel
            </span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
                @if ($canViewDashboard)
                    <li class="nav-item">
                        <x-nav-link
                            :href="route('dashboard')"
                            :active="request()->routeIs('dashboard')"
                            icon="bi bi-columns-gap"
                        >
                            Dashboard
                        </x-nav-link>
                    </li>
                @endif

                @if ($canAccessUserManagement)
                    <li class="nav-item {{ $userManagementActive ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $userManagementActive ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-fill-gear"></i>

                            <p>
                                User Management
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            @if ($canManageRoles)
                                <li class="nav-item">
                                    <x-nav-link
                                        :href="route('roles.index')"
                                        :active="request()->routeIs('roles.*')"
                                        icon="bi bi-person-badge"
                                        :child="true"
                                    >
                                        Roles
                                    </x-nav-link>
                                </li>
                            @endif

                            @if ($canManageAdminUsers)
                                <li class="nav-item">
                                    <x-nav-link
                                        :href="route('admin-users.index')"
                                        :active="request()->routeIs('admin-users.*')"
                                        icon="bi bi-people-fill"
                                        :child="true"
                                    >
                                        Admin Users
                                    </x-nav-link>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if ($canViewWriteups)
                    <li class="nav-item {{ $writeupsActive ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $writeupsActive ? 'active' : '' }}">
                            <i class="nav-icon bi bi-pencil-square"></i>

                            <p>
                                Writeups
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <x-nav-link
                                    :href="route('writeups.review.index')"
                                    :active="request()->routeIs('writeups.review.*')"
                                    icon="bi bi-check2-square"
                                    :child="true"
                                >
                                    Review Queue
                                </x-nav-link>
                            </li>

                            <li class="nav-item">
                                <x-nav-link
                                    :href="route('writeups.generic.index')"
                                    :active="request()->routeIs('writeups.generic.*')"
                                    icon="bi bi-card-text"
                                    :child="true"
                                >
                                    Generic Writeups
                                </x-nav-link>
                            </li>

                            <li class="nav-item">
                                <x-nav-link
                                    :href="route('writeups.bulk.index')"
                                    :active="request()->routeIs('writeups.bulk.*')"
                                    icon="bi bi-plus-square"
                                    :child="true"
                                >
                                    Bulk Create
                                </x-nav-link>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>