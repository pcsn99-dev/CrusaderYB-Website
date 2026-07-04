<template>
    <div class="col-12 col-md-6">
        <div class="card h-100 border">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div>
                        <div class="fw-semibold">
                            Generic Writeup #{{ number }}
                        </div>

                        <div class="text-secondary small">
                            {{ writeup.year }}
                            ·
                            {{ writeup.college?.college_name || 'No college' }}
                        </div>
                    </div>

                    <span
                        class="badge"
                        :class="writeup.is_active ? 'text-bg-success' : 'text-bg-secondary'"
                    >
                        {{ writeup.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <p class="mb-3 flex-grow-1">
                    {{ writeup.content }}
                </p>

                <div class="text-secondary small mb-3">
                    Created by {{ writeup.creator?.name || 'Unknown' }}
                    <br>
                    Updated {{ writeup.updated_at || 'N/A' }}
                </div>

                <div class="d-flex flex-wrap gap-2 mt-auto">
                    <button
                        type="button"
                        class="btn btn-outline-primary btn-sm"
                        @click="$emit('edit', writeup)"
                    >
                        <i class="bi bi-pencil"></i>
                        Edit
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-danger btn-sm"
                        @click="$emit('delete', writeup)"
                    >
                        <i class="bi bi-trash"></i>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
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
</script>