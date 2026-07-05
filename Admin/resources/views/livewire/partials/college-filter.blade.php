<div class="space-y-2">

    {{-- All Colleges --}}
    @php
        $allSelected = blank($collegeId);
    @endphp

    <button type="button"
            wire:click="setCollege(null)"
            class="flex w-full items-center justify-between gap-3 rounded-lg border px-3 py-2 text-left text-sm transition
                {{ $allSelected
                    ? 'border-[var(--cyb-primary)] bg-[var(--cyb-primary-soft)] text-[var(--cyb-primary)]'
                    : 'border-transparent bg-white text-[var(--cyb-text)] hover:bg-[var(--cyb-primary-soft)]/70' }}">
        <span class="flex min-w-0 items-center gap-2">
            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg
                {{ $allSelected ? 'bg-white/70' : 'bg-slate-50 text-[var(--cyb-muted)]' }}">
                <i class="bi bi-grid"></i>
            </span>

            <span class="min-w-0">
                <span class="block truncate font-semibold">
                    All Colleges
                </span>
            </span>
        </span>

        <span class="shrink-0 rounded-full bg-white/80 px-2 py-0.5 text-xs font-bold">
            {{ $totalCount }}
        </span>
    </button>

    {{-- College List --}}
    <div class="max-h-72 space-y-1 overflow-y-auto pr-1">
        @foreach ($colleges as $college)
            @php
                $isSelected = (int) $collegeId === (int) $college->id;
                $count = $collegeCounts[$college->id] ?? 0;
            @endphp

            <button type="button"
                    wire:click="setCollege({{ $college->id }})"
                    title="{{ $college->college_name }}"
                    class="flex w-full items-center justify-between gap-3 rounded-lg border px-3 py-2 text-left text-sm transition
                        {{ $isSelected
                            ? 'border-pink-300 bg-pink-50 text-pink-800'
                            : 'border-transparent bg-white text-[var(--cyb-text)] hover:bg-pink-50' }}">

                <span class="min-w-0 truncate font-medium">
                    {{ $college->college_name }}
                </span>

                <span class="shrink-0 rounded-full bg-white/80 px-2 py-0.5 text-xs font-bold
                    {{ $isSelected ? 'text-pink-800' : 'text-[var(--cyb-muted)]' }}">
                    {{ $count }}
                </span>
            </button>
        @endforeach
    </div>

</div>