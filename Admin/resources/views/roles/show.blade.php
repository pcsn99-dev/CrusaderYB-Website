<x-app-layout>
    @php
        $permissionsCount = $role->permissions->count();
        $usersCount = $role->users->count();

        $systemPermissions = $role->permissions->filter(function ($permission) {
            return str_contains($permission->slug, 'dashboard')
                || str_contains($permission->slug, 'roles')
                || str_contains($permission->slug, 'admin-users');
        });

        $writeupPermissions = $role->permissions->filter(function ($permission) {
            return str_contains($permission->slug, 'writeups');
        });

        $otherPermissions = $role->permissions->reject(function ($permission) use ($systemPermissions, $writeupPermissions) {
            return $systemPermissions->contains('id', $permission->id)
                || $writeupPermissions->contains('id', $permission->id);
        });

        $permissionGroups = [
            'System Management' => [
                'permissions' => $systemPermissions,
                'icon' => 'bi-shield-lock',
                'chip' => 'cyb-chip-role',
            ],
            'Writeup Workflow' => [
                'permissions' => $writeupPermissions,
                'icon' => 'bi-pencil-square',
                'chip' => 'cyb-chip-username',
            ],
            'Other Permissions' => [
                'permissions' => $otherPermissions,
                'icon' => 'bi-three-dots',
                'chip' => 'cyb-chip-neutral',
            ],
        ];
    @endphp

    <x-slot name="header">
        Role Details
    </x-slot>

    <x-slot name="subheader">
        View {{ $role->name }} permissions and assigned admin users.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-person-badge-fill"></i>
    </x-slot>

    <x-slot name="headerActions">
        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center">
            <a href="{{ route('roles.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-4 py-2 text-sm font-semibold text-[var(--cyb-text)] shadow-sm transition hover:bg-[var(--cyb-primary-soft)]">
                <i class="bi bi-arrow-left"></i>
                Back to Roles
            </a>

            <a href="{{ route('roles.edit', $role) }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--cyb-primary)] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[var(--cyb-primary-dark)]">
                <i class="bi bi-pencil-square"></i>
                Edit Role
            </a>
        </div>
    </x-slot>

    <div class="space-y-5">

        {{-- Role Summary --}}
        <div class="cyb-page-card overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-[var(--cyb-border)] bg-white px-5 py-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="cyb-badge-soft">
                            <i class="bi bi-person-badge"></i>
                            {{ $role->name }}
                        </span>

                        @if ($role->is_protected)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                <i class="bi bi-lock-fill"></i>
                                Protected
                            </span>
                        @endif
                    </div>

                    <p class="mb-3 text-sm text-[var(--cyb-muted)]">
                        {{ $role->description ?: 'No description provided.' }}
                    </p>

                    <span class="cyb-chip cyb-chip-username">
                        <i class="bi bi-hash"></i>
                        {{ $role->slug }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:min-w-64">
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                        <div class="text-xs font-bold uppercase tracking-wider text-amber-700">
                            Permissions
                        </div>
                        <div class="mt-1 text-2xl font-bold text-amber-800">
                            {{ $permissionsCount }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3">
                        <div class="text-xs font-bold uppercase tracking-wider text-sky-700">
                            Users
                        </div>
                        <div class="mt-1 text-2xl font-bold text-sky-800">
                            {{ $usersCount }}
                        </div>
                    </div>
                </div>
            </div>

            @if ($role->is_protected)
                <div class="border-b border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-800">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-info-circle-fill mt-0.5"></i>

                        <div>
                            <p class="mb-1 font-semibold">
                                Protected system role
                            </p>

                            <p class="mb-0">
                                This role is protected to avoid accidental system lockout. Be careful when reviewing or changing this access group.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Permissions --}}
        <div class="cyb-page-card overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-[var(--cyb-border)] bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="cyb-badge-soft">
                        <i class="bi bi-key-fill"></i>
                        Permissions
                    </span>

                    <span class="text-sm text-[var(--cyb-muted)]">
                        Access granted to this role
                    </span>
                </div>

                <span class="cyb-chip cyb-chip-role">
                    {{ $permissionsCount }} {{ $permissionsCount === 1 ? 'permission' : 'permissions' }}
                </span>
            </div>

            <div class="p-5">
                @if ($permissionsCount)
                    <div class="space-y-4">
                        @foreach ($permissionGroups as $groupName => $group)
                            @php
                                $groupPermissions = $group['permissions'];
                            @endphp

                            @if ($groupPermissions->count())
                                <div class="overflow-hidden rounded-xl border border-[var(--cyb-border)]">
                                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[var(--cyb-border)] bg-[var(--cyb-primary-soft)] px-4 py-3">
                                        <span class="cyb-chip {{ $group['chip'] }}">
                                            <i class="bi {{ $group['icon'] }}"></i>
                                            {{ $groupName }}
                                        </span>

                                        <span class="text-xs font-semibold text-[var(--cyb-muted)]">
                                            {{ $groupPermissions->count() }} {{ $groupPermissions->count() === 1 ? 'item' : 'items' }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 divide-y divide-[var(--cyb-border)] md:grid-cols-2 md:divide-x md:divide-y-0">
                                        @foreach ($groupPermissions as $permission)
                                            <div class="p-4">
                                                <div class="mb-2 font-semibold text-[var(--cyb-text)]">
                                                    {{ $permission->name }}
                                                </div>

                                                <span class="cyb-chip cyb-chip-neutral">
                                                    <i class="bi bi-hash"></i>
                                                    {{ $permission->slug }}
                                                </span>

                                                @if ($permission->description)
                                                    <p class="mb-0 mt-2 text-sm text-[var(--cyb-muted)]">
                                                        {{ $permission->description }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="cyb-empty-state">
                        <div class="cyb-empty-state-icon">
                            <i class="bi bi-key"></i>
                        </div>

                        <h3 class="mb-1 text-base font-semibold text-[var(--cyb-text)]">
                            No permissions assigned
                        </h3>

                        <p class="mb-0 text-sm text-[var(--cyb-muted)]">
                            This role currently does not grant access to any module.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Assigned Users --}}
        <div class="cyb-page-card overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-[var(--cyb-border)] bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="cyb-badge-soft">
                        <i class="bi bi-people-fill"></i>
                        Assigned Users
                    </span>

                    <span class="text-sm text-[var(--cyb-muted)]">
                        Admin accounts currently using this role
                    </span>
                </div>

                <span class="cyb-chip cyb-chip-username">
                    {{ $usersCount }} {{ $usersCount === 1 ? 'user' : 'users' }}
                </span>
            </div>

            <div class="p-5">
                @if ($usersCount)
                    <div class="overflow-x-auto rounded-xl border border-[var(--cyb-border)]">
                        <table class="min-w-full divide-y divide-[var(--cyb-border)] bg-white text-sm">
                            <thead class="bg-[var(--cyb-primary-soft)]">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                        User
                                    </th>

                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                        Email
                                    </th>

                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                        Type
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-[var(--cyb-border)] bg-white">
                                @foreach ($role->users as $user)
                                    <tr class="transition hover:bg-[var(--cyb-primary-soft)]/60">
                                        <td class="px-4 py-4 align-middle">
                                            <div class="flex items-center gap-3">
                                                <x-avatar :name="$user->name" size="34" />

                                                <div class="font-semibold text-[var(--cyb-text)]">
                                                    {{ $user->name }}
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-4 py-4 align-middle text-[var(--cyb-muted)]">
                                            {{ $user->email }}
                                        </td>

                                        <td class="px-4 py-4 align-middle">
                                            <span class="cyb-chip cyb-chip-neutral">
                                                {{ ucfirst($user->type ?? 'N/A') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="cyb-empty-state">
                        <div class="cyb-empty-state-icon">
                            <i class="bi bi-person-x"></i>
                        </div>

                        <h3 class="mb-1 text-base font-semibold text-[var(--cyb-text)]">
                            No assigned users
                        </h3>

                        <p class="mb-0 text-sm text-[var(--cyb-muted)]">
                            No admin accounts are currently assigned to this role.
                        </p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>