<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

interface College {
    id: number;
    college_name: string;
}

interface ActiveYear {
    id: number;
    year: string;
    theme: string | null;
    status: boolean;
    subscription_start: string | null;
    subscription_end: string | null;
}

interface Pictorial {
    id: number;
    year: string;
    date: string;
    date_label: string;
    start_time: string;
    end_time: string;
    time_label: string;

    college: College | null;
    allowed_colleges: College[];

    is_delayed: boolean;

    no_of_slots: number;
    reserved_slots: number;
    remaining_slots: number;
    is_full: boolean;
}

interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

interface Props {
    activeYear: ActiveYear | null;
    colleges: College[];
}

const props = defineProps<Props>();

const filters = ref({
    collegeId: '',
    type: '',
    dateFrom: '',
    dateTo: '',
    availability: '',
});

const pictorials = ref<Pictorial[]>([]);

const pagination = ref<PaginationMeta>({
    current_page: 1,
    last_page: 1,
    per_page: 25,
    total: 0,
    from: null,
    to: null,
});

const isLoading = ref(false);
const loadError = ref<string | null>(null);

const hasFilters = computed(() => {
    return (
        filters.value.collegeId !== '' ||
        filters.value.type !== '' ||
        filters.value.dateFrom !== '' ||
        filters.value.dateTo !== '' ||
        filters.value.availability !== ''
    );
});

const pageNumbers = computed(() => {
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;

    const start = Math.max(1, current - 2);
    const end = Math.min(last, current + 2);

    const pages: number[] = [];

    for (let page = start; page <= end; page++) {
        pages.push(page);
    }

    return pages;
});

const loadSchedules = async (page = 1): Promise<void> => {
    if (!props.activeYear) {
        pictorials.value = [];
        return;
    }

    isLoading.value = true;
    loadError.value = null;

    try {
        const params = new URLSearchParams();

        if (filters.value.collegeId !== '') {
            params.set(
                'college_id',
                filters.value.collegeId,
            );
        }

        if (filters.value.type !== '') {
            params.set('type', filters.value.type);
        }

        if (filters.value.dateFrom !== '') {
            params.set(
                'date_from',
                filters.value.dateFrom,
            );
        }

        if (filters.value.dateTo !== '') {
            params.set(
                'date_to',
                filters.value.dateTo,
            );
        }

        if (filters.value.availability !== '') {
            params.set(
                'availability',
                filters.value.availability,
            );
        }

        params.set('page', page.toString());

        const response = await fetch(
            `/settings/pictorial-schedules/search?${params.toString()}`,
            {
                headers: {
                    Accept: 'application/json',
                },
            },
        );

        if (!response.ok) {
            throw new Error(
                'Unable to load pictorial schedules.',
            );
        }

        const result = await response.json();

        pictorials.value = result.data;
        pagination.value = result.meta;
    } catch (error) {
        pictorials.value = [];

        loadError.value =
            error instanceof Error
                ? error.message
                : 'Unable to load pictorial schedules.';
    } finally {
        isLoading.value = false;
    }
};

const clearFilters = async (): Promise<void> => {
    filters.value = {
        collegeId: '',
        type: '',
        dateFrom: '',
        dateTo: '',
        availability: '',
    };

    await loadSchedules(1);
};

const goToPage = async (page: number): Promise<void> => {
    if (
        page < 1 ||
        page > pagination.value.last_page ||
        page === pagination.value.current_page
    ) {
        return;
    }

    await loadSchedules(page);
};

onMounted(() => {
    loadSchedules();
});
</script>

<template>
    <div class="cyb-stack">
        <!-- Active year -->
        <div
            v-if="activeYear"
            class="cyb-card"
        >
            <div class="cyb-card-body">
                <div
                    class="d-flex flex-wrap align-items-center justify-content-between gap-3"
                >
                    <div>
                        <div class="cyb-section-description">
                            Active CYB Year
                        </div>

                        <div class="active-year-value">
                            {{ activeYear.year }}
                        </div>

                        <div
                            v-if="activeYear.theme"
                            class="text-muted small mt-1"
                        >
                            {{ activeYear.theme }}
                        </div>
                    </div>

                    <span class="cyb-pill cyb-pill-success">
                        <span
                            class="cyb-status-dot cyb-status-dot-success"
                        ></span>

                        Active
                    </span>
                </div>
            </div>
        </div>

        <!-- No active year -->
        <div
            v-else
            class="cyb-notice cyb-notice-warning rounded-3"
        >
            <i class="bi bi-exclamation-triangle"></i>

            <div>
                <strong>No active CYB year.</strong>

                <div class="mt-1">
                    Activate a year under Settings → Years before managing
                    pictorial schedules.
                </div>
            </div>
        </div>

        <template v-if="activeYear">
            <!-- Filters -->
            <div class="cyb-card">
                <div class="cyb-card-header">
                    <div>
                        <h2 class="cyb-section-title">
                            Filter Schedules
                        </h2>

                        <p class="cyb-section-description">
                            Showing schedules only for CYB {{ activeYear.year }}.
                        </p>
                    </div>

                    <span
                        v-if="hasFilters"
                        class="cyb-pill cyb-pill-primary"
                    >
                        Filters Applied
                    </span>
                </div>

                <div class="cyb-card-body">
                    <form
                        class="row g-3 align-items-end"
                        @submit.prevent="loadSchedules(1)"
                    >
                        <div class="col-12 col-md-6 col-xl-3">
                            <label class="cyb-form-label">
                                College
                            </label>

                            <select
                                v-model="filters.collegeId"
                                class="form-select cyb-form-control"
                            >
                                <option value="">
                                    All Colleges
                                </option>

                                <option
                                    v-for="college in colleges"
                                    :key="college.id"
                                    :value="college.id.toString()"
                                >
                                    {{ college.college_name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-2">
                            <label class="cyb-form-label">
                                Schedule Type
                            </label>

                            <select
                                v-model="filters.type"
                                class="form-select cyb-form-control"
                            >
                                <option value="">
                                    All Types
                                </option>

                                <option value="regular">
                                    Regular
                                </option>

                                <option value="delayed">
                                    Delayed
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-2">
                            <label class="cyb-form-label">
                                Date From
                            </label>

                            <input
                                v-model="filters.dateFrom"
                                type="date"
                                class="form-control cyb-form-control"
                            >
                        </div>

                        <div class="col-12 col-md-6 col-xl-2">
                            <label class="cyb-form-label">
                                Date To
                            </label>

                            <input
                                v-model="filters.dateTo"
                                type="date"
                                class="form-control cyb-form-control"
                            >
                        </div>

                        <div class="col-12 col-md-6 col-xl-3">
                            <label class="cyb-form-label">
                                Availability
                            </label>

                            <select
                                v-model="filters.availability"
                                class="form-select cyb-form-control"
                            >
                                <option value="">
                                    All
                                </option>

                                <option value="available">
                                    Available
                                </option>

                                <option value="full">
                                    Full
                                </option>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-2">
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    :disabled="isLoading"
                                >
                                    <span
                                        v-if="isLoading"
                                        class="spinner-border spinner-border-sm me-2"
                                    ></span>

                                    <i
                                        v-else
                                        class="bi bi-funnel me-2"
                                    ></i>

                                    Apply Filters
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-light border"
                                    :disabled="isLoading || !hasFilters"
                                    @click="clearFilters"
                                >
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Error -->
            <div
                v-if="loadError"
                class="cyb-notice cyb-notice-danger rounded-3"
            >
                <i class="bi bi-exclamation-circle"></i>

                {{ loadError }}
            </div>

            <!-- Results -->
            <div class="cyb-card">
                <div class="cyb-card-header">
                    <div>
                        <h2 class="cyb-section-title">
                            Pictorial Schedules
                        </h2>

                        <p class="cyb-section-description">
                            {{ pagination.total }}
                            schedule{{ pagination.total === 1 ? '' : 's' }}
                            found.
                        </p>
                    </div>

                    <div
                        v-if="pagination.from !== null"
                        class="text-muted small"
                    >
                        Showing
                        {{ pagination.from }}
                        –
                        {{ pagination.to }}
                    </div>
                </div>

                <div
                    v-if="isLoading"
                    class="cyb-empty-state"
                >
                    <div class="spinner-border"></div>

                    <p class="cyb-empty-state-text mt-3">
                        Loading pictorial schedules...
                    </p>
                </div>

                <div
                    v-else-if="pictorials.length === 0"
                    class="cyb-empty-state"
                >
                    <div class="cyb-empty-state-icon">
                        <i class="bi bi-calendar3"></i>
                    </div>

                    <h3 class="cyb-empty-state-title">
                        No schedules found
                    </h3>

                    <p class="cyb-empty-state-text">
                        No pictorial schedules match the current filters for
                        CYB {{ activeYear.year }}.
                    </p>
                </div>

                <template v-else>
                    <div class="table-responsive">
                        <table class="table cyb-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>College</th>
                                    <th>Type</th>
                                    <th>Reservations</th>
                                    <th>Availability</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="pictorial in pictorials"
                                    :key="pictorial.id"
                                >
                                    <td>
                                        <div class="cyb-table-primary">
                                            {{ pictorial.date_label }}
                                        </div>
                                    </td>

                                    <td>
                                        {{ pictorial.time_label }}
                                    </td>

                                    <td>
                                        <template v-if="pictorial.is_delayed">
                                            <div class="cyb-table-primary">
                                                Delayed Pictorial
                                            </div>

                                            <div class="cyb-table-secondary">
                                                {{
                                                    pictorial.allowed_colleges
                                                        .map(
                                                            (college) =>
                                                                college.college_name,
                                                        )
                                                        .join(', ') ||
                                                    'No colleges selected'
                                                }}
                                            </div>
                                        </template>

                                        <template v-else>
                                            {{
                                                pictorial.college
                                                    ?.college_name ?? '—'
                                            }}
                                        </template>
                                    </td>

                                    <td>
                                        <span
                                            v-if="pictorial.is_delayed"
                                            class="cyb-pill cyb-pill-warning"
                                        >
                                            Delayed
                                        </span>

                                        <span
                                            v-else
                                            class="cyb-pill cyb-pill-neutral"
                                        >
                                            Regular
                                        </span>
                                    </td>

                                    <td>
                                        {{ pictorial.reserved_slots }}
                                        /
                                        {{ pictorial.no_of_slots }}
                                    </td>

                                    <td>
                                        <span
                                            v-if="pictorial.is_full"
                                            class="cyb-pill cyb-pill-danger"
                                        >
                                            Full
                                        </span>

                                        <span
                                            v-else
                                            class="cyb-pill cyb-pill-success"
                                        >
                                            {{ pictorial.remaining_slots }}
                                            remaining
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        v-if="pagination.last_page > 1"
                        class="cyb-pagination-footer"
                    >
                        <div class="cyb-pagination-summary">
                            Page {{ pagination.current_page }}
                            of {{ pagination.last_page }}
                        </div>

                        <nav aria-label="Pictorial schedule pagination">
                            <ul class="pagination mb-0">
                                <li
                                    class="page-item"
                                    :class="{
                                        disabled:
                                            pagination.current_page === 1,
                                    }"
                                >
                                    <button
                                        type="button"
                                        class="page-link"
                                        :disabled="
                                            pagination.current_page === 1
                                        "
                                        @click="
                                            goToPage(
                                                pagination.current_page - 1,
                                            )
                                        "
                                    >
                                        Previous
                                    </button>
                                </li>

                                <li
                                    v-for="page in pageNumbers"
                                    :key="page"
                                    class="page-item"
                                    :class="{
                                        active:
                                            page ===
                                            pagination.current_page,
                                    }"
                                >
                                    <button
                                        type="button"
                                        class="page-link"
                                        @click="goToPage(page)"
                                    >
                                        {{ page }}
                                    </button>
                                </li>

                                <li
                                    class="page-item"
                                    :class="{
                                        disabled:
                                            pagination.current_page ===
                                            pagination.last_page,
                                    }"
                                >
                                    <button
                                        type="button"
                                        class="page-link"
                                        :disabled="
                                            pagination.current_page ===
                                            pagination.last_page
                                        "
                                        @click="
                                            goToPage(
                                                pagination.current_page + 1,
                                            )
                                        "
                                    >
                                        Next
                                    </button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </template>
            </div>
        </template>
    </div>
</template>

<style scoped>
.active-year-value {
    margin-top: 0.1rem;
    color: #212529;
    font-size: 1.55rem;
    font-weight: 650;
    line-height: 1.15;
}
</style>