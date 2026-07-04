<template>
    <div class="card">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <div>
                <h3 class="card-title m-0">Generic Writeup List</h3>
                <div class="text-secondary small">
                    Reusable writeups that can be assigned to students later.
                </div>
            </div>

            <button
                type="button"
                class="btn btn-primary btn-sm align-self-start align-self-md-center"
                :disabled="!hasSelectedGroup || currentCount >= 20"
                @click="$emit('create')"
            >
                <i class="bi bi-plus-lg"></i>
                New Writeup
            </button>
        </div>

        <div v-if="!hasSelectedGroup" class="alert alert-info m-3 mb-0">
            Select a school year and college first before creating generic writeups.
        </div>

        <div class="card-body">
            <div class="row g-3">
                <GenericWriteupCard
                    v-for="(writeup, index) in writeups"
                    :key="writeup.id"
                    :writeup="writeup"
                    :number="index + 1"
                    @edit="$emit('edit', $event)"
                    @delete="$emit('delete', $event)"
                />

                <div v-if="writeups.length === 0" class="col-12">
                    <div class="text-center text-secondary py-5">
                        <i class="bi bi-file-earmark-text fs-1 d-block mb-2"></i>
                        No generic writeups found.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import GenericWriteupCard from './GenericWriteupCard.vue';

defineProps({
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
});

defineEmits(['create', 'edit', 'delete']);
</script>