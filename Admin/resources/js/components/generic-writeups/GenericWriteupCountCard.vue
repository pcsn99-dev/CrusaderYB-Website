<template>
    <div class="cyb-card generic-writeup-count-card">
        <div class="generic-writeup-count-body">
            <div class="generic-writeup-count-header">
                <div>
                    <div class="generic-writeup-count-label">
                        Current Count
                    </div>

                    <div class="generic-writeup-count-value">
                        <span class="generic-writeup-count-current">
                            {{ currentCount }}
                        </span>

                        <span class="generic-writeup-count-limit">
                            / {{ maxLimit }}
                        </span>
                    </div>
                </div>

                <div
                    class="generic-writeup-count-icon"
                    :class="stateClass"
                >
                    <i class="bi" :class="iconName"></i>
                </div>
            </div>

            <div
                class="generic-writeup-count-message"
                :class="stateClass"
            >
                <i class="bi" :class="messageIcon"></i>

                <span>
                    {{ message }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        currentCount: number;
        hasSelectedGroup: boolean;
        maxLimit?: number;
    }>(),
    {
        maxLimit: 20,
    },
);

const remainingCount = computed(() => {
    return Math.max(props.maxLimit - props.currentCount, 0);
});

const isFull = computed(() => {
    return props.hasSelectedGroup
        && props.currentCount >= props.maxLimit;
});

const isNearLimit = computed(() => {
    return props.hasSelectedGroup
        && !isFull.value
        && remainingCount.value <= 3;
});

const stateClass = computed(() => {
    if (!props.hasSelectedGroup) {
        return 'is-neutral';
    }

    if (isFull.value) {
        return 'is-danger';
    }

    if (isNearLimit.value) {
        return 'is-warning';
    }

    return 'is-success';
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

<style scoped>
.generic-writeup-count-card {
    height: 100%;
}

.generic-writeup-count-body {
    padding: 1rem;
}

.generic-writeup-count-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.generic-writeup-count-label {
    color: var(--cyb-muted, #6c757d);
    font-size: 0.7rem;
    font-weight: 650;
    letter-spacing: 0.045em;
    text-transform: uppercase;
}

.generic-writeup-count-value {
    display: flex;
    align-items: flex-end;
    gap: 0.3rem;
    margin-top: 0.25rem;
}

.generic-writeup-count-current {
    color: var(--cyb-text, #212529);
    font-size: 1.9rem;
    font-weight: 700;
    line-height: 1;
}

.generic-writeup-count-limit {
    padding-bottom: 0.15rem;
    color: var(--cyb-muted, #6c757d);
    font-size: 0.8rem;
    font-weight: 600;
}

.generic-writeup-count-icon {
    display: flex;
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
    align-items: center;
    justify-content: center;
    border-radius: 0.65rem;
    font-size: 0.95rem;
}

.generic-writeup-count-message {
    display: flex;
    align-items: flex-start;
    gap: 0.55rem;
    margin-top: 1rem;
    padding: 0.7rem 0.75rem;
    border: 1px solid;
    border-radius: 0.6rem;
    font-size: 0.72rem;
    line-height: 1.45;
}

.generic-writeup-count-message > i {
    flex: 0 0 auto;
    margin-top: 0.05rem;
}

/* Neutral */

.generic-writeup-count-icon.is-neutral {
    background: #f1f3f5;
    color: #6c757d;
}

.generic-writeup-count-message.is-neutral {
    border-color: #dfe3e7;
    background: #f8f9fa;
    color: #687078;
}

/* Success */

.generic-writeup-count-icon.is-success {
    background: #e8f5ee;
    color: #197149;
}

.generic-writeup-count-message.is-success {
    border-color: #cce7d8;
    background: #f1faf5;
    color: #24724d;
}

/* Warning */

.generic-writeup-count-icon.is-warning {
    background: #fff4d8;
    color: #87620f;
}

.generic-writeup-count-message.is-warning {
    border-color: #ead8a8;
    background: #fff9e7;
    color: #7d641c;
}

/* Danger */

.generic-writeup-count-icon.is-danger {
    background: #fae8e8;
    color: #a33a3a;
}

.generic-writeup-count-message.is-danger {
    border-color: #edc6c6;
    background: #fdf2f2;
    color: #9a3d3d;
}
</style>