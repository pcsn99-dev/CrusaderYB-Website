<x-app-layout>
    <x-slot name="header">
        Admin User Details
    </x-slot>

    <x-slot name="subheader">
        View account information, access status, and password reset options for {{ $adminUser->name }}.
    </x-slot>

    <div class="space-y-5">

        {{-- Profile Summary --}}
        <div class="cyb-page-card overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-[var(--cyb-border)] bg-white px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-3">
                    <x-avatar :name="$adminUser->name" size="48" />

                    <div class="min-w-0">
                        <h2 class="mb-1 text-lg font-semibold text-[var(--cyb-primary)]">
                            {{ $adminUser->name }}
                        </h2>

                        <p class="mb-3 text-sm text-[var(--cyb-muted)]">
                            {{ $adminUser->email }}
                        </p>

                        <div class="flex flex-wrap gap-2">
                            @if ($adminUser->active)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-green-200 bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                    Inactive
                                </span>
                            @endif

                            @if ($adminUser->must_change_password)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-yellow-200 bg-yellow-50 px-2.5 py-1 text-xs font-semibold text-yellow-700">
                                    <i class="bi bi-key"></i>
                                    Must change password
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    <i class="bi bi-check2"></i>
                                    Password changed
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center">
                    <a href="{{ route('admin-users.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-4 py-2 text-sm font-semibold text-[var(--cyb-text)] shadow-sm transition hover:bg-[var(--cyb-primary-soft)]">
                        <i class="bi bi-arrow-left"></i>
                        Back
                    </a>

                    <a href="{{ route('admin-users.edit', $adminUser) }}"
                       class="inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--cyb-primary)] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[var(--cyb-primary-dark)]">
                        <i class="bi bi-pencil-square"></i>
                        Edit Account
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

            {{-- Account Information --}}
            <div class="cyb-page-card overflow-hidden lg:col-span-2">
                <div class="border-b border-[var(--cyb-border)] px-5 py-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="cyb-badge-soft">
                            <i class="bi bi-person-lines-fill"></i>
                            Account information
                        </span>

                        <span class="text-sm text-[var(--cyb-muted)]">
                            Basic details and system access information
                        </span>
                    </div>
                </div>

                <div class="p-5">
                    <dl class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Name
                            </dt>
                            <dd class="mt-1 font-semibold text-[var(--cyb-text)]">
                                {{ $adminUser->name }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Email
                            </dt>
                            <dd class="mt-1 font-semibold text-[var(--cyb-text)]">
                                {{ $adminUser->email }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Username
                            </dt>
                            <dd class="mt-1">
                                <code class="inline-flex rounded-md border border-[var(--cyb-border)] bg-slate-50 px-2 py-1 text-xs font-medium text-[var(--cyb-text)]">
                                    {{ $adminUser->username ?: 'N/A' }}
                                </code>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Role
                            </dt>
                            <dd class="mt-1 font-semibold text-[var(--cyb-text)]">
                                {{ $adminUser->role?->name ?? 'No role assigned' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Type
                            </dt>
                            <dd class="mt-1 font-semibold text-[var(--cyb-text)]">
                                {{ ucfirst($adminUser->type ?? 'N/A') }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Last Login
                            </dt>
                            <dd class="mt-1 font-semibold text-[var(--cyb-text)]">
                                {{ $adminUser->last_login_at?->format('M d, Y h:i A') ?? 'Never' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Temporary Password Expires
                            </dt>
                            <dd class="mt-1 font-semibold text-[var(--cyb-text)]">
                                {{ $adminUser->temporary_password_expires_at?->format('M d, Y h:i A') ?? 'N/A' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Created At
                            </dt>
                            <dd class="mt-1 font-semibold text-[var(--cyb-text)]">
                                {{ $adminUser->created_at?->format('M d, Y h:i A') ?? 'N/A' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Password Access --}}
            <div class="cyb-page-card overflow-hidden">
                <div class="border-b border-[var(--cyb-border)] px-5 py-4">
                    <span class="cyb-badge-accent">
                        <i class="bi bi-key-fill"></i>
                        Password access
                    </span>
                </div>

                <div class="p-5">
                    <p class="mb-4 text-sm text-[var(--cyb-muted)]">
                        Send a new temporary password to this user’s email. This will require the user to change their password after logging in.
                    </p>

                    @php
                        $confirmMessage = $adminUser->must_change_password
                            ? 'Send a new temporary password to this user?'
                            : 'This user has already changed their password. Sending a new temporary password will reset their current password and require them to change it again. Continue?';
                    @endphp

                    <form method="POST"
                          action="{{ route('admin-users.resend-temporary-password', $adminUser) }}"
                          onsubmit="return confirm({{ \Illuminate\Support\Js::from($confirmMessage) }});">
                        @csrf

                        <button type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-yellow-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-yellow-700">
                            <i class="bi bi-envelope-arrow-up"></i>
                            Resend Temporary Password
                        </button>
                    </form>

                    <div class="mt-4 rounded-lg border border-yellow-200 bg-yellow-50 px-3 py-2 text-xs text-yellow-800">
                        <i class="bi bi-info-circle me-1"></i>
                        This will reset the account password and mark it as needing a password change.
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>