<x-app-layout>
    <x-slot name="header">
        Audit Logs
    </x-slot>

    <x-slot name="subheader">
        View recent admin actions across the admin panel.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-activity"></i>
    </x-slot>

    <div class="space-y-4">

        {{-- Filters --}}
        <div class="cyb-page-card overflow-hidden">
            <div class="border-b border-[var(--cyb-border)] bg-white px-5 py-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="cyb-badge-soft">
                            <i class="bi bi-funnel"></i>
                            Filters
                        </span>

                        <h2 class="mb-0 mt-2 text-lg font-bold text-[var(--cyb-primary)]">
                            Search Audit Logs
                        </h2>

                        <p class="mb-0 mt-1 text-sm text-[var(--cyb-muted)]">
                            Filter by admin, module, action, record, or date.
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-12">

                    <div class="lg:col-span-4">
                        <label for="search" class="mb-1 block text-xs font-bold uppercase tracking-wide text-[var(--cyb-muted)]">
                            Search
                        </label>

                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ request('search') }}"
                            class="block w-full rounded-xl border border-[var(--cyb-border)] bg-white px-3 py-2.5 text-sm text-[var(--cyb-text)] shadow-sm transition focus:border-[var(--cyb-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)]/20"
                            placeholder="Admin, action, module, record..."
                        >
                    </div>

                    <div class="lg:col-span-2">
                        <label for="module" class="mb-1 block text-xs font-bold uppercase tracking-wide text-[var(--cyb-muted)]">
                            Module
                        </label>

                        <select
                            id="module"
                            name="module"
                            class="block w-full rounded-xl border border-[var(--cyb-border)] bg-white px-3 py-2.5 text-sm text-[var(--cyb-text)] shadow-sm transition focus:border-[var(--cyb-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)]/20"
                        >
                            <option value="">All Modules</option>

                            @foreach ($modules as $module)
                                <option value="{{ $module }}" @selected(request('module') === $module)>
                                    {{ ucfirst(str_replace('_', ' ', $module)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label for="action" class="mb-1 block text-xs font-bold uppercase tracking-wide text-[var(--cyb-muted)]">
                            Action
                        </label>

                        <select
                            id="action"
                            name="action"
                            class="block w-full rounded-xl border border-[var(--cyb-border)] bg-white px-3 py-2.5 text-sm text-[var(--cyb-text)] shadow-sm transition focus:border-[var(--cyb-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)]/20"
                        >
                            <option value="">All Actions</option>

                            @foreach ($actions as $action)
                                <option value="{{ $action }}" @selected(request('action') === $action)>
                                    {{ ucfirst(str_replace('_', ' ', $action)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label for="date_from" class="mb-1 block text-xs font-bold uppercase tracking-wide text-[var(--cyb-muted)]">
                            From
                        </label>

                        <input
                            id="date_from"
                            name="date_from"
                            type="date"
                            value="{{ request('date_from') }}"
                            class="block w-full rounded-xl border border-[var(--cyb-border)] bg-white px-3 py-2.5 text-sm text-[var(--cyb-text)] shadow-sm transition focus:border-[var(--cyb-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)]/20"
                        >
                    </div>

                    <div class="lg:col-span-2">
                        <label for="date_to" class="mb-1 block text-xs font-bold uppercase tracking-wide text-[var(--cyb-muted)]">
                            To
                        </label>

                        <input
                            id="date_to"
                            name="date_to"
                            type="date"
                            value="{{ request('date_to') }}"
                            class="block w-full rounded-xl border border-[var(--cyb-border)] bg-white px-3 py-2.5 text-sm text-[var(--cyb-text)] shadow-sm transition focus:border-[var(--cyb-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)]/20"
                        >
                    </div>

                    <div class="lg:col-span-12 flex flex-col gap-2 sm:flex-row sm:justify-end">
                        <a
                            href="{{ route('audit-logs.index') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-4 py-2 text-sm font-bold text-[var(--cyb-text)] shadow-sm transition hover:bg-slate-50"
                        >
                            <i class="bi bi-x-lg"></i>
                            Clear
                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--cyb-primary)] px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[var(--cyb-primary-dark)]"
                        >
                            <i class="bi bi-search"></i>
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Logs Table --}}
        <div class="cyb-page-card overflow-hidden">
            <div class="border-b border-[var(--cyb-border)] bg-white px-5 py-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span class="cyb-badge-soft">
                            <i class="bi bi-list-check"></i>
                            Activity
                        </span>

                        <h2 class="mb-0 mt-2 text-lg font-bold text-[var(--cyb-primary)]">
                            Admin Activity Logs
                        </h2>
                    </div>

                    <div class="text-sm text-[var(--cyb-muted)]">
                        Showing
                        <span class="font-bold text-[var(--cyb-text)]">{{ $auditLogs->firstItem() ?? 0 }}</span>
                        –
                        <span class="font-bold text-[var(--cyb-text)]">{{ $auditLogs->lastItem() ?? 0 }}</span>
                        of
                        <span class="font-bold text-[var(--cyb-text)]">{{ $auditLogs->total() }}</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Description</th>
                            <th>Record</th>
                            <th>Source</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($auditLogs as $log)
                            @php
                                $actionClass = match ($log->action) {
                                    'created', 'bulk_created', 'started_review' => 'border-green-200 bg-green-50 text-green-700',
                                    'updated', 'saved_changes', 'marked_reviewed' => 'border-blue-200 bg-blue-50 text-blue-700',
                                    'deleted' => 'border-red-200 bg-red-50 text-red-700',
                                    'flagged' => 'border-yellow-200 bg-yellow-50 text-yellow-800',
                                    'unflagged', 'released_review' => 'border-slate-200 bg-slate-50 text-slate-700',
                                    default => 'border-slate-200 bg-slate-50 text-slate-700',
                                };

                                $recordName = $log->auditable_type
                                    ? class_basename($log->auditable_type)
                                    : 'N/A';
                            @endphp

                            <tr>
                                <td class="text-nowrap">
                                    <div class="text-sm font-semibold text-[var(--cyb-text)]">
                                        {{ $log->created_at?->format('M d, Y') }}
                                    </div>

                                    <div class="text-xs text-[var(--cyb-muted)]">
                                        {{ $log->created_at?->format('h:i A') }}
                                    </div>
                                </td>

                                <td>
                                    <div class="text-sm font-semibold text-[var(--cyb-text)]">
                                        {{ $log->user?->name ?? 'System / Unknown' }}
                                    </div>

                                    <div class="text-xs text-[var(--cyb-muted)]">
                                        {{ $log->user?->email ?? 'No user account' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold {{ $actionClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="cyb-chip cyb-chip-neutral">
                                        {{ ucfirst(str_replace('_', ' ', $log->module)) }}
                                    </span>
                                </td>

                                <td style="min-width: 260px;">
                                    <div class="text-sm text-[var(--cyb-text)]">
                                        {{ $log->description ?? 'No description provided.' }}
                                    </div>

                                    @if ($log->old_values || $log->new_values)
                                        <details class="mt-2 text-xs text-[var(--cyb-muted)]">
                                            <summary class="cursor-pointer font-semibold text-[var(--cyb-primary)]">
                                                View changes
                                            </summary>

                                            <div class="mt-2 grid grid-cols-1 gap-2 lg:grid-cols-2">
                                                @if ($log->old_values)
                                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-2">
                                                        <div class="mb-1 font-bold text-slate-700">Old</div>
                                                        <pre class="mb-0 whitespace-pre-wrap text-xs">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </div>
                                                @endif

                                                @if ($log->new_values)
                                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-2">
                                                        <div class="mb-1 font-bold text-slate-700">New</div>
                                                        <pre class="mb-0 whitespace-pre-wrap text-xs">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </div>
                                                @endif
                                            </div>
                                        </details>
                                    @endif
                                </td>

                                <td>
                                    <div class="text-sm font-semibold text-[var(--cyb-text)]">
                                        {{ $recordName }}
                                    </div>

                                    <div class="text-xs text-[var(--cyb-muted)]">
                                        ID: {{ $log->auditable_id ?? 'N/A' }}
                                    </div>
                                </td>

                                <td>
                                    <div class="text-xs text-[var(--cyb-muted)]">
                                        <div>
                                            <i class="bi bi-globe me-1"></i>
                                            {{ $log->ip_address ?? 'N/A' }}
                                        </div>

                                        <div class="mt-1">
                                            <span class="font-semibold">
                                                {{ $log->method ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="cyb-empty-state">
                                        <div class="cyb-empty-state-icon">
                                            <i class="bi bi-activity"></i>
                                        </div>

                                        <h3 class="mb-1 text-base font-semibold text-[var(--cyb-text)]">
                                            No audit logs found
                                        </h3>

                                        <p class="mb-0 text-sm text-[var(--cyb-muted)]">
                                            Admin actions will appear here once logging is added to the controllers.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[var(--cyb-border)] bg-white px-5 py-4">
                {{ $auditLogs->links() }}
            </div>
        </div>

    </div>
</x-app-layout>