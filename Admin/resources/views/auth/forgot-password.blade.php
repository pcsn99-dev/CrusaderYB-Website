<x-guest-layout>
    <div class="auth-header">
        <h1 class="auth-title">
            Forgot Password
        </h1>

        <p class="auth-subtitle">
            Enter your email address and we’ll send you a password reset link.
        </p>
    </div>

    @if (session('status'))
        <div class="alert alert-success auth-alert" role="alert">
            <i class="bi bi-check2-circle"></i>

            <div>
                {{ session('status') }}
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
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
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                class="form-control cyb-form-control @error('email') is-invalid @enderror"
            >

            @error('email')
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
                <i class="bi bi-envelope me-1"></i>
                Email Password Reset Link
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