<div wire:poll.5s>
    <div class="row g-4 writeup-review-layout">

        {{-- =========================================================
             FILTER SIDEBAR
             ========================================================= --}}
        <aside class="col-12 col-xl-4">
            <div class="cyb-card writeup-filter-panel">
                <div class="cyb-card-header">
                    <div>
                        <h2 class="cyb-section-title">
                            Filters
                        </h2>

                        <p class="cyb-section-description">
                            Narrow down the review queue.
                        </p>
                    </div>

                    <span class="cyb-pill cyb-pill-neutral">
                        <i class="bi bi-funnel"></i>
                        Filter
                    </span>
                </div>

                <div class="cyb-card-body writeup-filter-body">

                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="btn btn-light border w-100 d-inline-flex align-items-center justify-content-center gap-2"
                    >
                        <i class="bi bi-x-lg"></i>
                        Clear Filters
                    </button>

                    @php
                        $contentCounts = $contentCounts ?? [];

                        $contentFilterOptions = [
                            'all' => [
                                'label' => 'All Writeups',
                                'description' => 'Show every submitted writeup.',
                                'icon' => 'bi-inboxes',
                                'class' => 'cyb-filter-option-active',
                            ],

                            'over_300' => [
                                'label' => 'Over 300 Characters',
                                'description' => 'Possible length violations.',
                                'icon' => 'bi-text-paragraph',
                                'class' => 'cyb-filter-option-warning-active',
                            ],

                            'has_emoji' => [
                                'label' => 'Contains Emojis',
                                'description' => 'Writeups that may need cleanup.',
                                'icon' => 'bi-emoji-smile',
                                'class' => 'cyb-filter-option-info-active',
                            ],

                            'has_profanity' => [
                                'label' => 'Possible Profanity',
                                'description' => 'Needs manual checking only.',
                                'icon' => 'bi-exclamation-octagon',
                                'class' => 'cyb-filter-option-danger-active',
                            ],
                        ];

                        $activeContentLabel =
                            $contentFilterOptions[$contentFilter]['label']
                            ?? 'All Writeups';

                        $selectedStatusLabel = $status
                            ? ($statuses[$status]
                                ?? ucfirst(str_replace('_', ' ', $status)))
                            : 'All Statuses';

                        $selectedCollegeName = $collegeId
                            ? optional(
                                $colleges->firstWhere('id', $collegeId)
                            )->college_name
                            : 'All Colleges';
                    @endphp

                    {{-- Content Checks --}}
                    <div class="writeup-filter-group">
                        <button
                            type="button"
                            wire:click="toggleFilterCard('content')"
                            class="writeup-filter-group-toggle"
                        >
                            <span class="writeup-filter-group-main">
                                <span class="writeup-filter-group-icon">
                                    <i class="bi bi-card-checklist"></i>
                                </span>

                                <span class="writeup-filter-group-text">
                                    <span class="writeup-filter-group-title">
                                        Content Checks
                                    </span>

                                    <span class="writeup-filter-group-selection">
                                        {{ $activeContentLabel }}
                                    </span>
                                </span>
                            </span>

                            <i
                                class="bi {{ $contentFiltersOpen
                                    ? 'bi-chevron-up'
                                    : 'bi-chevron-down' }}"
                            ></i>
                        </button>

                        @if ($contentFiltersOpen)
                            <div class="writeup-filter-group-body">
                                <div class="cyb-filter-list">
                                    @foreach ($contentFilterOptions as $filterKey => $option)
                                        @php
                                            $isActive =
                                                $contentFilter === $filterKey;

                                            $count =
                                                $contentCounts[$filterKey] ?? null;
                                        @endphp

                                        <button
                                            type="button"
                                            wire:click="setContentFilter('{{ $filterKey }}')"
                                            class="cyb-filter-option {{ $isActive ? $option['class'] : '' }}"
                                        >
                                            <span class="cyb-filter-option-main">
                                                <span class="cyb-filter-option-icon">
                                                    <i class="bi {{ $option['icon'] }}"></i>
                                                </span>

                                                <span class="cyb-filter-option-text">
                                                    <span class="cyb-filter-option-title">
                                                        {{ $option['label'] }}
                                                    </span>

                                                    <span class="cyb-filter-option-description">
                                                        {{ $option['description'] }}
                                                    </span>
                                                </span>
                                            </span>

                                            @if (! is_null($count))
                                                <span class="cyb-filter-count">
                                                    {{ $count }}
                                                </span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Status --}}
                    <div class="writeup-filter-group">
                        <button
                            type="button"
                            wire:click="toggleFilterCard('status')"
                            class="writeup-filter-group-toggle"
                        >
                            <span class="writeup-filter-group-main">
                                <span class="writeup-filter-group-icon">
                                    <i class="bi bi-list-check"></i>
                                </span>

                                <span class="writeup-filter-group-text">
                                    <span class="writeup-filter-group-title">
                                        Status
                                    </span>

                                    <span class="writeup-filter-group-selection">
                                        {{ $selectedStatusLabel }}
                                    </span>
                                </span>
                            </span>

                            <i
                                class="bi {{ $statusFiltersOpen
                                    ? 'bi-chevron-up'
                                    : 'bi-chevron-down' }}"
                            ></i>
                        </button>

                        @if ($statusFiltersOpen)
                            <div class="writeup-filter-group-body">
                                @include('livewire.partials.status-filter')
                            </div>
                        @endif
                    </div>

                    {{-- Colleges --}}
                    <div class="writeup-filter-group">
                        <button
                            type="button"
                            wire:click="toggleFilterCard('college')"
                            class="writeup-filter-group-toggle"
                        >
                            <span class="writeup-filter-group-main">
                                <span class="writeup-filter-group-icon">
                                    <i class="bi bi-building"></i>
                                </span>

                                <span class="writeup-filter-group-text">
                                    <span class="writeup-filter-group-title">
                                        Colleges / Schools
                                    </span>

                                    <span class="writeup-filter-group-selection">
                                        {{ $selectedCollegeName }}
                                    </span>
                                </span>
                            </span>

                            <i
                                class="bi {{ $collegeFiltersOpen
                                    ? 'bi-chevron-up'
                                    : 'bi-chevron-down' }}"
                            ></i>
                        </button>

                        @if ($collegeFiltersOpen)
                            <div class="writeup-filter-group-body">
                                @include('livewire.partials.college-filter')
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </aside>

        {{-- =========================================================
             WRITEUP QUEUE
             ========================================================= --}}
        <section class="col-12 col-xl-8">
            <div class="cyb-card writeup-queue-card">

                {{-- Toolbar --}}
                <div class="writeup-queue-toolbar">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h2 class="cyb-section-title">
                                Writeup Queue
                            </h2>

                            @if ($writeups->total() > 0)
                                <span class="cyb-pill cyb-pill-neutral">
                                    {{ $writeups->total() }}
                                    {{ $writeups->total() === 1
                                        ? 'writeup'
                                        : 'writeups' }}
                                </span>
                            @endif
                        </div>

                        <p class="cyb-section-description">
                            @if ($writeups->total() > 0)
                                Showing
                                {{ $writeups->firstItem() }}
                                –
                                {{ $writeups->lastItem() }}
                                of
                                {{ $writeups->total() }}
                            @else
                                No writeups found
                            @endif
                        </p>
                    </div>

                    <div class="writeup-search">
                        <label
                            for="search"
                            class="visually-hidden"
                        >
                            Search writeups
                        </label>

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>

                            <input
                                id="search"
                                type="search"
                                wire:model.live.debounce.500ms="search"
                                class="form-control"
                                placeholder="Student name, ID, or SLMIS ID"
                                autocomplete="off"
                            >
                        </div>
                    </div>
                </div>

                {{-- =================================================
                     QUEUE ROWS
                     ================================================= --}}
                <div class="writeup-list">
                    @forelse ($writeups as $writeup)
                        @php
                            $student = $writeup->studentInfo;

                            $rawWriteup = trim(
                                strip_tags(
                                    $writeup->edited_writeup
                                    ?: $writeup->writeup
                                )
                            );

                            $preview =
                                \Illuminate\Support\Str::limit(
                                    $rawWriteup,
                                    300
                                );

                            $characterCount =
                                \Illuminate\Support\Str::length(
                                    $rawWriteup
                                );

                            $hasEmoji = preg_match(
                                '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u',
                                $rawWriteup
                            );

                            $profanityTerms = collect(
                                config('cyb.profanity_terms', [
                                    'fuck',
                                    'shit',
                                    'bitch',
                                    'asshole',
                                    'puta',
                                    'putangina',
                                    'tangina',
                                    'gago',
                                    'ulol',
                                    'tarantado',
                                    'yawa',
                                ])
                            )
                                ->map(fn ($term) => trim((string) $term))
                                ->filter();

                            $lowerWriteup =
                                mb_strtolower($rawWriteup);

                            $hasProfanity =
                                $profanityTerms->contains(
                                    fn ($term) =>
                                        str_contains(
                                            $lowerWriteup,
                                            mb_strtolower($term)
                                        )
                                );

                            $statusPill = match ($writeup->review_status) {
                                'reviewed' => 'cyb-pill-success',
                                'in_review' => 'cyb-pill-info',
                                'flagged' => 'cyb-pill-warning',
                                default => 'cyb-pill-neutral',
                            };

                            $statusIcon = match ($writeup->review_status) {
                                'reviewed' => 'bi-check2-circle',
                                'in_review' => 'bi-pencil-square',
                                'flagged' => 'bi-flag-fill',
                                default => 'bi-hourglass-split',
                            };
                        @endphp

                        <article
                            wire:key="writeup-row-{{ $writeup->id }}"
                            class="writeup-row"
                        >
                            {{-- Flag --}}
                            <div class="writeup-row-flag">
                                @if ($canProofread)
                                    <button
                                        type="button"
                                        wire:key="flag-button-{{ $writeup->id }}"
                                        wire:click="toggleFlag({{ $writeup->id }})"
                                        title="{{ $writeup->is_flagged
                                            ? 'Remove flag'
                                            : 'Flag writeup' }}"
                                        aria-label="{{ $writeup->is_flagged
                                            ? 'Remove flag'
                                            : 'Flag writeup' }}"
                                        class="writeup-flag-button {{ $writeup->is_flagged ? 'is-flagged' : '' }}"
                                    >
                                        <i class="bi {{ $writeup->is_flagged
                                            ? 'bi-flag-fill'
                                            : 'bi-flag' }}"></i>
                                    </button>
                                @else
                                    <span
                                        class="writeup-flag-display {{ $writeup->is_flagged ? 'is-flagged' : '' }}"
                                        title="{{ $writeup->is_flagged
                                            ? 'Flagged'
                                            : 'Not flagged' }}"
                                    >
                                        <i class="bi {{ $writeup->is_flagged
                                            ? 'bi-flag-fill'
                                            : 'bi-flag' }}"></i>
                                    </span>
                                @endif
                            </div>

                            {{-- Main clickable content --}}
                            <a
                                href="{{ route('writeups.review.show', $writeup) }}"
                                class="writeup-row-link"
                            >
                                {{-- Student --}}
                                <div class="writeup-student">
                                    <div class="writeup-student-name">
                                        {{ $student?->formatted_full_name
                                            ?? 'No student info' }}
                                    </div>

                                    <div class="writeup-student-meta">
                                        @if ($student?->year)
                                            <span class="cyb-pill cyb-pill-neutral">
                                                Batch {{ $student->year }}
                                            </span>
                                        @endif

                                        @if ($student?->program?->is_graduate_program)
                                            <span class="cyb-pill cyb-pill-primary">
                                                Graduate
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Content --}}
                                <div class="writeup-content">
                                    <div class="writeup-academic">
                                        <span class="writeup-college">
                                            {{ $student?->college?->college_name
                                                ?? 'No college' }}
                                        </span>

                                        @if ($student?->program)
                                            <span class="writeup-academic-divider">
                                                ·
                                            </span>

                                            <span>
                                                {{ $student->program->program_name }}
                                            </span>
                                        @endif
                                    </div>

                                    <p class="writeup-preview">
                                        {{ $preview
                                            ?: 'No writeup content available.' }}
                                    </p>

                                    @if (
                                        $characterCount > 300
                                        || $hasEmoji
                                        || $hasProfanity
                                        || $writeup->lockedBy
                                    )
                                        <div class="writeup-warnings">
                                            @if ($characterCount > 300)
                                                <span class="cyb-pill cyb-pill-danger">
                                                    <i class="bi bi-exclamation-triangle"></i>
                                                    Over 300
                                                </span>
                                            @endif

                                            @if ($hasEmoji)
                                                <span class="cyb-pill cyb-pill-info">
                                                    <i class="bi bi-emoji-smile"></i>
                                                    Emoji
                                                </span>
                                            @endif

                                            @if ($hasProfanity)
                                                <span class="cyb-pill cyb-pill-warning">
                                                    <i class="bi bi-exclamation-octagon"></i>
                                                    Check Language
                                                </span>
                                            @endif

                                            @if ($writeup->lockedBy)
                                                <span class="cyb-pill cyb-pill-primary">
                                                    <i class="bi bi-lock"></i>

                                                    {{ $writeup->lockedBy->name }}

                                                    @if ($writeup->locked_at)
                                                        ·
                                                        {{ $writeup->locked_at->diffForHumans() }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </a>

                            {{-- Status / Action --}}
                            <div class="writeup-row-actions">
                                <span class="cyb-pill {{ $statusPill }}">
                                    <i class="bi {{ $statusIcon }}"></i>

                                    {{ ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $writeup->review_status
                                                ?? 'pending'
                                        )
                                    ) }}
                                </span>

                                @if (
                                    $canProofread
                                    && ! $writeup->is_done
                                    && $writeup->review_status !== 'reviewed'
                                )
                                    <button
                                        type="button"
                                        wire:click="markReviewedFromQueue({{ $writeup->id }})"
                                        wire:confirm="Mark this writeup as reviewed?"
                                        class="btn btn-sm btn-outline-success writeup-reviewed-button"
                                    >
                                        <i class="bi bi-check2-circle"></i>
                                        Mark Reviewed
                                    </button>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="cyb-empty-state">
                            <div class="cyb-empty-state-icon">
                                <i class="bi bi-inbox"></i>
                            </div>

                            <h3 class="cyb-empty-state-title">
                                No writeups found
                            </h3>

                            <p class="cyb-empty-state-text">
                                Try clearing the filters or searching for another student.
                            </p>
                        </div>
                    @endforelse
                </div>

                {{-- =================================================
                     PAGINATION
                     ================================================= --}}
                <div class="cyb-pagination-footer">
                    <div class="cyb-pagination-summary">
                        @if ($writeups->total() > 0)
                            Showing
                            <strong>{{ $writeups->firstItem() }}</strong>
                            to
                            <strong>{{ $writeups->lastItem() }}</strong>
                            of
                            <strong>{{ $writeups->total() }}</strong>
                            writeups
                        @else
                            No records to display
                        @endif
                    </div>

                    @if ($writeups->hasPages())
                        <div class="writeup-pagination">
                            <button
                                type="button"
                                wire:click="previousPage"
                                wire:loading.attr="disabled"
                                @disabled($writeups->onFirstPage())
                                class="btn btn-sm btn-light border"
                            >
                                <i class="bi bi-chevron-left me-1"></i>
                                Previous
                            </button>

                            <span class="writeup-page-number">
                                Page
                                {{ $writeups->currentPage() }}
                                of
                                {{ $writeups->lastPage() }}
                            </span>

                            <button
                                type="button"
                                wire:click="nextPage"
                                wire:loading.attr="disabled"
                                @disabled(! $writeups->hasMorePages())
                                class="btn btn-sm btn-light border"
                            >
                                Next
                                <i class="bi bi-chevron-right ms-1"></i>
                            </button>
                        </div>
                    @endif
                </div>

            </div>
        </section>

    </div>

    @push('styles')
        <style>
            /*
             * Writeup Review Queue specific styles.
             * Shared card, pills, empty-state, filters and pagination
             * come from components.css / tables.css.
             */

            .writeup-review-layout {
                align-items: flex-start;
            }

            /*
             * Filter panel
             */

            .writeup-filter-panel {
                overflow: visible;
            }

            .writeup-filter-body {
                display: flex;
                flex-direction: column;
                gap: 0.85rem;
            }

            .writeup-filter-group {
                overflow: hidden;
                border: 1px solid var(--cyb-border, #e7eaed);
                border-radius: 0.65rem;
                background: #fff;
            }

            .writeup-filter-group-toggle {
                display: flex;
                width: 100%;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.8rem 0.9rem;
                border: 0;
                background: #f8f9fa;
                color: var(--cyb-text, #212529);
                text-align: left;
            }

            .writeup-filter-group-toggle:hover {
                background: #f3f5f7;
            }

            .writeup-filter-group-main {
                display: flex;
                min-width: 0;
                align-items: center;
                gap: 0.7rem;
            }

            .writeup-filter-group-icon {
                display: flex;
                width: 32px;
                height: 32px;
                flex: 0 0 32px;
                align-items: center;
                justify-content: center;
                border-radius: 0.55rem;
                background: #e9edf1;
                color: #495057;
                font-size: 0.85rem;
            }

            .writeup-filter-group-text {
                min-width: 0;
            }

            .writeup-filter-group-title {
                display: block;
                color: #292d32;
                font-size: 0.82rem;
                font-weight: 600;
            }

            .writeup-filter-group-selection {
                display: block;
                overflow: hidden;
                margin-top: 0.1rem;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.71rem;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .writeup-filter-group-toggle > i {
                flex: 0 0 auto;
                color: #8a929a;
                font-size: 0.78rem;
            }

            .writeup-filter-group-body {
                padding: 0.7rem;
                border-top: 1px solid var(--cyb-border-soft, #edf0f2);
            }

            /*
             * Queue toolbar
             */

            .writeup-queue-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 1rem 1.2rem;
                border-bottom: 1px solid var(--cyb-border-soft, #edf0f2);
                background: #fff;
            }

            .writeup-search {
                width: min(100%, 360px);
            }

            .writeup-search .form-control,
            .writeup-search .input-group-text {
                min-height: 40px;
            }

            .writeup-search .input-group-text {
                background: #f8f9fa;
                color: #6c757d;
            }

            /*
             * Queue rows
             */

            .writeup-list {
                background: #fff;
            }

            .writeup-row {
                display: grid;
                grid-template-columns:
                    36px
                    minmax(0, 1fr)
                    145px;
                gap: 0.85rem;
                align-items: flex-start;
                padding: 1rem 1.1rem;
                border-bottom: 1px solid var(--cyb-border-soft, #edf0f2);
                transition: background 0.12s ease;
            }

            .writeup-row:last-child {
                border-bottom: 0;
            }

            .writeup-row:hover {
                background: #fafbfc;
            }

            /*
             * Flag
             */

            .writeup-row-flag {
                padding-top: 0.05rem;
            }

            .writeup-flag-button,
            .writeup-flag-display {
                display: inline-flex;
                width: 32px;
                height: 32px;
                align-items: center;
                justify-content: center;
                padding: 0;
                border-radius: 0.55rem;
                font-size: 0.9rem;
            }

            .writeup-flag-button {
                border: 1px solid transparent;
                background: transparent;
                color: #c4c9ce;
                transition:
                    background 0.15s ease,
                    color 0.15s ease,
                    border-color 0.15s ease;
            }

            .writeup-flag-button:hover {
                background: #fff8e1;
                color: #a87913;
            }

            .writeup-flag-button.is-flagged {
                border-color: #f2c6c6;
                background: #fdf0f0;
                color: #b84444;
            }

            .writeup-flag-button.is-flagged:hover {
                background: #fae4e4;
            }

            .writeup-flag-display {
                color: #c4c9ce;
            }

            .writeup-flag-display.is-flagged {
                color: #b84444;
            }

            /*
             * Main clickable area
             */

            .writeup-row-link {
                display: grid;
                grid-template-columns: minmax(145px, 180px) minmax(0, 1fr);
                gap: 1rem;
                min-width: 0;
                color: inherit;
                text-decoration: none;
            }

            .writeup-row-link:hover {
                color: inherit;
            }

            .writeup-student {
                min-width: 0;
            }

            .writeup-student-name {
                overflow: hidden;
                color: var(--cyb-text, #212529);
                font-size: 0.86rem;
                font-weight: 600;
                text-overflow: ellipsis;
                white-space: nowrap;
                transition: color 0.12s ease;
            }

            .writeup-row-link:hover .writeup-student-name {
                color: var(--cyb-primary, #0d6efd);
            }

            .writeup-student-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 0.35rem;
                margin-top: 0.4rem;
            }

            /*
             * Writeup content
             */

            .writeup-content {
                min-width: 0;
            }

            .writeup-academic {
                overflow: hidden;
                color: #5e666d;
                font-size: 0.74rem;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .writeup-college {
                color: #3e5267;
                font-weight: 600;
            }

            .writeup-academic-divider {
                padding: 0 0.3rem;
                color: #adb5bd;
            }

            .writeup-preview {
                display: -webkit-box;
                overflow: hidden;
                margin: 0.45rem 0 0;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.78rem;
                line-height: 1.55;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 3;
            }

            .writeup-warnings {
                display: flex;
                flex-wrap: wrap;
                gap: 0.35rem;
                margin-top: 0.55rem;
            }

            /*
             * Status / actions
             */

            .writeup-row-actions {
                display: flex;
                min-width: 0;
                flex-direction: column;
                align-items: flex-end;
                gap: 0.6rem;
            }

            .writeup-reviewed-button {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                white-space: nowrap;
                font-size: 0.73rem;
                font-weight: 500;
            }

            /*
             * Pagination
             */

            .writeup-pagination {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .writeup-page-number {
                display: inline-flex;
                min-height: 31px;
                align-items: center;
                padding: 0.25rem 0.65rem;
                border: 1px solid var(--cyb-border, #e7eaed);
                border-radius: 0.4rem;
                background: #f8f9fa;
                color: #5f676f;
                font-size: 0.72rem;
                font-weight: 600;
                white-space: nowrap;
            }

            /*
             * Responsive
             */

            @media (min-width: 1200px) {
                .writeup-filter-panel {
                    position: sticky;
                    top: 1rem;
                }
            }

            @media (max-width: 991.98px) {
                .writeup-row {
                    grid-template-columns:
                        36px
                        minmax(0, 1fr);
                }

                .writeup-row-actions {
                    grid-column: 2;
                    flex-direction: row;
                    align-items: center;
                    justify-content: space-between;
                }
            }

            @media (max-width: 767.98px) {
                .writeup-queue-toolbar {
                    align-items: stretch;
                    flex-direction: column;
                }

                .writeup-search {
                    width: 100%;
                }

                .writeup-row-link {
                    grid-template-columns: 1fr;
                    gap: 0.65rem;
                }

                .writeup-preview {
                    -webkit-line-clamp: 4;
                }

                .writeup-row-actions {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .cyb-pagination-footer {
                    align-items: stretch;
                }

                .writeup-pagination {
                    width: 100%;
                    justify-content: space-between;
                }
            }

            @media (max-width: 575.98px) {
                .writeup-row {
                    grid-template-columns: 28px minmax(0, 1fr);
                    gap: 0.65rem;
                    padding: 0.9rem;
                }

                .writeup-flag-button,
                .writeup-flag-display {
                    width: 28px;
                    height: 28px;
                }

                .writeup-pagination .btn {
                    font-size: 0.72rem;
                }
            }
        </style>
    @endpush
</div>