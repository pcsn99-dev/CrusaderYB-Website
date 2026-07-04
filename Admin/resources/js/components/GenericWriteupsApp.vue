<template>
    <div class="card">
        <div class="card-body">
            <h5 class="mb-2">Vue is working</h5>
            <p class="text-secondary mb-0">
                Loaded {{ initialWriteups.length }} generic writeups.
            </p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    initialWriteups: Array,
    years: Array,
    colleges: Array,
    selectedYear: String,
    selectedCollegeId: String,
    currentCount: Number,
});
</script>