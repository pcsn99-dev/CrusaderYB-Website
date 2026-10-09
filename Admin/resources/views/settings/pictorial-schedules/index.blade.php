<x-app-layout>
    <x-slot name="header">
        Pictorial Schedules
    </x-slot>

    <x-slot name="subheader">
        Manage regular and delayed pictorial schedules, capacities, and reservations.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-calendar-week"></i>
    </x-slot>

    <x-slot name="breadcrumbs">
        <li class="breadcrumb-item">
            Settings
        </li>

        <li class="breadcrumb-item active">
            Pictorial Schedules
        </li>
    </x-slot>

    <div class="cyb-page">
        <div class="cyb-card">
            <div class="cyb-card-header">
                <div>
                    <h2 class="cyb-section-title">
                        Schedule Overview
                    </h2>

                    <p class="cyb-section-description">
                        @if ($activeYear)
                            Showing schedules for CYB {{ $activeYear->year }}.
                        @else
                            No active CYB year is currently configured.
                        @endif
                    </p>
                </div>
            </div>

            @if (!$activeYear)
                <div class="cyb-card-body">
                    <div class="cyb-notice cyb-notice-warning rounded-3">
                        <i class="bi bi-exclamation-triangle"></i>

                        <div>
                            Activate a CYB year before creating pictorial schedules.
                        </div>
                    </div>
                </div>
            @elseif ($pictorials->isEmpty())
                <div class="cyb-empty-state">
                    <div class="cyb-empty-state-icon">
                        <i class="bi bi-calendar-week"></i>
                    </div>

                    <h3 class="cyb-empty-state-title">
                        No pictorial schedules yet
                    </h3>

                    <p class="cyb-empty-state-text">
                        Pictorial schedules created for {{ $activeYear->year }}
                        will appear here.
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table cyb-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>College</th>
                                <th>Type</th>
                                <th>Reservations</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($pictorials as $pictorial)
                                <tr>
                                    <td>
                                        {{ $pictorial->date->format('M d, Y') }}
                                    </td>

                                    <td>
                                        {{ $pictorial->getPictorialTime() }}
                                    </td>

                                    <td>
                                        @if ($pictorial->is_delayed)
                                            {{ $pictorial->allowedColleges->count() }}
                                            eligible college(s)
                                        @else
                                            {{ $pictorial->college?->college_name ?? '—' }}
                                        @endif
                                    </td>

                                    <td>
                                        @if ($pictorial->is_delayed)
                                            <span class="cyb-pill cyb-pill-warning">
                                                Delayed
                                            </span>
                                        @else
                                            <span class="cyb-pill cyb-pill-neutral">
                                                Regular
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $pictorial->reserved_slots }}
                                        /
                                        {{ $pictorial->no_of_slots }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($pictorials->hasPages())
                    <div class="cyb-pagination-footer">
                        <div class="cyb-pagination-summary">
                            Showing
                            {{ $pictorials->firstItem() }}
                            –
                            {{ $pictorials->lastItem() }}
                            of
                            {{ $pictorials->total() }}
                        </div>

                        {{ $pictorials->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>