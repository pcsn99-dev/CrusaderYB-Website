<x-app-layout>
    <x-slot name="header">
        Edit Admin User
    </x-slot>

    <x-slot name="subheader">
        Update {{ $adminUser->name }}’s account details, assigned role, and account status.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-pencil-square"></i>
    </x-slot>

    <x-slot name="headerActions">
        <a
            href="{{ route('admin-users.index') }}"
            class="btn btn-light border d-inline-flex align-items-center gap-2"
        >
            <i class="bi bi-arrow-left"></i>
            Back to Admin Users
        </a>
    </x-slot>

    <div class="cyb-page">
        <div class="cyb-card">
            <div class="cyb-card-header">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="cyb-pill cyb-pill-primary">
                            <i class="bi bi-pencil-square"></i>
                            Editing Account
                        </span>

                        @if ($adminUser->active)
                            <span class="cyb-pill cyb-pill-success">
                                <span class="cyb-status-dot cyb-status-dot-success"></span>
                                Active
                            </span>
                        @else
                            <span class="cyb-pill cyb-pill-danger">
                                <span class="cyb-status-dot cyb-status-dot-danger"></span>
                                Inactive
                            </span>
                        @endif
                    </div>

                    <p class="cyb-section-description mt-2">
                        {{ $adminUser->email }}
                    </p>
                </div>
            </div>

            <div class="cyb-card-body">
                <form
                    method="POST"
                    action="{{ route('admin-users.update', $adminUser) }}"
                >
                    @csrf
                    @method('PUT')

                    @include('admin-users.partials.form', [
                        'adminUser' => $adminUser,
                        'roles' => $roles,
                        'buttonText' => 'Update Admin User',
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>