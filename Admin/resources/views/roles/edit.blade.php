<x-app-layout>
    <x-slot name="header">
        Edit Role
    </x-slot>

    <x-slot name="subheader">
        Update {{ $role->name }} details and assigned permissions.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-pencil-square"></i>
    </x-slot>

    <x-slot name="headerActions">
        <a href="{{ route('roles.index') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-4 py-2 text-sm font-semibold text-[var(--cyb-text)] shadow-sm transition hover:bg-[var(--cyb-primary-soft)]">
            <i class="bi bi-arrow-left"></i>
            Back to Roles
        </a>
    </x-slot>

    <div class="cyb-page-card overflow-hidden">

        {{-- Card Toolbar --}}
        <div class="flex flex-col gap-3 border-b border-[var(--cyb-border)] bg-white px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <span class="cyb-badge-soft">
                    <i class="bi bi-person-badge"></i>
                    Editing role
                </span>

                <span class="cyb-chip cyb-chip-role">
                    <i class="bi bi-shield-lock"></i>
                    {{ $role->name }}
                </span>

                @if ($role->is_protected)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                        <i class="bi bi-lock-fill"></i>
                        Protected
                    </span>
                @endif
            </div>

            <span class="text-sm text-[var(--cyb-muted)]">
                Review access carefully before saving changes.
            </span>
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
                            This role is protected to avoid system lockout. Its name and permissions are preserved.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="p-5">
            <form method="POST" action="{{ route('roles.update', $role) }}">
                @csrf
                @method('PUT')

                @include('roles.partials.form', [
                    'role' => $role,
                    'permissions' => $permissions,
                    'selectedPermissionIds' => old('permission_ids', $selectedPermissionIds),
                    'buttonText' => 'Update Role',
                ])
            </form>
        </div>

    </div>
</x-app-layout>