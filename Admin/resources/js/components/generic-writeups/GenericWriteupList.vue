<template>
    <div class="overflow-hidden rounded-2xl border border-[var(--cyb-border)] bg-white shadow-sm">
        <!-- Header -->

        <div class="border-b border-[var(--cyb-border)] bg-white px-5 py-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-[var(--cyb-primary-soft)] px-2.5 py-1 text-xs font-bold text-[var(--cyb-primary)]">
                            <i class="bi bi-card-text"></i>
                            Generic Writeups
                        </span>

                        <span
                            class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold"
                            :class="hasReachedLimit
                                ? 'border-yellow-200 bg-yellow-50 text-yellow-800'
                                : 'border-slate-200 bg-slate-50 text-slate-600'"
                        >
                            {{ currentCount }} / {{ maxLimit }}
                        </span>
                    </div>

                    <h3 class="mb-0 mt-2 text-lg font-bold text-[var(--cyb-primary)]">
                        Generic Writeup List
                    </h3>

                    <p class="mb-0 mt-1 max-w-md text-sm text-[var(--cyb-muted)]">
                        Reusable writeups that can be assigned to students later.
                    </p>
                </div>

                <div class="flex shrink-0 justify-start lg:justify-end">
                    <button
                        type="button"
                        :disabled="!canCreate"
                        @click="$emit('create')"
                        class="inline-flex !w-auto min-w-[9.5rem] items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-bold shadow-sm transition disabled:cursor-not-allowed disabled:opacity-50"
                        :class="canCreate
                            ? 'bg-[var(--cyb-primary)] text-white hover:bg-[var(--cyb-primary-dark)]'
                            : 'border border-slate-200 bg-slate-50 text-slate-500'"
                    >
                        <i class="bi bi-plus-lg"></i>
                        New Writeup
                    </button>
                </div>
            </div>
        </div>

        <!-- Notice -->
        <div
            v-if="!hasSelectedGroup"
            class="border-b border-blue-100 bg-blue-50 px-5 py-3 text-sm text-blue-700"
        >
            <i class="bi bi-info-circle me-1"></i>
            Select a school year and college first before creating generic writeups.
        </div>

        <div
            v-else-if="hasReachedLimit"
            class="border-b border-yellow-100 bg-yellow-50 px-5 py-3 text-sm text-yellow-800"
        >
            <i class="bi bi-exclamation-triangle me-1"></i>
            Maximum generic writeups reached for this year and college.
        </div>

        <!-- Content -->
        <div class="p-4 sm:p-5">
            <div
                v-if="writeups.length > 0"
                class="grid grid-cols-1 gap-4 lg:grid-cols-2"
            >
                <GenericWriteupCard
                    v-for="(writeup, index) in writeups"
                    :key="writeup.id"
                    :writeup="writeup"
                    :number="index + 1"
                    @edit="$emit('edit', $event)"
                    @delete="$emit('delete', $event)"
                />
            </div>

            <div
                v-else
                class="rounded-2xl border border-dashed border-[var(--cyb-border)] bg-slate-50 px-5 py-12 text-center"
            >
                <div class="mx-auto mb-3 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-[var(--cyb-primary)] shadow-sm">
                    <i class="bi bi-file-earmark-text text-2xl"></i>
                </div>

                <h4 class="mb-1 text-base font-bold text-[var(--cyb-text)]">
                    No generic writeups found
                </h4>

                <p class="mb-0 text-sm text-[var(--cyb-muted)]">
                    {{
                        hasSelectedGroup
                            ? 'Create the first generic writeup for this year and college.'
                            : 'Select a year and college to view or create generic writeups.'
                    }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import GenericWriteupCard from './GenericWriteupCard.vue';

const props = defineProps({
    writeups: {
        type: Array,
        required: true,
    },
    hasSelectedGroup: {
        type: Boolean,
        required: true,
    },
    currentCount: {
        type: Number,
        required: true,
    },
    maxLimit: {
        type: Number,
        default: 20,
    },
});

defineEmits(['create', 'edit', 'delete']);

const hasReachedLimit = computed(() => {
    return props.currentCount >= props.maxLimit;
});

const canCreate = computed(() => {
    return props.hasSelectedGroup && !hasReachedLimit.value;
});
</script>