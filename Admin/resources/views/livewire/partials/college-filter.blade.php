<div class="cyb-filter-list">
    @php
        $allSelected = blank($collegeId);
    @endphp

    {{-- All Colleges --}}
    <button
        type="button"
        wire:click="setCollege(null)"
        class="cyb-filter-option {{ $allSelected ? 'cyb-filter-option-active' : '' }}"
    >
        <span class="cyb-filter-option-main">
            <span class="cyb-filter-option-icon">
                <i class="bi bi-grid"></i>
            </span>

            <span class="cyb-filter-option-text">
                <span class="cyb-filter-option-title">
                    All Colleges
                </span>
            </span>
        </span>

        <span class="cyb-filter-count">
            {{ $totalCount }}
        </span>
    </button>

    {{-- College List --}}
    <div class="cyb-filter-list cyb-filter-scroll">
        @foreach ($colleges as $college)
            @php
                $isSelected =
                    (int) $collegeId === (int) $college->id;

                $count =
                    $collegeCounts[$college->id] ?? 0;
            @endphp

            <button
                type="button"
                wire:click="setCollege({{ $college->id }})"
                title="{{ $college->college_name }}"
                class="cyb-filter-option {{ $isSelected ? 'cyb-filter-option-active' : '' }}"
            >
                <span class="cyb-filter-option-main">
                    <span class="cyb-filter-option-icon">
                        <i class="bi bi-building"></i>
                    </span>

                    <span class="cyb-filter-option-text">
                        <span class="cyb-filter-option-title">
                            {{ $college->college_name }}
                        </span>
                    </span>
                </span>

                <span class="cyb-filter-count">
                    {{ $count }}
                </span>
            </button>
        @endforeach
    </div>
</div>