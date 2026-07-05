<template>
    <div class="overflow-hidden rounded-2xl border border-[var(--cyb-border)] bg-white shadow-sm">
        <form ref="filterForm" method="GET" :action="action" class="p-4">

            <!-- Header -->
            <div class="mb-4 flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--cyb-primary-soft)] text-[var(--cyb-primary)]">
                    <i class="bi bi-funnel"></i>
                </span>

                <div class="min-w-0">
                    <h3 class="mb-0 text-sm font-bold uppercase tracking-wide text-[var(--cyb-primary)]">
                        Filters
                    </h3>

                    <p class="mb-0 mt-1 text-xs leading-5 text-[var(--cyb-muted)]">
                        Select a school year or college to update the list automatically.
                    </p>
                </div>
            </div>

            <!-- Fields -->
            <div class="space-y-3">
                <div>
                    <label
                        for="generic-writeup-year"
                        class="mb-1 block text-xs font-bold uppercase tracking-wide text-[var(--cyb-muted)]"
                    >
                        School Year
                    </label>

                    <div class="relative">
                        <select
                            id="generic-writeup-year"
                            name="year"
                            v-model="localYear"
                            @change="applyFilters"
                            class="block w-full appearance-none rounded-xl border border-[var(--cyb-border)] bg-white px-3 py-2.5 pr-9 text-sm font-semibold text-[var(--cyb-text)] shadow-sm transition focus:border-[var(--cyb-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)]/20"
                        >
                            <option value="">All Years</option>

                            <option
                                v-for="item in years"
                                :key="item"
                                :value="String(item)"
                            >
                                {{ item }}
                            </option>
                        </select>

                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[var(--cyb-muted)]">
                            <i class="bi bi-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                <div>
                    <label
                        for="generic-writeup-college"
                        class="mb-1 block text-xs font-bold uppercase tracking-wide text-[var(--cyb-muted)]"
                    >
                        College
                    </label>

                    <div class="relative">
                        <select
                            id="generic-writeup-college"
                            name="college_id"
                            v-model="localCollegeId"
                            @change="applyFilters"
                            class="block w-full appearance-none rounded-xl border border-[var(--cyb-border)] bg-white px-3 py-2.5 pr-9 text-sm font-semibold text-[var(--cyb-text)] shadow-sm transition focus:border-[var(--cyb-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)]/20"
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

                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[var(--cyb-muted)]">
                            <i class="bi bi-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Active Filters -->
            <div
                v-if="hasActiveFilters"
                class="mt-4 rounded-xl border border-[var(--cyb-border)] bg-[var(--cyb-primary-soft)]/60 px-3 py-3"
            >
                <p class="mb-2 text-xs font-bold uppercase tracking-wide text-[var(--cyb-muted)]">
                    Active Filters
                </p>

                <div class="flex flex-wrap gap-2">
                    <span
                        v-if="localYear"
                        class="inline-flex items-center gap-1.5 rounded-full border border-blue-100 bg-white px-2.5 py-1 text-xs font-semibold text-[var(--cyb-primary)]"
                    >
                        <i class="bi bi-calendar3"></i>
                        SY {{ localYear }}
                    </span>

                    <span
                        v-if="selectedCollegeName"
                        class="inline-flex max-w-full items-center gap-1.5 rounded-full border border-pink-100 bg-white px-2.5 py-1 text-xs font-semibold text-pink-700"
                    >
                        <i class="bi bi-building shrink-0"></i>
                        <span class="truncate">
                            {{ selectedCollegeName }}
                        </span>
                    </span>
                </div>

                <p class="mb-0 mt-2 text-xs leading-5 text-[var(--cyb-muted)]">
                    Choose “All Years” or “All Colleges” to remove a filter.
                </p>
            </div>

            <div
                v-else
                class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-xs text-[var(--cyb-muted)]"
            >
                Showing all generic writeups.
            </div>

        </form>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    years: {
        type: Array,
        default: () => [],
    },
    colleges: {
        type: Array,
        default: () => [],
    },
    year: {
        type: String,
        default: '',
    },
    collegeId: {
        type: String,
        default: '',
    },
    action: {
        type: String,
        default: '/writeups/generic',
    },
});

const emit = defineEmits(['update:year', 'update:collegeId']);

const localYear = ref(props.year || '');
const localCollegeId = ref(props.collegeId || '');

watch(
    () => props.year,
    (value) => {
        localYear.value = value || '';
    }
);

watch(
    () => props.collegeId,
    (value) => {
        localCollegeId.value = value || '';
    }
);

const hasActiveFilters = computed(() => {
    return Boolean(localYear.value || localCollegeId.value);
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

function applyFilters() {
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
    const targetUrl = queryString ? `${props.action}?${queryString}` : props.action;

    window.location.assign(targetUrl);
}
</script>