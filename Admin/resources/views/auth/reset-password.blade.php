<x-guest-layout>
    <div class="auth-header">
        <h1 class="auth-title">
            Reset Password
        </h1>

        <p class="auth-subtitle">
            Enter your email address and create a new password for your account.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        {{-- Password Reset Token --}}
        <input
            type="hidden"
            name="token"
            value="{{ $request->route('token') }}"
        >

        {{-- Email --}}
        <div class="mb-3">
            <label
                for="email"
                class="cyb-form-label"
            >
                Email
            </label>

            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
                required
                autofocus
                autocomplete="username"
                class="form-control cyb-form-control @error('email') is-invalid @enderror"
            >

            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Password --}}
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
                class="form-control cyb-form-control @error('password_confirmation') is-invalid @enderror"
            >

            @error('password_confirmation')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="d-grid">
            <button
                type="submit"
                class="btn btn-primary auth-submit-button"
            >
                <i class="bi bi-key me-1"></i>
                Reset Password
            </button>
        </div>
    </form>

    <div class="auth-footer">
        <a
            href="{{ route('login') }}"
            class="auth-link"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Back to Login
        </a>
    </div>

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

            .auth-submit-button {
                min-height: 42px;
                font-weight: 600;
            }

            .auth-footer {
                margin-top: 1rem;
                text-align: center;
            }

            .auth-link {
                color: var(--cyb-primary, #0d6efd);
                font-size: 0.8rem;
                font-weight: 500;
                text-decoration: none;
            }

            .auth-link:hover {
                text-decoration: underline;
            }
        </style>
    @endpush
</x-guest-layout>