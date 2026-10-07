<x-app-layout>
    <x-slot name="header">
        Generic Writeups
    </x-slot>

    <x-slot name="subheader">
        Manage reusable writeups for bulk assignment.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-files"></i>
    </x-slot>

    <x-slot name="headerActions">
        <button
            type="button"
            class="btn btn-primary d-inline-flex align-items-center gap-2"
            data-bs-toggle="modal"
            data-bs-target="#createGenericWriteupModal"
        >
            <i class="bi bi-plus-lg"></i>
            Create Generic Writeup
        </button>
    </x-slot>

    <div class="cyb-page">
        <div
            id="generic-writeups-app"
            data-writeups='@json($genericWriteups->items())'
            data-years='@json($years)'
            data-colleges='@json($colleges)'
            data-selected-year="{{ $selectedYear }}"
            data-selected-college-id="{{ $selectedCollegeId }}"
            data-current-count="{{ $currentCount }}"
        ></div>
    </div>

    {{-- =========================================================
         CREATE GENERIC WRITEUP
         ========================================================= --}}
    <div
        class="modal fade"
        id="createGenericWriteupModal"
        tabindex="-1"
        aria-labelledby="createGenericWriteupModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
            <form
                method="POST"
                action="{{ route('writeups.generic.store') }}"
                class="modal-content cyb-modal"
            >
                @csrf

                <div class="modal-header">
                    <div>
                        <h5
                            class="modal-title"
                            id="createGenericWriteupModalLabel"
                        >
                            New Generic Writeup
                        </h5>

                        <p class="cyb-modal-description">
                            Create reusable content for a specific school year and college.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>

                <div class="modal-body">
                    <div class="row g-4">

                        {{-- School Year --}}
                        <div class="col-12 col-md-6">
                            <label
                                for="create_year"
                                class="cyb-form-label"
                            >
                                School Year
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="year"
                                id="create_year"
                                required
                                class="form-select cyb-form-control @error('year') is-invalid @enderror"
                            >
                                <option value="">
                                    Select Year
                                </option>

                                @foreach ($years as $year)
                                    <option
                                        value="{{ $year }}"
                                        @selected(old('year', $selectedYear) == $year)
                                    >
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>

                            @error('year')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- College --}}
                        <div class="col-12 col-md-6">
                            <label
                                for="create_college_id"
                                class="cyb-form-label"
                            >
                                College
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="college_id"
                                id="create_college_id"
                                required
                                class="form-select cyb-form-control @error('college_id') is-invalid @enderror"
                            >
                                <option value="">
                                    Select College
                                </option>

                                @foreach ($colleges as $college)
                                    <option
                                        value="{{ $college->id }}"
                                        @selected(
                                            old('college_id', $selectedCollegeId)
                                            == $college->id
                                        )
                                    >
                                        {{ $college->college_name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('college_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Content --}}
                        <div class="col-12">
                            <label
                                for="create_content"
                                class="cyb-form-label"
                            >
                                Writeup Content
                                <span class="text-danger">*</span>
                            </label>

                            <textarea
                                name="content"
                                id="create_content"
                                rows="7"
                                maxlength="300"
                                required
                                class="form-control cyb-form-control generic-writeup-textarea @error('content') is-invalid @enderror"
                                placeholder="Enter the reusable writeup content..."
                            >{{ old('content') }}</textarea>

                            @error('content')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="cyb-form-help">
                                Maximum of 300 characters.
                            </div>
                        </div>

                        {{-- Active --}}
                        <div class="col-12">
                            <div class="generic-writeup-status">
                                <div class="form-check generic-writeup-status-check">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        class="form-check-input"
                                        id="create_is_active"
                                        @checked(old('is_active', true))
                                    >

                                    <label
                                        class="form-check-label"
                                        for="create_is_active"
                                    >
                                        <span class="generic-writeup-status-title">
                                            Active
                                        </span>

                                        <span class="generic-writeup-status-description">
                                            Active generic writeups are available for assignment.
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

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
                        Save Writeup
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- =========================================================
         EDIT GENERIC WRITEUPS
         ========================================================= --}}
    @foreach ($genericWriteups as $genericWriteup)
        <div
            class="modal fade"
            id="editGenericWriteupModal{{ $genericWriteup->id }}"
            tabindex="-1"
            aria-labelledby="editGenericWriteupModalLabel{{ $genericWriteup->id }}"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
                <form
                    method="POST"
                    action="{{ route('writeups.generic.update', $genericWriteup) }}"
                    class="modal-content cyb-modal"
                >
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <div>
                            <h5
                                class="modal-title"
                                id="editGenericWriteupModalLabel{{ $genericWriteup->id }}"
                            >
                                Edit Generic Writeup
                            </h5>

                            <p class="cyb-modal-description">
                                Update the assignment scope, content, or availability.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-4">

                            {{-- School Year --}}
                            <div class="col-12 col-md-6">
                                <label
                                    for="edit_year_{{ $genericWriteup->id }}"
                                    class="cyb-form-label"
                                >
                                    School Year
                                    <span class="text-danger">*</span>
                                </label>

                                <select
                                    name="year"
                                    id="edit_year_{{ $genericWriteup->id }}"
                                    required
                                    class="form-select cyb-form-control"
                                >
                                    @foreach ($years as $year)
                                        <option
                                            value="{{ $year }}"
                                            @selected(
                                                old(
                                                    'year',
                                                    $genericWriteup->year
                                                ) == $year
                                            )
                                        >
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- College --}}
                            <div class="col-12 col-md-6">
                                <label
                                    for="edit_college_id_{{ $genericWriteup->id }}"
                                    class="cyb-form-label"
                                >
                                    College
                                    <span class="text-danger">*</span>
                                </label>

                                <select
                                    name="college_id"
                                    id="edit_college_id_{{ $genericWriteup->id }}"
                                    required
                                    class="form-select cyb-form-control"
                                >
                                    @foreach ($colleges as $college)
                                        <option
                                            value="{{ $college->id }}"
                                            @selected(
                                                old(
                                                    'college_id',
                                                    $genericWriteup->college_id
                                                ) == $college->id
                                            )
                                        >
                                            {{ $college->college_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Content --}}
                            <div class="col-12">
                                <label
                                    for="edit_content_{{ $genericWriteup->id }}"
                                    class="cyb-form-label"
                                >
                                    Writeup Content
                                    <span class="text-danger">*</span>
                                </label>

                                <textarea
                                    name="content"
                                    id="edit_content_{{ $genericWriteup->id }}"
                                    rows="7"
                                    maxlength="300"
                                    required
                                    class="form-control cyb-form-control generic-writeup-textarea"
                                    placeholder="Enter the reusable writeup content..."
                                >{{ old('content', $genericWriteup->content) }}</textarea>

                                <div class="cyb-form-help">
                                    Maximum of 300 characters.
                                </div>
                            </div>

                            {{-- Active --}}
                            <div class="col-12">
                                <div class="generic-writeup-status">
                                    <div class="form-check generic-writeup-status-check">
                                        <input
                                            type="checkbox"
                                            name="is_active"
                                            value="1"
                                            class="form-check-input"
                                            id="edit_is_active_{{ $genericWriteup->id }}"
                                            @checked(
                                                old(
                                                    'is_active',
                                                    $genericWriteup->is_active
                                                )
                                            )
                                        >

                                        <label
                                            class="form-check-label"
                                            for="edit_is_active_{{ $genericWriteup->id }}"
                                        >
                                            <span class="generic-writeup-status-title">
                                                Active
                                            </span>

                                            <span class="generic-writeup-status-description">
                                                Active generic writeups are available for assignment.
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

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
                            Update Writeup
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    @push('styles')
        <style>
            /*
             * Generic Writeups page-specific styles.
             * Form labels, controls and help text use shared CYB styles.
             */

            .cyb-modal {
                overflow: hidden;
                border: 0;
                border-radius: 0.8rem;
                box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.14);
            }

            .cyb-modal .modal-header {
                align-items: flex-start;
                padding: 1.1rem 1.25rem;
                border-bottom-color: var(--cyb-border-soft, #edf0f2);
            }

            .cyb-modal .modal-title {
                color: var(--cyb-text, #212529);
                font-size: 1rem;
                font-weight: 650;
            }

            .cyb-modal-description {
                margin: 0.2rem 0 0;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.76rem;
                line-height: 1.45;
            }

            .cyb-modal .modal-body {
                padding: 1.25rem;
            }

            .cyb-modal .modal-footer {
                padding: 0.9rem 1.25rem;
                border-top-color: var(--cyb-border-soft, #edf0f2);
                background: #fafbfc;
            }

            .generic-writeup-textarea {
                min-height: 170px;
                line-height: 1.6;
                resize: vertical;
            }

            .generic-writeup-status {
                padding: 0.9rem 1rem;
                border: 1px solid var(--cyb-border, #e7eaed);
                border-radius: 0.65rem;
                background: #f8f9fa;
            }

            .generic-writeup-status-check {
                display: flex;
                align-items: flex-start;
                gap: 0.7rem;
                margin: 0;
                padding: 0;
            }

            .generic-writeup-status-check .form-check-input {
                width: 1.05rem;
                height: 1.05rem;
                flex: 0 0 1.05rem;
                margin: 0.15rem 0 0;
                float: none;
                cursor: pointer;
            }

            .generic-writeup-status-check .form-check-label {
                cursor: pointer;
            }

            .generic-writeup-status-title {
                display: block;
                color: var(--cyb-text, #212529);
                font-size: 0.82rem;
                font-weight: 600;
            }

            .generic-writeup-status-description {
                display: block;
                margin-top: 0.15rem;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.74rem;
                line-height: 1.45;
            }
        </style>
    @endpush
</x-app-layout>