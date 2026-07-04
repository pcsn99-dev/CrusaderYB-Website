<template>
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title m-0">Filters</h3>
        </div>

        <div class="card-body">
            <form method="GET" action="/writeups/generic" class="d-grid gap-3">
                <div>
                    <label class="form-label">School Year</label>
                    <select
                        name="year"
                        class="form-select"
                        :value="year"
                        @input="$emit('update:year', $event.target.value)"
                    >
                        <option value="">All Years</option>
                        <option v-for="item in years" :key="item" :value="item">
                            {{ item }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="form-label">College</label>
                    <select
                        name="college_id"
                        class="form-select"
                        :value="collegeId"
                        @input="$emit('update:collegeId', $event.target.value)"
                    >
                        <option value="">All Colleges</option>
                        <option
                            v-for="college in colleges"
                            :key="college.id"
                            :value="String(college.id)"
                        >
                            {{ college.college_name }}
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i>
                    Apply Filters
                </button>

                <a href="/writeups/generic" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                    Clear Filters
                </a>
            </form>
        </div>
    </div>
</template>

<script setup>
defineProps({
    years: Array,
    colleges: Array,
    year: String,
    collegeId: String,
});

defineEmits(['update:year', 'update:collegeId']);
</script>