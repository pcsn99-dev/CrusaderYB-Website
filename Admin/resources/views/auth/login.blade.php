<x-guest-layout>
    {{-- Session Status --}}
    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            <i class="bi bi-check2-circle me-1"></i>
            {{ session('status') }}
        </div>
    @endif

    <div class="auth-header">
        <h1 class="auth-title">
            Sign In
        </h1>

        <p class="auth-subtitle">
            Access your Crusader Yearbook account.
        </p>
    </div>

    {{-- Google Login --}}
    <div class="d-grid mb-4">
        <a
            href="{{ route('google.redirect') }}"
            class="btn btn-light border auth-google-button"
        >
            <i class="bi bi-google"></i>
            Continue with Google
        </a>
    </div>

    {{-- Divider --}}
    <div class="auth-divider">
        <span>
            Or sign in with email
        </span>
    </div>

    {{-- Email Login --}}
    <form method="POST" action="{{ route('login') }}">
        @csrf

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
                value="{{ old('email') }}"
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
                Password
            </label>

            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="form-control cyb-form-control @error('password') is-invalid @enderror"
            >

            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Remember --}}
        <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
            <div class="form-check mb-0">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="form-check-input"
                >

                <label
                    for="remember_me"
                    class="form-check-label auth-remember-label"
                >
                    Remember me
                </label>
            </div>

            @if (Route::has('password.request'))
                <a
                    href="{{ route('password.request') }}"
                    class="auth-link"
                >
                    Forgot your password?
                </a>
            @endif
        </div>

        {{-- Submit --}}
        <div class="d-grid">
            <button
                type="submit"
                class="btn btn-primary auth-submit-button"
            >
                <i class="bi bi-box-arrow-in-right me-1"></i>
                Log In
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
                margin: 0.35rem 0 0;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.82rem;
                line-height: 1.45;
            }

            .auth-google-button {
                display: inline-flex;
                min-height: 42px;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                font-size: 0.85rem;
                font-weight: 600;
            }

            .auth-divider {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                margin: 1.25rem 0;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.72rem;
            }

            .auth-divider::before,
            .auth-divider::after {
                content: "";
                flex: 1;
                height: 1px;
                background: var(--cyb-border, #e7eaed);
            }

            .auth-divider span {
                white-space: nowrap;
            }

            .auth-remember-label {
                color: var(--cyb-muted, #6c757d);
                font-size: 0.8rem;
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

            .auth-submit-button {
                min-height: 42px;
                font-weight: 600;
            }
        </style>
    @endpush
</x-guest-layout>