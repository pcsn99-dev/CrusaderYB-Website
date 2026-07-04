<x-app-layout>
    <x-slot name="header">
        Generic Writeups
    </x-slot>

    <x-slot name="subheader">
        Manage reusable writeups for bulk assignment.
    </x-slot>

    <div
        id="generic-writeups-app"
        data-writeups='@json($genericWriteups->items())'
        data-years='@json($years)'
        data-colleges='@json($colleges)'
        data-selected-year="{{ $selectedYear }}"
        data-selected-college-id="{{ $selectedCollegeId }}"
        data-current-count="{{ $currentCount }}"
    ></div>
    

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