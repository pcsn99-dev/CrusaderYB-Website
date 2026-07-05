<template>
    <article
        class="group flex h-full flex-col overflow-hidden rounded-2xl border border-[var(--cyb-border)] bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-[var(--cyb-primary)]/30 hover:shadow-md"
    >
        <!-- Header -->
        <div class="border-b border-[var(--cyb-border)] bg-slate-50/80 px-4 py-3">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-600">
                            #{{ number }}
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold"
                            :class="statusClass"
                        >
                            <i class="bi" :class="writeup.is_active ? 'bi-check2-circle' : 'bi-pause-circle'"></i>
                            {{ writeup.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <h4 class="mb-0 truncate text-sm font-bold text-[var(--cyb-primary)]">
                        Generic Writeup
                    </h4>

                    <p class="mb-0 mt-1 truncate text-xs text-[var(--cyb-muted)]">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ writeup.year || 'No year' }}

                        <span class="mx-1">·</span>

                        <i class="bi bi-building me-1"></i>
                        {{ writeup.college?.college_name || 'No college' }}
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex shrink-0 items-center gap-1">
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-blue-700 transition hover:bg-blue-100"
                        title="Edit writeup"
                        @click="$emit('edit', writeup)"
                    >
                        <i class="bi bi-pencil"></i>
                    </button>

                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-700 transition hover:bg-red-100"
                        title="Delete writeup"
                        @click="$emit('delete', writeup)"
                    >
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="flex flex-1 flex-col p-4">
            <div class="mb-4 flex-1">
                <p
                    class="cyb-writeup-preview mb-0 text-sm leading-7 text-[var(--cyb-text)]"
                    :title="writeup.content || 'No content available.'"
                >
                    {{ writeup.content || 'No content available.' }}
                </p>
            </div>

            <!-- Meta -->
            <div class="mt-auto rounded-xl border border-[var(--cyb-border)] bg-slate-50 px-3 py-2.5">
                <div class="grid gap-2 text-xs text-[var(--cyb-muted)]">
                    <div class="flex min-w-0 items-center gap-2">
                        <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-white text-slate-500">
                            <i class="bi bi-person"></i>
                        </span>

                        <span class="min-w-0 truncate">
                            Created by
                            <span class="font-semibold text-[var(--cyb-text)]">
                                {{ writeup.creator?.name || 'Unknown' }}
                            </span>
                        </span>
                    </div>

                    <div class="flex min-w-0 items-center gap-2">
                        <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-white text-slate-500">
                            <i class="bi bi-clock-history"></i>
                        </span>

                        <span class="min-w-0 truncate">
                            Updated
                            <span class="font-semibold text-[var(--cyb-text)]">
                                {{ formattedUpdatedAt }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </article>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    writeup: {
        type: Object,
        required: true,
    },
    number: {
        type: Number,
        required: true,
    },
});

defineEmits(['edit', 'delete']);

const statusClass = computed(() => {
    return props.writeup.is_active
        ? 'border-green-200 bg-green-50 text-green-700'
        : 'border-slate-200 bg-slate-50 text-slate-600';
});

const formattedUpdatedAt = computed(() => {
    if (!props.writeup.updated_at) {
        return 'N/A';
    }

    const date = new Date(props.writeup.updated_at);

    if (Number.isNaN(date.getTime())) {
        return props.writeup.updated_at;
    }

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(date);
});
</script>

<style scoped>
.cyb-writeup-preview {
    display: -webkit-box;
    overflow: hidden;
    white-space: pre-line;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 4;
}
</style>