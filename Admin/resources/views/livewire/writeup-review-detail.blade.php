<div @unless($canEdit) wire:poll.5s @endunless>
    <x-alert />

    @php
        $student = $writeup->studentInfo;

        $originalText = trim((string) ($writeup->writeup ?? ''));

        $currentText = trim(
            (string) (
                $editedWriteup
                ?: ($writeup->edited_writeup ?: $writeup->writeup)
            )
        );

        $originalCount = \Illuminate\Support\Str::length($originalText);
        $currentCount = $this->characterCount;

        $hasEmoji = preg_match(
            '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u',
            $currentText
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

        $lowerCurrentText = mb_strtolower($currentText);

        $hasPossibleProfanity = $profanityTerms->contains(
            fn ($term) =>
                str_contains(
                    $lowerCurrentText,
                    mb_strtolower($term)
                )
        );

        $statusMeta = match ($writeup->review_status) {
            'reviewed' => [
                'label' => 'Reviewed',
                'icon' => 'bi-check2-circle',
                'class' => 'cyb-pill-success',
            ],

            'in_review' => [
                'label' => 'In Review',
                'icon' => 'bi-pencil-square',
                'class' => 'cyb-pill-info',
            ],

            'flagged' => [
                'label' => 'Flagged',
                'icon' => 'bi-flag-fill',
                'class' => 'cyb-pill-warning',
            ],

            default => [
                'label' => 'Pending',
                'icon' => 'bi-hourglass-split',
                'class' => 'cyb-pill-neutral',
            ],
        };

        $characterWarning = $currentCount > $maxCharacters;

        $characterNearLimit =
            $this->remainingCharacters <= 20
            && ! $characterWarning;

        $activePanel = in_array(
            $activePanel ?? 'review',
            ['original', 'review'],
            true
        )
            ? $activePanel
            : 'review';
    @endphp

    <div class="cyb-stack">

        {{-- =========================================================
             STUDENT / ACTION HEADER
             ========================================================= --}}
        <section class="cyb-card">
            <div class="writeup-detail-header">
                <div class="writeup-detail-student">
                    <div class="writeup-detail-student-icon">
                        <i class="bi bi-person-lines-fill"></i>
                    </div>

                    <div class="writeup-detail-student-content">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h2 class="writeup-detail-student-name">
                                {{ $student?->formatted_full_name ?? 'No student info' }}
                            </h2>

                            <span class="cyb-pill {{ $statusMeta['class'] }}">
                                <i class="bi {{ $statusMeta['icon'] }}"></i>
                                {{ $statusMeta['label'] }}
                            </span>

                            @if ($writeup->is_flagged)
                                <span class="cyb-pill cyb-pill-danger">
                                    <i class="bi bi-flag-fill"></i>
                                    Flagged
                                </span>
                            @endif
                        </div>

                        <div class="writeup-detail-meta">
                            <span>
                                <i class="bi bi-person-vcard"></i>
                                {{ $student?->university_id ?? 'No University ID' }}
                            </span>

                            <span>
                                <i class="bi bi-card-list"></i>
                                {{ $student?->slmis_id ?? 'No SLMIS ID' }}
                            </span>

                            <span>
                                <i class="bi bi-calendar3"></i>
                                Batch {{ $student?->year ?? 'N/A' }}
                            </span>

                            <span>
                                <i class="bi bi-building"></i>
                                {{ $student?->college?->college_name ?? 'No college' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="writeup-detail-actions">
                    <button
                        type="button"
                        onclick="
                            if (window.history.length > 1) {
                                window.history.back();
                            } else {
                                window.location.href = '{{ route('writeups.review.index') }}';
                            }
                        "
                        class="btn btn-light border"
                    >
                        <i class="bi bi-arrow-left me-1"></i>
                        Back
                    </button>

                    @if ($canProofread && $canStartReview)
                        <button
                            type="button"
                            wire:click="startReview"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-play-circle me-1"></i>
                            Start Review
                        </button>
                    @endif

                    @if ($canProofread && $canEdit)
                        <button
                            type="button"
                            wire:click="saveChanges"
                            class="btn btn-success"
                        >
                            <i class="bi bi-save me-1"></i>
                            Save
                        </button>

                        <button
                            type="button"
                            wire:click="markReviewed"
                            onclick="return confirm({{ \Illuminate\Support\Js::from('Mark this writeup as reviewed?') }});"
                            class="btn btn-outline-success"
                        >
                            <i class="bi bi-check2-circle me-1"></i>
                            Mark Reviewed
                        </button>

                        <button
                            type="button"
                            wire:click="releaseReview"
                            onclick="return confirm({{ \Illuminate\Support\Js::from('Release this writeup so another staff member can review it?') }});"
                            class="btn btn-light border"
                        >
                            <i class="bi bi-unlock me-1"></i>
                            Release
                        </button>
                    @endif

                    @if ($canProofread)
                        <button
                            type="button"
                            wire:click="toggleFlag"
                            class="btn {{ $writeup->is_flagged
                                ? 'btn-outline-secondary'
                                : 'btn-outline-danger' }}"
                        >
                            <i class="bi {{ $writeup->is_flagged
                                ? 'bi-flag'
                                : 'bi-flag-fill' }} me-1"></i>

                            {{ $writeup->is_flagged
                                ? 'Remove Flag'
                                : 'Flag Writeup' }}
                        </button>
                    @endif
                </div>
            </div>

            @if ($writeup->lockedBy)
                <div class="cyb-notice cyb-notice-info writeup-lock-notice">
                    <i class="bi bi-lock"></i>

                    <div>
                        Currently being reviewed by
                        <strong>{{ $writeup->lockedBy->name }}</strong>

                        @if ($writeup->locked_at)
                            · {{ $writeup->locked_at->diffForHumans() }}
                        @endif
                    </div>
                </div>
            @endif
        </section>

        {{-- =========================================================
             MAIN CONTENT
             ========================================================= --}}
        <div class="row g-4">

            {{-- Main Workspace --}}
            <section class="col-12 col-xl-9">
                <div class="cyb-card">

                    {{-- Tabs --}}
                    <div class="writeup-tabs">
                        <div class="writeup-tabs-main">
                            <button
                                type="button"
                                wire:click="setActivePanel('original')"
                                class="writeup-tab {{ $activePanel === 'original' ? 'active' : '' }}"
                            >
                                <i class="bi bi-file-earmark-text"></i>

                                Original Submission

                                <span class="writeup-tab-count">
                                    {{ $originalCount }}
                                </span>
                            </button>

                            <button
                                type="button"
                                wire:click="setActivePanel('review')"
                                class="writeup-tab {{ $activePanel === 'review' ? 'active review' : '' }}"
                            >
                                <i class="bi bi-pencil-square"></i>

                                Review Workspace

                                <span class="writeup-tab-count">
                                    {{ $currentCount }}/{{ $maxCharacters }}
                                </span>
                            </button>
                        </div>

                        <div class="writeup-tab-help">
                            @if ($activePanel === 'original')
                                Read-only student submission.
                            @else
                                Rule-based cleanup only. Keep the student’s voice.
                            @endif
                        </div>
                    </div>

                    {{-- =================================================
                         ORIGINAL
                         ================================================= --}}
                    @if ($activePanel === 'original')
                        <div class="writeup-workspace">
                            <div class="writeup-workspace-heading">
                                <div>
                                    <h3 class="cyb-section-title">
                                        Original Submission
                                    </h3>

                                    <p class="cyb-section-description">
                                        The student's original submitted text.
                                    </p>
                                </div>

                                <span class="cyb-pill cyb-pill-neutral">
                                    {{ $originalCount }}
                                    characters
                                </span>
                            </div>

                            <div class="writeup-readonly-content">
                                @if ($writeup->writeup)
                                    <div class="writeup-rendered-text">
                                        {!! $this->renderedOriginalWriteup !!}
                                    </div>
                                @else
                                    <div class="cyb-empty-state py-5">
                                        <div class="cyb-empty-state-icon">
                                            <i class="bi bi-file-earmark-x"></i>
                                        </div>

                                        <h3 class="cyb-empty-state-title">
                                            No original content
                                        </h3>

                                        <p class="cyb-empty-state-text">
                                            No original writeup content is available.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- =================================================
                         REVIEW
                         ================================================= --}}
                    @if ($activePanel === 'review')
                        <div class="writeup-workspace">
                            <div class="writeup-workspace-heading">
                                <div>
                                    <h3 class="cyb-section-title">
                                        Review Workspace
                                    </h3>

                                    <p class="cyb-section-description">
                                        Review and make permitted corrections to the submitted writeup.
                                    </p>
                                </div>

                                <span
                                    class="cyb-pill
                                        {{ $characterWarning
                                            ? 'cyb-pill-danger'
                                            : ($characterNearLimit
                                                ? 'cyb-pill-warning'
                                                : 'cyb-pill-success') }}"
                                >
                                    {{ $currentCount }} / {{ $maxCharacters }}
                                </span>
                            </div>

                            @if ($canProofread && $canEdit)
                                <div class="writeup-formatting-help">
                                    <span class="writeup-formatting-label">
                                        Formatting
                                    </span>

                                    <code class="cyb-code">
                                        **bold**
                                    </code>

                                    <code class="cyb-code">
                                        *italic*
                                    </code>

                                    <span class="writeup-formatting-note">
                                        Avoid rewriting style unless required by the rules.
                                    </span>
                                </div>

                                <textarea
                                    wire:model.live.debounce.300ms="editedWriteup"
                                    maxlength="{{ $maxCharacters }}"
                                    rows="12"
                                    class="form-control writeup-editor @error('editedWriteup') is-invalid @enderror"
                                    placeholder="Edit the student's writeup here..."
                                ></textarea>

                                @error('editedWriteup')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <div class="writeup-editor-footer">
                                    <span
                                        class="{{ $this->remainingCharacters <= 20
                                            ? 'text-danger'
                                            : 'text-muted' }}"
                                    >
                                        {{ $this->remainingCharacters }}
                                        characters remaining
                                    </span>
                                </div>

                                @if (filled($editedWriteup))
                                    <div class="writeup-preview-card">
                                        <div class="writeup-preview-header">
                                            <div>
                                                <h4 class="writeup-preview-title">
                                                    <i class="bi bi-eye me-1"></i>
                                                    Preview
                                                </h4>

                                                <div class="writeup-preview-subtitle">
                                                    Formatted output
                                                </div>
                                            </div>
                                        </div>

                                        <div class="writeup-preview-body">
                                            <div class="writeup-rendered-text">
                                                {!! $this->renderedEditedWriteup !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="writeup-readonly-content">
                                    @if ($writeup->edited_writeup || $writeup->writeup)
                                        <div class="writeup-rendered-text">
                                            {!! \Illuminate\Support\Str::markdown(
                                                $writeup->edited_writeup ?: $writeup->writeup,
                                                [
                                                    'html_input' => 'strip',
                                                    'allow_unsafe_links' => false,
                                                ]
                                            ) !!}
                                        </div>
                                    @else
                                        <div class="cyb-empty-state py-5">
                                            <div class="cyb-empty-state-icon">
                                                <i class="bi bi-file-earmark-x"></i>
                                            </div>

                                            <h3 class="cyb-empty-state-title">
                                                No writeup content
                                            </h3>

                                            <p class="cyb-empty-state-text">
                                                No reviewed or original writeup content is available.
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <div class="cyb-notice cyb-notice-info writeup-view-notice">
                                    @if (! $canProofread)
                                        <i class="bi bi-eye"></i>

                                        <div>
                                            You only have permission to view writeups.
                                        </div>
                                    @else
                                        <i class="bi bi-info-circle"></i>

                                        <div>
                                            Start reviewing this writeup to edit the reviewed version.
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                </div>
            </section>

            {{-- =========================================================
                 SIDE CONTEXT
                 ========================================================= --}}
            <aside class="col-12 col-xl-3">
                <div class="cyb-stack writeup-side-stack">

                    {{-- Rule Checks --}}
                    <section class="cyb-card">
                        <div class="cyb-card-header">
                            <div>
                                <h2 class="cyb-section-title">
                                    Rule Checks
                                </h2>

                                <p class="cyb-section-description">
                                    Automated indicators for manual review.
                                </p>
                            </div>
                        </div>

                        <div class="cyb-card-body">
                            <div class="writeup-rule-list">

                                <div
                                    class="writeup-rule
                                        {{ $characterWarning
                                            ? 'writeup-rule-danger'
                                            : ($characterNearLimit
                                                ? 'writeup-rule-warning'
                                                : 'writeup-rule-success') }}"
                                >
                                    <i
                                        class="bi
                                            {{ $characterWarning
                                                ? 'bi-exclamation-triangle'
                                                : ($characterNearLimit
                                                    ? 'bi-exclamation-circle'
                                                    : 'bi-check2-circle') }}"
                                    ></i>

                                    <div>
                                        <div class="writeup-rule-title">
                                            {{ $currentCount }} / {{ $maxCharacters }}
                                        </div>

                                        <div class="writeup-rule-description">
                                            {{ $characterWarning
                                                ? 'Over the limit'
                                                : ($characterNearLimit
                                                    ? 'Near the limit'
                                                    : 'Within limit') }}
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="writeup-rule {{ $hasEmoji
                                        ? 'writeup-rule-warning'
                                        : 'writeup-rule-success' }}"
                                >
                                    <i
                                        class="bi {{ $hasEmoji
                                            ? 'bi-emoji-smile'
                                            : 'bi-check2-circle' }}"
                                    ></i>

                                    <div>
                                        <div class="writeup-rule-title">
                                            {{ $hasEmoji
                                                ? 'Emoji Found'
                                                : 'No Emoji Found' }}
                                        </div>

                                        <div class="writeup-rule-description">
                                            Manual check only.
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="writeup-rule {{ $hasPossibleProfanity
                                        ? 'writeup-rule-warning'
                                        : 'writeup-rule-success' }}"
                                >
                                    <i
                                        class="bi {{ $hasPossibleProfanity
                                            ? 'bi-exclamation-octagon'
                                            : 'bi-check2-circle' }}"
                                    ></i>

                                    <div>
                                        <div class="writeup-rule-title">
                                            {{ $hasPossibleProfanity
                                                ? 'Check Language'
                                                : 'No Flagged Words' }}
                                        </div>

                                        <div class="writeup-rule-description">
                                            Not an automatic rejection.
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </section>

                    {{-- Academic Context --}}
                    <section class="cyb-card">
                        <div class="cyb-card-header">
                            <div>
                                <h2 class="cyb-section-title">
                                    Academic Context
                                </h2>

                                <p class="cyb-section-description">
                                    Student program information.
                                </p>
                            </div>
                        </div>

                        <div class="cyb-card-body">
                            <dl class="writeup-context-list mb-0">
                                <div>
                                    <dt>
                                        College
                                    </dt>

                                    <dd>
                                        {{ $student?->college?->college_name ?? 'No college' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt>
                                        Program
                                    </dt>

                                    <dd>
                                        {{ $student?->program?->program_name ?? 'No program' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt>
                                        Major
                                    </dt>

                                    <dd>
                                        {{ $student?->major?->major_name ?? 'None' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </section>

                </div>
            </aside>

        </div>
    </div>

    @push('styles')
        <style>
            /*
             * Writeup review detail specific styles.
             * Shared cards, pills, notices, empty states and form controls
             * come from components.css.
             */

            .writeup-detail-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1.5rem;
                padding: 1.1rem 1.2rem;
            }

            .writeup-detail-student {
                display: flex;
                min-width: 0;
                align-items: flex-start;
                gap: 0.85rem;
            }

            .writeup-detail-student-icon {
                display: flex;
                width: 42px;
                height: 42px;
                flex: 0 0 42px;
                align-items: center;
                justify-content: center;
                border-radius: 0.65rem;
                background: #eef3f8;
                color: #495057;
                font-size: 1rem;
            }

            .writeup-detail-student-content {
                min-width: 0;
            }

            .writeup-detail-student-name {
                margin: 0;
                color: var(--cyb-text, #212529);
                font-size: 1rem;
                font-weight: 650;
            }

            .writeup-detail-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 0.35rem 1rem;
                margin-top: 0.45rem;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.74rem;
            }

            .writeup-detail-meta span {
                display: inline-flex;
                align-items: center;
                gap: 0.3rem;
            }

            .writeup-detail-actions {
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                gap: 0.5rem;
            }

            .writeup-detail-actions .btn {
                font-size: 0.78rem;
                font-weight: 500;
            }

            .writeup-lock-notice {
                border-top: 1px solid #d9e4f5;
            }

            /*
             * Tabs
             */

            .writeup-tabs {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 0.9rem 1rem;
                border-bottom: 1px solid var(--cyb-border-soft, #edf0f2);
                background: #fff;
            }

            .writeup-tabs-main {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .writeup-tab {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                min-height: 36px;
                padding: 0.4rem 0.7rem;
                border: 1px solid var(--cyb-border, #e7eaed);
                border-radius: 0.5rem;
                background: #fff;
                color: #687078;
                font-size: 0.76rem;
                font-weight: 600;
                transition:
                    background 0.15s ease,
                    border-color 0.15s ease,
                    color 0.15s ease;
            }

            .writeup-tab:hover {
                background: #f8f9fa;
            }

            .writeup-tab.active {
                border-color: #cfd6dd;
                background: #f1f3f5;
                color: #343a40;
            }

            .writeup-tab.active.review {
                border-color: #e8d39a;
                background: #fff8e1;
                color: #735d18;
            }

            .writeup-tab-count {
                display: inline-flex;
                min-height: 20px;
                align-items: center;
                padding: 0.08rem 0.4rem;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.8);
                font-size: 0.65rem;
            }

            .writeup-tab-help {
                color: var(--cyb-muted, #6c757d);
                font-size: 0.72rem;
            }

            /*
             * Workspace
             */

            .writeup-workspace {
                padding: 1.1rem;
            }

            .writeup-workspace-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .writeup-readonly-content {
                min-height: 340px;
                padding: 1.1rem;
                border: 1px solid var(--cyb-border, #e7eaed);
                border-radius: 0.65rem;
                background: #fafbfc;
            }

            .writeup-rendered-text {
                color: #343a40;
                font-size: 0.9rem;
                line-height: 1.75;
            }

            .writeup-rendered-text > :last-child {
                margin-bottom: 0;
            }

            /*
             * Editor
             */

            .writeup-formatting-help {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.5rem;
                margin-bottom: 0.75rem;
                padding: 0.65rem 0.75rem;
                border: 1px solid var(--cyb-border, #e7eaed);
                border-radius: 0.55rem;
                background: #f8f9fa;
            }

            .writeup-formatting-label {
                color: #343a40;
                font-size: 0.73rem;
                font-weight: 600;
            }

            .writeup-formatting-note {
                color: var(--cyb-muted, #6c757d);
                font-size: 0.7rem;
            }

            .writeup-editor {
                min-height: 320px;
                padding: 0.9rem 1rem;
                border-color: #dfe3e7;
                font-size: 0.9rem;
                line-height: 1.75;
                resize: vertical;
            }

            .writeup-editor:focus {
                border-color: #86b7fe;
                box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.12);
            }

            .writeup-editor-footer {
                display: flex;
                justify-content: flex-end;
                margin-top: 0.45rem;
                font-size: 0.72rem;
                font-weight: 500;
            }

            /*
             * Preview
             */

            .writeup-preview-card {
                overflow: hidden;
                margin-top: 1rem;
                border: 1px solid #d9e4f5;
                border-radius: 0.65rem;
                background: #fff;
            }

            .writeup-preview-header {
                padding: 0.75rem 0.9rem;
                border-bottom: 1px solid #d9e4f5;
                background: #f4f8fd;
            }

            .writeup-preview-title {
                margin: 0;
                color: #355779;
                font-size: 0.8rem;
                font-weight: 600;
            }

            .writeup-preview-subtitle {
                margin-top: 0.1rem;
                color: #6c757d;
                font-size: 0.7rem;
            }

            .writeup-preview-body {
                max-height: 280px;
                overflow-y: auto;
                padding: 1rem;
                background: #fbfcfd;
            }

            .writeup-view-notice {
                margin-top: 0.75rem;
                border: 1px solid #d9e4f5;
                border-radius: 0.55rem;
            }

            /*
             * Side cards
             */

            .writeup-side-stack {
                gap: 1rem;
            }

            .writeup-rule-list {
                display: flex;
                flex-direction: column;
                gap: 0.55rem;
            }

            .writeup-rule {
                display: flex;
                align-items: flex-start;
                gap: 0.65rem;
                padding: 0.7rem 0.75rem;
                border: 1px solid var(--cyb-border, #e7eaed);
                border-radius: 0.55rem;
            }

            .writeup-rule > i {
                margin-top: 0.05rem;
            }

            .writeup-rule-success {
                border-color: #cce7d8;
                background: #f1faf5;
                color: #24724d;
            }

            .writeup-rule-warning {
                border-color: #ead8a8;
                background: #fff9e7;
                color: #7d641c;
            }

            .writeup-rule-danger {
                border-color: #edc6c6;
                background: #fdf2f2;
                color: #9a3d3d;
            }

            .writeup-rule-title {
                font-size: 0.75rem;
                font-weight: 650;
            }

            .writeup-rule-description {
                margin-top: 0.1rem;
                font-size: 0.68rem;
                opacity: 0.85;
            }

            .writeup-context-list {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .writeup-context-list dt {
                margin-bottom: 0.2rem;
                color: var(--cyb-muted, #6c757d);
                font-size: 0.68rem;
                font-weight: 650;
                letter-spacing: 0.035em;
                text-transform: uppercase;
            }

            .writeup-context-list dd {
                margin: 0;
                color: #343a40;
                font-size: 0.8rem;
                line-height: 1.45;
            }

            /*
             * Responsive
             */

            @media (min-width: 1200px) {
                .writeup-side-stack {
                    position: sticky;
                    top: 1rem;
                }
            }

            @media (max-width: 991.98px) {
                .writeup-detail-header {
                    flex-direction: column;
                }

                .writeup-detail-actions {
                    justify-content: flex-start;
                }

                .writeup-tabs {
                    align-items: flex-start;
                    flex-direction: column;
                }
            }

            @media (max-width: 767.98px) {
                .writeup-workspace-heading {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .writeup-detail-student {
                    flex-direction: column;
                }

                .writeup-detail-actions {
                    width: 100%;
                }

                .writeup-detail-actions .btn {
                    flex: 1 1 auto;
                }
            }
        </style>
    @endpush
</div>