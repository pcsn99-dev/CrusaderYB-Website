<div class="space-y-2">

    @php
        $statusOptions = [
            null => [
                'label' => 'All Writeups',
                'count' => $statusCounts['all'] ?? 0,
                'icon' => 'bi-inboxes',
                'active' => 'border-[var(--cyb-primary)] bg-[var(--cyb-primary-soft)] text-[var(--cyb-primary)]',
                'hover' => 'hover:bg-[var(--cyb-primary-soft)]/70',
                'selected' => blank($status) && ! $flaggedOnly,
            ],
            'pending' => [
                'label' => 'Pending Review',
                'count' => $statusCounts['pending'] ?? 0,
                'icon' => 'bi-hourglass-split',
                'active' => 'border-slate-300 bg-slate-50 text-slate-700',
                'hover' => 'hover:bg-slate-50',
                'selected' => $status === 'pending' && ! $flaggedOnly,
            ],
            'in_review' => [
                'label' => 'In Review',
                'count' => $statusCounts['in_review'] ?? 0,
                'icon' => 'bi-pencil-square',
                'active' => 'border-sky-300 bg-sky-50 text-sky-800',
                'hover' => 'hover:bg-sky-50',
                'selected' => $status === 'in_review' && ! $flaggedOnly,
            ],
            'reviewed' => [
                'label' => 'Reviewed',
                'count' => $statusCounts['reviewed'] ?? 0,
                'icon' => 'bi-check2-circle',
                'active' => 'border-green-300 bg-green-50 text-green-800',
                'hover' => 'hover:bg-green-50',
                'selected' => $status === 'reviewed' && ! $flaggedOnly,
            ],
        ];
    @endphp

    @foreach ($statusOptions as $statusKey => $option)
        <button type="button"
                wire:click="setStatus({{ is_null($statusKey) ? 'null' : "'" . $statusKey . "'" }})"
                class="flex w-full items-center justify-between gap-3 rounded-lg border px-3 py-2 text-left text-sm transition
                    {{ $option['selected']
                        ? $option['active']
                        : 'border-transparent bg-white text-[var(--cyb-text)] ' . $option['hover'] }}">

            <span class="flex min-w-0 items-center gap-2">
                <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg
                    {{ $option['selected'] ? 'bg-white/70' : 'bg-slate-50 text-[var(--cyb-muted)]' }}">
                    <i class="bi {{ $option['icon'] }}"></i>
                </span>

                <span class="truncate font-semibold">
                    {{ $option['label'] }}
                </span>
            </span>

            <span class="shrink-0 rounded-full bg-white/80 px-2 py-0.5 text-xs font-bold
                {{ $option['selected'] ? '' : 'text-[var(--cyb-muted)]' }}">
                {{ $option['count'] }}
            </span>
        </button>
    @endforeach

    {{-- Flagged Only --}}
    @php
        $flaggedSelected = $flaggedOnly;
    @endphp

    <button type="button"
            wire:click="toggleFlaggedOnly"
            class="flex w-full items-center justify-between gap-3 rounded-lg border px-3 py-2 text-left text-sm transition
                {{ $flaggedSelected
                    ? 'border-red-300 bg-red-50 text-red-800'
                    : 'border-transparent bg-white text-[var(--cyb-text)] hover:bg-red-50' }}">

        <span class="flex min-w-0 items-center gap-2">
            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg
                {{ $flaggedSelected ? 'bg-white/70' : 'bg-slate-50 text-[var(--cyb-muted)]' }}">
                <i class="bi {{ $flaggedSelected ? 'bi-flag-fill' : 'bi-flag' }}"></i>
            </span>

            <span class="truncate font-semibold">
                Flagged Only
            </span>
        </span>

        <span class="shrink-0 rounded-full bg-white/80 px-2 py-0.5 text-xs font-bold
            {{ $flaggedSelected ? '' : 'text-[var(--cyb-muted)]' }}">
            {{ $statusCounts['flagged_only'] ?? 0 }}
        </span>
    </button>

</div>