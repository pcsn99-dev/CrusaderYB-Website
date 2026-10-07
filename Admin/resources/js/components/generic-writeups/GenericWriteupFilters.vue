<template>
    <div class="cyb-card generic-writeup-filter-card">
        <form
            method="GET"
            :action="action"
            class="generic-writeup-filter-body"
        >
            <!-- Header -->
            <div class="generic-writeup-filter-header">
                <div class="generic-writeup-filter-icon">
                    <i class="bi bi-funnel"></i>
                </div>

                <div>
                    <h3 class="generic-writeup-filter-title">
                        Filters
                    </h3>

                    <p class="generic-writeup-filter-description">
                        Select a school year or college to update the list automatically.
                    </p>
                </div>
            </div>

            <!-- Fields -->
            <div class="generic-writeup-filter-fields">
                <div>
                    <label
                        for="generic-writeup-year"
                        class="cyb-form-label"
                    >
                        School Year
                    </label>

                    <select
                        id="generic-writeup-year"
                        name="year"
                        v-model="localYear"
                        class="form-select cyb-form-control"
                        @change="applyFilters"
                    >
                        <option value="">
                            All Years
                        </option>

                        <option
                            v-for="item in years"
                            :key="item"
                            :value="String(item)"
                        >
                            {{ item }}
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        for="generic-writeup-college"
                        class="cyb-form-label"
                    >
                        College
                    </label>

                    <select
                        id="generic-writeup-college"
                        name="college_id"
                        v-model="localCollegeId"
                        class="form-select cyb-form-control"
                        @change="applyFilters"
                    >
                        <option value="">
                            All Colleges
                        </option>

                        <option
                            v-for="college in colleges"
                            :key="college.id"
                            :value="String(college.id)"
                        >
                            {{ college.college_name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Active Filters -->
            <div
                v-if="hasActiveFilters"
                class="generic-writeup-active-filters"
            >
                <div class="generic-writeup-active-filters-label">
                    Active Filters
                </div>

                <div class="generic-writeup-active-filter-list">
                    <span
                        v-if="localYear"
                        class="cyb-pill cyb-pill-primary"
                    >
                        <i class="bi bi-calendar3"></i>
                        SY {{ localYear }}
                    </span>

                    <span
                        v-if="selectedCollegeName"
                        class="cyb-pill cyb-pill-info generic-writeup-college-pill"
                        :title="selectedCollegeName"
                    >
                        <i class="bi bi-building"></i>

                        <span>
                            {{ selectedCollegeName }}
                        </span>
                    </span>
                </div>

                <p class="generic-writeup-active-filters-help">
                    Choose “All Years” or “All Colleges” to remove a filter.
                </p>
            </div>

            <div
                v-else
                class="generic-writeup-filter-empty"
            >
                <i class="bi bi-info-circle"></i>

                <span>
                    Showing all generic writeups.
                </span>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';

interface College {
    id: number | string;
    college_name: string;
}

const props = withDefaults(
    defineProps<{
        years?: Array<string | number>;
        colleges?: College[];
        year?: string;
        collegeId?: string;
        action?: string;
    }>(),
    {
        years: () => [],
        colleges: () => [],
        year: '',
        collegeId: '',
        action: '/writeups/generic',
    },
);

const emit = defineEmits<{
    'update:year': [value: string];
    'update:collegeId': [value: string];
}>();

const localYear = ref(props.year || '');
const localCollegeId = ref(props.collegeId || '');

watch(
    () => props.year,
    (value) => {
        localYear.value = value || '';
    },
);

watch(
    () => props.collegeId,
    (value) => {
        localCollegeId.value = value || '';
    },
);

const hasActiveFilters = computed(() => {
    return Boolean(
        localYear.value ||
        localCollegeId.value,
    );
});

const selectedCollegeName = computed(() => {
    if (!localCollegeId.value) {
        return '';
    }

    const selected = props.colleges.find((college) => {
        return String(college.id) === String(localCollegeId.value);
    });

    return selected?.college_name || '';
});

function applyFilters(): void {
    emit('update:year', localYear.value);
    emit('update:collegeId', localCollegeId.value);

    const params = new URLSearchParams();

    if (localYear.value) {
        params.set('year', localYear.value);
    }

    if (localCollegeId.value) {
        params.set('college_id', localCollegeId.value);
    }

    const queryString = params.toString();

    const targetUrl = queryString
        ? `${props.action}?${queryString}`
        : props.action;

    window.location.assign(targetUrl);
}
</script>

<style scoped>
.generic-writeup-filter-card {
    height: 100%;
}

.generic-writeup-filter-body {
    padding: 1rem;
}

.generic-writeup-filter-header {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.generic-writeup-filter-icon {
    display: flex;
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    align-items: center;
    justify-content: center;
    border-radius: 0.6rem;
    background: #eef3f8;
    color: #495057;
    font-size: 0.9rem;
}

.generic-writeup-filter-title {
    margin: 0;
    color: var(--cyb-text, #212529);
    font-size: 0.9rem;
    font-weight: 650;
}

.generic-writeup-filter-description {
    margin: 0.2rem 0 0;
    color: var(--cyb-muted, #6c757d);
    font-size: 0.74rem;
    line-height: 1.45;
}

.generic-writeup-filter-fields {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
}

.generic-writeup-active-filters {
    margin-top: 1rem;
    padding: 0.8rem;
    border: 1px solid var(--cyb-border, #e7eaed);
    border-radius: 0.6rem;
    background: #f8f9fa;
}

.generic-writeup-active-filters-label {
    margin-bottom: 0.5rem;
    color: var(--cyb-muted, #6c757d);
    font-size: 0.68rem;
    font-weight: 650;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.generic-writeup-active-filter-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.generic-writeup-college-pill {
    max-width: 100%;
}

.generic-writeup-college-pill span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.generic-writeup-active-filters-help {
    margin: 0.5rem 0 0;
    color: var(--cyb-muted, #6c757d);
    font-size: 0.7rem;
    line-height: 1.4;
}

.generic-writeup-filter-empty {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1rem;
    padding: 0.7rem 0.75rem;
    border: 1px solid var(--cyb-border, #e7eaed);
    border-radius: 0.6rem;
    background: #f8f9fa;
    color: var(--cyb-muted, #6c757d);
    font-size: 0.72rem;
}
</style>