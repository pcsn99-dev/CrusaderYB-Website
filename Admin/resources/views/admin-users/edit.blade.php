<x-app-layout>
    <x-slot name="header">
        Edit Admin User
    </x-slot>

    <x-slot name="subheader">
        Update {{ $adminUser->name }}’s account details, assigned role, and account status.
    </x-slot>

    <div class="cyb-page-card overflow-hidden">

        {{-- Card Toolbar --}}
        <div class="flex flex-col gap-3 border-b border-[var(--cyb-border)] bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <span class="cyb-badge-soft">
                    <i class="bi bi-pencil-square"></i>
                    Editing account
                </span>

                <span class="text-sm text-[var(--cyb-muted)]">
                    {{ $adminUser->email }}
                </span>
            </div>

            <a href="{{ route('admin-users.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-4 py-2 text-sm font-semibold text-[var(--cyb-text)] shadow-sm transition hover:bg-[var(--cyb-primary-soft)]">
                <i class="bi bi-arrow-left"></i>
                Back to Admin Users
            </a>
        </div>

        <div class="p-5">
            <form method="POST" action="{{ route('admin-users.update', $adminUser) }}">
                @csrf
                @method('PUT')

                @include('admin-users.partials.form', [
                    'adminUser' => $adminUser,
                    'roles' => $roles,
                    'buttonText' => 'Update Admin User',
                ])
            </form>
        </div>

    </div>
</x-app-layout>