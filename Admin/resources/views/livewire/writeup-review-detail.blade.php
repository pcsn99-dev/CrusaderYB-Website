<div class="space-y-4" @unless($canEdit) wire:poll.5s @endunless>
    <x-alert />

    @php
        $student = $writeup->studentInfo;

        $originalText = trim((string) ($writeup->writeup ?? ''));
        $currentText = trim((string) ($editedWriteup ?: ($writeup->edited_writeup ?: $writeup->writeup)));

        $originalCount = \Illuminate\Support\Str::length($originalText);
        $currentCount = $this->characterCount;

        $hasEmoji = preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $currentText);

        $profanityTerms = collect(config('cyb.profanity_terms', [
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
        ]))
            ->map(fn ($term) => trim((string) $term))
            ->filter();

        $lowerCurrentText = mb_strtolower($currentText);
        $hasPossibleProfanity = $profanityTerms->contains(
            fn ($term) => str_contains($lowerCurrentText, mb_strtolower($term))
        );

        $statusMeta = match ($writeup->review_status) {
            'reviewed' => [
                'label' => 'Reviewed',
                'icon' => 'bi-check2-circle',
                'class' => 'border-green-200 bg-green-50 text-green-700',
            ],
            'in_review' => [
                'label' => 'In Review',
                'icon' => 'bi-pencil-square',
                'class' => 'border-sky-200 bg-sky-50 text-sky-700',
            ],
            'flagged' => [
                'label' => 'Flagged',
                'icon' => 'bi-flag-fill',
                'class' => 'border-yellow-200 bg-yellow-50 text-yellow-700',
            ],
            default => [
                'label' => 'Pending',
                'icon' => 'bi-hourglass-split',
                'class' => 'border-slate-200 bg-slate-50 text-slate-700',
            ],
        };

        $characterWarning = $currentCount > $maxCharacters;
        $characterNearLimit = $this->remainingCharacters <= 20 && ! $characterWarning;

        $activePanel = in_array($activePanel ?? 'review', ['original', 'review'], true)
            ? $activePanel
            : 'review';
    @endphp

    {{-- Header / Actions --}}
    <div class="cyb-page-card overflow-hidden">
        <div class="border-b border-[var(--cyb-border)] bg-white px-4 py-3">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">

                {{-- Student Summary --}}
                <div class="flex min-w-0 items-start gap-3">
                    <div class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--cyb-primary)] text-white shadow-sm">
                        <i class="bi bi-person-lines-fill text-lg"></i>
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="mb-0 truncate text-base font-bold text-[var(--cyb-primary)]">
                                {{ $student?->formatted_full_name ?? 'No student info' }}
                            </h2>

                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $statusMeta['class'] }}">
                                <i class="bi {{ $statusMeta['icon'] }}"></i>
                                {{ $statusMeta['label'] }}
                            </span>

                            @if ($writeup->is_flagged)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-[11px] font-bold text-red-700">
                                    <i class="bi bi-flag-fill"></i>
                                    Flagged
                                </span>
                            @endif
                        </div>

                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[var(--cyb-muted)]">
                            <span>
                                <i class="bi bi-person-vcard me-1"></i>
                                {{ $student?->university_id ?? 'No University ID' }}
                            </span>

                            <span>
                                <i class="bi bi-card-list me-1"></i>
                                {{ $student?->slmis_id ?? 'No SLMIS ID' }}
                            </span>

                            <span>
                                <i class="bi bi-calendar3 me-1"></i>
                                Batch {{ $student?->year ?? 'N/A' }}
                            </span>

                            <span class="truncate">
                                <i class="bi bi-building me-1"></i>
                                {{ $student?->college?->college_name ?? 'No college' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex flex-wrap items-center justify-start gap-2 xl:justify-end">
                    
                    <button type="button"
                            onclick="
                                if (window.history.length > 1) {
                                    window.history.back();
                                } else {
                                    window.location.href = '{{ route('writeups.review.index') }}';
                                }
                            "
                            class="inline-flex items-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-3 py-2 text-xs font-semibold text-[var(--cyb-text)] shadow-sm transition hover:bg-[var(--cyb-primary-soft)]">
                        <i class="bi bi-arrow-left"></i>
                        Back
                    </button>

                    @if ($canProofread && $canStartReview)
                        <button type="button"
                                wire:click="startReview"
                                class="inline-flex items-center gap-2 rounded-lg bg-[var(--cyb-primary)] px-3 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[var(--cyb-primary-dark)]">
                            <i class="bi bi-play-circle"></i>
                            Start Review
                        </button>
                    @endif

                    @if ($canProofread && $canEdit)
                        <button type="button"
                                wire:click="saveChanges"
                                class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-xs font-bold text-green-700 shadow-sm transition hover:bg-green-100">
                            <i class="bi bi-save"></i>
                            Save
                        </button>

                        <button type="button"
                                wire:click="markReviewed"
                                onclick="return confirm({{ \Illuminate\Support\Js::from('Mark this writeup as reviewed?') }});"
                                class="inline-flex items-center gap-2 rounded-lg border border-yellow-200 bg-yellow-50 px-3 py-2 text-xs font-bold text-yellow-800 shadow-sm transition hover:bg-yellow-100">
                            <i class="bi bi-check2-circle"></i>
                            Mark Reviewed
                        </button>

                        <button type="button"
                                wire:click="releaseReview"
                                onclick="return confirm({{ \Illuminate\Support\Js::from('Release this writeup so another staff member can review it?') }});"
                                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-100">
                            <i class="bi bi-unlock"></i>
                            Release
                        </button>
                    @endif

                    @if ($canProofread)
                        <button type="button"
                                wire:click="toggleFlag"
                                class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-xs font-bold shadow-sm transition
                                    {{ $writeup->is_flagged
                                        ? 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                        : 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' }}">
                            <i class="bi {{ $writeup->is_flagged ? 'bi-flag' : 'bi-flag-fill' }}"></i>
                            {{ $writeup->is_flagged ? 'Remove Flag' : 'Flag Writeup' }}
                        </button>
                    @endif
                </div>
            </div>
        </div>

        @if ($writeup->lockedBy)
            <div class="border-b border-blue-100 bg-blue-50 px-4 py-2 text-xs font-medium text-blue-700">
                <i class="bi bi-lock me-1"></i>
                Currently being reviewed by {{ $writeup->lockedBy->name }}
                @if ($writeup->locked_at)
                    · {{ $writeup->locked_at->diffForHumans() }}
                @endif
            </div>
        @endif
    </div>

    {{-- Main Layout --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">

        {{-- Main Content --}}
        <section class="xl:col-span-9">
            <div class="cyb-page-card overflow-hidden">

                {{-- Tabs --}}
                <div class="border-b border-[var(--cyb-border)] bg-white px-4 py-3">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap gap-2">
                            <button type="button"
                                    wire:click="setActivePanel('original')"
                                    class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-xs font-bold transition
                                        {{ $activePanel === 'original'
                                            ? 'border-slate-300 bg-slate-100 text-slate-800'
                                            : 'border-[var(--cyb-border)] bg-white text-[var(--cyb-muted)] hover:bg-slate-50' }}">
                                <i class="bi bi-file-earmark-text"></i>
                                Original Submission
                                <span class="rounded-full bg-white/80 px-2 py-0.5 text-[10px]">
                                    {{ $originalCount }}
                                </span>
                            </button>

                            <button type="button"
                                    wire:click="setActivePanel('review')"
                                    class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-xs font-bold transition
                                        {{ $activePanel === 'review'
                                            ? 'border-yellow-300 bg-yellow-50 text-yellow-800'
                                            : 'border-[var(--cyb-border)] bg-white text-[var(--cyb-muted)] hover:bg-yellow-50' }}">
                                <i class="bi bi-pencil-square"></i>
                                Review Workspace
                                <span class="rounded-full bg-white/80 px-2 py-0.5 text-[10px]">
                                    {{ $currentCount }}/{{ $maxCharacters }}
                                </span>
                            </button>
                        </div>

                        <div class="text-xs text-[var(--cyb-muted)]">
                            @if ($activePanel === 'original')
                                Read-only student submission.
                            @else
                                Rule-based cleanup only. Keep the student’s voice.
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Original Tab --}}
                @if ($activePanel === 'original')
                    <div class="bg-white p-4">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-700">
                                <i class="bi bi-file-earmark-text"></i>
                                Original Submission
                            </span>

                            <span class="text-xs font-semibold text-[var(--cyb-muted)]">
                                {{ $originalCount }} characters
                            </span>
                        </div>

                        <div class="min-h-[340px] rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                            @if ($writeup->writeup)
                                <div class="text-sm leading-7 text-slate-700">
                                    {!! $this->renderedOriginalWriteup !!}
                                </div>
                            @else
                                <p class="mb-0 text-sm text-[var(--cyb-muted)]">
                                    No original writeup content available.
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Review Tab --}}
                @if ($activePanel === 'review')
                    <div class="bg-white p-4">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full border border-yellow-200 bg-yellow-50 px-2.5 py-1 text-xs font-bold text-yellow-800">
                                <i class="bi bi-pencil-square"></i>
                                Review Workspace
                            </span>

                            <span class="rounded-full border px-2.5 py-1 text-xs font-bold
                                {{ $characterWarning
                                    ? 'border-red-200 bg-red-50 text-red-700'
                                    : ($characterNearLimit
                                        ? 'border-yellow-200 bg-yellow-50 text-yellow-800'
                                        : 'border-green-200 bg-green-50 text-green-700') }}">
                                {{ $currentCount }} / {{ $maxCharacters }}
                            </span>
                        </div>

                        @if ($canProofread && $canEdit)

                            <div class="mb-3 rounded-xl border border-[var(--cyb-border)] bg-slate-50 px-3 py-2">
                                <div class="flex flex-wrap items-center gap-2 text-xs text-[var(--cyb-muted)]">
                                    <span class="font-bold text-[var(--cyb-text)]">
                                        Formatting:
                                    </span>

                                    <code class="rounded border bg-white px-2 py-1 text-[11px]">**bold**</code>
                                    <code class="rounded border bg-white px-2 py-1 text-[11px]">*italic*</code>

                                    <span class="text-[11px]">
                                        Avoid rewriting style unless needed for rules.
                                    </span>
                                </div>
                            </div>

                            <textarea
                                wire:model.live.debounce.300ms="editedWriteup"
                                maxlength="{{ $maxCharacters }}"
                                rows="12"
                                class="block min-h-[320px] w-full rounded-xl border border-[var(--cyb-border)] bg-white px-4 py-3 text-sm leading-7 text-[var(--cyb-text)] shadow-sm transition placeholder:text-slate-400 focus:border-[var(--cyb-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)]/20"
                                placeholder="Edit the student's writeup here..."></textarea>

                            @error('editedWriteup')
                                <div class="mt-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">
                                    <i class="bi bi-exclamation-circle me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="mt-2 flex justify-end">
                                <span class="text-xs font-semibold
                                    {{ $this->remainingCharacters <= 20 ? 'text-red-600' : 'text-[var(--cyb-muted)]' }}">
                                    {{ $this->remainingCharacters }} characters remaining
                                </span>
                            </div>

                            {{-- Preview only during active review --}}
                            @if (filled($editedWriteup))
                                <div class="mt-4 overflow-hidden rounded-xl border border-blue-100 bg-white">
                                    <div class="border-b border-blue-100 bg-[var(--cyb-primary-soft)] px-4 py-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-white px-2.5 py-1 text-xs font-bold text-[var(--cyb-primary)]">
                                                <i class="bi bi-eye"></i>
                                                Preview
                                            </span>

                                            <span class="text-xs text-[var(--cyb-muted)]">
                                                Formatted output
                                            </span>
                                        </div>
                                    </div>

                                    <div class="max-h-[260px] overflow-y-auto bg-[var(--cyb-primary-soft)]/30 p-4">
                                        <div class="text-sm leading-7 text-[var(--cyb-text)]">
                                            {!! $this->renderedEditedWriteup !!}
                                        </div>
                                    </div>
                                </div>
                            @endif

                        @else

                            <div class="min-h-[340px] rounded-xl border border-yellow-100 bg-yellow-50/50 p-4">
                                @if ($writeup->edited_writeup || $writeup->writeup)
                                    <div class="text-sm leading-7 text-[var(--cyb-text)]">
                                        {!! \Illuminate\Support\Str::markdown(
                                            $writeup->edited_writeup ?: $writeup->writeup,
                                            [
                                                'html_input' => 'strip',
                                                'allow_unsafe_links' => false,
                                            ]
                                        ) !!}
                                    </div>
                                @else
                                    <p class="mb-0 text-sm text-[var(--cyb-muted)]">
                                        No writeup content available.
                                    </p>
                                @endif
                            </div>

                            <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                                @if (! $canProofread)
                                    <i class="bi bi-eye me-1"></i>
                                    You only have permission to view writeups.
                                @else
                                    <i class="bi bi-info-circle me-1"></i>
                                    Start reviewing this writeup to edit the reviewed version.
                                @endif
                            </div>

                        @endif
                    </div>
                @endif

            </div>
        </section>

        {{-- Side Context --}}
        <aside class="xl:col-span-3">
            <div class="space-y-3">

                {{-- Rule Checks --}}
                <div class="cyb-page-card overflow-hidden">
                    <div class="border-b border-[var(--cyb-border)] bg-[var(--cyb-accent-soft)] px-4 py-3">
                        <span class="cyb-chip cyb-chip-neutral">
                            <i class="bi bi-clipboard-check"></i>
                            Rule Checks
                        </span>
                    </div>

                    <div class="space-y-2 p-3">
                        <div class="flex items-start gap-3 rounded-lg border px-3 py-2
                            {{ $characterWarning
                                ? 'border-red-200 bg-red-50 text-red-700'
                                : ($characterNearLimit
                                    ? 'border-yellow-200 bg-yellow-50 text-yellow-800'
                                    : 'border-green-200 bg-green-50 text-green-700') }}">
                            <i class="bi {{ $characterWarning ? 'bi-exclamation-triangle' : ($characterNearLimit ? 'bi-exclamation-circle' : 'bi-check2-circle') }} mt-0.5"></i>

                            <div class="min-w-0">
                                <p class="mb-0 text-xs font-bold">
                                    {{ $currentCount }} / {{ $maxCharacters }}
                                </p>
                                <p class="mb-0 text-[11px] opacity-80">
                                    {{ $characterWarning ? 'Over the limit' : ($characterNearLimit ? 'Near the limit' : 'Within limit') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 rounded-lg border px-3 py-2
                            {{ $hasEmoji ? 'border-pink-200 bg-pink-50 text-pink-700' : 'border-green-200 bg-green-50 text-green-700' }}">
                            <i class="bi {{ $hasEmoji ? 'bi-emoji-smile' : 'bi-check2-circle' }} mt-0.5"></i>

                            <div>
                                <p class="mb-0 text-xs font-bold">
                                    {{ $hasEmoji ? 'Emoji found' : 'No emoji found' }}
                                </p>
                                <p class="mb-0 text-[11px] opacity-80">
                                    Manual check only.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 rounded-lg border px-3 py-2
                            {{ $hasPossibleProfanity ? 'border-orange-200 bg-orange-50 text-orange-700' : 'border-green-200 bg-green-50 text-green-700' }}">
                            <i class="bi {{ $hasPossibleProfanity ? 'bi-exclamation-octagon' : 'bi-check2-circle' }} mt-0.5"></i>

                            <div>
                                <p class="mb-0 text-xs font-bold">
                                    {{ $hasPossibleProfanity ? 'Check language' : 'No flagged words' }}
                                </p>
                                <p class="mb-0 text-[11px] opacity-80">
                                    Not automatic rejection.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Academic Context --}}
                <div class="cyb-page-card overflow-hidden">
                    <div class="border-b border-[var(--cyb-border)] bg-[var(--cyb-primary-soft)] px-4 py-3">
                        <span class="cyb-chip cyb-chip-username">
                            <i class="bi bi-mortarboard"></i>
                            Academic Context
                        </span>
                    </div>

                    <div class="space-y-3 p-3 text-sm">
                        <div>
                            <p class="mb-1 text-[11px] font-bold uppercase tracking-wide text-[var(--cyb-muted)]">
                                College
                            </p>
                            <p class="mb-0 text-xs font-semibold text-[var(--cyb-primary)]">
                                {{ $student?->college?->college_name ?? 'No college' }}
                            </p>
                        </div>

                        <div>
                            <p class="mb-1 text-[11px] font-bold uppercase tracking-wide text-[var(--cyb-muted)]">
                                Program
                            </p>
                            <p class="mb-0 text-xs text-[var(--cyb-text)]">
                                {{ $student?->program?->program_name ?? 'No program' }}
                            </p>
                        </div>

                        <div>
                            <p class="mb-1 text-[11px] font-bold uppercase tracking-wide text-[var(--cyb-muted)]">
                                Major
                            </p>

                            @if ($student?->major)
                                <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-700">
                                    {{ $student->major->major_name }}
                                </span>
                            @else
                                <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-500">
                                    None
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </aside>

    </div>
</div>