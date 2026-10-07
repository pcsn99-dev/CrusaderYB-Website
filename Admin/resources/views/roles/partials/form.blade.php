@php
    $selectedPermissionIds = collect($selectedPermissionIds ?? [])
        ->map(fn ($id) => (int) $id)
        ->toArray();

    $systemPermissions = $permissions->filter(function ($permission) {
        return str_contains($permission->slug, 'dashboard')
            || str_contains($permission->slug, 'roles')
            || str_contains($permission->slug, 'admin-users');
    });

    $studentPermissions = $permissions->filter(function ($permission) {
        return str_contains($permission->slug, 'student');
    });

    $writeupPermissions = $permissions->filter(function ($permission) {
        return str_contains($permission->slug, 'writeups');
    });

    $otherPermissions = $permissions->reject(function ($permission) use (
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

    $isProtected = (bool) ($role?->is_protected ?? false);
@endphp

<div class="role-form">

    {{-- Role Details --}}
    <section class="role-form-section">
        <div class="role-form-section-heading">
            <div>
                <h2 class="role-form-section-title">
                    Role Details
                </h2>

                <p class="role-form-section-description">
                    Give this role a clear name and description so administrators
                    can understand what it is intended for.
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <label
                    for="name"
                    class="form-label"
                >
                    Role Name
                    <span class="text-danger">*</span>
                </label>

                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name', $role?->name) }}"
                    required
                    @readonly($isProtected)
                    placeholder="Example: Editorial Staff"
                    class="form-control @error('name') is-invalid @enderror"
                >

                @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

                @if ($isProtected)
                    <div class="form-text">
                        <i class="bi bi-lock-fill me-1"></i>
                        Protected role names cannot be changed.
                    </div>
                @endif
            </div>

            <div class="col-12 col-lg-6">
                <label
                    for="description"
                    class="form-label"
                >
                    Description
                </label>

                <textarea
                    name="description"
                    id="description"
                    rows="3"
                    placeholder="Briefly describe what this role is allowed to do."
                    class="form-control @error('description') is-invalid @enderror"
                >{{ old('description', $role?->description) }}</textarea>

                @error('description')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>
    </section>

    {{-- Permissions --}}
    <section class="role-form-section">
        <div class="permissions-card">
            <div class="permissions-header">
                <div>
                    <h2 class="role-form-section-title">
                        Permissions
                    </h2>

                    <p class="role-form-section-description">
                        Select the areas and actions this role can access in the admin panel.
                    </p>
                </div>

                @if (! $isProtected)
                    <div class="permissions-header-actions">
                        <button
                            type="button"
                            data-permission-toggle="all"
                            class="btn btn-sm btn-outline-primary"
                        >
                            <i class="bi bi-check2-square me-1"></i>
                            Select All
                        </button>

                        <button
                            type="button"
                            data-permission-toggle="none"
                            class="btn btn-sm btn-light border"
                        >
                            <i class="bi bi-square me-1"></i>
                            Clear
                        </button>
                    </div>
                @endif
            </div>

            @error('permission_ids')
                <div class="alert alert-danger rounded-0 border-start-0 border-end-0 mb-0">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    {{ $message }}
                </div>
            @enderror

            <div class="permissions-body">
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

                            <div class="permission-list">
                                @foreach ($groupPermissions as $permission)
                                    <label
                                        class="permission-item {{ $isProtected ? 'permission-item-disabled' : '' }}"
                                        for="permission-{{ $permission->id }}"
                                    >
                                        <div class="form-check permission-check">
                                            <input
                                                type="checkbox"
                                                name="permission_ids[]"
                                                id="permission-{{ $permission->id }}"
                                                value="{{ $permission->id }}"
                                                class="form-check-input permission-checkbox"
                                                @checked(in_array($permission->id, $selectedPermissionIds))
                                                @disabled($isProtected)
                                            >
                                        </div>

                                        <div class="permission-content">
                                            <div class="permission-title-row">
                                                <span class="permission-name">
                                                    {{ $permission->name }}
                                                </span>

                                                <code class="permission-slug">
                                                    {{ $permission->slug }}
                                                </code>
                                            </div>

                                            @if ($permission->description)
                                                <p class="permission-description">
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
                <div class="protected-role-notice">
                    <i class="bi bi-lock-fill"></i>

                    <div>
                        <strong>Protected role</strong>

                        <div>
                            Its permissions cannot be manually changed from this form.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Form Actions --}}
    <div class="role-form-actions">
        <a
            href="{{ route('roles.index') }}"
            class="btn btn-light border"
        >
            Cancel
        </a>

        <button
            type="submit"
            class="btn btn-primary"
        >
            <i class="bi bi-check2-circle me-1"></i>
            {{ $buttonText }}
        </button>
    </div>
</div>

@push('styles')
    <style>
        .role-form {
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
        }

        .role-form-section {
            margin: 0;
        }

        .role-form-section-heading {
            margin-bottom: 1rem;
        }

        .role-form-section-title {
            margin: 0;
            color: #212529;
            font-size: 1rem;
            font-weight: 600;
        }

        .role-form-section-description {
            max-width: 720px;
            margin: 0.25rem 0 0;
            color: #6c757d;
            font-size: 0.82rem;
            line-height: 1.5;
        }

        .role-form .form-label {
            margin-bottom: 0.45rem;
            color: #343a40;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .role-form .form-control {
            min-height: 42px;
            border-color: #dfe3e7;
            font-size: 0.9rem;
        }

        .role-form textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .role-form .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.12);
        }

        .role-form .form-control[readonly] {
            background: #f5f6f7;
            color: #6c757d;
        }

        .role-form .form-text {
            margin-top: 0.45rem;
            color: #6c757d;
            font-size: 0.76rem;
        }

        .permissions-card {
            overflow: hidden;
            border: 1px solid #e7eaed;
            border-radius: 0.75rem;
            background: #fff;
        }

        .permissions-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.15rem;
            border-bottom: 1px solid #edf0f2;
            background: #fff;
        }

        .permissions-header-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .permissions-body {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding: 1rem;
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

        .permission-count {
            display: inline-flex;
            align-items: center;
            min-height: 22px;
            padding: 0.1rem 0.45rem;
            border-radius: 999px;
            background: #e9ecef;
            color: #687078;
            font-size: 0.68rem;
            font-weight: 600;
        }

        .permission-group-description {
            margin: 0.15rem 0 0;
            color: #6c757d;
            font-size: 0.76rem;
            line-height: 1.4;
        }

        .permission-list {
            display: flex;
            flex-direction: column;
        }

        .permission-item {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            margin: 0;
            padding: 0.9rem 1rem;
            border-bottom: 1px solid #edf0f2;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .permission-item:last-child {
            border-bottom: 0;
        }

        .permission-item:hover {
            background: #fafbfc;
        }

        .permission-item-disabled {
            cursor: default;
        }

        .permission-item-disabled:hover {
            background: #fff;
        }

        .permission-check {
            flex: 0 0 auto;
            margin: 0;
            padding: 0;
        }

        .permission-check .form-check-input {
            width: 1.05rem;
            height: 1.05rem;
            margin: 0.15rem 0 0;
            float: none;
            cursor: pointer;
        }

        .permission-check .form-check-input:disabled {
            cursor: not-allowed;
        }

        .permission-content {
            min-width: 0;
            flex: 1;
        }

        .permission-title-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
        }

        .permission-name {
            color: #292d32;
            font-size: 0.86rem;
            font-weight: 600;
        }

        .permission-slug {
            padding: 0.15rem 0.4rem;
            border-radius: 0.35rem;
            background: #f1f3f5;
            color: #687078;
            font-size: 0.68rem;
            font-weight: 500;
        }

        .permission-description {
            margin: 0.25rem 0 0;
            color: #6c757d;
            font-size: 0.77rem;
            line-height: 1.45;
        }

        .protected-role-notice {
            display: flex;
            align-items: flex-start;
            gap: 0.7rem;
            padding: 0.9rem 1rem;
            border-top: 1px solid #f2dfaa;
            background: #fff8e1;
            color: #735d18;
            font-size: 0.8rem;
        }

        .protected-role-notice > i {
            margin-top: 0.1rem;
        }

        .role-form-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.65rem;
            padding-top: 1.25rem;
            border-top: 1px solid #e7eaed;
        }

        .role-form-actions .btn {
            min-width: 100px;
            min-height: 40px;
            font-weight: 500;
        }

        @media (max-width: 767.98px) {
            .permissions-header {
                align-items: stretch;
                flex-direction: column;
            }

            .permissions-header-actions {
                width: 100%;
            }

            .permissions-header-actions .btn {
                flex: 1;
            }

            .role-form-actions {
                align-items: stretch;
                flex-direction: column-reverse;
            }

            .role-form-actions .btn {
                width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const permissionButtons = document.querySelectorAll(
                '[data-permission-toggle]'
            );

            const permissionCheckboxes = document.querySelectorAll(
                '.permission-checkbox'
            );

            permissionButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const shouldCheck =
                        button.dataset.permissionToggle === 'all';

                    permissionCheckboxes.forEach((checkbox) => {
                        if (!checkbox.disabled) {
                            checkbox.checked = shouldCheck;
                        }
                    });
                });
            });
        });
    </script>
@endpush