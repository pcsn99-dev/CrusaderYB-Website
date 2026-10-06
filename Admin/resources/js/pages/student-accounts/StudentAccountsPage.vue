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
    <div class="container-fluid">
        <div class="mb-4">
            <h1 class="h3 mb-1">
                Student Accounts
            </h1>

            <p class="text-muted mb-0">
                Search and view CYB student account information.
            </p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form
                    class="row g-3"
                    @submit.prevent="searchStudents(1)"
                >
                    <div class="col-12 col-lg-6">
                        <label
                            for="student-search"
                            class="form-label"
                        >
                            Search Student
                        </label>

                        <input
                            id="student-search"
                            v-model.trim="filters.search"
                            type="text"
                            class="form-control"
                            placeholder="Student ID, first name, or last name"
                        >
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <label
                            for="college-filter"
                            class="form-label"
                        >
                            College
                        </label>

                        <select
                            id="college-filter"
                            v-model="filters.collegeId"
                            class="form-select"
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

                    <div class="col-12 col-md-6 col-lg-3">
                        <label
                            for="graduation-year-filter"
                            class="form-label"
                        >
                            Graduation Year
                        </label>

                        <select
                            id="graduation-year-filter"
                            v-model="filters.graduationYear"
                            class="form-select"
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
                        <div class="d-flex gap-2">
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="isLoading"
                            >
                                <span
                                    v-if="isLoading"
                                    class="spinner-border spinner-border-sm me-1"
                                    aria-hidden="true"
                                ></span>

                                <i
                                    v-else
                                    class="bi bi-search me-1"
                                ></i>

                                {{ isLoading ? 'Searching...' : 'Search Students' }}
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                :disabled="isLoading"
                                @click="resetFilters"
                            >
                                Clear
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="searchError"
                        class="col-12"
                    >
                        <div class="alert alert-danger mb-0">
                            {{ searchError }}
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div
                v-if="isLoading"
                class="card-body py-5 text-center"
            >
                <div
                    class="spinner-border"
                    role="status"
                >
                    <span class="visually-hidden">
                        Loading...
                    </span>
                </div>

                <p class="text-muted mt-3 mb-0">
                    Searching student accounts...
                </p>
            </div>

            <div
                v-else-if="!hasSearched"
                class="card-body py-5 text-center"
            >
                <i class="bi bi-search fs-1 text-muted"></i>

                <h2 class="h5 mt-3">
                    Find a student
                </h2>

                <p class="text-muted mb-0">
                    Search by student ID or name, or filter by college
                    and graduation year.
                </p>
            </div>

            <div
                v-else-if="students.length === 0"
                class="card-body py-5 text-center"
            >
                <i class="bi bi-person-x fs-1 text-muted"></i>

                <h2 class="h5 mt-3">
                    No students found
                </h2>

                <p class="text-muted mb-0">
                    Try changing your search or filters.
                </p>
            </div>

            <template v-else>
                <div class="card-header bg-white">
                    <div
                        class="d-flex flex-column flex-md-row
                               align-items-md-center
                               justify-content-between gap-2"
                    >
                        <div>
                            <strong>
                                {{ pagination.total }}
                            </strong>

                            student{{ pagination.total === 1 ? '' : 's' }}
                            found
                        </div>

                        <small class="text-muted">
                            Showing
                            {{ pagination.from }}
                            –
                            {{ pagination.to }}
                        </small>
                    </div>
                </div>

                <div class="table-responsive">
                    <table
                        class="table table-hover align-middle mb-0"
                    >
                        <thead class="table-light">
                            <tr>
                                <th>Student ID</th>
                                <th>Student</th>
                                <th>College</th>
                                <th>Program</th>
                                <th>Grad. Year</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="student in students"
                                :key="student.id"
                            >
                                <td class="text-nowrap">
                                    {{ student.university_id }}
                                </td>

                                <td>
                                    <strong>
                                        {{ student.full_name }}
                                    </strong>
                                </td>

                                <td>
                                    {{ student.college ?? '—' }}
                                </td>

                                <td>
                                    <div>
                                        {{ student.program ?? '—' }}
                                    </div>

                                    <small
                                        v-if="student.major"
                                        class="text-muted"
                                    >
                                        {{ student.major }}
                                    </small>
                                </td>

                                <td class="text-nowrap">
                                    {{ student.year ?? '—' }}
                                </td>

                                <td>
                                    <span
                                        v-if="student.is_subscribe"
                                        class="badge text-bg-success"
                                    >
                                        Subscribed
                                    </span>

                                    <span
                                        v-else
                                        class="badge text-bg-secondary"
                                    >
                                        Not Subscribed
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="pagination.last_page > 1"
                    class="card-footer bg-white"
                >
                    <nav
                        aria-label="Student account pagination"
                    >
                        <ul
                            class="pagination justify-content-end mb-0"
                        >
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