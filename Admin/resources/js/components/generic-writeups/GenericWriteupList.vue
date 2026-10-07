<template>
    <section class="cyb-card generic-writeup-list">
        <!-- Header -->
        <div class="generic-writeup-list-header">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="cyb-pill cyb-pill-primary">
                        <i class="bi bi-card-text"></i>
                        Generic Writeups
                    </span>

                    <span
                        class="cyb-pill"
                        :class="hasReachedLimit
                            ? 'cyb-pill-warning'
                            : 'cyb-pill-neutral'"
                    >
                        {{ currentCount }} / {{ maxLimit }}
                    </span>
                </div>

                <h2 class="cyb-section-title">
                    Generic Writeup List
                </h2>

                <p class="cyb-section-description">
                    Reusable writeups that can be assigned to students later.
                </p>
            </div>

            <div class="generic-writeup-list-actions">
                <button
                    type="button"
                    class="btn"
                    :class="canCreate
                        ? 'btn-primary'
                        : 'btn-light border'"
                    :disabled="!canCreate"
                    @click="$emit('create')"
                >
                    <i class="bi bi-plus-lg me-1"></i>
                    New Writeup
                </button>
            </div>
        </div>

        <!-- Notice -->
        <div
            v-if="!hasSelectedGroup"
            class="cyb-notice cyb-notice-info generic-writeup-list-notice"
        >
            <i class="bi bi-info-circle"></i>

            <div>
                Select a school year and college first before creating generic writeups.
            </div>
        </div>

        <div
            v-else-if="hasReachedLimit"
            class="cyb-notice cyb-notice-warning generic-writeup-list-notice"
        >
            <i class="bi bi-exclamation-triangle"></i>

            <div>
                Maximum generic writeups reached for this year and college.
            </div>
        </div>

        <!-- Content -->
        <div class="generic-writeup-list-body">
            <div
                v-if="writeups.length > 0"
                class="row g-4"
            >
                <div
                    v-for="(writeup, index) in writeups"
                    :key="writeup.id"
                    class="col-12 col-lg-6"
                >
                    <GenericWriteupCard
                        :writeup="writeup"
                        :number="index + 1"
                        @edit="$emit('edit', $event)"
                        @delete="$emit('delete', $event)"
                    />
                </div>
            </div>

            <div
                v-else
                class="cyb-empty-state generic-writeup-empty"
            >
                <div class="cyb-empty-state-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>

                <h3 class="cyb-empty-state-title">
                    No Generic Writeups Found
                </h3>

                <p class="cyb-empty-state-text">
                    {{
                        hasSelectedGroup
                            ? 'Create the first generic writeup for this year and college.'
                            : 'Select a year and college to view or create generic writeups.'
                    }}
                </p>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import GenericWriteupCard from './GenericWriteupCard.vue';

interface College {
    college_name?: string | null;
}

interface Creator {
    name?: string | null;
}

interface GenericWriteup {
    id: number | string;
    is_active: boolean;
    year?: string | number | null;
    content?: string | null;
    updated_at?: string | null;
    college?: College | null;
    creator?: Creator | null;
}

const props = withDefaults(
    defineProps<{
        writeups: GenericWriteup[];
        hasSelectedGroup: boolean;
        currentCount: number;
        maxLimit?: number;
    }>(),
    {
        maxLimit: 20,
    },
);

defineEmits<{
    create: [];
    edit: [writeup: GenericWriteup];
    delete: [writeup: GenericWriteup];
}>();

const hasReachedLimit = computed(() => {
    return props.currentCount >= props.maxLimit;
});

const canCreate = computed(() => {
    return props.hasSelectedGroup && !hasReachedLimit.value;
});
</script>

<style scoped>
.generic-writeup-list {
    overflow: hidden;
}

.generic-writeup-list-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.2rem;
    border-bottom: 1px solid var(--cyb-border-soft, #edf0f2);
    background: #fff;
}

.generic-writeup-list-actions {
    flex: 0 0 auto;
}

.generic-writeup-list-actions .btn {
    min-width: 140px;
}

.generic-writeup-list-notice {
    border-right: 0;
    border-left: 0;
    border-radius: 0;
}

.generic-writeup-list-body {
    padding: 1.1rem;
}

.generic-writeup-empty {
    padding-top: 3rem;
    padding-bottom: 3rem;
}

@media (max-width: 767.98px) {
    .generic-writeup-list-header {
        flex-direction: column;
    }

    .generic-writeup-list-actions {
        width: 100%;
    }

    .generic-writeup-list-actions .btn {
        width: 100%;
    }
}
</style>