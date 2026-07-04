<x-app-layout>
    <x-slot name="header">
        Bulk Create Missing Writeups
    </x-slot>

    <x-slot name="subheader">
        Randomly assign active generic writeups to subscribed students without pictorial attendance.
    </x-slot>

    <div class="row g-3">

        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title m-0">Selection</h3>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('writeups.bulk.index') }}" class="d-grid gap-3">
                        <div>
                            <label for="year" class="form-label">School Year</label>
                            <select id="year" name="year" class="form-select" required>
                                <option value="">Select Year</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" @selected($selectedYear == $year)>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="college_id" class="form-label">College</label>
                            <select id="college_id" name="college_id" class="form-select" required>
                                <option value="">Select College</option>
                                @foreach ($colleges as $college)
                                    <option value="{{ $college->id }}" @selected($selectedCollegeId == $college->id)>
                                        {{ $college->college_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                            Check Missing Writeups
                        </button>

                        <a href="{{ route('writeups.bulk.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                            Clear
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between gap-2">
                    <div>
                        <h3 class="card-title m-0">Bulk Creation Summary</h3>
                        <div class="text-secondary small">
                            Select a year and college before creating missing writeups.
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if (! $selectedYear || ! $selectedCollegeId)
                        <div class="text-center text-secondary py-5">
                            <i class="bi bi-arrow-left-circle fs-1 d-block mb-2"></i>
                            Choose a school year and college to continue.
                        </div>
                    @else
                        <div class="mb-3">
                            <div class="text-secondary small">Selected Group</div>
                            <div class="fw-semibold">
                                {{ $selectedYear }} · {{ $selectedCollege->college_name ?? 'Selected college' }}
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <button
                                type="button"
                                class="border rounded p-3 h-100 w-100 text-start bg-body"
                                data-bs-toggle="modal"
                                data-bs-target="#studentsMissingModal"
                            >
                                <div class="text-secondary small">Students Missing Writeups</div>
                                <div class="display-6 fw-semibold">{{ $studentCount }}</div>
                                <div class="small text-secondary">
                                    Click to view eligible students.
                                </div>
                            </button>

                            <a
                                href="{{ route('writeups.generic.index', ['year' => $selectedYear, 'college_id' => $selectedCollegeId]) }}"
                                class="text-decoration-none text-body"
                            >
                                <div class="border rounded p-3 h-100">
                                    <div class="text-secondary small">Active Generic Writeups</div>
                                    <div class="display-6 fw-semibold">{{ $genericWriteupCount }}</div>
                                    <div class="small text-secondary">
                                        Click to manage generic writeups for this group.
                                    </div>
                                </div>
                            </a>
                        </div>

                        @if ($studentCount <= 0)
                            <div class="alert alert-info">
                                No eligible students found for this year and college.
                            </div>
                        @elseif ($genericWriteupCount <= 0)
                            <div class="alert alert-warning">
                                No active generic writeups found. Create generic writeups first before bulk creation.
                            </div>
                        @else
                            <div class="alert alert-secondary">
                                The system will randomly assign the available generic writeups to all eligible students.
                            </div>

                            <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#studentPreview"
                                >
                                    <i class="bi bi-list-ul"></i>
                                    Preview Students
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmBulkCreateModal"
                                >
                                    <i class="bi bi-magic"></i>
                                    Bulk Create Writeups
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            @if ($selectedYear && $selectedCollegeId && $students->count())
                <div class="collapse mt-3" id="studentPreview">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title m-0">Student Preview</h3>
                            <div class="text-secondary small">
                                Eligible students for bulk writeup creation.
                            </div>
                        </div>

                        <div class="card-body p-0">
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
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="fw-semibold">
                                                    {{ $student->formatted_full_name ?? trim($student->last_name . ', ' . $student->first_name) }}
                                                </td>
                                                <td>{{ $student->university_id ?? 'N/A' }}</td>
                                                <td>{{ $student->program->program_name ?? 'N/A' }}</td>
                                                <td>{{ $student->major->major_name ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if ($studentCount > 20)
                                <div class="border-top px-3 py-2 small text-secondary">
                                    {{ $studentCount - 20 }} more eligible students are not shown in this preview.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>








    @if ($selectedYear && $selectedCollegeId && $studentCount > 0 && $genericWriteupCount > 0)
        <div class="modal fade" id="confirmBulkCreateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('writeups.bulk.store') }}" class="modal-content">
                    @csrf

                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <input type="hidden" name="college_id" value="{{ $selectedCollegeId }}">

                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Bulk Creation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p>
                            This will create writeups for
                            <strong>{{ $studentCount }}</strong>
                            eligible students under
                            <strong>{{ $selectedCollege->college_name ?? 'the selected college' }}</strong>
                            for school year
                            <strong>{{ $selectedYear }}</strong>.
                        </p>

                        <p class="mb-0">
                            The system will randomly reuse
                            <strong>{{ $genericWriteupCount }}</strong>
                            active generic writeups.
                        </p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-primary">
                            Confirm and Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif




    @if ($selectedYear && $selectedCollegeId && $students->count())
        <div class="modal fade" id="studentsMissingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Students Missing Writeups</h5>
                            <div class="text-secondary small">
                                Showing up to 20 eligible students.
                            </div>
                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>University ID</th>
                                        <th>Program</th>
                                        <th>Major</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($students as $student)
                                        <tr>
                                            <td class="fw-semibold">
                                                {{ $student->formatted_full_name ?? trim($student->last_name . ', ' . $student->first_name) }}
                                            </td>
                                            <td>{{ $student->university_id ?? 'N/A' }}</td>
                                            <td>{{ $student->program->program_name ?? 'N/A' }}</td>
                                            <td>{{ $student->major->major_name ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>


                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</x-app-layout>