<div wire:poll.5s>
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">

        {{-- Filters --}}
        <aside class="lg:col-span-4">
            <div class="cyb-page-card overflow-hidden">

                <div class="border-b border-[var(--cyb-border)] bg-white px-4 py-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="mb-1 text-sm font-bold uppercase tracking-wide text-[var(--cyb-primary)]">
                                Filters
                            </h2>

                            <p class="mb-0 text-xs text-[var(--cyb-muted)]">
                                Narrow down the review queue.
                            </p>
                        </div>

                        <span class="cyb-badge-soft">
                            <i class="bi bi-funnel"></i>
                        </span>
                    </div>
                </div>

                <div class="space-y-3 p-4">

                    <button type="button"
                            wire:click="clearFilters"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-4 py-2 text-sm font-semibold text-[var(--cyb-text)] shadow-sm transition hover:bg-[var(--cyb-primary-soft)]">
                        <i class="bi bi-x-lg"></i>
                        Clear Filters
                    </button>

                    @php
                        $contentCounts = $contentCounts ?? [];

                        $contentFilterOptions = [
                            'all' => [
                                'label' => 'All writeups',
                                'description' => 'Show every submitted writeup.',
                                'icon' => 'bi-inboxes',
                                'active' => 'border-[var(--cyb-primary)] bg-[var(--cyb-primary-soft)] text-[var(--cyb-primary)]',
                                'hover' => 'hover:bg-[var(--cyb-primary-soft)]/70',
                            ],
                            'over_300' => [
                                'label' => 'Over 300 characters',
                                'description' => 'Possible length violations.',
                                'icon' => 'bi-text-paragraph',
                                'active' => 'border-yellow-300 bg-yellow-50 text-yellow-800',
                                'hover' => 'hover:bg-yellow-50',
                            ],
                            'has_emoji' => [
                                'label' => 'Contains emojis',
                                'description' => 'Writeups that may need cleanup.',
                                'icon' => 'bi-emoji-smile',
                                'active' => 'border-pink-300 bg-pink-50 text-pink-800',
                                'hover' => 'hover:bg-pink-50',
                            ],
                            'has_profanity' => [
                                'label' => 'Possible profanity',
                                'description' => 'Needs manual checking only.',
                                'icon' => 'bi-exclamation-octagon',
                                'active' => 'border-red-300 bg-red-50 text-red-800',
                                'hover' => 'hover:bg-red-50',
                            ],
                        ];

                        $activeContentLabel = $contentFilterOptions[$contentFilter]['label'] ?? 'All writeups';
                        $selectedStatusLabel = $status ? ($statuses[$status] ?? ucfirst(str_replace('_', ' ', $status))) : 'All statuses';
                        $selectedCollegeName = $collegeId ? optional($colleges->firstWhere('id', $collegeId))->college_name : 'All colleges';
                    @endphp

                    <div class="overflow-hidden rounded-xl border border-[var(--cyb-border)] bg-white">
                        <button type="button"
                                wire:click="toggleFilterCard('content')"
                                class="flex w-full items-center justify-between gap-3 border-0 bg-[var(--cyb-primary-soft)] px-4 py-3 text-left">
                            <span class="min-w-0">
                                <span class="cyb-chip cyb-chip-role">
                                    <i class="bi bi-card-checklist"></i>
                                    Content Checks
                                </span>

                                <span class="mt-1 block truncate text-xs text-[var(--cyb-muted)]">
                                    {{ $activeContentLabel }}
                                </span>
                            </span>

                            <i class="bi {{ $contentFiltersOpen ? 'bi-chevron-up' : 'bi-chevron-down' }} text-sm text-[var(--cyb-muted)]"></i>
                        </button>

                        @if ($contentFiltersOpen)
                            <div class="space-y-2 p-3">
                                @foreach ($contentFilterOptions as $filterKey => $option)
                                    @php
                                        $isActive = $contentFilter === $filterKey;
                                        $count = $contentCounts[$filterKey] ?? null;
                                    @endphp

                                    <button type="button"
                                            wire:click="setContentFilter('{{ $filterKey }}')"
                                            class="flex w-full items-start justify-between gap-3 rounded-lg border px-3 py-2.5 text-left transition
                                                {{ $isActive
                                                    ? $option['active']
                                                    : 'border-transparent bg-white text-[var(--cyb-text)] ' . $option['hover'] }}">
                                        <span class="flex min-w-0 items-start gap-3">
                                            <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg
                                                {{ $isActive ? 'bg-white/70' : 'bg-slate-50 text-[var(--cyb-muted)]' }}">
                                                <i class="bi {{ $option['icon'] }}"></i>
                                            </span>

                                            <span class="min-w-0">
                                                <span class="block text-sm font-semibold">
                                                    {{ $option['label'] }}
                                                </span>

                                                <span class="block text-xs opacity-80">
                                                    {{ $option['description'] }}
                                                </span>
                                            </span>
                                        </span>

                                        @if (! is_null($count))
                                            <span class="shrink-0 rounded-full bg-white/80 px-2 py-0.5 text-xs font-bold">
                                                {{ $count }}
                                            </span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="overflow-hidden rounded-xl border border-[var(--cyb-border)] bg-white">
                        <button type="button"
                                wire:click="toggleFilterCard('status')"
                                class="flex w-full items-center justify-between gap-3 border-0 bg-[var(--cyb-primary-soft)] px-4 py-3 text-left">
                            <span class="min-w-0">
                                <span class="cyb-chip cyb-chip-neutral">
                                    <i class="bi bi-list-check"></i>
                                    Status
                                </span>

                                <span class="mt-1 block truncate text-xs text-[var(--cyb-muted)]">
                                    {{ $selectedStatusLabel }}
                                </span>
                            </span>

                            <i class="bi {{ $statusFiltersOpen ? 'bi-chevron-up' : 'bi-chevron-down' }} text-sm text-[var(--cyb-muted)]"></i>
                        </button>

                        @if ($statusFiltersOpen)
                            <div class="p-3">
                                @include('livewire.partials.status-filter')
                            </div>
                        @endif
                    </div>

                    <div class="overflow-hidden rounded-xl border border-[var(--cyb-border)] bg-white">
                        <button type="button"
                                wire:click="toggleFilterCard('college')"
                                class="flex w-full items-center justify-between gap-3 border-0 bg-[var(--cyb-primary-soft)] px-4 py-3 text-left">
                            <span class="min-w-0">
                                <span class="cyb-chip cyb-chip-username">
                                    <i class="bi bi-building"></i>
                                    Colleges / Schools
                                </span>

                                <span class="mt-1 block truncate text-xs text-[var(--cyb-muted)]">
                                    {{ $selectedCollegeName }}
                                </span>
                            </span>

                            <i class="bi {{ $collegeFiltersOpen ? 'bi-chevron-up' : 'bi-chevron-down' }} text-sm text-[var(--cyb-muted)]"></i>
                        </button>

                        @if ($collegeFiltersOpen)
                            <div class="p-3">
                                @include('livewire.partials.college-filter')
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </aside>

        {{-- Writeup List --}}
        <section class="lg:col-span-8">
            <div class="cyb-page-card overflow-hidden">

                {{-- List Toolbar --}}
                <div class="flex flex-col gap-3 border-b border-[var(--cyb-border)] bg-white px-5 py-4 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="cyb-badge-soft">
                            <i class="bi bi-inboxes"></i>
                            Writeup Queue
                        </span>

                        @if ($writeups->total() > 0)
                            <span class="text-xs text-[var(--cyb-muted)]">
                                Showing
                                <span class="font-semibold text-[var(--cyb-text)]">{{ $writeups->firstItem() }}</span>
                                –
                                <span class="font-semibold text-[var(--cyb-text)]">{{ $writeups->lastItem() }}</span>
                                of
                                <span class="font-semibold text-[var(--cyb-text)]">{{ $writeups->total() }}</span>
                            </span>
                        @else
                            <span class="text-xs text-[var(--cyb-muted)]">
                                No writeups found
                            </span>
                        @endif

                        <span wire:loading.delay class="cyb-chip cyb-chip-neutral">
                            <i class="bi bi-arrow-repeat"></i>
                            Updating
                        </span>
                    </div>

                    <div class="w-full xl:w-96">
                        <label for="search" class="sr-only">Search writeups</label>

                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[var(--cyb-muted)]">
                                <i class="bi bi-search"></i>
                            </span>

                            <input id="search"
                                   type="text"
                                   wire:model.live.debounce.500ms="search"
                                   class="block w-full rounded-lg border border-[var(--cyb-border)] bg-white py-2 pl-10 pr-3 text-sm text-[var(--cyb-text)] shadow-sm transition placeholder:text-slate-400 focus:border-[var(--cyb-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)]/20"
                                   placeholder="Search student name, ID, or SLMIS ID">
                        </div>
                    </div>
                </div>

                {{-- Rows --}}
                <div class="divide-y divide-[var(--cyb-border)] bg-white">
                    @forelse ($writeups as $writeup)
                        @php
                            $student = $writeup->studentInfo;

                            $rawWriteup = trim(strip_tags($writeup->edited_writeup ?: $writeup->writeup));
                            $preview = \Illuminate\Support\Str::limit($rawWriteup, 125);
                            $characterCount = \Illuminate\Support\Str::length($rawWriteup);

                            $hasEmoji = preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $rawWriteup);

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

                            $lowerWriteup = mb_strtolower($rawWriteup);
                            $hasProfanity = $profanityTerms->contains(fn ($term) => str_contains($lowerWriteup, mb_strtolower($term)));

                            $statusClass = match ($writeup->review_status) {
                                'reviewed' => 'border-green-200 bg-green-50 text-green-700',
                                'in_review' => 'border-sky-200 bg-sky-50 text-sky-700',
                                'flagged' => 'border-yellow-200 bg-yellow-50 text-yellow-700',
                                default => 'border-slate-200 bg-slate-50 text-slate-700',
                            };

                            $statusIcon = match ($writeup->review_status) {
                                'reviewed' => 'bi-check2-circle',
                                'in_review' => 'bi-pencil-square',
                                'flagged' => 'bi-flag-fill',
                                default => 'bi-hourglass-split',
                            };
                        @endphp

                        <div wire:key="writeup-row-{{ $writeup->id }}"
                             class="group flex gap-3 px-4 py-3 transition hover:bg-[var(--cyb-primary-soft)]/60">

                            {{-- Flag --}}
                            <div class="pt-0.5">
                                @if ($canProofread)
                                    <button type="button"
                                            wire:key="flag-button-{{ $writeup->id }}"
                                            wire:click="toggleFlag({{ $writeup->id }})"
                                            title="{{ $writeup->is_flagged ? 'Remove flag' : 'Flag writeup' }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-base transition
                                                {{ $writeup->is_flagged
                                                    ? 'bg-red-50 text-red-600 hover:bg-red-100'
                                                    : 'text-slate-300 hover:bg-yellow-50 hover:text-yellow-600' }}">
                                        @if ($writeup->is_flagged)
                                            <i class="bi bi-flag-fill"></i>
                                        @else
                                            <i class="bi bi-flag"></i>
                                        @endif
                                    </button>
                                @else
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-base
                                        {{ $writeup->is_flagged ? 'text-red-600' : 'text-slate-300' }}">
                                        @if ($writeup->is_flagged)
                                            <i class="bi bi-flag-fill"></i>
                                        @else
                                            <i class="bi bi-flag"></i>
                                        @endif
                                    </span>
                                @endif
                            </div>

                            {{-- Main Link --}}
                            <a href="{{ route('writeups.review.show', $writeup) }}"
                               class="grid min-w-0 flex-1 gap-3 text-decoration-none text-[var(--cyb-text)] lg:grid-cols-[11rem_1fr_auto]">

                                {{-- Student --}}
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-[var(--cyb-text)] group-hover:text-[var(--cyb-primary)]">
                                        {{ $student?->formatted_full_name ?? 'No student info' }}
                                    </div>

                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                        @if ($student?->year)
                                            <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-600">
                                                Batch {{ $student->year }}
                                            </span>
                                        @endif

                                        @if ($student?->program?->is_graduate_program)
                                            <span class="rounded-full border border-violet-200 bg-violet-50 px-2 py-0.5 text-[11px] font-semibold text-violet-700">
                                                Graduate
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- College, Program, Preview --}}
                                <div class="min-w-0">
                                    <div class="truncate text-xs">
                                        <span class="font-semibold text-[var(--cyb-primary)]">
                                            {{ $student?->college?->college_name ?? 'No college' }}
                                        </span>

                                        @if ($student?->program)
                                            <span class="text-[var(--cyb-muted)]"> — </span>
                                            <span class="text-[var(--cyb-text)]">
                                                {{ $student->program->program_name }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-1 truncate text-xs text-[var(--cyb-muted)]">
                                        {{ $preview ?: 'No writeup content available.' }}
                                    </div>

                                    @if ($characterCount > 300 || $hasEmoji || $hasProfanity || $writeup->lockedBy)
                                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                            @if ($characterCount > 300)
                                                <span class="rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-700">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                    Over 300
                                                </span>
                                            @endif

                                            @if ($hasEmoji)
                                                <span class="rounded-full border border-pink-200 bg-pink-50 px-2 py-0.5 text-[11px] font-semibold text-pink-700">
                                                    <i class="bi bi-emoji-smile me-1"></i>
                                                    Emoji
                                                </span>
                                            @endif

                                            @if ($hasProfanity)
                                                <span class="rounded-full border border-orange-200 bg-orange-50 px-2 py-0.5 text-[11px] font-semibold text-orange-700">
                                                    <i class="bi bi-exclamation-octagon me-1"></i>
                                                    Check language
                                                </span>
                                            @endif

                                            @if ($writeup->lockedBy)
                                                <span class="rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">
                                                    <i class="bi bi-lock me-1"></i>
                                                    {{ $writeup->lockedBy->name }}
                                                    @if ($writeup->locked_at)
                                                        · {{ $writeup->locked_at->diffForHumans() }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Status --}}
                                <div class="flex items-start justify-start lg:justify-end">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $statusClass }}">
                                        <i class="bi {{ $statusIcon }}"></i>
                                        {{ ucfirst(str_replace('_', ' ', $writeup->review_status ?? 'pending')) }}
                                    </span>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="cyb-empty-state">
                            <div class="cyb-empty-state-icon">
                                <i class="bi bi-inbox"></i>
                            </div>

                            <h3 class="mb-1 text-base font-semibold text-[var(--cyb-text)]">
                                No writeups found
                            </h3>

                            <p class="mb-0 text-sm text-[var(--cyb-muted)]">
                                Try clearing filters or searching for another student.
                            </p>
                        </div>
                    @endforelse
                </div>

                {{-- Compact Pagination --}}
                <div class="border-t border-[var(--cyb-border)] bg-white px-5 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-xs text-[var(--cyb-muted)]">
                            @if ($writeups->total() > 0)
                                Showing
                                <span class="font-semibold text-[var(--cyb-text)]">{{ $writeups->firstItem() }}</span>
                                to
                                <span class="font-semibold text-[var(--cyb-text)]">{{ $writeups->lastItem() }}</span>
                                of
                                <span class="font-semibold text-[var(--cyb-text)]">{{ $writeups->total() }}</span>
                                writeups
                            @else
                                No records to display
                            @endif
                        </div>

                        @if ($writeups->hasPages())
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        wire:click="previousPage"
                                        wire:loading.attr="disabled"
                                        @disabled($writeups->onFirstPage())
                                        class="inline-flex items-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-3 py-2 text-xs font-semibold text-[var(--cyb-text)] shadow-sm transition hover:bg-[var(--cyb-primary-soft)] disabled:cursor-not-allowed disabled:opacity-45">
                                    <i class="bi bi-chevron-left"></i>
                                    Previous
                                </button>

                                <span class="rounded-lg border border-[var(--cyb-border)] bg-[var(--cyb-primary-soft)] px-3 py-2 text-xs font-bold text-[var(--cyb-primary)]">
                                    Page {{ $writeups->currentPage() }} of {{ $writeups->lastPage() }}
                                </span>

                                <button type="button"
                                        wire:click="nextPage"
                                        wire:loading.attr="disabled"
                                        @disabled(! $writeups->hasMorePages())
                                        class="inline-flex items-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-3 py-2 text-xs font-semibold text-[var(--cyb-text)] shadow-sm transition hover:bg-[var(--cyb-primary-soft)] disabled:cursor-not-allowed disabled:opacity-45">
                                    Next
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </section>

    </div>
</div>