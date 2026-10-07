<x-guest-layout>
    <div class="auth-header">
        <h1 class="auth-title">
            Change Temporary Password
        </h1>

        <p class="auth-subtitle">
            You are using a temporary password. Please create a new password before continuing to the admin portal.
        </p>
    </div>

    @if ($temporaryPasswordExpired)
        <div class="alert alert-danger auth-alert" role="alert">
            <i class="bi bi-exclamation-triangle"></i>

            <div>
                Your temporary password has expired. Please ask a Super Admin to send you a new temporary password.
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger auth-alert" role="alert">
            <i class="bi bi-exclamation-circle"></i>

            <div>
                <div class="fw-semibold mb-1">
                    Please fix the following:
                </div>

                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form
        id="logout-form"
        method="POST"
        action="{{ route('logout') }}"
    >
        @csrf
    </form>

    <form
        method="POST"
        action="{{ route('password.force.update') }}"
    >
        @csrf
        @method('PATCH')

        {{-- Temporary Password --}}
        <div class="mb-3">
            <label
                for="current_password"
                class="cyb-form-label"
            >
                Temporary Password
            </label>

            <input
                id="current_password"
                type="password"
                name="current_password"
                required
                autocomplete="current-password"
                @disabled($temporaryPasswordExpired)
                class="form-control cyb-form-control @error('current_password') is-invalid @enderror"
            >

            @error('current_password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- New Password --}}
        <div class="mb-3">
            <label
                for="password"
                class="cyb-form-label"
            >
                New Password
            </label>

            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                @disabled($temporaryPasswordExpired)
                class="form-control cyb-form-control @error('password') is-invalid @enderror"
            >

            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div class="mb-4">
            <label
                for="password_confirmation"
                class="cyb-form-label"
            >
                Confirm New Password
            </label>

            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                @disabled($temporaryPasswordExpired)
                class="form-control cyb-form-control @error('password_confirmation') is-invalid @enderror"
            >

            @error('password_confirmation')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="auth-form-actions">
            <button
                type="submit"
                form="logout-form"
                class="btn btn-light border"
            >
                <i class="bi bi-box-arrow-left me-1"></i>
                Log Out
            </button>

            <button
                type="submit"
                class="btn btn-primary"
                @disabled($temporaryPasswordExpired)
            >
                <i class="bi bi-key me-1"></i>
                Change Password
            </button>
        </div>
    </form>

    @push('styles')
        <style>
            .auth-header {
                margin-bottom: 1.5rem;
                text-align: center;
            }

            .auth-title {
                margin: 0;
                color: var(--cyb-text, #212529);
                font-size: 1.35rem;
                font-weight: 700;
            }

            .auth-subtitle {
                margin: 0.4rem 0 0;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.82rem;
                line-height: 1.5;
            }

            .auth-alert {
                display: flex;
                align-items: flex-start;
                gap: 0.65rem;
                margin-bottom: 1rem;
                font-size: 0.8rem;
                line-height: 1.5;
            }

            .auth-alert > i {
                flex: 0 0 auto;
                margin-top: 0.1rem;
            }

            .auth-form-actions {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding-top: 0.25rem;
            }

            .auth-form-actions .btn {
                min-height: 40px;
                font-weight: 500;
            }

            @media (max-width: 575.98px) {
                .auth-form-actions {
                    align-items: stretch;
                    flex-direction: column-reverse;
                }

                .auth-form-actions .btn {
                    width: 100%;
                }
            }
        </style>
    @endpush
</x-guest-layout>