<template>
    <div class="overflow-hidden rounded-2xl border border-[var(--cyb-border)] bg-white shadow-sm">
        <div class="p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="mb-1 text-xs font-bold uppercase tracking-wide text-[var(--cyb-muted)]">
                        Current Count
                    </p>

                    <div class="flex items-end gap-1">
                        <span class="text-3xl font-bold leading-none text-[var(--cyb-primary)]">
                            {{ currentCount }}
                        </span>

                        <span class="pb-1 text-sm font-semibold text-[var(--cyb-muted)]">
                            / {{ maxLimit }}
                        </span>
                    </div>
                </div>

                <span
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                    :class="iconClass"
                >
                    <i class="bi" :class="iconName"></i>
                </span>
            </div>

            <div
                class="mt-4 rounded-xl border px-3 py-2 text-xs leading-5"
                :class="messageClass"
            >
                <div class="flex items-start gap-2">
                    <i class="bi mt-0.5" :class="messageIcon"></i>

                    <p class="mb-0">
                        {{ message }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    currentCount: {
        type: Number,
        required: true,
    },
    hasSelectedGroup: {
        type: Boolean,
        required: true,
    },
    maxLimit: {
        type: Number,
        default: 20,
    },
});

const remainingCount = computed(() => {
    return Math.max(props.maxLimit - props.currentCount, 0);
});

const isFull = computed(() => {
    return props.hasSelectedGroup && props.currentCount >= props.maxLimit;
});

const isNearLimit = computed(() => {
    return props.hasSelectedGroup && !isFull.value && remainingCount.value <= 3;
});

const iconClass = computed(() => {
    if (!props.hasSelectedGroup) {
        return 'bg-slate-100 text-slate-500';
    }

    if (isFull.value) {
        return 'bg-red-50 text-red-600';
    }

    if (isNearLimit.value) {
        return 'bg-yellow-50 text-yellow-700';
    }

    return 'bg-[var(--cyb-primary-soft)] text-[var(--cyb-primary)]';
});

const iconName = computed(() => {
    if (!props.hasSelectedGroup) {
        return 'bi-funnel';
    }

    if (isFull.value) {
        return 'bi-exclamation-triangle';
    }

    if (isNearLimit.value) {
        return 'bi-exclamation-circle';
    }

    return 'bi-check2-circle';
});

const messageClass = computed(() => {
    if (!props.hasSelectedGroup) {
        return 'border-slate-200 bg-slate-50 text-slate-600';
    }

    if (isFull.value) {
        return 'border-red-200 bg-red-50 text-red-700';
    }

    if (isNearLimit.value) {
        return 'border-yellow-200 bg-yellow-50 text-yellow-800';
    }

    return 'border-green-200 bg-green-50 text-green-700';
});

const messageIcon = computed(() => {
    if (!props.hasSelectedGroup) {
        return 'bi-info-circle';
    }

    if (isFull.value) {
        return 'bi-exclamation-triangle';
    }

    if (isNearLimit.value) {
        return 'bi-exclamation-circle';
    }

    return 'bi-check2-circle';
});

const message = computed(() => {
    if (!props.hasSelectedGroup) {
        return 'Select a school year and college to check the 20-writeup limit.';
    }

    if (isFull.value) {
        return 'Maximum generic writeups reached for this year and college.';
    }

    return `${remainingCount.value} more generic writeup${remainingCount.value === 1 ? '' : 's'} can still be created.`;
});
</script>