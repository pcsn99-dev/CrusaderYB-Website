<x-app-layout>
    <x-slot name="header">
        Years
    </x-slot>

    <x-slot name="subheader">
        Manage CYB years, themes, subscription periods, and the active year.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-calendar3"></i>
    </x-slot>

    <x-slot name="breadcrumbs">
        <li class="breadcrumb-item">
            Settings
        </li>

        <li class="breadcrumb-item active">
            Years
        </li>
    </x-slot>

    <div class="cyb-page">
        <div class="cyb-stack">

            {{-- Feedback --}}
            @if (session('success'))
                <div class="alert alert-success mb-0">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ session('info') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-0">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-circle"></i>

                        <div>
                            <div class="fw-semibold">
                                Please check the information you entered.
                            </div>

                            <ul class="mb-0 mt-2 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif


            {{-- Create Year --}}
            <div class="cyb-card">
                <div class="cyb-card-header">
                    <div>
                        <h2 class="cyb-section-title">
                            Add CYB Year
                        </h2>

                        <p class="cyb-section-description">
                            Create a new yearbook cycle and configure its subscription period.
                        </p>
                    </div>

                    <span class="cyb-pill cyb-pill-neutral">
                        <i class="bi bi-plus-circle"></i>
                        New Year
                    </span>
                </div>

                <div class="cyb-card-body">
                    <form
                        method="POST"
                        action="{{ route('settings.years.store') }}"
                    >
                        @csrf

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label
                                    for="year"
                                    class="cyb-form-label"
                                >
                                    Year
                                </label>

                                <input
                                    id="year"
                                    name="year"
                                    type="text"
                                    class="form-control cyb-form-control @error('year') is-invalid @enderror"
                                    value="{{ old('year') }}"
                                    placeholder="e.g. 2027"
                                    required
                                >

                                @error('year')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-8">
                                <label
                                    for="theme"
                                    class="cyb-form-label"
                                >
                                    Theme
                                </label>

                                <input
                                    id="theme"
                                    name="theme"
                                    type="text"
                                    class="form-control cyb-form-control @error('theme') is-invalid @enderror"
                                    value="{{ old('theme') }}"
                                    placeholder="Optional yearbook theme"
                                >

                                @error('theme')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label
                                    for="subscription_start"
                                    class="cyb-form-label"
                                >
                                    Subscription Start
                                </label>

                                <input
                                    id="subscription_start"
                                    name="subscription_start"
                                    type="date"
                                    class="form-control cyb-form-control @error('subscription_start') is-invalid @enderror"
                                    value="{{ old('subscription_start') }}"
                                >

                                @error('subscription_start')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label
                                    for="subscription_end"
                                    class="cyb-form-label"
                                >
                                    Subscription End
                                </label>

                                <input
                                    id="subscription_end"
                                    name="subscription_end"
                                    type="date"
                                    class="form-control cyb-form-control @error('subscription_end') is-invalid @enderror"
                                    value="{{ old('subscription_end') }}"
                                >

                                @error('subscription_end')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input
                                        id="activate"
                                        name="activate"
                                        type="checkbox"
                                        class="form-check-input"
                                        value="1"
                                        @checked(old('activate'))
                                    >

                                    <label
                                        for="activate"
                                        class="form-check-label"
                                    >
                                        Make this the active CYB year
                                    </label>
                                </div>

                                <div class="cyb-form-help">
                                    Activating this year will automatically deactivate the currently active year.
                                </div>
                            </div>
                        </div>

                        <div class="cyb-form-actions mt-4">
                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                <i class="bi bi-plus-lg me-2"></i>
                                Create Year
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            {{-- Existing Years --}}
            <div class="cyb-card">
                <div class="cyb-card-header">
                    <div>
                        <h2 class="cyb-section-title">
                            Configured Years
                        </h2>

                        <p class="cyb-section-description">
                            Review existing yearbook cycles and choose which year is currently active.
                        </p>
                    </div>

                    <span class="cyb-pill cyb-pill-neutral">
                        {{ $years->count() }}
                        {{ Str::plural('year', $years->count()) }}
                    </span>
                </div>

                @if ($years->isEmpty())
                    <div class="cyb-empty-state">
                        <div class="cyb-empty-state-icon">
                            <i class="bi bi-calendar3"></i>
                        </div>

                        <h3 class="cyb-empty-state-title">
                            No years configured
                        </h3>

                        <p class="cyb-empty-state-text">
                            Create your first CYB year using the form above.
                        </p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table cyb-table">
                            <thead>
                                <tr>
                                    <th>Year</th>
                                    <th>Theme</th>
                                    <th>Subscription Period</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($years as $year)
                                    <tr>
                                        <td>
                                            <div class="cyb-table-primary">
                                                {{ $year->year }}
                                            </div>
                                        </td>

                                        <td>
                                            {{ $year->theme ?: '—' }}
                                        </td>

                                        <td>
                                            @if ($year->subscription_start || $year->subscription_end)
                                                <div>
                                                    {{ $year->subscription_start?->format('M d, Y') ?? 'Open' }}
                                                    –
                                                    {{ $year->subscription_end?->format('M d, Y') ?? 'Open' }}
                                                </div>
                                            @else
                                                <span class="text-muted">
                                                    No restriction
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($year->status)
                                                <span class="cyb-pill cyb-pill-success">
                                                    <span class="cyb-status-dot cyb-status-dot-success"></span>
                                                    Active
                                                </span>
                                            @else
                                                <span class="cyb-pill cyb-pill-neutral">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="d-flex justify-content-end flex-wrap gap-2">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-light border"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editYearModal{{ $year->id }}"
                                                >
                                                    <i class="bi bi-pencil me-1"></i>
                                                    Edit
                                                </button>

                                                @unless ($year->status)
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-success"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#activateYearModal{{ $year->id }}"
                                                    >
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        Activate
                                                    </button>
                                                @endunless
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>


    {{-- Edit Modals --}}
    @foreach ($years as $year)
        <div
            class="modal fade"
            id="editYearModal{{ $year->id }}"
            tabindex="-1"
            aria-labelledby="editYearModalLabel{{ $year->id }}"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form
                        method="POST"
                        action="{{ route('settings.years.update', $year) }}"
                    >
                        @csrf
                        @method('PUT')

                        <div class="modal-header">
                            <div>
                                <h5
                                    class="modal-title"
                                    id="editYearModalLabel{{ $year->id }}"
                                >
                                    Edit CYB {{ $year->year }}
                                </h5>

                                <div class="text-muted small mt-1">
                                    Update the yearbook details and subscription period.
                                </div>
                            </div>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Close"
                            ></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label class="cyb-form-label">
                                        Year
                                    </label>

                                    <input
                                        name="year"
                                        type="text"
                                        class="form-control cyb-form-control"
                                        value="{{ $year->year }}"
                                        required
                                    >
                                </div>

                                <div class="col-12 col-md-8">
                                    <label class="cyb-form-label">
                                        Theme
                                    </label>

                                    <input
                                        name="theme"
                                        type="text"
                                        class="form-control cyb-form-control"
                                        value="{{ $year->theme }}"
                                    >
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="cyb-form-label">
                                        Subscription Start
                                    </label>

                                    <input
                                        name="subscription_start"
                                        type="date"
                                        class="form-control cyb-form-control"
                                        value="{{ $year->subscription_start?->format('Y-m-d') }}"
                                    >
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="cyb-form-label">
                                        Subscription End
                                    </label>

                                    <input
                                        name="subscription_end"
                                        type="date"
                                        class="form-control cyb-form-control"
                                        value="{{ $year->subscription_end?->format('Y-m-d') }}"
                                    >
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
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        {{-- Activate Confirmation --}}
        @unless ($year->status)
            <div
                class="modal fade"
                id="activateYearModal{{ $year->id }}"
                tabindex="-1"
                aria-labelledby="activateYearModalLabel{{ $year->id }}"
                aria-hidden="true"
            >
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form
                            method="POST"
                            action="{{ route('settings.years.activate', $year) }}"
                        >
                            @csrf
                            @method('PATCH')

                            <div class="modal-header">
                                <h5
                                    class="modal-title"
                                    id="activateYearModalLabel{{ $year->id }}"
                                >
                                    Activate CYB {{ $year->year }}?
                                </h5>

                                <button
                                    type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="Close"
                                ></button>
                            </div>

                            <div class="modal-body">
                                <div class="cyb-notice cyb-notice-warning rounded-3">
                                    <i class="bi bi-exclamation-triangle"></i>

                                    <div>
                                        <strong>
                                            This will change the active CYB year.
                                        </strong>

                                        <div class="mt-1">
                                            The currently active year will automatically be deactivated.
                                            Dashboard and current-cycle features will begin using
                                            CYB {{ $year->year }}.
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
                                    class="btn btn-success"
                                >
                                    <i class="bi bi-check-circle me-2"></i>
                                    Activate Year
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endunless
    @endforeach
</x-app-layout>