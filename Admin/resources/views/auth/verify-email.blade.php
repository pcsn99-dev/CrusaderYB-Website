<x-guest-layout>
    <div class="auth-header">
        <h1 class="auth-title">
            Verify Your Email
        </h1>

        <p class="auth-subtitle">
            Before continuing, please verify your email address using the link we sent to you.
            If you did not receive it, you can request another verification email below.
        </p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success auth-alert" role="alert">
            <i class="bi bi-check2-circle"></i>

            <div>
                A new verification link has been sent to the email address you provided.
            </div>
        </div>
    @endif

    <div class="auth-verification-actions">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <button
                type="submit"
                class="btn btn-primary"
            >
                <i class="bi bi-envelope-arrow-up me-1"></i>
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button
                type="submit"
                class="btn btn-light border"
            >
                <i class="bi bi-box-arrow-left me-1"></i>
                Log Out
            </button>
        </form>
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

            .auth-verification-actions {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
            }

            .auth-verification-actions form {
                margin: 0;
            }

            .auth-verification-actions .btn {
                min-height: 40px;
                font-weight: 500;
            }

            @media (max-width: 575.98px) {
                .auth-verification-actions {
                    align-items: stretch;
                    flex-direction: column;
                }

                .auth-verification-actions form,
                .auth-verification-actions .btn {
                    width: 100%;
                }
            }
        </style>
    @endpush
</x-guest-layout>