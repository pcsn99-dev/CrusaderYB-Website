<div class="cyb-stack">

    {{-- Section intro --}}
    <div>
        <h2 class="cyb-section-title">
            Account Details
        </h2>

        <p class="cyb-section-description">
            Fill in the staff member’s information and choose the role that controls their system access.
        </p>
    </div>

    {{-- Name and Email --}}
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <label
                for="name"
                class="cyb-form-label"
            >
                Full Name
                <span class="text-danger">*</span>
            </label>

            <input
                type="text"
                name="name"
                id="name"
                value="{{ old('name', $adminUser?->name) }}"
                required
                autocomplete="name"
                placeholder="Example: Juan Dela Cruz"
                class="form-control cyb-form-control @error('name') is-invalid @enderror"
            >

            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-12 col-md-6">
            <label
                for="email"
                class="cyb-form-label"
            >
                Email Address
                <span class="text-danger">*</span>
            </label>

            <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email', $adminUser?->email) }}"
                required
                autocomplete="email"
                placeholder="staff@example.com"
                class="form-control cyb-form-control @error('email') is-invalid @enderror"
            >

            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    {{-- Username and Role --}}
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <label
                for="username"
                class="cyb-form-label"
            >
                Username
                <span class="text-danger">*</span>
            </label>

            <input
                type="text"
                name="username"
                id="username"
                value="{{ old('username', $adminUser?->username) }}"
                required
                autocomplete="username"
                placeholder="Example: jdelacruz"
                class="form-control cyb-form-control @error('username') is-invalid @enderror"
            >

            @error('username')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-12 col-md-6">
            <label
                for="role_id"
                class="cyb-form-label"
            >
                Role
                <span class="text-danger">*</span>
            </label>

            <select
                name="role_id"
                id="role_id"
                required
                class="form-select cyb-form-control @error('role_id') is-invalid @enderror"
            >
                <option value="">
                    Select Role
                </option>

                @foreach ($roles as $role)
                    <option
                        value="{{ $role->id }}"
                        @selected((int) old('role_id', $adminUser?->role_id) === (int) $role->id)
                    >
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>

            @error('role_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

            <div class="cyb-form-help">
                The selected role determines which areas and actions this administrator can access.
            </div>
        </div>
    </div>

    {{-- Account Status --}}
    <div class="admin-account-status">
        <input
            type="hidden"
            name="active"
            value="0"
        >

        <div class="form-check admin-account-status-check">
            <input
                type="checkbox"
                name="active"
                id="active"
                value="1"
                class="form-check-input"
                @checked((bool) old('active', $adminUser?->active ?? true))
            >

            <label
                for="active"
                class="form-check-label admin-account-status-label"
            >
                <span class="admin-account-status-title">
                    Active Account
                </span>

                <span class="admin-account-status-description">
                    Active administrators can log in. Inactive accounts are blocked from accessing the admin panel.
                </span>
            </label>
        </div>

        @error('active')
            <div class="text-danger small mt-2">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $message }}
            </div>
        @enderror
    </div>

    {{-- Temporary Password Notice --}}
    @if (! $adminUser)
        <div class="cyb-notice cyb-notice-warning admin-password-notice">
            <i class="bi bi-key-fill"></i>

            <div>
                <div class="admin-password-notice-title">
                    Temporary password required
                </div>

                <div class="admin-password-notice-text">
                    The system will generate a temporary password and send it to this user’s email.
                    The user will be required to change it after signing in.
                </div>
            </div>
        </div>
    @endif

    {{-- Actions --}}
    <div class="cyb-form-actions">
        <a
            href="{{ route('admin-users.index') }}"
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
        /*
         * Admin-user form specific styles only.
         * General form spacing, labels, controls, notices,
         * and actions come from the shared CYB styles.
         */

        .admin-account-status {
            padding: 1rem;
            border: 1px solid var(--cyb-border, #e7eaed);
            border-radius: 0.7rem;
            background: #f8f9fa;
        }

        .admin-account-status-check {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin: 0;
            padding: 0;
        }

        .admin-account-status-check .form-check-input {
            width: 1.05rem;
            height: 1.05rem;
            flex: 0 0 1.05rem;
            margin: 0.15rem 0 0;
            float: none;
            cursor: pointer;
        }

        .admin-account-status-label {
            display: block;
            margin: 0;
            cursor: pointer;
        }

        .admin-account-status-title {
            display: block;
            color: var(--cyb-text, #212529);
            font-size: 0.86rem;
            font-weight: 600;
        }

        .admin-account-status-description {
            display: block;
            margin-top: 0.2rem;
            color: var(--cyb-muted, #6c757d);
            font-size: 0.78rem;
            line-height: 1.5;
        }

        .admin-password-notice {
            border: 1px solid #f2dfaa;
            border-radius: 0.7rem;
        }

        .admin-password-notice > i {
            margin-top: 0.1rem;
        }

        .admin-password-notice-title {
            font-size: 0.84rem;
            font-weight: 600;
        }

        .admin-password-notice-text {
            margin-top: 0.2rem;
            font-size: 0.78rem;
            line-height: 1.5;
        }
    </style>
@endpush