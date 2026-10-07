<div class="cyb-filter-list">
    @php
        $statusOptions = [
            null => [
                'label' => 'All Writeups',
                'count' => $statusCounts['all'] ?? 0,
                'icon' => 'bi-inboxes',
                'selected' => blank($status) && ! $flaggedOnly,
                'class' => 'cyb-filter-option-active',
            ],

            'pending' => [
                'label' => 'Pending Review',
                'count' => $statusCounts['pending'] ?? 0,
                'icon' => 'bi-hourglass-split',
                'selected' => $status === 'pending' && ! $flaggedOnly,
                'class' => 'cyb-filter-option-warning-active',
            ],

            'in_review' => [
                'label' => 'In Review',
                'count' => $statusCounts['in_review'] ?? 0,
                'icon' => 'bi-pencil-square',
                'selected' => $status === 'in_review' && ! $flaggedOnly,
                'class' => 'cyb-filter-option-info-active',
            ],

            'reviewed' => [
                'label' => 'Reviewed',
                'count' => $statusCounts['reviewed'] ?? 0,
                'icon' => 'bi-check2-circle',
                'selected' => $status === 'reviewed' && ! $flaggedOnly,
                'class' => 'cyb-filter-option-success-active',
            ],
        ];
    @endphp

    @foreach ($statusOptions as $statusKey => $option)
        <button
            type="button"
            wire:click="setStatus({{ is_null($statusKey) ? 'null' : "'" . $statusKey . "'" }})"
            class="cyb-filter-option {{ $option['selected'] ? $option['class'] : '' }}"
        >
            <span class="cyb-filter-option-main">
                <span class="cyb-filter-option-icon">
                    <i class="bi {{ $option['icon'] }}"></i>
                </span>

                <span class="cyb-filter-option-text">
                    <span class="cyb-filter-option-title">
                        {{ $option['label'] }}
                    </span>
                </span>
            </span>

            <span class="cyb-filter-count">
                {{ $option['count'] }}
            </span>
        </button>
    @endforeach

    {{-- Flagged Only --}}
    <button
        type="button"
        wire:click="toggleFlaggedOnly"
        class="cyb-filter-option {{ $flaggedOnly ? 'cyb-filter-option-danger-active' : '' }}"
    >
        <span class="cyb-filter-option-main">
            <span class="cyb-filter-option-icon">
                <i class="bi {{ $flaggedOnly ? 'bi-flag-fill' : 'bi-flag' }}"></i>
            </span>

            <span class="cyb-filter-option-text">
                <span class="cyb-filter-option-title">
                    Flagged Only
                </span>
            </span>
        </span>

        <span class="cyb-filter-count">
            {{ $statusCounts['flagged_only'] ?? 0 }}
        </span>
    </button>
</div>