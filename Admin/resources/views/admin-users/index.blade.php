<x-app-layout>
    <x-slot name="header">
        Admin Users
    </x-slot>

    <x-slot name="subheader">
        Manage staff access, assigned roles, account status, and temporary password resets.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-people-fill"></i>
    </x-slot>

    <x-slot name="headerActions">
        <a href="{{ route('admin-users.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--cyb-primary)] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[var(--cyb-primary-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)] focus:ring-offset-2">
            <i class="bi bi-person-plus"></i>
            Create Admin User
        </a>
    </x-slot>

    <div class="cyb-page-card overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex flex-col gap-3 border-b border-[var(--cyb-border)] bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <span class="cyb-badge-soft">
                    <i class="bi bi-shield-lock"></i>
                    {{ $adminUsers->total() }} admin {{ $adminUsers->total() === 1 ? 'user' : 'users' }}
                </span>

                <span class="text-sm text-[var(--cyb-muted)]">
                    Staff accounts with administrative access
                </span>
            </div>
        </div>

        {{-- Alerts --}}
        @if (session('success') || session('error'))
            <div class="px-5 pt-5">
                @if (session('success'))
                    <div class="mb-4 flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        <i class="bi bi-check-circle-fill mt-0.5"></i>

                        <div>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <i class="bi bi-exclamation-triangle-fill mt-0.5"></i>

                        <div>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Table --}}
        <div class="px-5 pb-5 pt-5">
            <div class="overflow-x-auto rounded-xl border border-[var(--cyb-border)]">
                <table class="min-w-full divide-y divide-[var(--cyb-border)] bg-white text-sm">
                    <thead class="bg-[var(--cyb-primary-soft)]">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                User
                            </th>

                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Username
                            </th>

                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Role
                            </th>

                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Status
                            </th>

                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Password
                            </th>

                            <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[var(--cyb-border)] bg-white">
                        @forelse ($adminUsers as $adminUser)
                            <tr class="transition hover:bg-[var(--cyb-primary-soft)]/60">
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex items-center gap-3">
                                        <x-avatar :name="$adminUser->name" size="36" />

                                        <div class="min-w-0">
                                            <div class="truncate font-semibold text-[var(--cyb-text)]">
                                                {{ $adminUser->name }}
                                            </div>

                                            <div class="truncate text-xs text-[var(--cyb-muted)]">
                                                {{ $adminUser->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-middle">
                                    <span class="cyb-chip cyb-chip-username">
                                        <i class="bi bi-at"></i>
                                        {{ $adminUser->username ?: 'N/A' }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 align-middle">
                                    @if ($adminUser->role)
                                        <span class="cyb-chip cyb-chip-role">
                                            <i class="bi bi-person-badge"></i>
                                            {{ $adminUser->role->name }}
                                        </span>
                                    @else
                                        <span class="cyb-chip cyb-chip-danger">
                                            <i class="bi bi-exclamation-circle"></i>
                                            No role
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 align-middle">
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
                                </td>

                                <td class="px-4 py-4 align-middle">
                                    @if ($adminUser->must_change_password)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-yellow-200 bg-yellow-50 px-2.5 py-1 text-xs font-semibold text-yellow-700">
                                            <i class="bi bi-key"></i>
                                            Must change
                                        </span>
                                    @else
                                        <span class="cyb-chip cyb-chip-neutral">
                                            <i class="bi bi-check2"></i>
                                            Set
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 align-middle text-right">
                                    <div class="dropdown">
                                        <button type="button"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[var(--cyb-border)] bg-white text-[var(--cyb-muted)] shadow-sm transition hover:bg-[var(--cyb-primary-soft)] hover:text-[var(--cyb-primary)]"
                                                data-bs-toggle="dropdown"
                                                data-bs-boundary="viewport"
                                                aria-expanded="false"
                                                aria-label="Open admin user actions">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a href="{{ route('admin-users.show', $adminUser) }}"
                                                   class="dropdown-item d-flex align-items-center gap-2">
                                                    <i class="bi bi-eye text-secondary"></i>
                                                    View details
                                                </a>
                                            </li>

                                            <li>
                                                <a href="{{ route('admin-users.edit', $adminUser) }}"
                                                   class="dropdown-item d-flex align-items-center gap-2">
                                                    <i class="bi bi-pencil-square text-primary"></i>
                                                    Edit account
                                                </a>
                                            </li>

                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>

                                            @if ($adminUser->active)
                                                <li>
                                                    <form method="POST"
                                                          action="{{ route('admin-users.deactivate', $adminUser) }}"
                                                          onsubmit="return confirm('Deactivate this admin account?');">
                                                        @csrf
                                                        @method('PATCH')

                                                        <button type="submit"
                                                                class="dropdown-item d-flex align-items-center gap-2 text-warning">
                                                            <i class="bi bi-pause-circle"></i>
                                                            Deactivate
                                                        </button>
                                                    </form>
                                                </li>
                                            @else
                                                <li>
                                                    <form method="POST"
                                                          action="{{ route('admin-users.activate', $adminUser) }}"
                                                          onsubmit="return confirm('Activate this admin account?');">
                                                        @csrf
                                                        @method('PATCH')

                                                        <button type="submit"
                                                                class="dropdown-item d-flex align-items-center gap-2 text-success">
                                                            <i class="bi bi-play-circle"></i>
                                                            Activate
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif

                                            <li>
                                                <form method="POST"
                                                      action="{{ route('admin-users.destroy', $adminUser) }}"
                                                      onsubmit="return confirm('Are you sure you want to delete this admin user?');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                                        <i class="bi bi-trash"></i>
                                                        Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <div class="cyb-empty-state">
                                        <div class="cyb-empty-state-icon">
                                            <i class="bi bi-people"></i>
                                        </div>

                                        <h3 class="mb-1 text-base font-semibold text-[var(--cyb-text)]">
                                            No admin users found
                                        </h3>

                                        <p class="mb-4 text-sm text-[var(--cyb-muted)]">
                                            Create an admin account to start assigning system access.
                                        </p>

                                        <a href="{{ route('admin-users.create') }}"
                                           class="inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--cyb-primary)] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[var(--cyb-primary-dark)]">
                                            <i class="bi bi-person-plus"></i>
                                            Create Admin User
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-[var(--cyb-muted)]">
                    @if ($adminUsers->total() > 0)
                        Showing
                        <span class="font-semibold text-[var(--cyb-text)]">{{ $adminUsers->firstItem() }}</span>
                        to
                        <span class="font-semibold text-[var(--cyb-text)]">{{ $adminUsers->lastItem() }}</span>
                        of
                        <span class="font-semibold text-[var(--cyb-text)]">{{ $adminUsers->total() }}</span>
                        admin users
                    @else
                        No records to display
                    @endif
                </div>

                <div>
                    {{ $adminUsers->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>