<x-app-layout>
    <x-slot name="header">
        Create Role
    </x-slot>

    <x-slot name="subheader">
        Create a role and choose the permissions that should belong to it.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-person-badge-fill"></i>
    </x-slot>

    <x-slot name="headerActions">
        <a href="{{ route('roles.index') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-4 py-2 text-sm font-semibold text-[var(--cyb-text)] shadow-sm transition hover:bg-[var(--cyb-primary-soft)]">
            <i class="bi bi-arrow-left"></i>
            Back to Roles
        </a>
    </x-slot>

    <div class="cyb-page-card overflow-hidden">
        <div class="border-b border-[var(--cyb-border)] bg-white px-5 py-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="cyb-badge-soft">
                    <i class="bi bi-plus-circle"></i>
                    New role
                </span>

                <span class="text-sm text-[var(--cyb-muted)]">
                    Define access by selecting one or more permissions.
                </span>
            </div>
        </div>

        <div class="p-5">
            <form method="POST" action="{{ route('roles.store') }}">
                @csrf

                @include('roles.partials.form', [
                    'role' => null,
                    'permissions' => $permissions,
                    'selectedPermissionIds' => old('permission_ids', []),
                    'buttonText' => 'Create Role',
                ])
            </form>
        </div>
    </div>
</x-app-layout>