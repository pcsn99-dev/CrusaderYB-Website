<x-app-layout>
    <x-slot name="header">
        Bulk Create Missing Writeups
    </x-slot>

    <x-slot name="subheader">
        Randomly assign active generic writeups to subscribed students without pictorial attendance.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-magic"></i>
    </x-slot>

    @php
        $hasSelection = filled($selectedYear) && filled($selectedCollegeId);
        $hasStudents = $hasSelection && $studentCount > 0;
        $hasGenericWriteups = $hasSelection && $genericWriteupCount > 0;
        $canBulkCreate = $hasStudents && $hasGenericWriteups;

        $selectedCollegeName = $selectedCollege->college_name ?? 'Selected college';
    @endphp

    <div class="cyb-page">
        <div class="row g-4 align-items-start">

            {{-- =========================================================
                 LEFT SIDEBAR
                 ========================================================= --}}
            <aside class="col-12 col-xl-4">
                <div class="bulk-writeup-sidebar">

                    {{-- Selection --}}
                    <section class="cyb-card">
                        <div class="cyb-card-header">
                            <div>
                                <h2 class="cyb-section-title">
                                    Choose Group
                                </h2>

                                <p class="cyb-section-description">
                                    Select a school year and college to check missing writeups.
                                </p>
                            </div>

                            <span class="cyb-pill cyb-pill-primary">
                                <i class="bi bi-funnel"></i>
                                Selection
                            </span>
                        </div>

                        <div class="cyb-card-body">
                            <form
                                method="GET"
                                action="{{ route('writeups.bulk.index') }}"
                                class="bulk-selection-form"
                            >
                                <div>
                                    <label
                                        for="year"
                                        class="cyb-form-label"
                                    >
                                        School Year
                                    </label>

                                    <select
                                        id="year"
                                        name="year"
                                        class="form-select cyb-form-control"
                                        required
                                    >
                                        <option value="">
                                            Select Year
                                        </option>

                                        @foreach ($years as $year)
                                            <option
                                                value="{{ $year }}"
                                                @selected($selectedYear == $year)
                                            >
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label
                                        for="college_id"
                                        class="cyb-form-label"
                                    >
                                        College
                                    </label>

                                    <select
                                        id="college_id"
                                        name="college_id"
                                        class="form-select cyb-form-control"
                                        required
                                    >
                                        <option value="">
                                            Select College
                                        </option>

                                        @foreach ($colleges as $college)
                                            <option
                                                value="{{ $college->id }}"
                                                @selected($selectedCollegeId == $college->id)
                                            >
                                                {{ $college->college_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <button
                                        type="submit"
                                        class="btn btn-primary flex-fill"
                                    >
                                        <i class="bi bi-search me-1"></i>
                                        Check Missing
                                    </button>

                                    @if ($hasSelection)
                                        <a
                                            href="{{ route('writeups.bulk.index') }}"
                                            class="btn btn-light border"
                                        >
                                            <i class="bi bi-x-lg me-1"></i>
                                            Clear
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </section>

                    {{-- How It Works --}}
                    <section class="cyb-card">
                        <div class="cyb-card-header">
                            <div>
                                <h2 class="cyb-section-title">
                                    How This Works
                                </h2>

                                <p class="cyb-section-description">
                                    Review the workflow before running a bulk operation.
                                </p>
                            </div>

                            <span class="cyb-pill cyb-pill-neutral">
                                <i class="bi bi-info-circle"></i>
                                Guide
                            </span>
                        </div>

                        <div class="cyb-card-body">
                            <div class="bulk-guide-list">
                                <div class="bulk-guide-step">
                                    <span class="bulk-guide-number">
                                        1
                                    </span>

                                    <div>
                                        <div class="bulk-guide-title">
                                            Select a group
                                        </div>

                                        <div class="bulk-guide-text">
                                            Choose the school year and college you want to process.
                                        </div>
                                    </div>
                                </div>

                                <div class="bulk-guide-step">
                                    <span class="bulk-guide-number">
                                        2
                                    </span>

                                    <div>
                                        <div class="bulk-guide-title">
                                            Check counts
                                        </div>

                                        <div class="bulk-guide-text">
                                            Make sure eligible students and active generic writeups are available.
                                        </div>
                                    </div>
                                </div>

                                <div class="bulk-guide-step">
                                    <span class="bulk-guide-number">
                                        3
                                    </span>

                                    <div>
                                        <div class="bulk-guide-title">
                                            Confirm creation
                                        </div>

                                        <div class="bulk-guide-text">
                                            The system randomly assigns active generic writeups to eligible students.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="cyb-notice cyb-notice-warning bulk-guide-warning">
                                <i class="bi bi-exclamation-triangle"></i>

                                <div>
                                    Review the selected group before confirming.
                                    Bulk creation affects multiple students at once.
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
            </aside>

            {{-- =========================================================
                 MAIN CONTENT
                 ========================================================= --}}
            <section class="col-12 col-xl-8">
                <div class="bulk-writeup-main">

                    {{-- Summary --}}
                    <section class="cyb-card">
                        <div class="cyb-card-header">
                            <div>
                                <h2 class="cyb-section-title">
                                    Missing Writeup Check
                                </h2>

                                <p class="cyb-section-description">
                                    Check student eligibility and available generic writeups before running bulk creation.
                                </p>
                            </div>

                            @if ($hasSelection)
                                <div class="d-flex flex-wrap gap-2 justify-content-xl-end">
                                    <span class="cyb-pill cyb-pill-primary">
                                        <i class="bi bi-calendar3"></i>
                                        SY {{ $selectedYear }}
                                    </span>

                                    <span
                                        class="cyb-pill cyb-pill-info bulk-college-pill"
                                        title="{{ $selectedCollegeName }}"
                                    >
                                        <i class="bi bi-building"></i>

                                        <span>
                                            {{ $selectedCollegeName }}
                                        </span>
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="cyb-card-body">
                            @if (! $hasSelection)

                                <div class="cyb-empty-state bulk-selection-empty">
                                    <div class="cyb-empty-state-icon">
                                        <i class="bi bi-arrow-left-circle"></i>
                                    </div>

                                    <h3 class="cyb-empty-state-title">
                                        Choose a School Year and College
                                    </h3>

                                    <p class="cyb-empty-state-text">
                                        Use the selection panel to check which students
                                        are eligible for bulk writeup creation.
                                    </p>
                                </div>

                            @else

                                {{-- Count Cards --}}
                                <div class="row g-4">
                                    <div class="col-12 col-md-6">
                                        <button
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#studentsMissingModal"
                                            @disabled($studentCount <= 0)
                                            class="bulk-summary-card bulk-summary-card-primary"
                                        >
                                            <div class="bulk-summary-card-header">
                                                <div>
                                                    <div class="bulk-summary-label">
                                                        Students Missing Writeups
                                                    </div>

                                                    <div class="bulk-summary-value-row">
                                                        <span class="bulk-summary-value">
                                                            {{ $studentCount }}
                                                        </span>

                                                        <span class="bulk-summary-unit">
                                                            eligible
                                                        </span>
                                                    </div>
                                                </div>

                                                <span class="bulk-summary-icon">
                                                    <i class="bi bi-people"></i>
                                                </span>
                                            </div>

                                            <div class="bulk-summary-help">
                                                @if ($studentCount > 0)
                                                    Click to view eligible students.
                                                @else
                                                    No eligible students found for this group.
                                                @endif
                                            </div>
                                        </button>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <a
                                            href="{{ route('writeups.generic.index', [
                                                'year' => $selectedYear,
                                                'college_id' => $selectedCollegeId,
                                            ]) }}"
                                            class="bulk-summary-card {{ $genericWriteupCount > 0
                                                ? 'bulk-summary-card-success'
                                                : 'bulk-summary-card-warning' }}"
                                        >
                                            <div class="bulk-summary-card-header">
                                                <div>
                                                    <div class="bulk-summary-label">
                                                        Active Generic Writeups
                                                    </div>

                                                    <div class="bulk-summary-value-row">
                                                        <span class="bulk-summary-value">
                                                            {{ $genericWriteupCount }}
                                                        </span>

                                                        <span class="bulk-summary-unit">
                                                            available
                                                        </span>
                                                    </div>
                                                </div>

                                                <span class="bulk-summary-icon">
                                                    <i class="bi bi-card-text"></i>
                                                </span>
                                            </div>

                                            <div class="bulk-summary-help">
                                                Click to manage generic writeups for this group.
                                            </div>
                                        </a>
                                    </div>
                                </div>

                                {{-- Status / Action --}}
                                <div class="mt-4">
                                    @if ($studentCount <= 0)

                                        <div class="cyb-notice cyb-notice-info bulk-status-notice">
                                            <i class="bi bi-info-circle"></i>

                                            <div>
                                                <div class="bulk-status-title">
                                                    No eligible students found.
                                                </div>

                                                <div class="bulk-status-text">
                                                    There are no subscribed students without pictorial attendance
                                                    and without writeups for this selected group.
                                                </div>
                                            </div>
                                        </div>

                                    @elseif ($genericWriteupCount <= 0)

                                        <div class="cyb-notice cyb-notice-warning bulk-status-notice">
                                            <i class="bi bi-exclamation-triangle"></i>

                                            <div class="bulk-status-content">
                                                <div>
                                                    <div class="bulk-status-title">
                                                        No active generic writeups found.
                                                    </div>

                                                    <div class="bulk-status-text">
                                                        Create active generic writeups first before running bulk creation.
                                                    </div>
                                                </div>

                                                <a
                                                    href="{{ route('writeups.generic.index', [
                                                        'year' => $selectedYear,
                                                        'college_id' => $selectedCollegeId,
                                                    ]) }}"
                                                    class="btn btn-sm btn-warning"
                                                >
                                                    <i class="bi bi-plus-lg me-1"></i>
                                                    Manage Generic Writeups
                                                </a>
                                            </div>
                                        </div>

                                    @else

                                        <div class="cyb-notice bulk-ready-notice">
                                            <i class="bi bi-check2-circle"></i>

                                            <div class="bulk-status-content">
                                                <div>
                                                    <div class="bulk-status-title">
                                                        Ready for bulk creation.
                                                    </div>

                                                    <div class="bulk-status-text">
                                                        The system will randomly assign
                                                        <strong>{{ $genericWriteupCount }}</strong>
                                                        active generic writeups to
                                                        <strong>{{ $studentCount }}</strong>
                                                        eligible students.
                                                    </div>
                                                </div>

                                                <button
                                                    type="button"
                                                    class="btn btn-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmBulkCreateModal"
                                                >
                                                    <i class="bi bi-magic me-1"></i>
                                                    Bulk Create Writeups
                                                </button>
                                            </div>
                                        </div>

                                    @endif
                                </div>

                            @endif
                        </div>
                    </section>

                    {{-- =================================================
                         BULK HISTORY
                         ================================================= --}}
                    @if ($hasSelection)
                        <section class="cyb-card">
                            <div class="cyb-card-header">
                                <div>
                                    <h2 class="cyb-section-title">
                                        Previous Bulk Operations
                                    </h2>

                                    <p class="cyb-section-description">
                                        Undo writeups still connected to their original bulk operation.
                                    </p>
                                </div>

                                <span class="cyb-pill cyb-pill-neutral">
                                    <i class="bi bi-layers"></i>

                                    {{ $bulkBatches->count() }}
                                    {{ \Illuminate\Support\Str::plural('batch', $bulkBatches->count()) }}
                                </span>
                            </div>

                            @if ($bulkBatches->isEmpty())

                                <div class="cyb-empty-state bulk-history-empty">
                                    <div class="cyb-empty-state-icon">
                                        <i class="bi bi-clock-history"></i>
                                    </div>

                                    <h3 class="cyb-empty-state-title">
                                        No Bulk Operations Yet
                                    </h3>

                                    <p class="cyb-empty-state-text">
                                        Bulk-create history for this school year and college
                                        will appear here.
                                    </p>
                                </div>

                            @else

                                <div class="bulk-history-list">
                                    @foreach ($bulkBatches as $batch)
                                        @php
                                            $undoableCount = (int) $batch->undoable_writeups_count;

                                            $stillLinkedTotal = (int) $batch->linked_writeups_total_count;

                                            $protectedCount = max(
                                                0,
                                                (int) $batch->created_count - $stillLinkedTotal
                                            );

                                            $isUndone = filled($batch->undone_at);

                                            $canUndoBatch =
                                                ! $isUndone
                                                && $undoableCount > 0;
                                        @endphp

                                        <article class="bulk-history-row">
                                            <div class="bulk-history-row-main">
                                                <div class="bulk-history-info">
                                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                                        <strong class="bulk-history-title">
                                                            Batch #{{ $batch->id }}
                                                        </strong>

                                                        @if ($isUndone)
                                                            <span class="cyb-pill cyb-pill-neutral">
                                                                <i class="bi bi-arrow-counterclockwise"></i>
                                                                Undone
                                                            </span>
                                                        @elseif ($canUndoBatch)
                                                            <span class="cyb-pill cyb-pill-success">
                                                                <span class="cyb-status-dot cyb-status-dot-success"></span>
                                                                Active
                                                            </span>
                                                        @else
                                                            <span class="cyb-pill cyb-pill-info">
                                                                <i class="bi bi-shield-check"></i>
                                                                Fully Protected
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="bulk-history-meta">
                                                        <span>
                                                            <i class="bi bi-person"></i>
                                                            {{ $batch->creator?->name ?? 'Unknown admin' }}
                                                        </span>

                                                        <span>
                                                            <i class="bi bi-calendar-event"></i>
                                                            {{ $batch->created_at?->format('M d, Y h:i A') }}
                                                        </span>
                                                    </div>

                                                    @if ($isUndone)
                                                        <div class="bulk-history-undone">
                                                            Undone by
                                                            <strong>
                                                                {{ $batch->undoneBy?->name ?? 'Unknown admin' }}
                                                            </strong>

                                                            @if ($batch->undone_at)
                                                                on {{ $batch->undone_at->format('M d, Y h:i A') }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="bulk-history-actions">
                                                    <div class="bulk-history-counts">
                                                        <div class="bulk-history-count">
                                                            <strong>
                                                                {{ $batch->created_count }}
                                                            </strong>

                                                            <span>
                                                                Created
                                                            </span>
                                                        </div>

                                                        <div class="bulk-history-count">
                                                            <strong>
                                                                {{ $undoableCount }}
                                                            </strong>

                                                            <span>
                                                                Undoable
                                                            </span>
                                                        </div>

                                                        <div class="bulk-history-count">
                                                            <strong>
                                                                {{ $protectedCount }}
                                                            </strong>

                                                            <span>
                                                                Protected
                                                            </span>
                                                        </div>
                                                    </div>

                                                    @if ($canUndoBatch)
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#undoBulkBatchModal{{ $batch->id }}"
                                                        >
                                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                                            Undo
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>

                                            @if (
                                                ! $isUndone
                                                && $undoableCount === 0
                                                && $protectedCount > 0
                                            )
                                                <div class="cyb-notice bulk-protected-notice">
                                                    <i class="bi bi-shield-check"></i>

                                                    <div>
                                                        All writeups from this batch have already been
                                                        individually re-reviewed and cannot be removed through Undo.
                                                    </div>
                                                </div>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>

                            @endif
                        </section>
                    @endif

                </div>
            </section>
        </div>
    </div>

    {{-- =============================================================
         CONFIRM BULK CREATE MODAL
         ============================================================= --}}
    @if ($canBulkCreate)
        <div
            class="modal fade"
            id="confirmBulkCreateModal"
            tabindex="-1"
            aria-labelledby="confirmBulkCreateModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <form
                    method="POST"
                    action="{{ route('writeups.bulk.store') }}"
                    class="modal-content bulk-modal"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="year"
                        value="{{ $selectedYear }}"
                    >

                    <input
                        type="hidden"
                        name="college_id"
                        value="{{ $selectedCollegeId }}"
                    >

                    <div class="modal-header">
                        <div>
                            <span class="cyb-pill cyb-pill-primary mb-2">
                                <i class="bi bi-magic"></i>
                                Confirm Action
                            </span>

                            <h5
                                class="modal-title"
                                id="confirmBulkCreateModalLabel"
                            >
                                Confirm Bulk Creation
                            </h5>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <div class="cyb-notice cyb-notice-warning bulk-modal-notice">
                            <i class="bi bi-exclamation-triangle"></i>

                            <div>
                                This action will create and mark multiple generic writeups as reviewed.
                            </div>
                        </div>

                        <div class="bulk-confirm-details">
                            <p>
                                This will create writeups for
                                <strong>{{ $studentCount }}</strong>
                                eligible students under
                                <strong>{{ $selectedCollegeName }}</strong>
                                for school year
                                <strong>{{ $selectedYear }}</strong>.
                            </p>

                            <p>
                                The system will randomly reuse
                                <strong>{{ $genericWriteupCount }}</strong>
                                active generic writeups.
                            </p>

                            <p>
                                These records remain linked to this bulk operation.
                                They may be undone later unless they are individually re-reviewed.
                            </p>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-light border"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-check2-circle me-1"></i>
                            Confirm and Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- =============================================================
         UNDO MODALS
         ============================================================= --}}
    @if ($hasSelection && $bulkBatches->isNotEmpty())
        @foreach ($bulkBatches as $batch)
            @php
                $undoableCount = (int) $batch->undoable_writeups_count;

                $stillLinkedTotal = (int) $batch->linked_writeups_total_count;

                $protectedCount = max(
                    0,
                    (int) $batch->created_count - $stillLinkedTotal
                );

                $canUndoBatch =
                    ! $batch->undone_at
                    && $undoableCount > 0;
            @endphp

            @if ($canUndoBatch)
                <div
                    class="modal fade"
                    id="undoBulkBatchModal{{ $batch->id }}"
                    tabindex="-1"
                    aria-labelledby="undoBulkBatchModalLabel{{ $batch->id }}"
                    aria-hidden="true"
                >
                    <div class="modal-dialog modal-dialog-centered">
                        <form
                            method="POST"
                            action="{{ route('writeups.bulk.undo', $batch) }}"
                            class="modal-content bulk-modal"
                        >
                            @csrf
                            @method('DELETE')

                            <div class="modal-header">
                                <div>
                                    <span class="cyb-pill cyb-pill-danger mb-2">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                        Undo Bulk Creation
                                    </span>

                                    <h5
                                        class="modal-title"
                                        id="undoBulkBatchModalLabel{{ $batch->id }}"
                                    >
                                        Undo Batch #{{ $batch->id }}?
                                    </h5>
                                </div>

                                <button
                                    type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="Close"
                                ></button>
                            </div>

                            <div class="modal-body">
                                <div class="cyb-notice cyb-notice-danger bulk-modal-notice">
                                    <i class="bi bi-exclamation-triangle"></i>

                                    <div>
                                        This removes all writeups that are still connected
                                        to this bulk operation.
                                    </div>
                                </div>

                                <div class="row g-3 mt-1">
                                    <div class="col-6">
                                        <div class="bulk-modal-stat">
                                            <strong class="text-danger">
                                                {{ $undoableCount }}
                                            </strong>

                                            <span>
                                                Writeups Removed
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="bulk-modal-stat">
                                            <strong class="text-success">
                                                {{ $protectedCount }}
                                            </strong>

                                            <span>
                                                Re-reviewed & Preserved
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <p class="bulk-modal-description">
                                    Students whose writeups are removed will appear as
                                    missing writeups again. Individually re-reviewed writeups
                                    will not be deleted.
                                </p>
                            </div>

                            <div class="modal-footer">
                                <button
                                    type="button"
                                    class="btn btn-light border"
                                    data-bs-dismiss="modal"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    class="btn btn-danger"
                                >
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                                    Confirm Undo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
    @endif

    {{-- =============================================================
         STUDENTS MISSING MODAL
         ============================================================= --}}
    @if ($hasStudents && $students->count())
        <div
            class="modal fade"
            id="studentsMissingModal"
            tabindex="-1"
            aria-labelledby="studentsMissingModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content bulk-modal">
                    <div class="modal-header">
                        <div>
                            <span class="cyb-pill cyb-pill-primary mb-2">
                                <i class="bi bi-people"></i>
                                Eligible Students
                            </span>

                            <h5
                                class="modal-title"
                                id="studentsMissingModalLabel"
                            >
                                Students Missing Writeups
                            </h5>

                            <p class="bulk-modal-subtitle">
                                Showing up to {{ $students->count() }} eligible students for
                                {{ $selectedCollegeName }} · SY {{ $selectedYear }}.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>

                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table cyb-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Name</th>
                                        <th>University ID</th>
                                        <th>Program</th>
                                        <th>Major</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($students as $student)
                                        <tr>
                                            <td class="text-muted">
                                                {{ $loop->iteration }}
                                            </td>

                                            <td>
                                                <strong>
                                                    {{ $student->formatted_full_name
                                                        ?? trim($student->last_name . ', ' . $student->first_name) }}
                                                </strong>
                                            </td>

                                            <td>
                                                @if ($student->university_id)
                                                    <span class="cyb-code">
                                                        {{ $student->university_id }}
                                                    </span>
                                                @else
                                                    —
                                                @endif
                                            </td>

                                            <td>
                                                {{ $student->program->program_name ?? 'N/A' }}
                                            </td>

                                            <td>
                                                {{ $student->major->major_name ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($studentCount > $students->count())
                            <div class="bulk-students-more">
                                <i class="bi bi-info-circle"></i>

                                {{ $studentCount - $students->count() }}
                                more eligible students are not shown in this list.
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-light border"
                            data-bs-dismiss="modal"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('styles')
        <style>
            /*
             * Bulk Writeup Creation specific styling.
             * Shared cards, forms, pills, notices, tables and empty states
             * come from the CYB shared styles.
             */

            .bulk-writeup-sidebar,
            .bulk-writeup-main {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .bulk-selection-form {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            /*
             * Guide
             */

            .bulk-guide-list {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .bulk-guide-step {
                display: flex;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .bulk-guide-number {
                display: inline-flex;
                width: 28px;
                height: 28px;
                flex: 0 0 28px;
                align-items: center;
                justify-content: center;
                border-radius: 0.5rem;
                background: #eef3f8;
                color: #49637c;
                font-size: 0.72rem;
                font-weight: 700;
            }

            .bulk-guide-title {
                color: var(--cyb-text, #212529);
                font-size: 0.8rem;
                font-weight: 600;
            }

            .bulk-guide-text {
                margin-top: 0.15rem;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.73rem;
                line-height: 1.45;
            }

            .bulk-guide-warning {
                margin-top: 1rem;
                border: 1px solid #ead8a8;
                border-radius: 0.6rem;
            }

            /*
             * Selected college
             */

            .bulk-college-pill {
                max-width: 260px;
            }

            .bulk-college-pill span {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            /*
             * Summary cards
             */

            .bulk-summary-card {
                display: block;
                width: 100%;
                min-height: 145px;
                padding: 1rem;
                border: 1px solid var(--cyb-border, #e7eaed);
                border-radius: 0.75rem;
                background: #fff;
                color: inherit;
                text-align: left;
                text-decoration: none;
                box-shadow: 0 0.125rem 0.4rem rgba(0, 0, 0, 0.03);
                transition:
                    transform 0.15s ease,
                    border-color 0.15s ease,
                    box-shadow 0.15s ease;
            }

            button.bulk-summary-card {
                appearance: none;
            }

            .bulk-summary-card:hover:not(:disabled) {
                color: inherit;
                transform: translateY(-1px);
                box-shadow: 0 0.4rem 1rem rgba(0, 0, 0, 0.06);
            }

            .bulk-summary-card:disabled {
                cursor: not-allowed;
                opacity: 0.6;
            }

            .bulk-summary-card-primary:hover:not(:disabled) {
                border-color: #b9cce0;
            }

            .bulk-summary-card-success {
                border-color: #cce7d8;
            }

            .bulk-summary-card-success:hover {
                border-color: #a8d4bd;
            }

            .bulk-summary-card-warning {
                border-color: #ead8a8;
                background: #fffdf7;
            }

            .bulk-summary-card-warning:hover {
                border-color: #dfc67f;
            }

            .bulk-summary-card-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
            }

            .bulk-summary-label {
                color: var(--cyb-muted, #6c757d);
                font-size: 0.68rem;
                font-weight: 650;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .bulk-summary-value-row {
                display: flex;
                align-items: flex-end;
                gap: 0.35rem;
                margin-top: 0.35rem;
            }

            .bulk-summary-value {
                color: var(--cyb-text, #212529);
                font-size: 2rem;
                font-weight: 700;
                line-height: 1;
            }

            .bulk-summary-unit {
                padding-bottom: 0.15rem;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.72rem;
                font-weight: 600;
            }

            .bulk-summary-icon {
                display: inline-flex;
                width: 40px;
                height: 40px;
                flex: 0 0 40px;
                align-items: center;
                justify-content: center;
                border-radius: 0.65rem;
                background: #f1f3f5;
                color: #607080;
                font-size: 0.95rem;
            }

            .bulk-summary-card-success .bulk-summary-icon {
                background: #e8f5ee;
                color: #197149;
            }

            .bulk-summary-card-warning .bulk-summary-icon {
                background: #fff4d8;
                color: #87620f;
            }

            .bulk-summary-help {
                margin-top: 1rem;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.72rem;
                line-height: 1.45;
            }

            /*
             * Status
             */

            .bulk-status-notice,
            .bulk-ready-notice {
                border-radius: 0.65rem;
            }

            .bulk-ready-notice {
                border: 1px solid #cce7d8;
                background: #f1faf5;
                color: #24724d;
            }

            .bulk-status-content {
                display: flex;
                width: 100%;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .bulk-status-title {
                font-size: 0.82rem;
                font-weight: 650;
            }

            .bulk-status-text {
                margin-top: 0.15rem;
                font-size: 0.76rem;
                line-height: 1.5;
            }

            /*
             * History
             */

            .bulk-history-list {
                background: #fff;
            }

            .bulk-history-row {
                padding: 1rem 1.2rem;
                border-bottom: 1px solid var(--cyb-border-soft, #edf0f2);
            }

            .bulk-history-row:last-child {
                border-bottom: 0;
            }

            .bulk-history-row-main {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1.25rem;
            }

            .bulk-history-info {
                min-width: 0;
            }

            .bulk-history-title {
                color: var(--cyb-text, #212529);
                font-size: 0.86rem;
            }

            .bulk-history-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 0.35rem 1rem;
                margin-top: 0.45rem;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.72rem;
            }

            .bulk-history-meta span {
                display: inline-flex;
                align-items: center;
                gap: 0.3rem;
            }

            .bulk-history-undone {
                margin-top: 0.4rem;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.72rem;
            }

            .bulk-history-actions {
                display: flex;
                flex: 0 0 auto;
                align-items: center;
                gap: 0.75rem;
            }

            .bulk-history-counts {
                display: flex;
                gap: 0.4rem;
            }

            .bulk-history-count {
                min-width: 70px;
                padding: 0.45rem 0.55rem;
                border: 1px solid var(--cyb-border, #e7eaed);
                border-radius: 0.5rem;
                background: #f8f9fa;
                text-align: center;
            }

            .bulk-history-count strong {
                display: block;
                color: #343a40;
                font-size: 0.92rem;
                line-height: 1.1;
            }

            .bulk-history-count span {
                display: block;
                margin-top: 0.15rem;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.58rem;
                font-weight: 650;
                letter-spacing: 0.03em;
                text-transform: uppercase;
            }

            .bulk-protected-notice {
                margin-top: 0.75rem;
                border: 1px solid #cce7d8;
                border-radius: 0.55rem;
                background: #f1faf5;
                color: #24724d;
            }

            /*
             * Modals
             */

            .bulk-modal {
                overflow: hidden;
                border: 0;
                border-radius: 0.8rem;
                box-shadow: 0 1.25rem 3.5rem rgba(0, 0, 0, 0.18);
            }

            .bulk-modal .modal-header {
                align-items: flex-start;
                padding: 1rem 1.2rem;
                border-bottom-color: var(--cyb-border-soft, #edf0f2);
            }

            .bulk-modal .modal-title {
                margin: 0;
                color: var(--cyb-text, #212529);
                font-size: 1rem;
                font-weight: 650;
            }

            .bulk-modal .modal-body {
                padding: 1.2rem;
            }

            .bulk-modal .modal-footer {
                padding: 0.85rem 1.2rem;
                border-top-color: var(--cyb-border-soft, #edf0f2);
                background: #fafbfc;
            }

            .bulk-modal-notice {
                border-radius: 0.55rem;
            }

            .bulk-confirm-details {
                margin-top: 1rem;
                color: #343a40;
                font-size: 0.82rem;
                line-height: 1.55;
            }

            .bulk-confirm-details p {
                margin-bottom: 0.8rem;
            }

            .bulk-confirm-details p:last-child {
                margin-bottom: 0;
            }

            .bulk-modal-stat {
                height: 100%;
                padding: 0.85rem;
                border: 1px solid var(--cyb-border, #e7eaed);
                border-radius: 0.6rem;
                background: #f8f9fa;
                text-align: center;
            }

            .bulk-modal-stat strong {
                display: block;
                font-size: 1.4rem;
                line-height: 1.1;
            }

            .bulk-modal-stat span {
                display: block;
                margin-top: 0.25rem;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.7rem;
                font-weight: 600;
            }

            .bulk-modal-description,
            .bulk-modal-subtitle {
                color: var(--cyb-muted, #6c757d);
                font-size: 0.76rem;
                line-height: 1.5;
            }

            .bulk-modal-description {
                margin: 1rem 0 0;
            }

            .bulk-modal-subtitle {
                margin: 0.25rem 0 0;
            }

            .bulk-students-more {
                display: flex;
                align-items: center;
                gap: 0.45rem;
                padding: 0.75rem 1rem;
                border-top: 1px solid var(--cyb-border-soft, #edf0f2);
                background: #f8f9fa;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.76rem;
            }

            /*
             * Responsive
             */

            @media (min-width: 1200px) {
                .bulk-writeup-sidebar {
                    position: sticky;
                    top: 1rem;
                }
            }

            @media (max-width: 991.98px) {
                .bulk-history-row-main {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .bulk-history-actions {
                    width: 100%;
                    justify-content: space-between;
                }
            }

            @media (max-width: 767.98px) {
                .bulk-status-content {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .bulk-status-content .btn {
                    width: 100%;
                }

                .bulk-history-actions {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .bulk-history-counts {
                    width: 100%;
                }

                .bulk-history-count {
                    flex: 1;
                    min-width: 0;
                }
            }
        </style>
    @endpush
</x-app-layout>