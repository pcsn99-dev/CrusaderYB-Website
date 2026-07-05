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

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">

        {{-- Left Side --}}
        <aside class="xl:col-span-4">
            <div class="space-y-4">

                {{-- Selection --}}
                <div class="cyb-page-card overflow-hidden">
                    <div class="border-b border-[var(--cyb-border)] bg-white px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="cyb-chip cyb-chip-role">
                                    <i class="bi bi-funnel"></i>
                                    Selection
                                </span>

                                <h2 class="mb-0 mt-2 text-base font-bold text-[var(--cyb-primary)]">
                                    Choose Group
                                </h2>

                                <p class="mb-0 mt-1 text-sm text-[var(--cyb-muted)]">
                                    Select a school year and college to check missing writeups.
                                </p>
                            </div>

                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--cyb-primary-soft)] text-[var(--cyb-primary)]">
                                <i class="bi bi-search"></i>
                            </span>
                        </div>
                    </div>

                    <div class="p-5">
                        <form method="GET" action="{{ route('writeups.bulk.index') }}" class="space-y-4">
                            <div>
                                <label for="year" class="mb-1 block text-xs font-bold uppercase tracking-wide text-[var(--cyb-muted)]">
                                    School Year
                                </label>

                                <select
                                    id="year"
                                    name="year"
                                    class="block w-full rounded-xl border border-[var(--cyb-border)] bg-white px-3 py-2.5 text-sm font-semibold text-[var(--cyb-text)] shadow-sm transition focus:border-[var(--cyb-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)]/20"
                                    required
                                >
                                    <option value="">Select Year</option>

                                    @foreach ($years as $year)
                                        <option value="{{ $year }}" @selected($selectedYear == $year)>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="college_id" class="mb-1 block text-xs font-bold uppercase tracking-wide text-[var(--cyb-muted)]">
                                    College
                                </label>

                                <select
                                    id="college_id"
                                    name="college_id"
                                    class="block w-full rounded-xl border border-[var(--cyb-border)] bg-white px-3 py-2.5 text-sm font-semibold text-[var(--cyb-text)] shadow-sm transition focus:border-[var(--cyb-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)]/20"
                                    required
                                >
                                    <option value="">Select College</option>

                                    @foreach ($colleges as $college)
                                        <option value="{{ $college->id }}" @selected($selectedCollegeId == $college->id)>
                                            {{ $college->college_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row">
                                <button
                                    type="submit"
                                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-[var(--cyb-primary)] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[var(--cyb-primary-dark)]"
                                >
                                    <i class="bi bi-search"></i>
                                    Check Missing
                                </button>

                                @if ($hasSelection)
                                    <a
                                        href="{{ route('writeups.bulk.index') }}"
                                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-4 py-2.5 text-sm font-bold text-[var(--cyb-text)] shadow-sm transition hover:bg-slate-50"
                                    >
                                        <i class="bi bi-x-lg"></i>
                                        Clear
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Instruction Card --}}
                <div class="cyb-page-card overflow-hidden">
                    <div class="border-b border-[var(--cyb-border)] bg-[var(--cyb-primary-soft)] px-5 py-4">
                        <span class="cyb-chip cyb-chip-neutral">
                            <i class="bi bi-info-circle"></i>
                            How this works
                        </span>
                    </div>

                    <div class="space-y-3 p-5">
                        <div class="flex gap-3">
                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white text-xs font-bold text-[var(--cyb-primary)] shadow-sm">
                                1
                            </span>

                            <div>
                                <p class="mb-0 text-sm font-bold text-[var(--cyb-text)]">
                                    Select a group
                                </p>
                                <p class="mb-0 text-xs leading-5 text-[var(--cyb-muted)]">
                                    Choose the school year and college you want to process.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white text-xs font-bold text-[var(--cyb-primary)] shadow-sm">
                                2
                            </span>

                            <div>
                                <p class="mb-0 text-sm font-bold text-[var(--cyb-text)]">
                                    Check counts
                                </p>
                                <p class="mb-0 text-xs leading-5 text-[var(--cyb-muted)]">
                                    Make sure there are students missing writeups and active generic writeups available.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white text-xs font-bold text-[var(--cyb-primary)] shadow-sm">
                                3
                            </span>

                            <div>
                                <p class="mb-0 text-sm font-bold text-[var(--cyb-text)]">
                                    Confirm creation
                                </p>
                                <p class="mb-0 text-xs leading-5 text-[var(--cyb-muted)]">
                                    The system randomly assigns active generic writeups to all eligible students.
                                </p>
                            </div>
                        </div>

                        <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-3 py-2 text-xs font-medium leading-5 text-yellow-800">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Review the selected group before confirming. Bulk creation affects multiple students at once.
                        </div>
                    </div>
                </div>

            </div>
        </aside>

        {{-- Main Content --}}
        <section class="xl:col-span-8">
            <div class="cyb-page-card overflow-hidden">

                {{-- Summary Header --}}
                <div class="border-b border-[var(--cyb-border)] bg-white px-5 py-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <span class="cyb-badge-soft">
                                <i class="bi bi-clipboard-data"></i>
                                Bulk Creation Summary
                            </span>

                            <h2 class="mb-0 mt-2 text-lg font-bold text-[var(--cyb-primary)]">
                                Missing Writeup Check
                            </h2>

                            <p class="mb-0 mt-1 text-sm text-[var(--cyb-muted)]">
                                Check student eligibility and available generic writeups before running bulk creation.
                            </p>
                        </div>

                        @if ($hasSelection)
                            <div class="flex flex-wrap gap-2 lg:justify-end">
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-100 bg-[var(--cyb-primary-soft)] px-2.5 py-1 text-xs font-bold text-[var(--cyb-primary)]">
                                    <i class="bi bi-calendar3"></i>
                                    SY {{ $selectedYear }}
                                </span>

                                <span class="inline-flex max-w-full items-center gap-1.5 rounded-full border border-pink-100 bg-pink-50 px-2.5 py-1 text-xs font-bold text-pink-700">
                                    <i class="bi bi-building"></i>
                                    <span class="truncate">
                                        {{ $selectedCollegeName }}
                                    </span>
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-5">
                    @if (! $hasSelection)
                        <div class="rounded-2xl border border-dashed border-[var(--cyb-border)] bg-slate-50 px-5 py-14 text-center">
                            <div class="mx-auto mb-3 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-[var(--cyb-primary)] shadow-sm">
                                <i class="bi bi-arrow-left-circle text-2xl"></i>
                            </div>

                            <h3 class="mb-1 text-base font-bold text-[var(--cyb-text)]">
                                Choose a school year and college
                            </h3>

                            <p class="mb-0 text-sm text-[var(--cyb-muted)]">
                                Use the selection panel to check which students are eligible for bulk writeup creation.
                            </p>
                        </div>
                    @else
                        {{-- Count Cards --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                            {{-- Students Missing --}}
                            <button
                                type="button"
                                data-bs-toggle="modal"
                                data-bs-target="#studentsMissingModal"
                                @disabled($studentCount <= 0)
                                class="group rounded-2xl border bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-60
                                    {{ $studentCount > 0 ? 'border-blue-100 hover:border-blue-200' : 'border-slate-200' }}"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="mb-1 text-xs font-bold uppercase tracking-wide text-[var(--cyb-muted)]">
                                            Students Missing Writeups
                                        </p>

                                        <div class="flex items-end gap-2">
                                            <span class="text-4xl font-bold leading-none text-[var(--cyb-primary)]">
                                                {{ $studentCount }}
                                            </span>

                                            <span class="pb-1 text-xs font-semibold text-[var(--cyb-muted)]">
                                                eligible
                                            </span>
                                        </div>
                                    </div>

                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                                        <i class="bi bi-people"></i>
                                    </span>
                                </div>

                                <p class="mb-0 mt-3 text-xs text-[var(--cyb-muted)]">
                                    @if ($studentCount > 0)
                                        Click to view eligible students.
                                    @else
                                        No eligible students found for this group.
                                    @endif
                                </p>
                            </button>

                            {{-- Generic Writeups --}}
                            <a
                                href="{{ route('writeups.generic.index', ['year' => $selectedYear, 'college_id' => $selectedCollegeId]) }}"
                                class="group rounded-2xl border bg-white p-4 text-left text-decoration-none shadow-sm transition hover:-translate-y-0.5 hover:shadow-md
                                    {{ $genericWriteupCount > 0 ? 'border-green-100 hover:border-green-200' : 'border-yellow-200 bg-yellow-50/40 hover:border-yellow-300' }}"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="mb-1 text-xs font-bold uppercase tracking-wide text-[var(--cyb-muted)]">
                                            Active Generic Writeups
                                        </p>

                                        <div class="flex items-end gap-2">
                                            <span class="text-4xl font-bold leading-none {{ $genericWriteupCount > 0 ? 'text-green-700' : 'text-yellow-800' }}">
                                                {{ $genericWriteupCount }}
                                            </span>

                                            <span class="pb-1 text-xs font-semibold text-[var(--cyb-muted)]">
                                                available
                                            </span>
                                        </div>
                                    </div>

                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $genericWriteupCount > 0 ? 'bg-green-50 text-green-700' : 'bg-yellow-100 text-yellow-800' }}">
                                        <i class="bi bi-card-text"></i>
                                    </span>
                                </div>

                                <p class="mb-0 mt-3 text-xs text-[var(--cyb-muted)]">
                                    Click to manage generic writeups for this group.
                                </p>
                            </a>

                        </div>

                        {{-- Status / Action --}}
                        <div class="mt-5">
                            @if ($studentCount <= 0)
                                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4 text-blue-700">
                                    <div class="flex gap-3">
                                        <i class="bi bi-info-circle mt-0.5"></i>

                                        <div>
                                            <p class="mb-1 font-bold">
                                                No eligible students found.
                                            </p>

                                            <p class="mb-0 text-sm">
                                                There are no subscribed students without pictorial attendance and without writeups for this selected group.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @elseif ($genericWriteupCount <= 0)
                                <div class="rounded-2xl border border-yellow-200 bg-yellow-50 px-4 py-4 text-yellow-800">
                                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                        <div class="flex gap-3">
                                            <i class="bi bi-exclamation-triangle mt-0.5"></i>

                                            <div>
                                                <p class="mb-1 font-bold">
                                                    No active generic writeups found.
                                                </p>

                                                <p class="mb-0 text-sm">
                                                    Create active generic writeups first before running bulk creation.
                                                </p>
                                            </div>
                                        </div>

                                        <a
                                            href="{{ route('writeups.generic.index', ['year' => $selectedYear, 'college_id' => $selectedCollegeId]) }}"
                                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-yellow-100 px-3 py-2 text-sm font-bold text-yellow-900 transition hover:bg-yellow-200"
                                        >
                                            <i class="bi bi-plus-lg"></i>
                                            Manage Generic Writeups
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-4 text-green-700">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="flex gap-3">
                                            <i class="bi bi-check2-circle mt-0.5"></i>

                                            <div>
                                                <p class="mb-1 font-bold">
                                                    Ready for bulk creation.
                                                </p>

                                                <p class="mb-0 text-sm">
                                                    The system will randomly assign
                                                    <strong>{{ $genericWriteupCount }}</strong>
                                                    active generic writeups to
                                                    <strong>{{ $studentCount }}</strong>
                                                    eligible students.
                                                </p>
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--cyb-primary)] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[var(--cyb-primary-dark)]"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmBulkCreateModal"
                                        >
                                            <i class="bi bi-magic"></i>
                                            Bulk Create Writeups
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>

    </div>

    {{-- Confirm Bulk Create Modal --}}
    @if ($canBulkCreate)
        <div class="modal fade" id="confirmBulkCreateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('writeups.bulk.store') }}" class="modal-content overflow-hidden rounded-2xl border-0 shadow-lg">
                    @csrf

                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <input type="hidden" name="college_id" value="{{ $selectedCollegeId }}">

                    <div class="modal-header border-bottom bg-[var(--cyb-primary-soft)] px-4 py-3">
                        <div>
                            <span class="cyb-chip cyb-chip-role">
                                <i class="bi bi-magic"></i>
                                Confirm Action
                            </span>

                            <h5 class="modal-title mt-2 font-bold text-[var(--cyb-primary)]">
                                Confirm Bulk Creation
                            </h5>
                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body px-4 py-4">
                        <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-3 py-3 text-sm text-yellow-800">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            This action will create writeups for multiple students.
                        </div>

                        <div class="mt-4 space-y-3 text-sm text-[var(--cyb-text)]">
                            <p class="mb-0">
                                This will create writeups for
                                <strong>{{ $studentCount }}</strong>
                                eligible students under
                                <strong>{{ $selectedCollegeName }}</strong>
                                for school year
                                <strong>{{ $selectedYear }}</strong>.
                            </p>

                            <p class="mb-0">
                                The system will randomly reuse
                                <strong>{{ $genericWriteupCount }}</strong>
                                active generic writeups.
                            </p>
                        </div>
                    </div>

                    <div class="modal-footer border-top bg-slate-50 px-4 py-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-primary fw-bold">
                            <i class="bi bi-check2-circle me-1"></i>
                            Confirm and Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Students Missing Modal --}}
    @if ($hasStudents && $students->count())
        <div class="modal fade" id="studentsMissingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content overflow-hidden rounded-2xl border-0 shadow-lg">

                    <div class="modal-header border-bottom bg-white px-4 py-3">
                        <div>
                            <span class="cyb-chip cyb-chip-username">
                                <i class="bi bi-people"></i>
                                Eligible Students
                            </span>

                            <h5 class="modal-title mt-2 font-bold text-[var(--cyb-primary)]">
                                Students Missing Writeups
                            </h5>

                            <div class="text-sm text-[var(--cyb-muted)]">
                                Showing up to {{ $students->count() }} eligible students for {{ $selectedCollegeName }} · SY {{ $selectedYear }}.
                            </div>
                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="60">#</th>
                                        <th>Name</th>
                                        <th>University ID</th>
                                        <th>Program</th>
                                        <th>Major</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($students as $student)
                                        <tr>
                                            <td class="text-secondary">
                                                {{ $loop->iteration }}
                                            </td>

                                            <td class="fw-semibold">
                                                {{ $student->formatted_full_name ?? trim($student->last_name . ', ' . $student->first_name) }}
                                            </td>

                                            <td>
                                                {{ $student->university_id ?? 'N/A' }}
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
                            <div class="border-top bg-slate-50 px-4 py-3 text-sm text-[var(--cyb-muted)]">
                                <i class="bi bi-info-circle me-1"></i>
                                {{ $studentCount - $students->count() }} more eligible students are not shown in this list.
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer border-top bg-slate-50 px-4 py-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</x-app-layout>