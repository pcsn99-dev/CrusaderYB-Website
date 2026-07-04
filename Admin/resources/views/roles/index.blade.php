<x-app-layout>
    @php
        $rolesCount = method_exists($roles, 'total') ? $roles->total() : $roles->count();
        $totalPermissions = $roles->sum('permissions_count');
        $totalAssignedUsers = $roles->sum('users_count');
    @endphp

    <x-slot name="header">
        Role Management
    </x-slot>

    <x-slot name="subheader">
        Manage admin roles and assign permissions for each role.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-person-badge-fill"></i>
    </x-slot>

    <x-slot name="headerActions">
        <a href="{{ route('roles.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--cyb-primary)] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[var(--cyb-primary-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)] focus:ring-offset-2">
            <i class="bi bi-plus-circle"></i>
            Create Role
        </a>
    </x-slot>

    <div class="cyb-page-card">

        {{-- Toolbar --}}
        <div class="flex flex-col gap-4 border-b border-[var(--cyb-border)] bg-white px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <span class="cyb-badge-soft">
                    <i class="bi bi-shield-lock"></i>
                    {{ $rolesCount }} {{ $rolesCount === 1 ? 'role' : 'roles' }}
                </span>

                <span class="cyb-chip cyb-chip-role">
                    <i class="bi bi-key"></i>
                    {{ $totalPermissions }} permissions
                </span>

                <span class="cyb-chip cyb-chip-username">
                    <i class="bi bi-people"></i>
                    {{ $totalAssignedUsers }} assigned users
                </span>
            </div>

            <div class="w-full lg:w-80">
                <label for="roles-search" class="sr-only">Search roles</label>

                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[var(--cyb-muted)]">
                        <i class="bi bi-search"></i>
                    </span>

                    <input id="roles-search"
                           type="search"
                           class="block w-full rounded-lg border border-[var(--cyb-border)] bg-white py-2 pl-10 pr-3 text-sm text-[var(--cyb-text)] shadow-sm transition placeholder:text-slate-400 focus:border-[var(--cyb-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)]/20"
                           placeholder="Search role name, slug, or count...">
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="px-5 pb-5 pt-5">
            <div class="overflow-x-auto rounded-xl border border-[var(--cyb-border)]">
                <table class="min-w-full divide-y divide-[var(--cyb-border)] bg-white text-sm">
                    <thead class="bg-[var(--cyb-primary-soft)]">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Role
                            </th>

                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Slug
                            </th>

                            <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Permissions
                            </th>

                            <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Users
                            </th>

                            <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[var(--cyb-muted)]">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody id="roles-table-body" class="divide-y divide-[var(--cyb-border)] bg-white">
                        @forelse ($roles as $role)
                            @php
                                $roleSlug = $role->slug ?? \Illuminate\Support\Str::slug($role->name);
                                $permissionsCount = $role->permissions_count ?? 0;
                                $usersCount = $role->users_count ?? 0;
                            @endphp

                            <tr class="transition hover:bg-[var(--cyb-primary-soft)]/60"
                                data-role-row
                                data-search="{{ strtolower($role->name . ' ' . $roleSlug . ' ' . $permissionsCount . ' permissions ' . $usersCount . ' users') }}">
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex items-center gap-3">
                                        <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--cyb-primary)] text-white shadow-sm">
                                            <i class="bi bi-person-badge"></i>
                                        </div>

                                        <div class="min-w-0">
                                            <div class="truncate font-semibold text-[var(--cyb-text)]">
                                                {{ $role->name }}
                                            </div>

                                            <div class="mt-1 text-xs text-[var(--cyb-muted)]">
                                                Access group for admin permissions
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-middle">
                                    <span class="cyb-chip cyb-chip-username">
                                        <i class="bi bi-hash"></i>
                                        {{ $roleSlug }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-center align-middle">
                                    <span class="inline-flex min-w-12 items-center justify-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                                        {{ $permissionsCount }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-center align-middle">
                                    <span class="inline-flex min-w-12 items-center justify-center rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700">
                                        {{ $usersCount }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-right align-middle">
                                    <div class="dropdown">
                                        <button type="button"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[var(--cyb-border)] bg-white text-[var(--cyb-muted)] shadow-sm transition hover:bg-[var(--cyb-primary-soft)] hover:text-[var(--cyb-primary)]"
                                                data-bs-toggle="dropdown"
                                                data-bs-boundary="viewport"
                                                aria-expanded="false"
                                                aria-label="Open role actions">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a href="{{ route('roles.show', $role) }}"
                                                   class="dropdown-item d-flex align-items-center gap-2">
                                                    <i class="bi bi-eye text-secondary"></i>
                                                    View details
                                                </a>
                                            </li>

                                            <li>
                                                <a href="{{ route('roles.edit', $role) }}"
                                                   class="dropdown-item d-flex align-items-center gap-2">
                                                    <i class="bi bi-pencil-square text-primary"></i>
                                                    Edit role
                                                </a>
                                            </li>

                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>

                                            <li>
                                                <form method="POST"
                                                      action="{{ route('roles.destroy', $role) }}"
                                                      onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                                        <i class="bi bi-trash"></i>
                                                        Delete role
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center">
                                    <div class="cyb-empty-state">
                                        <div class="cyb-empty-state-icon">
                                            <i class="bi bi-person-badge"></i>
                                        </div>

                                        <h3 class="mb-1 text-base font-semibold text-[var(--cyb-text)]">
                                            No roles found
                                        </h3>

                                        <p class="mb-4 text-sm text-[var(--cyb-muted)]">
                                            Create roles to organize admin access and permissions.
                                        </p>

                                        <a href="{{ route('roles.create') }}"
                                           class="inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--cyb-primary)] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[var(--cyb-primary-dark)]">
                                            <i class="bi bi-plus-circle"></i>
                                            Create Role
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse

                        <tr id="roles-no-results-row" class="hidden">
                            <td colspan="5" class="px-4 py-10 text-center">
                                <div class="text-sm text-[var(--cyb-muted)]">
                                    <i class="bi bi-search me-1"></i>
                                    No roles match your search.
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if (method_exists($roles, 'links'))
                <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm text-[var(--cyb-muted)]">
                        @if ($roles->total() > 0)
                            Showing
                            <span class="font-semibold text-[var(--cyb-text)]">{{ $roles->firstItem() }}</span>
                            to
                            <span class="font-semibold text-[var(--cyb-text)]">{{ $roles->lastItem() }}</span>
                            of
                            <span class="font-semibold text-[var(--cyb-text)]">{{ $roles->total() }}</span>
                            roles
                        @else
                            No records to display
                        @endif
                    </div>

                    <div>
                        {{ $roles->withQueryString()->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const searchInput = document.getElementById('roles-search');
                const rows = Array.from(document.querySelectorAll('[data-role-row]'));
                const noResultsRow = document.getElementById('roles-no-results-row');

                if (!searchInput || !rows.length || !noResultsRow) {
                    return;
                }

                searchInput.addEventListener('input', () => {
                    const value = searchInput.value.trim().toLowerCase();
                    let visibleCount = 0;

                    rows.forEach((row) => {
                        const matches = row.dataset.search.includes(value);
                        row.classList.toggle('hidden', !matches);

                        if (matches) {
                            visibleCount++;
                        }
                    });

                    noResultsRow.classList.toggle('hidden', visibleCount !== 0);
                });
            });
        </script>
    @endpush
</x-app-layout>