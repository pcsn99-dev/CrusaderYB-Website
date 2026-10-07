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
        <a
            href="{{ route('roles.index') }}"
            class="btn btn-light border d-inline-flex align-items-center gap-2"
        >
            <i class="bi bi-arrow-left"></i>
            Back to Roles
        </a>
    </x-slot>

    <div class="role-edit-page">
        <div class="card role-edit-card">

            {{-- Context Header --}}
            <div class="card-header role-edit-header">
                <div class="role-edit-header-main">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="role-edit-badge">
                            <i class="bi bi-person-badge"></i>
                            Editing Role
                        </span>

                        <span class="role-name-badge">
                            {{ $role->name }}
                        </span>

                        @if ($role->is_protected)
                            <span class="protected-badge">
                                <i class="bi bi-lock-fill"></i>
                                Protected
                            </span>
                        @endif
                    </div>

                    <div class="role-edit-helper">
                        Review role access carefully before saving changes.
                    </div>
                </div>
            </div>

            {{-- Protected Role Notice --}}
            @if ($role->is_protected)
                <div class="protected-role-banner">
                    <div class="protected-role-banner-icon">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>

                    <div>
                        <div class="protected-role-banner-title">
                            Protected system role
                        </div>

                        <div class="protected-role-banner-text">
                            This role is protected to prevent accidental
                            system lockout. Its name and permissions cannot
                            be changed manually.
                        </div>
                    </div>
                </div>
            @endif

            <div class="card-body role-edit-body">
                <form
                    method="POST"
                    action="{{ route('roles.update', $role) }}"
                >
                    @csrf
                    @method('PUT')

                    @include('roles.partials.form', [
                        'role' => $role,
                        'permissions' => $permissions,
                        'selectedPermissionIds' => old(
                            'permission_ids',
                            $selectedPermissionIds
                        ),
                        'buttonText' => 'Update Role',
                    ])
                </form>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .role-edit-page {
                padding-bottom: 2rem;
            }

            .role-edit-card {
                overflow: hidden;
                border: 1px solid #e7eaed;
                border-radius: 0.75rem;
                box-shadow: 0 0.125rem 0.4rem rgba(0, 0, 0, 0.03);
            }

            .role-edit-header {
                padding: 1rem 1.2rem;
                border-bottom: 1px solid #edf0f2;
                background: #fff;
            }

            .role-edit-header-main {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .role-edit-body {
                padding: 1.25rem;
            }

            .role-edit-badge,
            .role-name-badge,
            .protected-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                min-height: 28px;
                padding: 0.25rem 0.6rem;
                border-radius: 999px;
                font-size: 0.74rem;
                font-weight: 600;
            }

            .role-edit-badge {
                background: #eef3f8;
                color: #495057;
            }

            .role-name-badge {
                background: #f1f3f5;
                color: #495057;
            }

            .protected-badge {
                background: #e8f0fe;
                color: #315ca8;
            }

            .role-edit-helper {
                color: #6c757d;
                font-size: 0.8rem;
                text-align: right;
            }

            .protected-role-banner {
                display: flex;
                align-items: flex-start;
                gap: 0.8rem;
                padding: 1rem 1.2rem;
                border-bottom: 1px solid #d9e4f5;
                background: #f4f8fd;
                color: #355779;
            }

            .protected-role-banner-icon {
                flex: 0 0 auto;
                margin-top: 0.05rem;
                font-size: 1rem;
            }

            .protected-role-banner-title {
                font-size: 0.86rem;
                font-weight: 600;
            }

            .protected-role-banner-text {
                margin-top: 0.2rem;
                max-width: 720px;
                font-size: 0.78rem;
                line-height: 1.5;
            }

            @media (max-width: 767.98px) {
                .role-edit-header-main {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .role-edit-helper {
                    text-align: left;
                }

                .role-edit-body {
                    padding: 1rem;
                }
            }
        </style>
    @endpush
</x-app-layout>