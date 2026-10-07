<script setup lang="ts">
import { computed, ref } from 'vue';

import type {
    College,
    PaginationMeta,
    StudentAccountFilters,
    StudentSearchResponse,
    StudentSummary,
} from '@/types/student-account';

interface Props {
    colleges: College[];
    graduationYears: string[];
}

defineProps<Props>();

const filters = ref<StudentAccountFilters>({
    search: '',
    collegeId: null,
    graduationYear: '',
});

const students = ref<StudentSummary[]>([]);

const pagination = ref<PaginationMeta>({
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0,
    from: null,
    to: null,
});

const hasSearched = ref(false);
const isLoading = ref(false);
const searchError = ref<string | null>(null);

const hasFilters = computed(() => {
    return (
        filters.value.search.trim() !== '' ||
        filters.value.collegeId !== null ||
        filters.value.graduationYear !== ''
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

const searchStudents = async (page = 1): Promise<void> => {
    searchError.value = null;

    if (!hasFilters.value) {
        students.value = [];
        hasSearched.value = false;

        searchError.value =
            'Enter a student ID or name, or select a college or graduation year.';

        return;
    }

    isLoading.value = true;

    try {
        const params = new URLSearchParams();

        const search = filters.value.search.trim();

        if (search !== '') {
            params.set('search', search);
        }

        if (filters.value.collegeId !== null) {
            params.set(
                'college_id',
                filters.value.collegeId.toString(),
            );
        }

        if (filters.value.graduationYear !== '') {
            params.set(
                'year',
                filters.value.graduationYear,
            );
        }

        params.set('page', page.toString());

        const response = await fetch(
            `/student-accounts/search?${params.toString()}`,
            {
                headers: {
                    Accept: 'application/json',
                },
            },
        );

        if (!response.ok) {
            throw new Error('Unable to search student accounts.');
        }

        const result =
            (await response.json()) as StudentSearchResponse;

        students.value = result.data;
        pagination.value = result.meta;
        hasSearched.value = true;
    } catch (error) {
        students.value = [];

        searchError.value =
            error instanceof Error
                ? error.message
                : 'Unable to search student accounts.';
    } finally {
        isLoading.value = false;
    }
};

const goToPage = async (page: number): Promise<void> => {
    if (
        page < 1 ||
        page > pagination.value.last_page ||
        page === pagination.value.current_page
    ) {
        return;
    }

    await searchStudents(page);
};

const resetFilters = (): void => {
    filters.value = {
        search: '',
        collegeId: null,
        graduationYear: '',
    };

    students.value = [];

    pagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: 20,
        total: 0,
        from: null,
        to: null,
    };

    hasSearched.value = false;
    searchError.value = null;
};
</script>





<template>
    <div class="student-module">
        <!-- Search -->
        <div class="cyb-card search-card">
            <div class="cyb-card-header">
                <div>
                    <h2 class="cyb-section-title">
                        Find Student Accounts
                    </h2>

                    <p class="cyb-section-description">
                        Search by student ID or name, or narrow results by college and graduation year.
                    </p>
                </div>

                <span class="cyb-pill cyb-pill-neutral">
                    <i class="bi bi-search"></i>
                    Search
                </span>
            </div>

            <div class="cyb-card-body">
                <form
                    class="row g-3 align-items-end"
                    @submit.prevent="searchStudents(1)"
                >
                    <div class="col-12 col-xl-6">
                        <label
                            for="student-search"
                            class="cyb-form-label"
                        >
                            Search
                        </label>

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>

                            <input
                                id="student-search"
                                v-model.trim="filters.search"
                                type="text"
                                class="form-control cyb-form-control"
                                placeholder="Student ID, first name, or last name"
                            >
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <label
                            for="college-filter"
                            class="cyb-form-label"
                        >
                            College
                        </label>

                        <select
                            id="college-filter"
                            v-model="filters.collegeId"
                            class="form-select cyb-form-control"
                        >
                            <option :value="null">
                                All Colleges
                            </option>

                            <option
                                v-for="college in colleges"
                                :key="college.id"
                                :value="college.id"
                            >
                                {{ college.college_name }}
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <label
                            for="graduation-year-filter"
                            class="cyb-form-label"
                        >
                            Graduation Year
                        </label>

                        <select
                            id="graduation-year-filter"
                            v-model="filters.graduationYear"
                            class="form-select cyb-form-control"
                        >
                            <option value="">
                                All Years
                            </option>

                            <option
                                v-for="year in graduationYears"
                                :key="year"
                                :value="year"
                            >
                                {{ year }}
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
                                    aria-hidden="true"
                                ></span>

                                <i
                                    v-else
                                    class="bi bi-search me-2"
                                ></i>

                                {{
                                    isLoading
                                        ? 'Searching...'
                                        : 'Search Students'
                                }}
                            </button>

                            <button
                                type="button"
                                class="btn btn-light border"
                                :disabled="isLoading"
                                @click="resetFilters"
                            >
                                Clear Filters
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="searchError"
                        class="col-12"
                    >
                        <div class="cyb-notice cyb-notice-warning rounded-3">
                            <i class="bi bi-exclamation-circle"></i>

                            <div>
                                {{ searchError }}
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card module-card mt-4">
            <div
                v-if="isLoading"
                class="empty-state"
            >
                <div
                    class="spinner-border"
                    role="status"
                >
                    <span class="visually-hidden">
                        Loading...
                    </span>
                </div>

                <p class="empty-state-text mt-3">
                    Searching student accounts...
                </p>
            </div>

            <div
                v-else-if="!hasSearched"
                class="empty-state"
            >
                <div class="empty-state-icon">
                    <i class="bi bi-search"></i>
                </div>

                <h2 class="empty-state-title">
                    Search for a student
                </h2>

                <p class="empty-state-text">
                    Use the search field or filters above to find student
                    accounts. No student records are loaded until you search.
                </p>
            </div>

            <div
                v-else-if="students.length === 0"
                class="empty-state"
            >
                <div class="empty-state-icon">
                    <i class="bi bi-people"></i>
                </div>

                <h2 class="empty-state-title">
                    No students found
                </h2>

                <p class="empty-state-text">
                    Try adjusting your search term or filters.
                </p>
            </div>

            <template v-else>
                <div
                    class="card-header results-header"
                >
                    <div>
                        <div class="results-title">
                            Search Results
                        </div>

                        <div class="results-summary">
                            {{ pagination.total }}
                            student{{
                                pagination.total === 1
                                    ? ''
                                    : 's'
                            }}
                            found
                        </div>
                    </div>

                    <div class="results-range">
                        Showing
                        {{ pagination.from }}
                        –
                        {{ pagination.to }}
                    </div>
                </div>

                <div class="table-responsive">
                    <table
                        class="table student-table align-middle mb-0"
                    >
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>College</th>
                                <th>Program</th>
                                <th>Grad. Year</th>
                                <th>Status</th>
                                <th class="text-end">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="student in students"
                                :key="student.id"
                            >
                                <td>
                                    <div class="student-name">
                                        {{ student.full_name }}
                                    </div>

                                    <div class="student-id">
                                        {{ student.university_id }}
                                    </div>
                                </td>

                                <td>
                                    <span class="table-value">
                                        {{ student.college ?? '—' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="table-value">
                                        {{ student.program ?? '—' }}
                                    </div>

                                    <div
                                        v-if="student.major"
                                        class="table-secondary-value"
                                    >
                                        {{ student.major }}
                                    </div>
                                </td>

                                <td>
                                    <span class="table-value">
                                        {{ student.year ?? '—' }}
                                    </span>
                                </td>

                                <td>
                                    <div
                                        class="d-flex flex-wrap gap-2"
                                    >
                                        <span
                                            class="status-pill"
                                            :class="
                                                student.is_subscribe
                                                    ? 'status-success'
                                                    : 'status-neutral'
                                            "
                                        >
                                            {{
                                                student.is_subscribe
                                                    ? 'Subscribed'
                                                    : 'Not Subscribed'
                                            }}
                                        </span>

                                        <span
                                            v-if="student.is_third_party"
                                            class="status-pill status-info"
                                        >
                                            Third-Party
                                        </span>
                                    </div>
                                </td>

                                <td class="text-end">
                                    <a
                                        :href="
                                            `/student-accounts/${student.id}`
                                        "
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="pagination.last_page > 1"
                    class="card-footer pagination-footer"
                >
                    <div class="pagination-summary">
                        Page
                        {{ pagination.current_page }}
                        of
                        {{ pagination.last_page }}
                    </div>

                    <nav
                        aria-label="Student account pagination"
                    >
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
    </div>
</template>

<style scoped>
.student-module {
    font-size: 0.94rem;
}

.page-heading {
    max-width: 760px;
}

.page-title {
    margin: 0;
    font-size: 1.65rem;
    font-weight: 650;
    letter-spacing: -0.02em;
    color: #212529;
}

.page-description {
    margin: 0.4rem 0 0;
    color: #6c757d;
    font-size: 0.94rem;
}

.module-card {
    border: 1px solid #e9ecef;
    border-radius: 0.75rem;
    box-shadow: 0 0.125rem 0.45rem rgba(0, 0, 0, 0.035);
    overflow: hidden;
}

.search-card .card-body {
    padding: 1.25rem;
}

.form-label {
    margin-bottom: 0.45rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: #495057;
}

.form-control,
.form-select,
.input-group-text {
    min-height: 42px;
}

.input-group-text {
    background: #f8f9fa;
    color: #6c757d;
}

.btn {
    min-height: 40px;
    font-weight: 500;
}

.results-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: #fff;
    border-bottom: 1px solid #e9ecef;
}

.results-title {
    font-weight: 600;
    color: #212529;
}

.results-summary,
.results-range,
.pagination-summary {
    margin-top: 0.15rem;
    font-size: 0.82rem;
    color: #6c757d;
}

.student-table thead th {
    padding: 0.85rem 1rem;
    border-bottom-width: 1px;
    background: #f8f9fa;
    color: #6c757d;
    font-size: 0.76rem;
    font-weight: 650;
    letter-spacing: 0.025em;
    text-transform: uppercase;
    white-space: nowrap;
}

.student-table tbody td {
    padding: 1rem;
    border-color: #edf0f2;
    vertical-align: middle;
}

.student-table tbody tr:hover {
    background: #fafbfc;
}

.student-name {
    color: #212529;
    font-weight: 600;
}

.student-id {
    margin-top: 0.2rem;
    color: #6c757d;
    font-size: 0.82rem;
}

.table-value {
    color: #343a40;
}

.table-secondary-value {
    margin-top: 0.2rem;
    color: #6c757d;
    font-size: 0.8rem;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    min-height: 24px;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 600;
    line-height: 1;
}

.status-success {
    background: #e8f5ee;
    color: #197149;
}

.status-neutral {
    background: #f1f3f5;
    color: #687078;
}

.status-info {
    background: #e8f3f8;
    color: #24657b;
}

.empty-state {
    max-width: 520px;
    margin: 0 auto;
    padding: 4.5rem 1.5rem;
    text-align: center;
}

.empty-state-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 52px;
    height: 52px;
    margin: 0 auto;
    border-radius: 50%;
    background: #f1f3f5;
    color: #6c757d;
    font-size: 1.35rem;
}

.empty-state-title {
    margin: 1rem 0 0.35rem;
    font-size: 1rem;
    font-weight: 600;
    color: #343a40;
}

.empty-state-text {
    margin: 0;
    color: #6c757d;
    font-size: 0.88rem;
    line-height: 1.55;
}

.pagination-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.85rem 1.25rem;
    background: #fff;
}

@media (max-width: 767.98px) {
    .results-header,
    .pagination-footer {
        align-items: flex-start;
        flex-direction: column;
    }

    .student-table {
        min-width: 820px;
    }
}
</style>