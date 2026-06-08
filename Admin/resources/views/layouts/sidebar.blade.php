@php
    $user = Auth::user();

    // add new permission checks when adding more modules
    $canViewDashboard = $user?->hasPermission('view-admin-dashboard');
    $canManageRoles = $user?->hasPermission('manage-roles');
    $canManageAdminUsers = $user?->hasPermission('manage-admin-users');
    $canViewWriteups = $user?->hasPermission('view-writeups');

    // add  if a module has multiple permissions but should appear as one nav item
    // $canAccessSystemManagement = $canManageRoles || $canManageAdminUsers;
@endphp
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="#" class="brand-link">
            <span class="brand-text fw-light">My Dashboard</span>
        </a>
    </div>
    <div class="sidebar-wrapper">

    <nav class="mt-2">
        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
            @if ($canViewDashboard)
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="bi bi-columns-gap">
                    Dashboard
                </x-nav-link>
            @endif

            @if ($canManageRoles)
                <x-nav-link :href="route('roles.index')" :active="request()->routeIs('roles.*')" icon="bi bi-person-badge">
                    Roles
                </x-nav-link>
            @endif

             @if ($canManageAdminUsers)
                <x-nav-link :href="route('admin-users.index')" :active="request()->routeIs('admin-users.*')" icon="bi bi-people-fill">
                    Admin Users
                </x-nav-link>
            @endif

            {{-- @if ( $canViewWriteups)
                <x-nav-link :href="route('writeups.index')" :active="request()->routeIs('writeups.*')" icon="bi bi-people-fill">
                    WriteUp
                </x-nav-link>
            @endif --}}



        </ul>
    </nav>
    </div>
</aside>