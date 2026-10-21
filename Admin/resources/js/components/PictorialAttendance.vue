<script setup lang="ts">
import {
    computed,
    ref,
    watch,
} from 'vue';

interface ActiveYear {
    id: number;
    year: string;
    theme: string | null;
}

interface AttendancePenalty {
    id: number;
    type: string;
    amount: number;
}

interface AttendanceReservation {
    id: number;

    student: {
        id: number | null;
        name: string | null;
        university_id: string | null;
        slmis_id: number | null;
        email: string | null;
        contact_number: string | null;
        college: string | null;
        program: string | null;
        major: string | null;
    };

    schedule: {
        id: number | null;
        date: string | null;
        date_label: string | null;
        start_time: string | null;
        end_time: string | null;
        time_label: string | null;
    };

    attendance: {
        status:
            | 'pending'
            | 'present'
            | 'late'
            | 'absent'
            | 'cancelled';

        checked_in_at: string | null;
        checked_in_time: string | null;
        checked_in_time_value: string | null;
        minutes_late: number | null;
    };

    penalty: AttendancePenalty | null;
}

interface Summary {
    total: number;
    pending: number;
    present: number;
    late: number;
    absent: number;
}

interface Props {
    activeYear: ActiveYear | null;
    canManage: boolean;
}

interface AttendanceScheduleGroup {
    scheduleId: number | null;
    startTime: string | null;
    endTime: string | null;
    timeLabel: string | null;
    reservations: AttendanceReservation[];
}

type AttendanceFilter =
    | 'all'
    | 'pending'
    | 'present'
    | 'late'
    | 'absent';

const props = defineProps<Props>();

const selectedDate = ref('');
const reservations = ref<AttendanceReservation[]>([]);

const loading = ref(false);
const submitting = ref(false);

const errorMessage = ref<string | null>(null);
const attendanceError = ref<string | null>(null);
const successMessage = ref<string | null>(null);

const activeFilter = ref<AttendanceFilter>('all');
const searchQuery = ref('');

const summary = ref<Summary>({
    total: 0,
    pending: 0,
    present: 0,
    late: 0,
    absent: 0,
});

const arrivalModalOpen = ref(false);
const selectedReservation =
    ref<AttendanceReservation | null>(null);

const arrivalTime = ref('');

const currentPage = ref(1);
const groupsPerPage = 4;

let successTimer: number | null = null;

const showSuccess = (
    message: string,
): void => {
    successMessage.value = message;

    if (successTimer !== null) {
        window.clearTimeout(successTimer);
    }

    successTimer = window.setTimeout(() => {
        successMessage.value = null;
        successTimer = null;
    }, 4000);
};

const filteredReservations = computed(() => {
    const query =
        searchQuery.value
            .trim()
            .toLowerCase();

    return reservations.value.filter(
        (reservation) => {
            const matchesStatus =
                activeFilter.value === 'all' ||
                reservation.attendance.status ===
                    activeFilter.value;

            if (!matchesStatus) {
                return false;
            }

            if (!query) {
                return true;
            }

            const searchableText = [
                reservation.student.name,
                reservation.student.university_id,
                reservation.student.slmis_id,
                reservation.student.email,
                reservation.student.college,
                reservation.student.program,
                reservation.student.major,
            ]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            return searchableText.includes(
                query,
            );
        },
    );
});

const groupedReservations = computed<
    AttendanceScheduleGroup[]
>(() => {
    const groups = new Map<
        string,
        AttendanceScheduleGroup
    >();

    for (
        const reservation of
        filteredReservations.value
    ) {
        const key = String(
            reservation.schedule.id ??
                `${reservation.schedule.start_time}-${reservation.schedule.end_time}`,
        );

        if (!groups.has(key)) {
            groups.set(key, {
                scheduleId:
                    reservation.schedule.id,

                startTime:
                    reservation.schedule.start_time,

                endTime:
                    reservation.schedule.end_time,

                timeLabel:
                    reservation.schedule.time_label,

                reservations: [],
            });
        }

        groups
            .get(key)!
            .reservations.push(
                reservation,
            );
    }

    return Array
        .from(groups.values())
        .sort((a, b) =>
            (a.startTime ?? '')
                .localeCompare(
                    b.startTime ?? '',
                ),
        );
});

const totalPages = computed(() =>
    Math.max(
        1,
        Math.ceil(
            groupedReservations.value.length /
                groupsPerPage,
        ),
    ),
);

const paginatedGroups = computed(() => {
    const start =
        (currentPage.value - 1) *
        groupsPerPage;

    return groupedReservations.value.slice(
        start,
        start + groupsPerPage,
    );
});

const getGroupPendingCount = (
    group: AttendanceScheduleGroup,
): number => {
    return group.reservations.filter(
        (reservation) =>
            reservation.attendance.status ===
            'pending',
    ).length;
};

const getGroupCompletedCount = (
    group: AttendanceScheduleGroup,
): number => {
    return group.reservations.filter(
        (reservation) =>
            reservation.attendance.status !==
            'pending',
    ).length;
};

const isGroupComplete = (
    group: AttendanceScheduleGroup,
): boolean => {
    return (
        group.reservations.length > 0 &&
        getGroupPendingCount(group) === 0
    );
};

const loadReservations =
    async (
        resetFilter = true,
    ): Promise<void> => {
        if (!selectedDate.value) {
            reservations.value = [];

            summary.value = {
                total: 0,
                pending: 0,
                present: 0,
                late: 0,
                absent: 0,
            };

            return;
        }

        loading.value = true;
        errorMessage.value = null;

        try {
            const params =
                new URLSearchParams({
                    date: selectedDate.value,
                });

            const response = await fetch(
                `/pictorial-attendance/reservations?${params.toString()}`,
                {
                    headers: {
                        Accept: 'application/json',
                    },
                },
            );

            const result =
                await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message ??
                        'Unable to load reservations.',
                );
            }

            reservations.value =
                result.data ?? [];

            summary.value =
                result.meta?.summary ?? {
                    total: 0,
                    pending: 0,
                    present: 0,
                    late: 0,
                    absent: 0,
                };

            if (resetFilter) {
                activeFilter.value = 'all';
                searchQuery.value = '';
            }

            currentPage.value = 1;
        } catch (error) {
            reservations.value = [];

            errorMessage.value =
                error instanceof Error
                    ? error.message
                    : 'Unable to load reservations.';
        } finally {
            loading.value = false;
        }
    };

const getCurrentTimeValue = (): string => {
    const now = new Date();

    const hours = String(
        now.getHours(),
    ).padStart(2, '0');

    const minutes = String(
        now.getMinutes(),
    ).padStart(2, '0');

    return `${hours}:${minutes}`;
};

const openPresentModal = (
    reservation: AttendanceReservation,
): void => {
    selectedReservation.value =
        reservation;

    arrivalTime.value =
        reservation.attendance
            .checked_in_time_value ??
        '';

    attendanceError.value = null;
    arrivalModalOpen.value = true;
};

const closePresentModal = (): void => {
    if (submitting.value) {
        return;
    }

    arrivalModalOpen.value = false;
    selectedReservation.value = null;
    arrivalTime.value = '';
    attendanceError.value = null;
};

const useCurrentTime = (): void => {
    arrivalTime.value =
        getCurrentTimeValue();
};

const updateAttendance =
    async (
        reservation: AttendanceReservation,
        action:
            | 'present'
            | 'absent'
            | 'revert',
        time?: string,
    ): Promise<boolean> => {
        submitting.value = true;
        attendanceError.value = null;
        errorMessage.value = null;

        try {
            const csrfToken = document
                .querySelector<HTMLMetaElement>(
                    'meta[name="csrf-token"]',
                )
                ?.getAttribute('content');

            const payload: {
                action:
                    | 'present'
                    | 'absent'
                    | 'revert';
                arrival_time?: string;
            } = {
                action,
            };

            if (
                action === 'present' &&
                time
            ) {
                payload.arrival_time = time;
            }

            const response = await fetch(
                `/pictorial-attendance/${reservation.id}`,
                {
                    method: 'PATCH',

                    headers: {
                        Accept: 'application/json',
                        'Content-Type':
                            'application/json',
                        'X-CSRF-TOKEN':
                            csrfToken ?? '',
                    },

                    credentials: 'same-origin',

                    body: JSON.stringify(
                        payload,
                    ),
                },
            );

            const result =
                await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message ??
                        'Unable to update attendance.',
                );
            }

            showSuccess(
                result.message ??
                    'Attendance updated successfully.',
            );

            await loadReservations(false);

            return true;
        } catch (error) {
            const message =
                error instanceof Error
                    ? error.message
                    : 'Unable to update attendance.';

            attendanceError.value =
                message;

            errorMessage.value =
                message;

            return false;
        } finally {
            submitting.value = false;
        }
    };

const confirmPresent =
    async (): Promise<void> => {
        if (
            !selectedReservation.value ||
            !arrivalTime.value
        ) {
            attendanceError.value =
                'Select an arrival time.';

            return;
        }

        const success =
            await updateAttendance(
                selectedReservation.value,
                'present',
                arrivalTime.value,
            );

        if (success) {
            closePresentModal();
        }
    };

const markAbsent = async (
    reservation: AttendanceReservation,
): Promise<void> => {
    if (
        !confirm(
            `Mark ${reservation.student.name ?? 'this student'} as absent?`,
        )
    ) {
        return;
    }

    await updateAttendance(
        reservation,
        'absent',
    );
};

const revertAttendance = async (
    reservation: AttendanceReservation,
): Promise<void> => {
    if (
        !confirm(
            `Revert attendance for ${reservation.student.name ?? 'this student'}?`,
        )
    ) {
        return;
    }

    await updateAttendance(
        reservation,
        'revert',
    );
};

const statusLabel = (
    status: AttendanceReservation['attendance']['status'],
): string => {
    return {
        pending: 'Pending',
        present: 'Present',
        late: 'Late',
        absent: 'Absent',
        cancelled: 'Cancelled',
    }[status];
};

const previousPage = (): void => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const nextPage = (): void => {
    if (
        currentPage.value <
        totalPages.value
    ) {
        currentPage.value++;
    }
};

watch(
    [
        searchQuery,
        activeFilter,
    ],
    () => {
        currentPage.value = 1;
    },
);
</script>

<template>
    <div class="cyb-stack">
        <!-- Success toast -->
        <div
            v-if="successMessage"
            class="attendance-toast"
            role="status"
        >
            <div class="attendance-toast-icon">
                <i class="bi bi-check-lg"></i>
            </div>

            <div class="flex-grow-1">
                <div class="fw-semibold">
                    Success
                </div>

                <div class="attendance-toast-message">
                    {{ successMessage }}
                </div>
            </div>

            <button
                type="button"
                class="btn-close"
                aria-label="Dismiss"
                @click="successMessage = null"
            ></button>
        </div>

        <!-- Header / date -->
        <div
            v-if="activeYear"
            class="cyb-card"
        >
            <div class="cyb-card-body">
                <div class="attendance-header">
                    <div class="attendance-date-section">
                        <label class="cyb-form-label">
                            Attendance Date
                        </label>

                        <input
                            v-model="selectedDate"
                            type="date"
                            class="form-control cyb-form-control attendance-date-input"
                            @change="loadReservations"
                        >

                        <div class="cyb-form-help">
                            Select the pictorial date to load all active reservations.
                        </div>
                    </div>

                    <div class="attendance-year-card">
                        <span class="attendance-year-label">
                            Active CYB Year
                        </span>

                        <strong>
                            {{ activeYear.year }}
                        </strong>
                    </div>
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
                No active CYB year is configured.
            </div>
        </div>

        <!-- No selected date -->
        <div
            v-if="
                activeYear &&
                !selectedDate
            "
            class="cyb-card"
        >
            <div class="cyb-empty-state">
                <div class="cyb-empty-state-icon">
                    <i class="bi bi-calendar-event"></i>
                </div>

                <h3 class="cyb-empty-state-title">
                    Select a pictorial date
                </h3>

                <p class="cyb-empty-state-text">
                    Reservations for the selected day will appear here.
                </p>
            </div>
        </div>

        <template
            v-if="
                activeYear &&
                selectedDate
            "
        >
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

            <!-- Summary -->
            <div class="attendance-summary-grid">
                <div class="attendance-summary-card">
                    <span>Total</span>

                    <strong>
                        {{ summary.total }}
                    </strong>
                </div>

                <div class="attendance-summary-card">
                    <span>Pending</span>

                    <strong>
                        {{ summary.pending }}
                    </strong>
                </div>

                <div class="attendance-summary-card">
                    <span>Present</span>

                    <strong>
                        {{ summary.present }}
                    </strong>
                </div>

                <div class="attendance-summary-card">
                    <span>Late</span>

                    <strong>
                        {{ summary.late }}
                    </strong>
                </div>

                <div class="attendance-summary-card">
                    <span>Absent</span>

                    <strong>
                        {{ summary.absent }}
                    </strong>
                </div>
            </div>

            <!-- Search / filters -->
            <div class="cyb-card">
                <div class="cyb-card-body">
                    <div class="attendance-toolbar">
                        <div class="attendance-search">
                            <i class="bi bi-search"></i>

                            <input
                                v-model="searchQuery"
                                type="search"
                                class="form-control"
                                placeholder="Search student name, ID, email..."
                            >
                        </div>

                        <div class="attendance-match-count">
                            {{
                                filteredReservations.length
                            }}
                            result{{
                                filteredReservations.length === 1
                                    ? ''
                                    : 's'
                            }}
                        </div>
                    </div>

                    <div class="attendance-filter-row">
                        <button
                            type="button"
                            class="attendance-filter-pill"
                            :class="{
                                active:
                                    activeFilter === 'all',
                            }"
                            @click="
                                activeFilter = 'all'
                            "
                        >
                            All
                            <span>
                                {{ summary.total }}
                            </span>
                        </button>

                        <button
                            type="button"
                            class="attendance-filter-pill"
                            :class="{
                                active:
                                    activeFilter === 'pending',
                            }"
                            @click="
                                activeFilter = 'pending'
                            "
                        >
                            Pending
                            <span>
                                {{ summary.pending }}
                            </span>
                        </button>

                        <button
                            type="button"
                            class="attendance-filter-pill"
                            :class="{
                                active:
                                    activeFilter === 'present',
                            }"
                            @click="
                                activeFilter = 'present'
                            "
                        >
                            Present
                            <span>
                                {{ summary.present }}
                            </span>
                        </button>

                        <button
                            type="button"
                            class="attendance-filter-pill"
                            :class="{
                                active:
                                    activeFilter === 'late',
                            }"
                            @click="
                                activeFilter = 'late'
                            "
                        >
                            Late
                            <span>
                                {{ summary.late }}
                            </span>
                        </button>

                        <button
                            type="button"
                            class="attendance-filter-pill"
                            :class="{
                                active:
                                    activeFilter === 'absent',
                            }"
                            @click="
                                activeFilter = 'absent'
                            "
                        >
                            Absent
                            <span>
                                {{ summary.absent }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div
                v-if="loading"
                class="cyb-card"
            >
                <div class="cyb-empty-state">
                    <div class="spinner-border"></div>

                    <p class="cyb-empty-state-text mt-3">
                        Loading reservations...
                    </p>
                </div>
            </div>

            <!-- Empty -->
            <div
                v-else-if="
                    filteredReservations.length === 0
                "
                class="cyb-card"
            >
                <div class="cyb-empty-state">
                    <div class="cyb-empty-state-icon">
                        <i class="bi bi-person-search"></i>
                    </div>

                    <h3 class="cyb-empty-state-title">
                        No matching reservations
                    </h3>

                    <p class="cyb-empty-state-text">
                        Try changing the filter or search term.
                    </p>
                </div>
            </div>

            <!-- Grouped schedules -->
            <div
                v-else
                class="attendance-schedule-groups"
            >
                <section
                    v-for="group in paginatedGroups"
                    :key="
                        group.scheduleId ??
                        group.timeLabel
                    "
                    class="schedule-group"
                >
                    <div class="schedule-group-header">
                        <div>
                            <div class="schedule-group-time">
                                {{
                                    group.timeLabel ??
                                    'Unknown schedule'
                                }}
                            </div>

                            <div class="schedule-group-meta">
                                {{
                                    group.reservations.length
                                }}
                                reservation{{
                                    group.reservations.length === 1
                                        ? ''
                                        : 's'
                                }}

                                ·

                                {{
                                    getGroupCompletedCount(
                                        group,
                                    )
                                }}
                                completed

                                ·

                                {{
                                    getGroupPendingCount(
                                        group,
                                    )
                                }}
                                pending
                            </div>
                        </div>

                        <span
                            v-if="
                                isGroupComplete(
                                    group,
                                )
                            "
                            class="schedule-complete-badge"
                        >
                            <i class="bi bi-check-circle me-1"></i>
                            Complete
                        </span>

                        <i
                            v-else
                            class="bi bi-clock schedule-group-icon"
                        ></i>
                    </div>

                    <div class="attendance-list">
                        <article
                            v-for="reservation in group.reservations"
                            :key="reservation.id"
                            class="attendance-card"
                        >
                            <div class="attendance-card-main">
                                <div>
                                    <div class="attendance-student-name">
                                        {{
                                            reservation.student.name
                                            ?? 'Unknown student'
                                        }}
                                    </div>

                                    <div class="attendance-student-meta">
                                        {{
                                            reservation.student.university_id
                                            ?? 'No University ID'
                                        }}
                                    </div>
                                </div>

                                <span
                                    class="attendance-status"
                                    :class="
                                        `attendance-status-${reservation.attendance.status}`
                                    "
                                >
                                    {{
                                        statusLabel(
                                            reservation.attendance.status,
                                        )
                                    }}
                                </span>
                            </div>

                            <div
                                v-if="
                                    reservation.attendance.checked_in_time ||
                                    reservation.attendance.minutes_late !== null ||
                                    reservation.penalty
                                "
                                class="attendance-details"
                            >
                                <div
                                    v-if="
                                        reservation.attendance.checked_in_time
                                    "
                                >
                                    <span class="attendance-detail-label">
                                        Arrival
                                    </span>

                                    <strong>
                                        {{
                                            reservation.attendance.checked_in_time
                                        }}
                                    </strong>
                                </div>

                                <div
                                    v-if="
                                        reservation.attendance.minutes_late !== null
                                    "
                                >
                                    <span class="attendance-detail-label">
                                        Minutes Late
                                    </span>

                                    <strong>
                                        {{
                                            reservation.attendance.minutes_late
                                        }}
                                    </strong>
                                </div>

                                <div
                                    v-if="reservation.penalty"
                                >
                                    <span class="attendance-detail-label">
                                        Penalty
                                    </span>

                                    <strong class="attendance-penalty">
                                        ₱{{
                                            reservation.penalty.amount.toFixed(
                                                2,
                                            )
                                        }}
                                    </strong>
                                </div>
                            </div>

                            <div class="attendance-secondary-info">
                                <div>
                                    <i class="bi bi-building me-1"></i>

                                    {{
                                        reservation.student.college
                                        ?? '—'
                                    }}

                                    <template
                                        v-if="
                                            reservation.student.program
                                        "
                                    >
                                        ·
                                        {{
                                            reservation.student.program
                                        }}
                                    </template>
                                </div>

                                <div>
                                    <i class="bi bi-envelope me-1"></i>

                                    {{
                                        reservation.student.email
                                        ?? 'No email'
                                    }}
                                </div>
                            </div>

                            <div
                                v-if="canManage"
                                class="attendance-actions"
                            >
                                <template
                                    v-if="
                                        reservation.attendance.status ===
                                        'pending'
                                    "
                                >
                                    <button
                                        type="button"
                                        class="btn btn-success attendance-action-button"
                                        :disabled="submitting"
                                        @click="
                                            openPresentModal(
                                                reservation,
                                            )
                                        "
                                    >
                                        <i class="bi bi-check-lg me-1"></i>
                                        Present
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-outline-danger attendance-action-button"
                                        :disabled="submitting"
                                        @click="
                                            markAbsent(
                                                reservation,
                                            )
                                        "
                                    >
                                        <i class="bi bi-x-lg me-1"></i>
                                        Absent
                                    </button>
                                </template>

                                <template v-else>
                                    <button
                                        type="button"
                                        class="btn btn-light border attendance-action-button"
                                        :disabled="submitting"
                                        @click="
                                            openPresentModal(
                                                reservation,
                                            )
                                        "
                                    >
                                        <i class="bi bi-pencil me-1"></i>
                                        Change
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary attendance-action-button"
                                        :disabled="submitting"
                                        @click="
                                            revertAttendance(
                                                reservation,
                                            )
                                        "
                                    >
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                                        Revert
                                    </button>
                                </template>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- Pagination -->
                <div
                    v-if="totalPages > 1"
                    class="attendance-pagination"
                >
                    <button
                        type="button"
                        class="btn btn-light border"
                        :disabled="currentPage === 1"
                        @click="previousPage"
                    >
                        <i class="bi bi-chevron-left me-1"></i>
                        Previous
                    </button>

                    <div class="attendance-page-info">
                        Page
                        <strong>
                            {{ currentPage }}
                        </strong>
                        of
                        <strong>
                            {{ totalPages }}
                        </strong>
                    </div>

                    <button
                        type="button"
                        class="btn btn-light border"
                        :disabled="
                            currentPage === totalPages
                        "
                        @click="nextPage"
                    >
                        Next
                        <i class="bi bi-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>
        </template>

        <!-- Present modal -->
        <div
            v-if="arrivalModalOpen"
            class="modal fade show d-block"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">
                                Mark Present
                            </h5>

                            <div
                                v-if="selectedReservation"
                                class="text-muted small mt-1"
                            >
                                {{
                                    selectedReservation.student.name
                                }}

                                ·

                                {{
                                    selectedReservation.schedule.time_label
                                }}
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            :disabled="submitting"
                            @click="closePresentModal"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <div
                            v-if="attendanceError"
                            class="cyb-notice cyb-notice-danger rounded-3 mb-3"
                        >
                            <i class="bi bi-exclamation-circle"></i>

                            <div>
                                {{ attendanceError }}
                            </div>
                        </div>

                        <label class="cyb-form-label">
                            Arrival Time
                        </label>

                        <div class="arrival-time-layout">
                            <input
                                v-model="arrivalTime"
                                type="time"
                                class="form-control cyb-form-control"
                            >

                            <button
                                type="button"
                                class="btn btn-outline-primary current-time-button"
                                @click="useCurrentTime"
                            >
                                <i class="bi bi-clock me-1"></i>
                                Use Current Time
                            </button>
                        </div>

                        <div class="cyb-form-help mt-2">
                            The time will always use the reservation's pictorial date,
                            even if attendance is recorded later.
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-light border"
                            :disabled="submitting"
                            @click="closePresentModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            class="btn btn-success"
                            :disabled="
                                submitting ||
                                !arrivalTime
                            "
                            @click="confirmPresent"
                        >
                            <span
                                v-if="submitting"
                                class="spinner-border spinner-border-sm me-2"
                            ></span>

                            {{
                                submitting
                                    ? 'Saving...'
                                    : 'Confirm Present'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="arrivalModalOpen"
            class="modal-backdrop fade show"
        ></div>
    </div>
</template>

<style scoped>
.attendance-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1.5rem;
}

.attendance-date-section {
    flex: 1 1 auto;
}

.attendance-date-input {
    max-width: 280px;
}

.attendance-year-card {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.15rem;

    padding: 0.75rem 0.95rem;

    border: 1px solid #e7eaed;
    border-radius: 0.65rem;

    background: #f8f9fa;
}

.attendance-year-card strong {
    color: #212529;
    font-size: 1.15rem;
}

.attendance-year-label {
    color: #6c757d;
    font-size: 0.75rem;
}

.attendance-summary-grid {
    display: grid;
    grid-template-columns:
        repeat(5, minmax(0, 1fr));
    gap: 0.75rem;
}

.attendance-summary-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;

    padding: 0.85rem 1rem;

    border: 1px solid #e7eaed;
    border-radius: 0.7rem;

    background: #fff;
}

.attendance-summary-card span {
    color: #6c757d;
    font-size: 0.82rem;
}

.attendance-summary-card strong {
    color: #212529;
    font-size: 1.2rem;
}

.attendance-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.attendance-search {
    position: relative;
    width: min(100%, 440px);
}

.attendance-search > i {
    position: absolute;
    top: 50%;
    left: 0.85rem;
    transform: translateY(-50%);

    color: #6c757d;
    pointer-events: none;
}

.attendance-search .form-control {
    padding-left: 2.4rem;
}

.attendance-match-count {
    color: #6c757d;
    font-size: 0.85rem;
}

.attendance-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;

    margin-top: 1rem;
}

.attendance-filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;

    padding: 0.48rem 0.78rem;

    border: 1px solid #dee2e6;
    border-radius: 999px;

    background: #fff;
    color: #495057;

    font-size: 0.84rem;
    font-weight: 600;

    transition:
        background-color 0.15s ease,
        border-color 0.15s ease,
        color 0.15s ease,
        transform 0.15s ease;
}

.attendance-filter-pill:hover {
    border-color: #adb5bd;
    background: #f8f9fa;
    transform: translateY(-1px);
}

.attendance-filter-pill span {
    color: #6c757d;
    font-size: 0.76rem;
}

.attendance-filter-pill.active {
    border-color: #0d6efd;
    background: #e7f1ff;
    color: #0a58ca;
}

.attendance-filter-pill.active span {
    color: #0a58ca;
}

.attendance-schedule-groups {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.schedule-group {
    overflow: hidden;

    border: 1px solid #e4e8ec;
    border-radius: 0.85rem;

    background: #fff;

    box-shadow:
        0 1px 2px
        rgba(0, 0, 0, 0.03);
}

.schedule-group-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;

    padding: 0.9rem 1rem;

    border-bottom: 1px solid #e7eaed;
    background: #f8f9fa;
}

.schedule-group-time {
    color: #212529;
    font-size: 1rem;
    font-weight: 650;
}

.schedule-group-meta {
    margin-top: 0.2rem;

    color: #6c757d;
    font-size: 0.8rem;
}

.schedule-group-icon {
    color: #6c757d;
    font-size: 1.1rem;
}

.schedule-complete-badge {
    display: inline-flex;
    align-items: center;

    padding: 0.3rem 0.6rem;

    border-radius: 999px;

    background: #d1e7dd;
    color: #0f5132;

    font-size: 0.76rem;
    font-weight: 650;
}

.attendance-list {
    display: flex;
    flex-direction: column;
}

.attendance-card {
    padding: 1rem;

    background: #fff;

    transition:
        background-color 0.15s ease;
}

.attendance-card:hover {
    background: #fcfcfd;
}

.attendance-card + .attendance-card {
    border-top: 1px solid #eef0f2;
}

.attendance-card-main {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.attendance-student-name {
    color: #212529;
    font-size: 1rem;
    font-weight: 650;
}

.attendance-student-meta {
    margin-top: 0.15rem;

    color: #6c757d;
    font-size: 0.85rem;
}

.attendance-status {
    display: inline-flex;
    align-items: center;

    padding: 0.3rem 0.65rem;

    border-radius: 999px;

    font-size: 0.76rem;
    font-weight: 650;
}

.attendance-status-pending {
    background: #e9ecef;
    color: #495057;
}

.attendance-status-present {
    background: #d1e7dd;
    color: #0f5132;
}

.attendance-status-late {
    background: #fff3cd;
    color: #664d03;
}

.attendance-status-absent {
    background: #f8d7da;
    color: #842029;
}

.attendance-status-cancelled {
    background: #e9ecef;
    color: #6c757d;
}

.attendance-details {
    display: grid;
    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 0.75rem;

    margin-top: 0.9rem;
    padding: 0.75rem;

    border-radius: 0.6rem;

    background: #f8f9fa;
}

.attendance-details > div {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.attendance-detail-label {
    color: #6c757d;
    font-size: 0.74rem;
}

.attendance-details strong {
    font-size: 0.9rem;
}

.attendance-penalty {
    color: #b02a37;
}

.attendance-secondary-info {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 0.5rem;

    margin-top: 0.85rem;

    color: #6c757d;
    font-size: 0.8rem;
}

.attendance-actions {
    display: flex;
    gap: 0.5rem;

    margin-top: 0.95rem;
}

.attendance-action-button {
    min-width: 110px;

    transition:
        transform 0.15s ease,
        box-shadow 0.15s ease;
}

.attendance-action-button:hover:not(:disabled) {
    transform: translateY(-1px);

    box-shadow:
        0 2px 6px
        rgba(0, 0, 0, 0.08);
}

.arrival-time-layout {
    display: grid;
    grid-template-columns:
        minmax(0, 1fr)
        auto;

    gap: 0.75rem;
}

.current-time-button {
    white-space: nowrap;
}

.attendance-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;

    padding: 0.75rem 0 0.25rem;
}

.attendance-page-info {
    color: #6c757d;
    font-size: 0.85rem;
}

.attendance-toast {
    position: fixed;
    top: 1.25rem;
    right: 1.25rem;
    z-index: 1090;

    display: flex;
    align-items: flex-start;
    gap: 0.75rem;

    width: min(
        420px,
        calc(100vw - 2rem)
    );

    padding: 0.9rem 1rem;

    border: 1px solid #badbcc;
    border-radius: 0.75rem;

    background: #d1e7dd;
    color: #0f5132;

    box-shadow:
        0 0.5rem 1.25rem
        rgba(0, 0, 0, 0.12);
}

.attendance-toast-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 1.75rem;
    height: 1.75rem;
    flex: 0 0 1.75rem;

    border-radius: 50%;

    background: #0f5132;
    color: #fff;
}

.attendance-toast-message {
    margin-top: 0.15rem;
    font-size: 0.88rem;
}

@media (max-width: 991.98px) {
    .attendance-summary-grid {
        grid-template-columns:
            repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 767.98px) {
    .attendance-header {
        align-items: stretch;
        flex-direction: column;
    }

    .attendance-date-input {
        max-width: none;
    }

    .attendance-year-card {
        align-items: flex-start;
    }

    .attendance-summary-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .attendance-toolbar {
        align-items: stretch;
        flex-direction: column;
    }

    .attendance-search {
        width: 100%;
    }

    .attendance-details {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .attendance-secondary-info {
        flex-direction: column;
    }

    .attendance-actions {
        display: grid;
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .attendance-action-button {
        width: 100%;
        min-width: 0;
    }

    .arrival-time-layout {
        grid-template-columns: 1fr;
    }

    .attendance-pagination {
        justify-content: space-between;
        gap: 0.5rem;
    }

    .attendance-toast {
        top: 0.75rem;
        right: 0.75rem;
        left: 0.75rem;

        width: auto;
    }
}

@media (max-width: 420px) {
    .attendance-summary-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .attendance-card {
        padding: 0.85rem;
    }

    .attendance-details {
        grid-template-columns: 1fr;
    }

    .attendance-filter-row {
        gap: 0.4rem;
    }

    .attendance-filter-pill {
        flex: 1 1 auto;
        justify-content: center;
    }

    .schedule-group-header {
        align-items: flex-start;
    }

    .attendance-page-info {
        white-space: nowrap;
        font-size: 0.78rem;
    }
}
</style>