<x-app-layout>
    <x-slot name="header">
        Create Admin User
    </x-slot>

    <x-slot name="subheader">
        Add a staff account, assign a role, and send a temporary password for first login.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-person-plus-fill"></i>
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
                            <i class="bi bi-person-plus"></i>
                            New Account
                        </span>
                    </div>

                    <p class="cyb-section-description mt-2">
                        A temporary password will be generated and emailed automatically after the account is created.
                    </p>
                </div>
            </div>

            <div class="cyb-card-body">
                <form
                    method="POST"
                    action="{{ route('admin-users.store') }}"
                >
                    @csrf

                    @include('admin-users.partials.form', [
                        'adminUser' => null,
                        'roles' => $roles,
                        'buttonText' => 'Create Admin User',
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>