<script setup lang="ts">
import { ref } from 'vue';
import type {
    College,
    StudentAccountFilters,
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

const hasSearched = ref(false);

const searchStudents = (): void => {
    hasSearched.value = true;

    console.log('Student filters:', filters.value);
};

const resetFilters = (): void => {
    filters.value = {
        search: '',
        collegeId: null,
        graduationYear: '',
    };

    hasSearched.value = false;
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
                    @submit.prevent="searchStudents"
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
                            >
                                <i class="bi bi-search me-1"></i>

                                Search Students
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                @click="resetFilters"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body py-5 text-center">
                <template v-if="!hasSearched">
                    <i class="bi bi-search fs-1 text-muted"></i>

                    <h2 class="h5 mt-3">
                        Find a student
                    </h2>

                    <p class="text-muted mb-0">
                        Search by student ID or name, or filter by college
                        and graduation year.
                    </p>
                </template>

                <template v-else>
                    <i class="bi bi-tools fs-1 text-muted"></i>

                    <h2 class="h5 mt-3">
                        Student search is ready for backend integration
                    </h2>

                    <p class="text-muted mb-0">
                        The next development step will connect these filters
                        to the student database.
                    </p>
                </template>
            </div>
        </div>
    </div>
</template>