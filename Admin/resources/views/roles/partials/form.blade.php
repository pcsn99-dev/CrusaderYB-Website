@php
    $selectedPermissionIds = collect($selectedPermissionIds ?? [])
        ->map(fn ($id) => (int) $id)
        ->toArray();

    $systemPermissions = $permissions->filter(function ($permission) {
        return str_contains($permission->slug, 'dashboard')
            || str_contains($permission->slug, 'roles')
            || str_contains($permission->slug, 'admin-users');
    });

    $writeupPermissions = $permissions->filter(function ($permission) {
        return str_contains($permission->slug, 'writeups');
    });

    $otherPermissions = $permissions->reject(function ($permission) use ($systemPermissions, $writeupPermissions) {
        return $systemPermissions->contains('id', $permission->id)
            || $writeupPermissions->contains('id', $permission->id);
    });

    $permissionGroups = [
        'System Management' => [
            'permissions' => $systemPermissions,
            'icon' => 'bi-shield-lock',
            'chip' => 'cyb-chip-role',
            'description' => 'Dashboard, roles, and admin user access.',
        ],
        'Writeup Workflow' => [
            'permissions' => $writeupPermissions,
            'icon' => 'bi-pencil-square',
            'chip' => 'cyb-chip-username',
            'description' => 'Writeup review, creation, and management access.',
        ],
        'Other Permissions' => [
            'permissions' => $otherPermissions,
            'icon' => 'bi-three-dots',
            'chip' => 'cyb-chip-neutral',
            'description' => 'Additional system permissions not grouped above.',
        ],
    ];

    $isProtected = (bool) ($role?->is_protected ?? false);
@endphp

<div class="space-y-6">

    {{-- Role Details --}}
    <div>
        <h2 class="mb-1 text-base font-semibold text-[var(--cyb-primary)]">
            Role Details
        </h2>

        <p class="mb-0 text-sm text-[var(--cyb-muted)]">
            Give this role a clear name and description so staff can understand what it is for.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div>
            <label for="name" class="mb-1 block text-sm font-semibold text-[var(--cyb-text)]">
                Role Name
            </label>

            <input type="text"
                   name="name"
                   id="name"
                   value="{{ old('name', $role?->name) }}"
                   required
                   @readonly($isProtected)
                   placeholder="Example: Editorial Staff"
                   class="@error('name') border-red-300 focus:border-red-500 focus:ring-red-200 @else border-[var(--cyb-border)] focus:border-[var(--cyb-primary)] focus:ring-[var(--cyb-primary)]/20 @enderror block w-full rounded-lg px-3 py-2 text-sm shadow-sm read-only:bg-slate-100">

            @error('name')
                <p class="mt-2 flex items-center gap-1 text-sm text-red-600">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror

            @if ($isProtected)
                <p class="mt-2 text-xs text-[var(--cyb-muted)]">
                    Protected role names cannot be changed.
                </p>
            @endif
        </div>

        <div>
            <label for="description" class="mb-1 block text-sm font-semibold text-[var(--cyb-text)]">
                Description
            </label>

            <textarea name="description"
                      id="description"
                      rows="3"
                      placeholder="Briefly describe what this role is allowed to do."
                      class="@error('description') border-red-300 focus:border-red-500 focus:ring-red-200 @else border-[var(--cyb-border)] focus:border-[var(--cyb-primary)] focus:ring-[var(--cyb-primary)]/20 @enderror block w-full rounded-lg px-3 py-2 text-sm shadow-sm">{{ old('description', $role?->description) }}</textarea>

            @error('description')
                <p class="mt-2 flex items-center gap-1 text-sm text-red-600">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>

    {{-- Permissions --}}
    <div class="rounded-xl border border-[var(--cyb-border)] bg-white">
        <div class="flex flex-col gap-3 border-b border-[var(--cyb-border)] px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="mb-1 text-base font-semibold text-[var(--cyb-primary)]">
                    Permissions
                </h3>

                <p class="mb-0 text-sm text-[var(--cyb-muted)]">
                    Select what this role can access inside the admin panel.
                </p>
            </div>

            @if (! $isProtected)
                <div class="flex items-center gap-2">
                    <button type="button"
                            data-permission-toggle="all"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-3 py-2 text-xs font-semibold text-[var(--cyb-primary)] shadow-sm transition hover:bg-[var(--cyb-primary-soft)]">
                        <i class="bi bi-check2-square"></i>
                        Select all
                    </button>

                    <button type="button"
                            data-permission-toggle="none"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-3 py-2 text-xs font-semibold text-[var(--cyb-muted)] shadow-sm transition hover:bg-slate-50">
                        <i class="bi bi-square"></i>
                        Clear
                    </button>
                </div>
            @endif
        </div>

        @error('permission_ids')
            <div class="border-b border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $message }}
            </div>
        @enderror

        <div class="space-y-4 p-4">
            @foreach ($permissionGroups as $groupName => $group)
                @php
                    $groupPermissions = $group['permissions'];
                @endphp

                @if ($groupPermissions->count())
                    <div class="overflow-hidden rounded-xl border border-[var(--cyb-border)]">
                        <div class="flex flex-col gap-2 border-b border-[var(--cyb-border)] bg-[var(--cyb-primary-soft)] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-2">
                                <span class="cyb-chip {{ $group['chip'] }}">
                                    <i class="bi {{ $group['icon'] }}"></i>
                                    {{ $groupName }}
                                </span>

                                <span class="text-xs text-[var(--cyb-muted)]">
                                    {{ $groupPermissions->count() }} {{ $groupPermissions->count() === 1 ? 'permission' : 'permissions' }}
                                </span>
                            </div>

                            <p class="mb-0 text-xs text-[var(--cyb-muted)]">
                                {{ $group['description'] }}
                            </p>
                        </div>

                        <div class="divide-y divide-[var(--cyb-border)]">
                            @foreach ($groupPermissions as $permission)
                                <label class="flex cursor-pointer items-start gap-3 px-4 py-3 transition hover:bg-[var(--cyb-primary-soft)]/70">
                                    <input type="checkbox"
                                           name="permission_ids[]"
                                           value="{{ $permission->id }}"
                                           class="permission-checkbox mt-1 rounded border-[var(--cyb-border)] text-[var(--cyb-primary)] shadow-sm focus:ring-[var(--cyb-primary)]/30"
                                           @checked(in_array($permission->id, $selectedPermissionIds))
                                           @disabled($isProtected)>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-semibold text-[var(--cyb-text)]">
                                                {{ $permission->name }}
                                            </span>

                                            <span class="cyb-chip cyb-chip-neutral">
                                                <i class="bi bi-hash"></i>
                                                {{ $permission->slug }}
                                            </span>
                                        </div>

                                        @if ($permission->description)
                                            <p class="mb-0 mt-1 text-sm text-[var(--cyb-muted)]">
                                                {{ $permission->description }}
                                            </p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        @if ($isProtected)
            <div class="border-t border-[var(--cyb-border)] bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                <i class="bi bi-lock-fill me-1"></i>
                This is a protected role. Its permissions cannot be manually changed here.
            </div>
        @endif
    </div>

    {{-- Actions --}}
    <div class="flex flex-col-reverse gap-3 border-t border-[var(--cyb-border)] pt-5 sm:flex-row sm:items-center sm:justify-end">
        <a href="{{ route('roles.index') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-4 py-2 text-sm font-semibold text-[var(--cyb-text)] shadow-sm transition hover:bg-[var(--cyb-primary-soft)]">
            Cancel
        </a>

        <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--cyb-primary)] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[var(--cyb-primary-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)] focus:ring-offset-2">
            <i class="bi bi-check2-circle"></i>
            {{ $buttonText }}
        </button>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-permission-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const shouldCheck = button.dataset.permissionToggle === 'all';

                    document.querySelectorAll('.permission-checkbox').forEach((checkbox) => {
                        if (!checkbox.disabled) {
                            checkbox.checked = shouldCheck;
                        }
                    });
                });
            });
        });
    </script>
@endpush