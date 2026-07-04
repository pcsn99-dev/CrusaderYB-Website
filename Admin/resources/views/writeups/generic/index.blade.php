<x-app-layout>
    <x-slot name="header">
        Generic Writeups
    </x-slot>

    <x-slot name="subheader">
        Manage reusable writeups for bulk assignment.
    </x-slot>

    <div class="row g-3">

        {{-- Filter / Summary --}}
        <div class="col-12 col-xl-3">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title m-0">Filters</h3>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('writeups.generic.index') }}" class="d-grid gap-3">
                        <div>
                            <label for="year" class="form-label">School Year</label>
                            <select id="year" name="year" class="form-select">
                                <option value="">All Years</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" @selected($selectedYear == $year)>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="college_id" class="form-label">College</label>
                            <select id="college_id" name="college_id" class="form-select">
                                <option value="">All Colleges</option>
                                @foreach ($colleges as $college)
                                    <option value="{{ $college->id }}" @selected($selectedCollegeId == $college->id)>
                                        {{ $college->college_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i>
                            Apply Filters
                        </button>

                        <a href="{{ route('writeups.generic.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                            Clear Filters
                        </a>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small">Current Count</div>

                    <div class="d-flex align-items-end gap-2">
                        <div class="display-6 fw-semibold mb-0">{{ $currentCount }}</div>
                        <div class="text-secondary mb-2">/ 20</div>
                    </div>

                    @if ($selectedYear && $selectedCollegeId)
                        @if ($currentCount >= 20)
                            <div class="alert alert-warning small mb-0 mt-3">
                                Maximum generic writeups reached for this year and college.
                            </div>
                        @else
                            <div class="text-secondary small mt-2">
                                {{ 20 - $currentCount }} more writeups can still be created.
                            </div>
                        @endif
                    @else
                        <div class="text-secondary small mt-2">
                            Select a year and college to manage the 20-writeup limit properly.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="col-12 col-xl-9">
            <div class="card">

                <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div>
                        <h3 class="card-title m-0">Generic Writeup List</h3>
                        <div class="text-secondary small">
                            Reusable writeups that can be assigned to students later.
                        </div>
                    </div>

                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#createGenericWriteupModal"
                        @disabled($selectedYear && $selectedCollegeId && $currentCount >= 20)
                    >
                        <i class="bi bi-plus-lg"></i>
                        New Writeup
                    </button>
                </div>


                <div class="card-body">
                    <div class="row g-3">
                        @forelse ($genericWriteups as $genericWriteup)
                            <div class="col-12 col-md-6">
                                <div class="card h-100 border">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <div>
                                                <div class="fw-semibold">
                                                    Generic Writeup #{{ $genericWriteups->firstItem() + $loop->index }}
                                                </div>

                                                <div class="text-secondary small">
                                                    {{ $genericWriteup->year }}
                                                    ·
                                                    {{ $genericWriteup->college->college_name ?? 'No college' }}
                                                </div>
                                            </div>

                                            @if ($genericWriteup->is_active)
                                                <span class="badge text-bg-success">Active</span>
                                            @else
                                                <span class="badge text-bg-secondary">Inactive</span>
                                            @endif
                                        </div>

                                        <p class="mb-3 flex-grow-1">
                                            {{ $genericWriteup->content }}
                                        </p>

                                        <div class="text-secondary small mb-3">
                                            Created by {{ $genericWriteup->creator->name ?? 'Unknown' }}
                                            <br>
                                            Updated {{ $genericWriteup->updated_at?->diffForHumans() ?? 'N/A' }}
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 mt-auto">
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editGenericWriteupModal{{ $genericWriteup->id }}"
                                            >
                                                <i class="bi bi-pencil"></i>
                                                Edit
                                            </button>

                                            <form
                                                method="POST"
                                                action="{{ route('writeups.generic.destroy', $genericWriteup) }}"
                                                onsubmit="return confirm('Delete this generic writeup?')"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center text-secondary py-5">
                                    <i class="bi bi-file-earmark-text fs-1 d-block mb-2"></i>
                                    No generic writeups found.
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $genericWriteups->links() }}
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createGenericWriteupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="POST" action="{{ route('writeups.generic.store') }}" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">New Generic Writeup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label">School Year</label>
                            <select name="year" class="form-select" required>
                                <option value="">Select Year</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" @selected(old('year', $selectedYear) == $year)>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">College</label>
                            <select name="college_id" class="form-select" required>
                                <option value="">Select College</option>
                                @foreach ($colleges as $college)
                                    <option value="{{ $college->id }}" @selected(old('college_id', $selectedCollegeId) == $college->id)>
                                        {{ $college->college_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Writeup Content</label>
                            <textarea
                                name="content"
                                rows="6"
                                maxlength="300"
                                class="form-control"
                                required
                            >{{ old('content') }}</textarea>
                            <div class="form-text">Maximum of 300 characters.</div>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    class="form-check-input"
                                    id="create_is_active"
                                    checked
                                >
                                <label class="form-check-label" for="create_is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Save Writeup
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modals --}}
    @foreach ($genericWriteups as $genericWriteup)
        <div class="modal fade" id="editGenericWriteupModal{{ $genericWriteup->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <form method="POST" action="{{ route('writeups.generic.update', $genericWriteup) }}" class="modal-content">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Generic Writeup</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-12 col-md-6">
                                <label class="form-label">School Year</label>
                                <select name="year" class="form-select" required>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}" @selected(old('year', $genericWriteup->year) == $year)>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">College</label>
                                <select name="college_id" class="form-select" required>
                                    @foreach ($colleges as $college)
                                        <option value="{{ $college->id }}" @selected(old('college_id', $genericWriteup->college_id) == $college->id)>
                                            {{ $college->college_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Writeup Content</label>
                                <textarea
                                    name="content"
                                    rows="6"
                                    maxlength="300"
                                    class="form-control"
                                    required
                                >{{ old('content', $genericWriteup->content) }}</textarea>
                                <div class="form-text">Maximum of 300 characters.</div>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        class="form-check-input"
                                        id="edit_is_active_{{ $genericWriteup->id }}"
                                        @checked(old('is_active', $genericWriteup->is_active))
                                    >
                                    <label class="form-check-label" for="edit_is_active_{{ $genericWriteup->id }}">
                                        Active
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-primary">
                            Update Writeup
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

</x-app-layout>