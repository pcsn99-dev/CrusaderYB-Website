<script setup lang="ts">
import {
    computed,
    ref,
} from 'vue';

interface YearOption {
    id: number;
    year: string;
    status: boolean | number;
}

interface Props {
    years: YearOption[];
    canManage: boolean;
}

interface PenaltyTypeOption {
    value: string;
    label: string;
}

interface PenaltyRecord {
    id: number;

    penalty_type: string;
    penalty_type_label: string;

    amount: number;

    source:
        | 'attendance'
        | 'other';

    source_label: string;

    student: {
        id: number | null;
        name: string | null;
        university_id: string | null;
        slmis_id: number | null;
        year: string | null;
        college: string | null;
        program: string | null;
    };

    reservation: {
        id: number;
        pictorial_id: number | null;
        date: string | null;
        date_label: string | null;
        time_label: string | null;
        year: string | null;
    } | null;
}

interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;

    total_records: number;
    total_amount: number;

    year: string | null;
}

const props = defineProps<Props>();

const selectedYear = ref('');

const searchQuery = ref('');
const penaltyType = ref('');
const source = ref('');

const penalties =
    ref<PenaltyRecord[]>([]);

const penaltyTypes =
    ref<PenaltyTypeOption[]>([]);

const loading = ref(false);

const errorMessage =
    ref<string | null>(null);

const pagination =
    ref<PaginationMeta>({
        current_page: 1,
        last_page: 1,
        per_page: 25,
        total: 0,
        from: null,
        to: null,

        total_records: 0,
        total_amount: 0,

        year: null,
    });

const hasFilters = computed(() => {
    return (
        searchQuery.value.trim() !== '' ||
        penaltyType.value !== '' ||
        source.value !== ''
    );
});

const selectedYearData = computed(() => {
    return props.years.find(
        (year) =>
            String(year.year) ===
            selectedYear.value,
    );
});

const pageNumbers = computed(() => {
    const current =
        pagination.value.current_page;

    const last =
        pagination.value.last_page;

    const start =
        Math.max(
            1,
            current - 2,
        );

    const end =
        Math.min(
            last,
            current + 2,
        );

    const pages: number[] = [];

    for (
        let page = start;
        page <= end;
        page++
    ) {
        pages.push(page);
    }

    return pages;
});

const formatAmount = (
    amount: number,
): string => {
    return new Intl.NumberFormat(
        'en-PH',
        {
            style: 'currency',
            currency: 'PHP',
        },
    ).format(amount);
};

const resetPenaltyData = (): void => {
    penalties.value = [];
    penaltyTypes.value = [];

    pagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: 25,
        total: 0,
        from: null,
        to: null,

        total_records: 0,
        total_amount: 0,

        year: null,
    };
};

const handleYearChange =
    async (): Promise<void> => {
        searchQuery.value = '';
        penaltyType.value = '';
        source.value = '';

        errorMessage.value = null;

        if (!selectedYear.value) {
            resetPenaltyData();
            return;
        }

        await loadPenalties(1);
    };

const loadPenalties = async (
    page = 1,
): Promise<void> => {
    if (!selectedYear.value) {
        resetPenaltyData();
        return;
    }

    loading.value = true;
    errorMessage.value = null;

    try {
        const params =
            new URLSearchParams();

        params.set(
            'year',
            selectedYear.value,
        );

        params.set(
            'page',
            page.toString(),
        );

        params.set(
            'per_page',
            pagination.value
                .per_page
                .toString(),
        );

        const search =
            searchQuery.value.trim();

        if (search !== '') {
            params.set(
                'q',
                search,
            );
        }

        if (
            penaltyType.value !== ''
        ) {
            params.set(
                'penalty_type',
                penaltyType.value,
            );
        }

        if (source.value !== '') {
            params.set(
                'source',
                source.value,
            );
        }

        const response = await fetch(
            `/penalties/search?${params.toString()}`,
            {
                headers: {
                    Accept:
                        'application/json',
                },
            },
        );

        const result =
            await response.json();

        if (!response.ok) {
            throw new Error(
                result.message ??
                    'Unable to load penalties.',
            );
        }

        penalties.value =
            result.data ?? [];

        pagination.value = {
            current_page:
                result.meta
                    ?.current_page ?? 1,

            last_page:
                result.meta
                    ?.last_page ?? 1,

            per_page:
                result.meta
                    ?.per_page ?? 25,

            total:
                result.meta
                    ?.total ?? 0,

            from:
                result.meta
                    ?.from ?? null,

            to:
                result.meta
                    ?.to ?? null,

            total_records:
                result.meta
                    ?.total_records ?? 0,

            total_amount:
                Number(
                    result.meta
                        ?.total_amount ?? 0,
                ),

            year:
                result.meta
                    ?.year ??
                selectedYear.value,
        };

        penaltyTypes.value =
            result.filters
                ?.penalty_types ?? [];
    } catch (error) {
        penalties.value = [];

        errorMessage.value =
            error instanceof Error
                ? error.message
                : 'Unable to load penalties.';
    } finally {
        loading.value = false;
    }
};

const applyFilters =
    async (): Promise<void> => {
        await loadPenalties(1);
    };

const clearFilters =
    async (): Promise<void> => {
        searchQuery.value = '';
        penaltyType.value = '';
        source.value = '';

        await loadPenalties(1);
    };

const goToPage = async (
    page: number,
): Promise<void> => {
    if (
        page < 1 ||
        page >
            pagination.value.last_page ||
        page ===
            pagination.value.current_page
    ) {
        return;
    }

    await loadPenalties(page);
};

const changePerPage =
    async (): Promise<void> => {
        await loadPenalties(1);
    };
</script>

<template>
    <div class="cyb-stack">
        <!-- Year Selection -->
        <div class="cyb-card">
            <div class="cyb-card-header">
                <div>
                    <h2 class="cyb-section-title">
                        Select CYB Year
                    </h2>

                    <p class="cyb-section-description">
                        Choose a year to view its student penalty records.
                    </p>
                </div>
            </div>

            <div class="cyb-card-body">
                <div class="penalty-year-layout">
                    <div class="penalty-year-field">
                        <label class="cyb-form-label">
                            CYB Year
                        </label>

                        <select
                            v-model="selectedYear"
                            class="form-select cyb-form-control penalty-year-select"
                            :disabled="loading"
                            @change="handleYearChange"
                        >
                            <option value="">
                                Select a year
                            </option>

                            <option
                                v-for="year in years"
                                :key="year.id"
                                :value="String(year.year)"
                            >
                                {{ year.year }}
                                {{
                                    Boolean(year.status)
                                        ? ' — Active'
                                        : ''
                                }}
                            </option>
                        </select>

                        <div class="cyb-form-help">
                            Penalty records are grouped according to the student's CYB year.
                        </div>
                    </div>

                    <div
                        v-if="selectedYear"
                        class="penalty-year-summary"
                    >
                        <div class="penalty-summary-box">
                            <div class="penalty-summary-icon">
                                <i class="bi bi-list-ul"></i>
                            </div>

                            <div>
                                <span>
                                    Penalty Records
                                </span>

                                <strong>
                                    {{
                                        pagination.total_records
                                    }}
                                </strong>
                            </div>
                        </div>

                        <div class="penalty-summary-box">
                            <div class="penalty-summary-icon">
                                <i class="bi bi-cash-stack"></i>
                            </div>

                            <div>
                                <span>
                                    Total Penalties
                                </span>

                                <strong class="penalty-total-amount">
                                    {{
                                        formatAmount(
                                            pagination.total_amount,
                                        )
                                    }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="
                        selectedYear &&
                        selectedYearData &&
                        Boolean(
                            selectedYearData.status,
                        )
                    "
                    class="penalty-active-year-note"
                >
                    <i class="bi bi-check-circle-fill"></i>

                    <span>
                        {{ selectedYear }} is the current active CYB year.
                    </span>
                </div>
            </div>
        </div>

        <!-- Nothing selected yet -->
        <div
            v-if="!selectedYear"
            class="cyb-card"
        >
            <div class="cyb-empty-state">
                <div class="cyb-empty-state-icon">
                    <i class="bi bi-calendar3"></i>
                </div>

                <h3 class="cyb-empty-state-title">
                    Select a CYB year
                </h3>

                <p class="cyb-empty-state-text">
                    Choose a year above to load its penalty records.
                </p>
            </div>
        </div>

        <template v-else>
            <!-- Filters -->
            <div class="cyb-card">
                <div class="cyb-card-header">
                    <div>
                        <h2 class="cyb-section-title">
                            Find Penalties
                        </h2>

                        <p class="cyb-section-description">
                            Search and filter penalty records for CYB
                            {{ selectedYear }}.
                        </p>
                    </div>

                    <button
                        v-if="canManage"
                        type="button"
                        class="btn btn-primary"
                        disabled
                    >
                        <i class="bi bi-plus-lg me-2"></i>

                        Add Penalty
                    </button>
                </div>

                <div class="cyb-card-body">
                    <form
                        class="penalty-filter-grid"
                        @submit.prevent="applyFilters"
                    >
                        <div class="penalty-search">
                            <label class="cyb-form-label">
                                Student
                            </label>

                            <div class="search-input-wrapper">
                                <i class="bi bi-search"></i>

                                <input
                                    v-model="searchQuery"
                                    type="search"
                                    class="form-control cyb-form-control"
                                    placeholder="Name, University ID, SLMIS ID..."
                                >
                            </div>
                        </div>

                        <div>
                            <label class="cyb-form-label">
                                Penalty Type
                            </label>

                            <select
                                v-model="penaltyType"
                                class="form-select cyb-form-control"
                            >
                                <option value="">
                                    All Types
                                </option>

                                <option
                                    v-for="type in penaltyTypes"
                                    :key="type.value"
                                    :value="type.value"
                                >
                                    {{ type.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="cyb-form-label">
                                Source
                            </label>

                            <select
                                v-model="source"
                                class="form-select cyb-form-control"
                            >
                                <option value="">
                                    All Sources
                                </option>

                                <option value="attendance">
                                    Attendance
                                </option>

                                <option value="other">
                                    Manual / Other
                                </option>
                            </select>
                        </div>

                        <div class="penalty-filter-actions">
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="loading"
                            >
                                <span
                                    v-if="loading"
                                    class="spinner-border spinner-border-sm me-2"
                                ></span>

                                <i
                                    v-else
                                    class="bi bi-funnel me-2"
                                ></i>

                                Apply
                            </button>

                            <button
                                type="button"
                                class="btn btn-light border"
                                :disabled="
                                    loading ||
                                    !hasFilters
                                "
                                @click="clearFilters"
                            >
                                Clear
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Error -->
            <div
                v-if="errorMessage"
                class="cyb-notice cyb-notice-danger rounded-3"
            >
                <i class="bi bi-exclamation-circle"></i>

                <div>
                    {{ errorMessage }}
                </div>
            </div>

            <!-- Results -->
            <div class="cyb-card">
                <div class="cyb-card-header penalty-results-header">
                    <div>
                        <h2 class="cyb-section-title">
                            Penalty Records
                        </h2>

                        <p class="cyb-section-description">
                            <template v-if="hasFilters">
                                {{
                                    pagination.total_records
                                }}
                                matching
                                record{{
                                    pagination.total_records === 1
                                        ? ''
                                        : 's'
                                }}
                                for CYB
                                {{ selectedYear }}.
                            </template>

                            <template v-else>
                                {{
                                    pagination.total_records
                                }}
                                penalty
                                record{{
                                    pagination.total_records === 1
                                        ? ''
                                        : 's'
                                }}
                                for CYB
                                {{ selectedYear }}.
                            </template>
                        </p>
                    </div>

                    <div class="penalty-results-tools">
                        <div
                            v-if="
                                pagination.from !== null
                            "
                            class="penalty-showing-text"
                        >
                            Showing
                            {{ pagination.from }}
                            –
                            {{ pagination.to }}
                            of
                            {{ pagination.total }}
                        </div>

                        <select
                            v-model.number="pagination.per_page"
                            class="form-select form-select-sm penalty-per-page"
                            :disabled="loading"
                            @change="changePerPage"
                        >
                            <option :value="10">
                                10 per page
                            </option>

                            <option :value="25">
                                25 per page
                            </option>

                            <option :value="50">
                                50 per page
                            </option>

                            <option :value="100">
                                100 per page
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Loading -->
                <div
                    v-if="loading"
                    class="cyb-empty-state"
                >
                    <div class="spinner-border"></div>

                    <p class="cyb-empty-state-text mt-3">
                        Loading penalties...
                    </p>
                </div>

                <!-- Empty -->
                <div
                    v-else-if="
                        penalties.length === 0
                    "
                    class="cyb-empty-state"
                >
                    <div class="cyb-empty-state-icon">
                        <i class="bi bi-exclamation-diamond"></i>
                    </div>

                    <h3 class="cyb-empty-state-title">
                        No penalties found
                    </h3>

                    <p class="cyb-empty-state-text">
                        <template v-if="hasFilters">
                            No penalty records match the current search or filters.
                        </template>

                        <template v-else>
                            There are no penalty records for CYB
                            {{ selectedYear }}.
                        </template>
                    </p>
                </div>

                <template v-else>
                    <!-- Desktop -->
                    <div class="penalty-desktop-table table-responsive">
                        <table class="table cyb-table align-middle">
                            <thead>
                                <tr>
                                    <th>
                                        Student
                                    </th>

                                    <th>
                                        Penalty
                                    </th>

                                    <th>
                                        Source
                                    </th>

                                    <th>
                                        Reservation
                                    </th>

                                    <th class="text-end">
                                        Amount
                                    </th>

                                    <th
                                        v-if="canManage"
                                        class="text-end"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="penalty in penalties"
                                    :key="penalty.id"
                                >
                                    <td>
                                        <div class="penalty-student-name">
                                            {{
                                                penalty.student.name
                                                ?? 'Unknown student'
                                            }}
                                        </div>

                                        <div class="penalty-student-id">
                                            {{
                                                penalty.student.university_id
                                                ?? 'No University ID'
                                            }}
                                        </div>

                                        <div class="penalty-student-program">
                                            {{
                                                penalty.student.college
                                                ?? '—'
                                            }}

                                            <template
                                                v-if="
                                                    penalty.student.program
                                                "
                                            >
                                                ·
                                                {{
                                                    penalty.student.program
                                                }}
                                            </template>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="penalty-type-label">
                                            {{
                                                penalty.penalty_type_label
                                            }}
                                        </span>
                                    </td>

                                    <td>
                                        <span
                                            class="penalty-source-badge"
                                            :class="
                                                penalty.source ===
                                                'attendance'
                                                    ? 'penalty-source-attendance'
                                                    : 'penalty-source-other'
                                            "
                                        >
                                            {{
                                                penalty.source_label
                                            }}
                                        </span>
                                    </td>

                                    <td>
                                        <template
                                            v-if="penalty.reservation"
                                        >
                                            <div class="penalty-reservation-date">
                                                {{
                                                    penalty.reservation.date_label
                                                    ?? '—'
                                                }}
                                            </div>

                                            <div class="penalty-reservation-time">
                                                {{
                                                    penalty.reservation.time_label
                                                    ?? '—'
                                                }}
                                            </div>
                                        </template>

                                        <span
                                            v-else
                                            class="penalty-not-linked"
                                        >
                                            Not linked
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <strong class="penalty-amount">
                                            {{
                                                formatAmount(
                                                    penalty.amount,
                                                )
                                            }}
                                        </strong>
                                    </td>

                                    <td
                                        v-if="canManage"
                                        class="text-end"
                                    >
                                        <div class="penalty-row-actions">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-light border"
                                                title="Edit penalty"
                                                disabled
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete penalty"
                                                disabled
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile -->
                    <div class="penalty-mobile-list">
                        <article
                            v-for="penalty in penalties"
                            :key="penalty.id"
                            class="penalty-mobile-card"
                        >
                            <div class="penalty-mobile-header">
                                <div>
                                    <div class="penalty-student-name">
                                        {{
                                            penalty.student.name
                                            ?? 'Unknown student'
                                        }}
                                    </div>

                                    <div class="penalty-student-id">
                                        {{
                                            penalty.student.university_id
                                            ?? 'No University ID'
                                        }}
                                    </div>
                                </div>

                                <strong class="penalty-amount">
                                    {{
                                        formatAmount(
                                            penalty.amount,
                                        )
                                    }}
                                </strong>
                            </div>

                            <div class="penalty-mobile-tags">
                                <span class="penalty-type-chip">
                                    {{
                                        penalty.penalty_type_label
                                    }}
                                </span>

                                <span
                                    class="penalty-source-badge"
                                    :class="
                                        penalty.source ===
                                        'attendance'
                                            ? 'penalty-source-attendance'
                                            : 'penalty-source-other'
                                    "
                                >
                                    {{
                                        penalty.source_label
                                    }}
                                </span>
                            </div>

                            <div class="penalty-mobile-body">
                                <div>
                                    <span>
                                        College / Program
                                    </span>

                                    <strong>
                                        {{
                                            penalty.student.college
                                            ?? '—'
                                        }}

                                        <template
                                            v-if="
                                                penalty.student.program
                                            "
                                        >
                                            ·
                                            {{
                                                penalty.student.program
                                            }}
                                        </template>
                                    </strong>
                                </div>

                                <div>
                                    <span>
                                        SLMIS ID
                                    </span>

                                    <strong>
                                        {{
                                            penalty.student.slmis_id
                                            ?? '—'
                                        }}
                                    </strong>
                                </div>

                                <div
                                    v-if="penalty.reservation"
                                >
                                    <span>
                                        Reservation
                                    </span>

                                    <strong>
                                        {{
                                            penalty.reservation.date_label
                                            ?? '—'
                                        }}

                                        <template
                                            v-if="
                                                penalty.reservation.time_label
                                            "
                                        >
                                            ·
                                            {{
                                                penalty.reservation.time_label
                                            }}
                                        </template>
                                    </strong>
                                </div>

                                <div v-else>
                                    <span>
                                        Reservation
                                    </span>

                                    <strong>
                                        Not linked
                                    </strong>
                                </div>
                            </div>

                            <div
                                v-if="canManage"
                                class="penalty-mobile-actions"
                            >
                                <button
                                    type="button"
                                    class="btn btn-light border"
                                    disabled
                                >
                                    <i class="bi bi-pencil me-1"></i>

                                    Edit
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-outline-danger"
                                    disabled
                                >
                                    <i class="bi bi-trash me-1"></i>

                                    Delete
                                </button>
                            </div>
                        </article>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="
                            pagination.last_page > 1
                        "
                        class="penalty-pagination"
                    >
                        <div class="penalty-pagination-summary">
                            Page
                            <strong>
                                {{
                                    pagination.current_page
                                }}
                            </strong>
                            of
                            <strong>
                                {{
                                    pagination.last_page
                                }}
                            </strong>
                        </div>

                        <nav aria-label="Penalty pagination">
                            <ul class="pagination mb-0">
                                <li
                                    class="page-item"
                                    :class="{
                                        disabled:
                                            pagination.current_page ===
                                            1,
                                    }"
                                >
                                    <button
                                        type="button"
                                        class="page-link"
                                        :disabled="
                                            pagination.current_page ===
                                            1
                                        "
                                        @click="
                                            goToPage(
                                                pagination.current_page -
                                                1,
                                            )
                                        "
                                    >
                                        <span class="d-none d-sm-inline">
                                            Previous
                                        </span>

                                        <i class="bi bi-chevron-left d-sm-none"></i>
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
                                        @click="
                                            goToPage(page)
                                        "
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
                                                pagination.current_page +
                                                1,
                                            )
                                        "
                                    >
                                        <span class="d-none d-sm-inline">
                                            Next
                                        </span>

                                        <i class="bi bi-chevron-right d-sm-none"></i>
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
.penalty-year-layout {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1.5rem;
}

.penalty-year-field {
    flex: 1 1 auto;
}

.penalty-year-select {
    width: 100%;
    max-width: 300px;
}

.penalty-year-summary {
    display: grid;
    grid-template-columns:
        repeat(2, minmax(180px, 1fr));
    gap: 0.75rem;
}

.penalty-summary-box {
    display: flex;
    align-items: center;
    gap: 0.8rem;

    min-height: 76px;
    padding: 0.8rem 1rem;

    border: 1px solid #e3e7eb;
    border-radius: 0.7rem;

    background: #f8f9fa;
}

.penalty-summary-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 38px;
    height: 38px;
    flex: 0 0 38px;

    border-radius: 0.6rem;

    background: #fff;

    border: 1px solid #e2e6ea;

    color: #6c757d;
}

.penalty-summary-box > div:last-child {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.penalty-summary-box span {
    color: #6c757d;
    font-size: 0.76rem;
}

.penalty-summary-box strong {
    color: #212529;
    font-size: 1.15rem;
}

.penalty-summary-box .penalty-total-amount {
    color: #b02a37;
}

.penalty-active-year-note {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;

    margin-top: 1rem;

    color: #198754;

    font-size: 0.8rem;
    font-weight: 600;
}

.penalty-filter-grid {
    display: grid;

    grid-template-columns:
        minmax(280px, 2fr)
        minmax(180px, 1fr)
        minmax(180px, 1fr)
        auto;

    gap: 1rem;

    align-items: end;
}

.penalty-search {
    min-width: 0;
}

.search-input-wrapper {
    position: relative;
}

.search-input-wrapper > i {
    position: absolute;

    top: 50%;
    left: 0.85rem;

    transform:
        translateY(-50%);

    color: #6c757d;

    pointer-events: none;
}

.search-input-wrapper .form-control {
    padding-left: 2.4rem;
}

.penalty-filter-actions {
    display: flex;
    gap: 0.5rem;
}

.penalty-results-header {
    gap: 1rem;
}

.penalty-results-tools {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.penalty-showing-text {
    color: #6c757d;

    font-size: 0.8rem;

    white-space: nowrap;
}

.penalty-per-page {
    width: auto;
    min-width: 120px;
}

.penalty-student-name {
    color: #212529;

    font-size: 0.92rem;
    font-weight: 650;
}

.penalty-student-id {
    margin-top: 0.15rem;

    color: #6c757d;

    font-size: 0.8rem;
}

.penalty-student-program {
    margin-top: 0.15rem;

    color: #6c757d;

    font-size: 0.74rem;
}

.penalty-type-label {
    color: #343a40;

    font-weight: 600;
}

.penalty-source-badge {
    display: inline-flex;
    align-items: center;

    padding: 0.3rem 0.6rem;

    border-radius: 999px;

    font-size: 0.74rem;
    font-weight: 650;

    white-space: nowrap;
}

.penalty-source-attendance {
    background: #e7f1ff;
    color: #0a58ca;
}

.penalty-source-other {
    background: #e9ecef;
    color: #495057;
}

.penalty-reservation-date {
    color: #343a40;

    font-size: 0.84rem;
    font-weight: 600;
}

.penalty-reservation-time {
    margin-top: 0.1rem;

    color: #6c757d;

    font-size: 0.78rem;
}

.penalty-not-linked {
    color: #adb5bd;

    font-size: 0.82rem;
}

.penalty-amount {
    color: #b02a37;

    white-space: nowrap;
}

.penalty-row-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.35rem;
}

.penalty-row-actions .btn {
    transition:
        transform 0.15s ease,
        box-shadow 0.15s ease,
        background-color 0.15s ease;
}

.penalty-row-actions .btn:hover:not(:disabled) {
    transform:
        translateY(-1px);

    box-shadow:
        0 2px 6px
        rgba(0, 0, 0, 0.08);
}

.penalty-mobile-list {
    display: none;
}

.penalty-mobile-card {
    padding: 1rem;

    border-bottom:
        1px solid #e9ecef;
}

.penalty-mobile-card:last-child {
    border-bottom: 0;
}

.penalty-mobile-header {
    display: flex;

    align-items: flex-start;
    justify-content: space-between;

    gap: 1rem;
}

.penalty-mobile-tags {
    display: flex;
    flex-wrap: wrap;

    gap: 0.4rem;

    margin-top: 0.8rem;
}

.penalty-type-chip {
    display: inline-flex;
    align-items: center;

    padding: 0.3rem 0.6rem;

    border-radius: 999px;

    background: #f8f9fa;

    border: 1px solid #dee2e6;

    color: #495057;

    font-size: 0.74rem;
    font-weight: 600;
}

.penalty-mobile-body {
    display: grid;

    gap: 0.7rem;

    margin-top: 0.8rem;

    padding: 0.8rem;

    border-radius: 0.6rem;

    background: #f8f9fa;
}

.penalty-mobile-body > div {
    display: flex;
    flex-direction: column;

    gap: 0.15rem;
}

.penalty-mobile-body span {
    color: #6c757d;

    font-size: 0.72rem;
}

.penalty-mobile-body strong {
    color: #343a40;

    font-size: 0.84rem;
}

.penalty-mobile-actions {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 0.5rem;

    margin-top: 0.9rem;
}

.penalty-pagination {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 1rem;

    padding: 1rem;

    border-top:
        1px solid #e9ecef;
}

.penalty-pagination-summary {
    color: #6c757d;

    font-size: 0.82rem;
}

@media (max-width: 1199.98px) {
    .penalty-filter-grid {
        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );
    }

    .penalty-filter-actions {
        grid-column:
            1 / -1;
    }
}

@media (max-width: 991.98px) {
    .penalty-year-layout {
        align-items: stretch;
        flex-direction: column;
    }

    .penalty-year-select {
        max-width: none;
    }

    .penalty-year-summary {
        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );
    }

    .penalty-results-header {
        align-items: flex-start;
    }

    .penalty-results-tools {
        align-items: flex-end;
        flex-direction: column;
    }
}

@media (max-width: 767.98px) {
    .penalty-filter-grid {
        grid-template-columns:
            1fr;
    }

    .penalty-filter-actions {
        grid-column: auto;
    }

    .penalty-filter-actions .btn {
        flex: 1 1 0;
    }

    .penalty-results-header {
        align-items: stretch;
        flex-direction: column;
    }

    .penalty-results-tools {
        align-items: stretch;
        flex-direction: row;
        justify-content: space-between;
    }

    .penalty-desktop-table {
        display: none;
    }

    .penalty-mobile-list {
        display: block;
    }

    .penalty-pagination {
        align-items: stretch;
        flex-direction: column;
    }

    .penalty-pagination-summary {
        text-align: center;
    }

    .penalty-pagination nav {
        display: flex;
        justify-content: center;
    }
}

@media (max-width: 520px) {
    .penalty-year-summary {
        grid-template-columns: 1fr;
    }

    .penalty-results-tools {
        align-items: stretch;
        flex-direction: column;
    }

    .penalty-per-page {
        width: 100%;
    }

    .penalty-mobile-header {
        align-items: flex-start;
    }

    .penalty-mobile-actions {
        grid-template-columns: 1fr 1fr;
    }
}
</style>