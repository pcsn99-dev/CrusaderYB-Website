<x-guest-layout>
    <div class="auth-header">
        <h1 class="auth-title">
            Confirm Password
        </h1>

        <p class="auth-subtitle">
            This is a secure area of the application. Please confirm your password before continuing.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-4">
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
                autofocus
                class="form-control cyb-form-control @error('password') is-invalid @enderror"
            >

            @error('password')
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
                <i class="bi bi-shield-lock me-1"></i>
                Confirm
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

            .auth-submit-button {
                min-height: 42px;
                font-weight: 600;
            }
        </style>
    @endpush
</x-guest-layout>