<template>
    <div class="card">
        <div class="card-body">
            <div class="text-secondary small">Current Count</div>

            <div class="d-flex align-items-end gap-2">
                <div class="display-6 fw-semibold mb-0">{{ currentCount }}</div>
                <div class="text-secondary mb-2">/ 20</div>
            </div>

            <div v-if="hasSelectedGroup && currentCount >= 20" class="alert alert-warning small mb-0 mt-3">
                Maximum generic writeups reached for this year and college.
            </div>

            <div v-else-if="hasSelectedGroup" class="text-secondary small mt-2">
                {{ 20 - currentCount }} more writeups can still be created.
            </div>

            <div v-else class="text-secondary small mt-2">
                Select a year and college to manage the 20-writeup limit properly.
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    currentCount: {
        type: Number,
        required: true,
    },
    hasSelectedGroup: {
        type: Boolean,
        required: true,
    },
});
</script>