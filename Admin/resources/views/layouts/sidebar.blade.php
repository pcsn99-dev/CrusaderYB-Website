@php
    $user = Auth::user();

    // add new permission checks when adding more modules
    $canViewDashboard = $user?->hasPermission('view-admin-dashboard');
    $canManageRoles = $user?->hasPermission('manage-roles');
    $canManageAdminUsers = $user?->hasPermission('manage-admin-users');
    $canViewWriteups = $user?->hasPermission('view-writeups');

    $userManagementActive = request()->routeIs('roles.*') ||
                            request()->routeIs('admin-users.*');

    // add  if a module has multiple permissions but should appear as one nav item
    // $canAccessSystemManagement = $canManageRoles || $canManageAdminUsers;
@endphp
<aside class="app-sidebar bg-primary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="#" class="brand-link">
            <span class="brand-text fw-light">My Dashboard</span>
        </a>
    </div>
    <div class="sidebar-wrapper">

    <nav class="mt-2">
        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
            @if ($canViewDashboard)
            <li class="nav-item">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="bi bi-columns-gap">
                    Dashboard
                </x-nav-link>
            </li>
            @endif

            <li class="nav-item ">
                <a href="#" class="nav-link {{ $userManagementActive ? 'active' : ''}}">
                  <i class="nav-icon bi-person-fill-gear"></i>
                  <p>
                    User Management
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>

                <ul class="nav nav-treeview">
                    @if ($canManageRoles)
                        <li class="nav-item">
                            <x-nav-link :href="route('roles.index')" :active="request()->routeIs('roles.*')" icon="bi bi-person-badge" :child="true">
                                Roles
                            </x-nav-link>
                        </li>
                    @endif

                    @if ($canManageAdminUsers)
                        <li class="nav-item">
                            <x-nav-link :href="route('admin-users.index')" :active="request()->routeIs('admin-users.*')" icon="bi bi-people-fill" :child="true">
                                Admin Users
                            </x-nav-link>
                        </li>
                    @endif
                </ul>
                    
            </li>



            @if ( $canViewWriteups)
                <x-nav-link :href="route('writeups.review.index')" :active="request()->routeIs('writeups.*')" icon="bi bi-people-fill">
                    WriteUp
                </x-nav-link>
            @endif



        </ul>
    </nav>
    </div>
</aside>