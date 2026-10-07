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
        <a
            href="{{ route('roles.index') }}"
            class="btn btn-light border d-inline-flex align-items-center gap-2"
        >
            <i class="bi bi-arrow-left"></i>
            Back to Roles
        </a>
    </x-slot>

    <div class="role-create-page">
        <div class="card role-create-card">
            <div class="card-header role-create-header">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="role-create-badge">
                        <i class="bi bi-plus-circle"></i>
                        New Role
                    </span>

                    <span class="role-create-helper">
                        Define access by selecting one or more permissions.
                    </span>
                </div>
            </div>

            <div class="card-body role-create-body">
                <form
                    method="POST"
                    action="{{ route('roles.store') }}"
                >
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
    </div>

    @push('styles')
        <style>
            .role-create-page {
                padding-bottom: 2rem;
            }

            .role-create-card {
                overflow: hidden;
                border: 1px solid #e7eaed;
                border-radius: 0.75rem;
                box-shadow: 0 0.125rem 0.4rem rgba(0, 0, 0, 0.03);
            }

            .role-create-header {
                padding: 1rem 1.2rem;
                border-bottom: 1px solid #edf0f2;
                background: #fff;
            }

            .role-create-body {
                padding: 1.25rem;
            }

            .role-create-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                min-height: 28px;
                padding: 0.25rem 0.6rem;
                border-radius: 999px;
                background: #eef3f8;
                color: #495057;
                font-size: 0.74rem;
                font-weight: 600;
            }

            .role-create-helper {
                color: #6c757d;
                font-size: 0.8rem;
            }

            @media (max-width: 767.98px) {
                .role-create-body {
                    padding: 1rem;
                }
            }
        </style>
    @endpush
</x-app-layout>