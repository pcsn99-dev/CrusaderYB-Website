<x-app-layout>
    <x-slot name="header">
        Create Admin User
    </x-slot>

    <x-slot name="subheader">
        Add a staff account, assign a role, and send a temporary password for first login.
    </x-slot>

    <div class="cyb-page-card overflow-hidden">

        {{-- Card Toolbar --}}
        <div class="flex flex-col gap-3 border-b border-[var(--cyb-border)] bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <span class="cyb-badge-soft">
                    <i class="bi bi-person-plus"></i>
                    New account
                </span>

                <span class="text-sm text-[var(--cyb-muted)]">
                    Temporary password will be emailed automatically
                </span>
            </div>

            <a href="{{ route('admin-users.index') }}"
            class="inline-flex items-center justify-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-4 py-2 text-sm font-semibold text-[var(--cyb-text)] shadow-sm transition hover:bg-[var(--cyb-primary-soft)]">
                <i class="bi bi-arrow-left"></i>
                Back to Admin Users
            </a>
        </div>

        <div class="p-5">
            <form method="POST" action="{{ route('admin-users.store') }}">
                @csrf

                @include('admin-users.partials.form', [
                    'adminUser' => null,
                    'roles' => $roles,
                    'buttonText' => 'Create Admin User',
                ])
            </form>
        </div>

    </div>
  
</x-app-layout>