<template>
    <article class="cyb-card generic-writeup-card h-100">
        <!-- Header -->
        <div class="generic-writeup-card-header">
            <div class="generic-writeup-card-heading">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="cyb-pill cyb-pill-neutral">
                        #{{ number }}
                    </span>

                    <span
                        class="cyb-pill"
                        :class="writeup.is_active
                            ? 'cyb-pill-success'
                            : 'cyb-pill-neutral'"
                    >
                        <span
                            class="cyb-status-dot"
                            :class="writeup.is_active
                                ? 'cyb-status-dot-success'
                                : ''"
                        ></span>

                        {{ writeup.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <h3 class="generic-writeup-card-title">
                    Generic Writeup
                </h3>

                <div class="generic-writeup-card-context">
                    <span>
                        <i class="bi bi-calendar3"></i>
                        {{ writeup.year || 'No year' }}
                    </span>

                    <span class="generic-writeup-context-divider">
                        ·
                    </span>

                    <span>
                        <i class="bi bi-building"></i>
                        {{ writeup.college?.college_name || 'No college' }}
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="generic-writeup-card-actions">
                <button
                    type="button"
                    class="btn btn-sm btn-light border generic-writeup-action"
                    title="Edit writeup"
                    aria-label="Edit writeup"
                    @click="$emit('edit', writeup)"
                >
                    <i class="bi bi-pencil-square"></i>
                </button>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger generic-writeup-action"
                    title="Delete writeup"
                    aria-label="Delete writeup"
                    @click="$emit('delete', writeup)"
                >
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="generic-writeup-card-body">
            <div class="generic-writeup-content">
                <p
                    class="generic-writeup-preview"
                    :title="writeup.content || 'No content available.'"
                >
                    {{ writeup.content || 'No content available.' }}
                </p>
            </div>

            <!-- Metadata -->
            <div class="generic-writeup-meta">
                <div class="generic-writeup-meta-row">
                    <span class="generic-writeup-meta-icon">
                        <i class="bi bi-person"></i>
                    </span>

                    <span class="generic-writeup-meta-text">
                        Created by

                        <strong>
                            {{ writeup.creator?.name || 'Unknown' }}
                        </strong>
                    </span>
                </div>

                <div class="generic-writeup-meta-row">
                    <span class="generic-writeup-meta-icon">
                        <i class="bi bi-clock-history"></i>
                    </span>

                    <span class="generic-writeup-meta-text">
                        Updated

                        <strong>
                            {{ formattedUpdatedAt }}
                        </strong>
                    </span>
                </div>
            </div>
        </div>
    </article>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface College {
    college_name?: string | null;
}

interface Creator {
    name?: string | null;
}

interface GenericWriteup {
    is_active: boolean;
    year?: string | number | null;
    content?: string | null;
    updated_at?: string | null;
    college?: College | null;
    creator?: Creator | null;
}

const props = defineProps<{
    writeup: GenericWriteup;
    number: number;
}>();

defineEmits<{
    edit: [writeup: GenericWriteup];
    delete: [writeup: GenericWriteup];
}>();

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
.generic-writeup-card {
    display: flex;
    min-height: 100%;
    flex-direction: column;
    transition:
        border-color 0.15s ease,
        box-shadow 0.15s ease,
        transform 0.15s ease;
}

.generic-writeup-card:hover {
    border-color: #ccd7e2;
    box-shadow: 0 0.35rem 1rem rgba(0, 0, 0, 0.06);
    transform: translateY(-1px);
}

.generic-writeup-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid var(--cyb-border-soft, #edf0f2);
    background: #fafbfc;
}

.generic-writeup-card-heading {
    min-width: 0;
}

.generic-writeup-card-title {
    margin: 0;
    color: var(--cyb-text, #212529);
    font-size: 0.9rem;
    font-weight: 650;
}

.generic-writeup-card-context {
    display: flex;
    min-width: 0;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.3rem;
    margin-top: 0.35rem;
    color: var(--cyb-muted, #6c757d);
    font-size: 0.72rem;
}

.generic-writeup-card-context span {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    min-width: 0;
}

.generic-writeup-context-divider {
    color: #adb5bd;
}

.generic-writeup-card-actions {
    display: flex;
    flex: 0 0 auto;
    align-items: center;
    gap: 0.35rem;
}

.generic-writeup-action {
    display: inline-flex;
    width: 34px;
    height: 34px;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.generic-writeup-card-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    padding: 1rem;
}

.generic-writeup-content {
    flex: 1;
}

.generic-writeup-preview {
    display: -webkit-box;
    overflow: hidden;
    margin: 0;
    color: #343a40;
    font-size: 0.84rem;
    line-height: 1.65;
    white-space: pre-line;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 4;
}

.generic-writeup-meta {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
    margin-top: 1rem;
    padding: 0.75rem;
    border: 1px solid var(--cyb-border, #e7eaed);
    border-radius: 0.6rem;
    background: #f8f9fa;
}

.generic-writeup-meta-row {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 0.55rem;
}

.generic-writeup-meta-icon {
    display: flex;
    width: 26px;
    height: 26px;
    flex: 0 0 26px;
    align-items: center;
    justify-content: center;
    border-radius: 0.45rem;
    background: #fff;
    color: #7a8289;
    font-size: 0.72rem;
}

.generic-writeup-meta-text {
    overflow: hidden;
    color: var(--cyb-muted, #6c757d);
    font-size: 0.72rem;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.generic-writeup-meta-text strong {
    color: var(--cyb-text, #212529);
    font-weight: 600;
}

@media (max-width: 575.98px) {
    .generic-writeup-card-header {
        gap: 0.75rem;
    }

    .generic-writeup-card-context {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.2rem;
    }

    .generic-writeup-context-divider {
        display: none !important;
    }
}
</style>