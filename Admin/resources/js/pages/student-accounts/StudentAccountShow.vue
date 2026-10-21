<script setup lang="ts">

import {
    computed,
    ref,
} from 'vue';

import type {
    AttendanceStatus,
    AvailablePictorialSchedule,
    CurrentStudentReservation,
    ReservationAvailabilityResponse,
    ReschedulePenaltyPreview,
    StoreStudentReservationResponse,
    StudentAccountDetail,
} from '@/types/student-account';

interface Props {
    student: StudentAccountDetail;
}

const props = defineProps<Props>();

const studentData = ref<StudentAccountDetail>({
    ...props.student,
});

const isUpdatingSubscription = ref(false);
const isUpdatingThirdParty = ref(false);

const reservationModalOpen = ref(false);

const isLoadingSchedules = ref(false);
const isSavingReservation = ref(false);

const availableSchedules =
    ref<AvailablePictorialSchedule[]>([]);

const currentReservation =
    ref<CurrentStudentReservation | null>(null);

const reschedulePenalty =
    ref<ReschedulePenaltyPreview | null>(null);

const selectedPictorialId =
    ref<number | null>(null);

const waivePenalty = ref(false);

const reservationError =
    ref<string | null>(null);

const actionMessage = ref<string | null>(null);
const actionError = ref<string | null>(null);

const selectedSchedule = computed(() => {
    return (
        availableSchedules.value.find(
            (schedule) =>
                schedule.id ===
                selectedPictorialId.value,
        ) ?? null
    );
});

const formatDate = (
    value: string | null | undefined,
): string => {
    if (!value) {
        return '—';
    }

    const normalizedValue =
        value.includes('T')
            ? value
            : `${value}T00:00:00`;

    const date =
        new Date(normalizedValue);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat(
        'en-PH',
        {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        },
    ).format(date);
};

const formatTime = (
    value: string | null | undefined,
): string => {
    if (!value) {
        return '—';
    }

    const parts = value.split(':');

    const hours = Number(parts[0]);
    const minutes = Number(parts[1]);

    if (
        Number.isNaN(hours) ||
        Number.isNaN(minutes)
    ) {
        return value;
    }

    const date = new Date();

    date.setHours(hours, minutes, 0, 0);

    return new Intl.DateTimeFormat('en-PH', {
        hour: 'numeric',
        minute: '2-digit',
    }).format(date);
};


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

let actionMessageTimer: number | null = null;
let actionErrorTimer: number | null = null;

const showActionMessage = (
    message: string,
): void => {
    actionMessage.value = message;

    if (actionMessageTimer !== null) {
        window.clearTimeout(
            actionMessageTimer,
        );
    }

    actionMessageTimer =
        window.setTimeout(() => {
            actionMessage.value = null;
            actionMessageTimer = null;
        }, 4000);
};

const showActionError = (
    message: string,
): void => {
    actionError.value = message;

    if (actionErrorTimer !== null) {
        window.clearTimeout(
            actionErrorTimer,
        );
    }

    actionErrorTimer =
        window.setTimeout(() => {
            actionError.value = null;
            actionErrorTimer = null;
        }, 5000);
};


const attendanceLabel = (
    status: AttendanceStatus,
): string => {
    switch (status) {
        case 'present':
            return 'Present';

        case 'absent':
            return 'Absent';

        case 'late':
            return 'Late';

        default:
            return 'Not Recorded';
    }
};

const attendanceClass = (
    status: AttendanceStatus,
): string => {
    switch (status) {
        case 'present':
            return 'status-success';

        case 'absent':
            return 'status-danger';

        case 'late':
            return 'status-warning';

        default:
            return 'status-neutral';
    }
};

const penaltySourceClass = (
    source: 'attendance' | 'manual',
): string => {
    return source === 'attendance'
        ? 'status-primary'
        : 'status-neutral';
};



const getCsrfToken = (): string => {
    const element = document.querySelector<HTMLMetaElement>(
        'meta[name="csrf-token"]',
    );

    return element?.content ?? '';
};

const readJsonResponse = async (
    response: Response,
): Promise<any> => {
    const contentType = response.headers.get('content-type');

    if (!contentType?.includes('application/json')) {
        throw new Error(
            `Server returned an unexpected response (${response.status}).`,
        );
    }

    return response.json();
};

const updateSubscription = async (
    newStatus: boolean,
): Promise<void> => {
    if (isUpdatingSubscription.value) {
        return;
    }

    const confirmed = window.confirm(
        newStatus
            ? 'Subscribe this student?'
            : 'Unsubscribe this student?',
    );

    if (!confirmed) {
        return;
    }

    isUpdatingSubscription.value = true;
    actionMessage.value = null;
    actionError.value = null;

    try {
        const response = await fetch(
            `/student-accounts/${studentData.value.id}/subscription`,
            {
                method: 'PATCH',

                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },

                body: JSON.stringify({
                    is_subscribe: newStatus,
                }),
            },
        );

        const result = await readJsonResponse(response);

        if (!response.ok) {
            throw new Error(
                result.message ??
                    'Unable to update subscription status.',
            );
        }

        if (!result.student) {
            throw new Error(
                'The server returned an invalid response.',
            );
        }

        studentData.value.is_subscribe =
            result.student.is_subscribe;

        studentData.value.subscribe_date =
            result.student.subscribe_date;

        studentData.value.unsubscribe_date =
            result.student.unsubscribe_date;

        actionMessage.value = result.message;
    } catch (error) {
        actionError.value =
            error instanceof Error
                ? error.message
                : 'Unable to update subscription status.';
    } finally {
        isUpdatingSubscription.value = false;
    }
};

const updateThirdParty = async (
    newStatus: boolean,
): Promise<void> => {
    if (isUpdatingThirdParty.value) {
        return;
    }

    const confirmed = window.confirm(
        newStatus
            ? 'Mark this student as using a third-party photo?'
            : 'Remove this student\'s third-party photo status?',
    );

    if (!confirmed) {
        return;
    }

    isUpdatingThirdParty.value = true;
    actionMessage.value = null;
    actionError.value = null;

    try {
        const response = await fetch(
            `/student-accounts/${studentData.value.id}/third-party`,
            {
                method: 'PATCH',

                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },

                body: JSON.stringify({
                    is_third_party: newStatus,
                }),
            },
        );

        const result = await readJsonResponse(response);

        if (!response.ok) {
            throw new Error(
                result.message ??
                    'Unable to update third-party photo status.',
            );
        }

        if (!result.student) {
            throw new Error(
                'The server returned an invalid response.',
            );
        }

        studentData.value.is_third_party =
            result.student.is_third_party;

        actionMessage.value = result.message;
    } catch (error) {
        actionError.value =
            error instanceof Error
                ? error.message
                : 'Unable to update third-party photo status.';
    } finally {
        isUpdatingThirdParty.value = false;
    }
};


const resetReservationModal = (): void => {
    availableSchedules.value = [];
    currentReservation.value = null;
    reschedulePenalty.value = null;

    selectedPictorialId.value = null;
    waivePenalty.value = false;

    reservationError.value = null;
};

const openReservationModal =
    async (): Promise<void> => {
        resetReservationModal();

        reservationModalOpen.value = true;
        isLoadingSchedules.value = true;

        try {
            const response = await fetch(
                `/student-accounts/${studentData.value.id}/available-pictorials`,
                {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With':
                            'XMLHttpRequest',
                    },
                },
            );

            const result =
                await readJsonResponse(
                    response,
                ) as ReservationAvailabilityResponse;

            if (!response.ok) {
                throw new Error(
                    (result as any).message ??
                        'Unable to load pictorial schedules.',
                );
            }

            availableSchedules.value =
                result.data.schedules ?? [];

            currentReservation.value =
                result.data.current_reservation;

            reschedulePenalty.value =
                result.data.reschedule_penalty;
        } catch (error) {
            reservationError.value =
                error instanceof Error
                    ? error.message
                    : 'Unable to load pictorial schedules.';
        } finally {
            isLoadingSchedules.value =
                false;
        }
    };

const closeReservationModal =
    (): void => {
        if (isSavingReservation.value) {
            return;
        }

        reservationModalOpen.value = false;

        resetReservationModal();
    };

const saveReservation =
    async (): Promise<void> => {
        if (!selectedPictorialId.value) {
            reservationError.value =
                'Select a pictorial schedule.';
            return;
        }

        reservationError.value = null;
        isSavingReservation.value = true;

        try {
            const response = await fetch(
                `/student-accounts/${studentData.value.id}/reservation`,
                {
                    method: 'POST',

                    headers: {
                        Accept: 'application/json',
                        'Content-Type':
                            'application/json',
                        'X-CSRF-TOKEN':
                            getCsrfToken(),
                        'X-Requested-With':
                            'XMLHttpRequest',
                    },

                    body: JSON.stringify({
                        pictorial_id:
                            selectedPictorialId.value,

                        waive_penalty:
                            waivePenalty.value,
                    }),
                },
            );

            const result =
                await readJsonResponse(
                    response,
                ) as StoreStudentReservationResponse;

            if (!response.ok) {
                throw new Error(
                    (result as any).message ??
                        'Unable to save reservation.',
                );
            }

            actionError.value = null;

            showActionMessage(
                result.message ??
                    'Reservation saved successfully.',
            );

            reservationModalOpen.value =
                false;

            resetReservationModal();

            //reload the page after a short delay to reflect the updated reservation
            window.setTimeout(() => {
                window.location.reload();
            }, 700);
        } catch (error) {
            const message =
                error instanceof Error
                    ? error.message
                    : 'Unable to save reservation.';

            reservationError.value = message;

            showActionError(message);
        } finally {
            isSavingReservation.value =
                false;
        }
    };





</script>

<template>
    <div class="student-module container-fluid px-0">

        <div
            v-if="actionMessage"
            class="student-toast student-toast-success"
            role="status"
        >
            <div class="student-toast-icon">
                <i class="bi bi-check-lg"></i>
            </div>

            <div class="flex-grow-1">
                <div class="fw-semibold">
                    Success
                </div>

                <div class="student-toast-message">
                    {{ actionMessage }}
                </div>
            </div>

            <button
                type="button"
                class="btn-close"
                aria-label="Dismiss"
                @click="actionMessage = null"
            ></button>
        </div>

        <div
            v-if="actionError"
            class="student-toast student-toast-error"
            role="alert"
        >
            <div class="student-toast-icon">
                <i class="bi bi-exclamation-lg"></i>
            </div>

            <div class="flex-grow-1">
                <div class="fw-semibold">
                    Error
                </div>

                <div class="student-toast-message">
                    {{ actionError }}
                </div>
            </div>

            <button
                type="button"
                class="btn-close"
                aria-label="Dismiss"
                @click="actionError = null"
            ></button>
        </div>



        <div class="detail-header mb-4">
            <a
                href="/student-accounts"
                class="back-link"
            >
                <i class="bi bi-arrow-left"></i>
                Student Accounts
            </a>

            <div
                class="d-flex flex-column flex-lg-row
                       justify-content-between
                       align-items-lg-start gap-3 mt-3"
            >
                <div>
                    <h1 class="student-title">
                        {{ studentData.full_name }}
                    </h1>

                    <div class="student-meta">
                        <span>
                            {{ studentData.university_id }}
                        </span>

                        <span class="meta-divider">
                            ·
                        </span>

                        <span>
                            {{ studentData.college || 'No college' }}
                        </span>

                        <span class="meta-divider">
                            ·
                        </span>

                        <span>
                            {{ studentData.program || 'No program' }}
                        </span>

                        <template
                            v-if="studentData.graduation_year"
                        >
                            <span class="meta-divider">
                                ·
                            </span>

                            <span>
                                Class of
                                {{ studentData.graduation_year }}
                            </span>
                        </template>
                    </div>
                </div>

                <div class="status-group">
                    <span
                        class="status-pill"
                        :class="
                            studentData.is_subscribe
                                ? 'status-success'
                                : 'status-neutral'
                        "
                    >
                        {{
                            studentData.is_subscribe
                                ? 'Subscribed'
                                : 'Not Subscribed'
                        }}
                    </span>

                    <span
                        v-if="studentData.is_third_party"
                        class="status-pill status-info"
                    >
                        Third-Party Photo
                    </span>
                </div>
            </div>
        </div>



        <div class="row g-4">
            <div class="col-12 col-xl-7">
                <div class="card module-card h-100">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">
                                Student Information
                            </h2>

                            <p class="section-description">
                                Personal and academic information
                            </p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="info-section">
                            <div class="info-section-label">
                                Contact
                            </div>

                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <div class="info-label">
                                        Email
                                    </div>

                                    <div class="info-value">
                                        {{ studentData.email || '—' }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="info-label">
                                        Contact Number
                                    </div>

                                    <div class="info-value">
                                        {{
                                            studentData.contact_number
                                                || '—'
                                        }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="info-label">
                                        Current Address
                                    </div>

                                    <div class="info-value">
                                        {{
                                            studentData.current_address
                                                || '—'
                                        }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="info-label">
                                        Permanent Address
                                    </div>

                                    <div class="info-value">
                                        {{
                                            studentData.permanent_address
                                                || '—'
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-divider"></div>

                        <div class="info-section">
                            <div class="info-section-label">
                                Academic
                            </div>

                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <div class="info-label">
                                        College
                                    </div>

                                    <div class="info-value">
                                        {{
                                            studentData.college
                                                || '—'
                                        }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="info-label">
                                        Program
                                    </div>

                                    <div class="info-value">
                                        {{
                                            studentData.program
                                                || '—'
                                        }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="info-label">
                                        Major
                                    </div>

                                    <div class="info-value">
                                        {{
                                            studentData.major
                                                || '—'
                                        }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="info-label">
                                        Graduation Year
                                    </div>

                                    <div class="info-value">
                                        {{
                                            studentData
                                                .graduation_year
                                                || '—'
                                        }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="info-label">
                                        Expected Graduation
                                    </div>

                                    <div class="info-value">
                                        {{
                                            studentData
                                                .expected_graduation_date
                                                || '—'
                                        }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="info-label">
                                        SLMIS ID
                                    </div>

                                    <div class="info-value">
                                        {{
                                            studentData.slmis_id
                                                ?? '—'
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="card module-card h-100">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">
                                CYB Status
                            </h2>

                            <p class="section-description">
                                Current yearbook participation details
                            </p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="status-row">
                            <div>
                                <div class="info-label">
                                    Subscription
                                </div>

                                <div class="info-value">
                                    {{
                                        studentData.is_subscribe
                                            ? 'Subscribed'
                                            : 'Not Subscribed'
                                    }}
                                </div>
                            </div>

                            <span
                                class="status-dot"
                                :class="
                                    studentData.is_subscribe
                                        ? 'dot-success'
                                        : 'dot-neutral'
                                "
                            ></span>
                        </div>

                        <div class="status-row">
                            <div>
                                <div class="info-label">
                                    Photo Source
                                </div>

                                <div class="info-value">
                                    {{
                                        studentData.is_third_party
                                            ? 'Third-Party Photo'
                                            : 'CYB Pictorial'
                                    }}
                                </div>
                            </div>
                        </div>

                        <div class="status-row">
                            <div>
                                <div class="info-label">
                                    Contract Agreement
                                </div>

                                <div class="info-value">
                                    {{
                                        studentData.is_agree_contract
                                            ? 'Agreed'
                                            : 'Not Agreed'
                                    }}
                                </div>
                            </div>
                        </div>

                        <div class="status-row">
                            <div>
                                <div class="info-label">
                                    Subscription Date
                                </div>

                                <div class="info-value">
                                    {{
                                        formatDate(
                                            studentData.subscribe_date,
                                        )
                                    }}
                                </div>
                            </div>
                        </div>

                        <div class="status-row">
                            <div>
                                <div class="info-label">
                                    Unsubscribe Date
                                </div>

                                <div class="info-value">
                                    {{
                                        formatDate(
                                            studentData.unsubscribe_date,
                                        )
                                    }}
                                </div>
                            </div>
                        </div>

                        <div class="status-row border-bottom-0">
                            <div>
                                <div class="info-label">
                                    Picture Claimed
                                </div>

                                <div class="info-value">
                                    {{
                                        studentData.claim_pic
                                            ? 'Yes'
                                            : 'No'
                                    }}

                                    <span
                                        v-if="
                                            studentData.claim_pic_date
                                        "
                                        class="secondary-inline"
                                    >
                                        {{
                                            formatDate(
                                                studentData
                                                    .claim_pic_date,
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="
                    studentData.permissions.manage_subscription ||
                    studentData.permissions.manage_third_party
                "
                class="col-12"
            >
                <div class="card module-card">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">
                                Admin Actions
                            </h2>

                            <p class="section-description">
                                Changes are recorded in the audit log.
                            </p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div
                                v-if="
                                    studentData.permissions
                                        .manage_subscription
                                "
                                class="col-12 col-lg-6"
                            >
                                <div class="action-panel">
                                    <div>
                                        <div class="action-title">
                                            Subscription
                                        </div>

                                        <p class="action-description">
                                            Change whether this student
                                            is currently subscribed to CYB.
                                        </p>
                                    </div>

                                    <button
                                        v-if="
                                            !studentData.is_subscribe
                                        "
                                        type="button"
                                        class="btn btn-success"
                                        :disabled="
                                            isUpdatingSubscription
                                        "
                                        @click="
                                            updateSubscription(true)
                                        "
                                    >
                                        Subscribe Student
                                    </button>

                                    <button
                                        v-else
                                        type="button"
                                        class="btn btn-outline-danger"
                                        :disabled="
                                            isUpdatingSubscription
                                        "
                                        @click="
                                            updateSubscription(false)
                                        "
                                    >
                                        Unsubscribe Student
                                    </button>
                                </div>
                            </div>

                            <div
                                v-if="
                                    studentData.permissions
                                        .manage_third_party
                                "
                                class="col-12 col-lg-6"
                            >
                                <div class="action-panel">
                                    <div>
                                        <div class="action-title">
                                            Photo Source
                                        </div>

                                        <p class="action-description">
                                            Mark students who will submit
                                            photos outside the CYB pictorial.
                                        </p>
                                    </div>

                                    <button
                                        v-if="
                                            !studentData.is_third_party
                                        "
                                        type="button"
                                        class="btn btn-outline-primary"
                                        :disabled="
                                            isUpdatingThirdParty
                                        "
                                        @click="
                                            updateThirdParty(true)
                                        "
                                    >
                                        Mark as Third-Party
                                    </button>

                                    <button
                                        v-else
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        :disabled="
                                            isUpdatingThirdParty
                                        "
                                        @click="
                                            updateThirdParty(false)
                                        "
                                    >
                                        Remove Third-Party Status
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card module-card">
                    <div
                        class="section-header d-flex
                               justify-content-between
                               align-items-center"
                    >
                        <div>
                            <h2 class="section-title">
                                Pictorial Reservations
                            </h2>

                            <p class="section-description">
                                Reservation and attendance history
                            </p>
                        </div>

                        <div class="reservation-header-actions">
                            <div class="record-count">
                                {{ studentData.reservations.length }}
                                record{{
                                    studentData.reservations.length === 1
                                        ? ''
                                        : 's'
                                }}
                            </div>

                            <button
                                v-if="
                                    studentData.permissions
                                        .manage_reservations
                                "
                                type="button"
                                class="btn btn-primary btn-sm"
                                @click="openReservationModal"
                            >
                                <i class="bi bi-calendar-plus me-1"></i>

                                {{
                                    studentData.reservations.length > 0
                                        ? 'Manage Reservation'
                                        : 'Book Schedule'
                                }}
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="
                            studentData.reservations.length === 0
                        "
                        class="empty-state"
                    >
                        <div class="empty-state-icon">
                            <i class="bi bi-calendar-x"></i>
                        </div>

                        <h3 class="empty-state-title">
                            No pictorial reservation
                        </h3>

                        <p class="empty-state-text">
                            This student does not have any
                            reservation records yet.
                        </p>
                    </div>

                    <div
                        v-else
                        class="table-responsive"
                    >
                        <table
                            class="table reservation-table
                                   align-middle mb-0"
                        >
                            <thead>
                                <tr>
                                    <th>Schedule</th>
                                    <th>Reservation</th>
                                    <th>Attendance</th>
                                    <th>Reschedule Date</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="reservation
                                        in studentData.reservations"
                                    :key="reservation.id"
                                >
                                    <td>
                                        <div class="schedule-date">
                                            {{
                                                reservation.pictorial
                                                    ? formatDate(
                                                        reservation
                                                            .pictorial
                                                            .date,
                                                    )
                                                    : '—'
                                            }}
                                        </div>

                                        <div
                                            v-if="
                                                reservation.pictorial
                                            "
                                            class="schedule-time"
                                        >
                                            {{
                                                formatTime(
                                                    reservation
                                                        .pictorial
                                                        .start_time,
                                                )
                                            }}
                                            –
                                            {{
                                                formatTime(
                                                    reservation
                                                        .pictorial
                                                        .end_time,
                                                )
                                            }}
                                        </div>
                                    </td>

                                    <td>
                                        <span
                                            class="status-pill"
                                            :class="
                                                reservation
                                                    .is_rescheduled
                                                    ? 'status-neutral'
                                                    : 'status-primary'
                                            "
                                        >
                                            {{
                                                reservation
                                                    .is_rescheduled
                                                    ? 'Rescheduled'
                                                    : 'Current'
                                            }}
                                        </span>
                                    </td>

                                    <td>
                                        <span
                                            class="status-pill"
                                            :class="
                                                attendanceClass(
                                                    reservation
                                                        .attendance_status,
                                                )
                                            "
                                        >
                                            {{
                                                attendanceLabel(
                                                    reservation
                                                        .attendance_status,
                                                )
                                            }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="table-value">
                                            {{
                                                formatDate(
                                                    reservation
                                                        .reschedule_date,
                                                )
                                            }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card module-card">
                    <div
                        class="section-header d-flex
                            justify-content-between
                            align-items-center"
                    >
                        <div>
                            <h2 class="section-title">
                                Penalty History
                            </h2>

                            <p class="section-description">
                                Penalties recorded for this student
                            </p>
                        </div>

                        <div class="record-count">
                            {{ studentData.penalties.length }}
                            record{{
                                studentData.penalties.length === 1
                                    ? ''
                                    : 's'
                            }}
                        </div>
                    </div>

                    <div
                        v-if="studentData.penalties.length === 0"
                        class="empty-state"
                    >
                        <div class="empty-state-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>

                        <h3 class="empty-state-title">
                            No penalties
                        </h3>

                        <p class="empty-state-text">
                            This student does not have any recorded penalties.
                        </p>
                    </div>

                    <div
                        v-else
                        class="table-responsive"
                    >
                        <table
                            class="table penalty-table align-middle mb-0"
                        >
                            <thead>
                                <tr>
                                    <th>Penalty</th>
                                    <th>Source</th>
                                    <th>Related Pictorial</th>
                                    <th class="text-end">
                                        Amount
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="penalty in studentData.penalties"
                                    :key="penalty.id"
                                >
                                    <td>
                                        <div class="penalty-name">
                                            {{
                                                penalty.penalty_type_label
                                            }}
                                        </div>
                                    </td>

                                    <td>
                                        <span
                                            class="status-pill"
                                            :class="
                                                penaltySourceClass(
                                                    penalty.source,
                                                )
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
                                            <div class="schedule-date">
                                                {{
                                                    formatDate(
                                                        penalty.reservation
                                                            .date,
                                                    )
                                                }}
                                            </div>

                                            <div class="schedule-time">
                                                {{
                                                    formatTime(
                                                        penalty.reservation
                                                            .start_time,
                                                    )
                                                }}
                                                –
                                                {{
                                                    formatTime(
                                                        penalty.reservation
                                                            .end_time,
                                                    )
                                                }}
                                            </div>
                                        </template>

                                        <span
                                            v-else
                                            class="table-value text-muted"
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
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        </div>

        <!-- Reservation Modal -->
        <div
            v-if="reservationModalOpen"
            class="modal fade show d-block"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="modal-dialog
                    modal-dialog-centered
                    modal-lg
                    modal-dialog-scrollable"
            >
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">
                                {{
                                    currentReservation
                                        ? 'Reschedule Pictorial'
                                        : 'Book Pictorial Schedule'
                                }}
                            </h5>

                            <div class="text-muted small mt-1">
                                {{ studentData.full_name }}
                                · CYB
                                {{ studentData.graduation_year }}
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            :disabled="isSavingReservation"
                            @click="closeReservationModal"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <div
                            v-if="reservationError"
                            class="cyb-notice
                                cyb-notice-danger
                                rounded-3
                                mb-3"
                        >
                            <i class="bi bi-exclamation-circle"></i>

                            <div>
                                {{ reservationError }}
                            </div>
                        </div>

                        <!-- Loading -->
                        <div
                            v-if="isLoadingSchedules"
                            class="reservation-loading"
                        >
                            <div
                                class="spinner-border"
                                role="status"
                            ></div>

                            <div class="mt-3 text-muted">
                                Loading available schedules...
                            </div>
                        </div>

                        <template v-else>
                            <!-- Existing Reservation -->
                            <div
                                v-if="currentReservation"
                                class="current-reservation-panel"
                            >
                                <div class="current-reservation-icon">
                                    <i class="bi bi-calendar-check"></i>
                                </div>

                                <div>
                                    <div class="reservation-panel-label">
                                        Current Reservation
                                    </div>

                                    <strong>
                                        {{
                                            currentReservation.date_label
                                            ?? '—'
                                        }}
                                    </strong>

                                    <div class="text-muted small mt-1">
                                        {{
                                            currentReservation.time_label
                                            ?? '—'
                                        }}
                                    </div>
                                </div>
                            </div>

                          
                            <!-- Penalty Preview -->
                            <div
                                v-if="
                                    (currentReservation && reschedulePenalty) ||
                                    (selectedSchedule?.is_delayed &&
                                    selectedSchedule.delayed_penalty)
                                "
                                class="penalty-preview-section"
                            >
                                <div class="penalty-preview-heading">
                                    <div>
                                        <div class="penalty-preview-title">
                                            Penalties
                                        </div>

                                        <div class="penalty-preview-subtitle">
                                            Review the charges that will be applied to this reservation.
                                        </div>
                                    </div>

                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>

                                <div class="penalty-preview-list">
                                    <!-- Rescheduling Penalty -->
                                    <div
                                        v-if="
                                            currentReservation &&
                                            reschedulePenalty
                                        "
                                        class="penalty-preview-item"
                                    >
                                        <div class="penalty-preview-content">
                                            <div class="penalty-preview-label">
                                                Rescheduling Penalty
                                            </div>

                                            <strong class="penalty-preview-name">
                                                {{
                                                    reschedulePenalty.label
                                                }}
                                            </strong>

                                            <div class="penalty-preview-description">
                                                Based on
                                                {{
                                                    reschedulePenalty.days_before
                                                }}
                                                day{{
                                                    reschedulePenalty.days_before === 1
                                                        ? ''
                                                        : 's'
                                                }}
                                                before the current pictorial schedule.
                                            </div>
                                        </div>

                                        <div class="penalty-preview-amount">
                                            <strong
                                                :class="{
                                                    'text-decoration-line-through text-muted':
                                                        waivePenalty,
                                                }"
                                            >
                                                {{
                                                    formatAmount(
                                                        reschedulePenalty.amount,
                                                    )
                                                }}
                                            </strong>

                                            <span
                                                v-if="waivePenalty"
                                                class="penalty-waived-label"
                                            >
                                                Waived
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Delayed Schedule Penalty -->
                                    <div
                                        v-if="
                                            selectedSchedule?.is_delayed &&
                                            selectedSchedule.delayed_penalty
                                        "
                                        class="penalty-preview-item penalty-preview-delayed"
                                    >
                                        <div class="penalty-preview-content">
                                            <div class="penalty-preview-label">
                                                Delayed Schedule Penalty
                                            </div>

                                            <strong class="penalty-preview-name">
                                                {{
                                                    selectedSchedule
                                                        .delayed_penalty
                                                        .label
                                                }}
                                            </strong>

                                            <div class="penalty-preview-description">
                                                This selected schedule is marked as delayed and carries an additional fixed penalty.
                                            </div>
                                        </div>

                                        <div class="penalty-preview-amount">
                                            <strong>
                                                {{
                                                    formatAmount(
                                                        selectedSchedule
                                                            .delayed_penalty
                                                            .amount,
                                                    )
                                                }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="
                                    currentReservation &&
                                    reschedulePenalty
                                "
                                class="waive-penalty-panel"
                            >
                                <div class="form-check">
                                    <input
                                        id="waive-reschedule-penalty"
                                        v-model="waivePenalty"
                                        class="form-check-input"
                                        type="checkbox"
                                    >

                                    <label
                                        class="form-check-label"
                                        for="waive-reschedule-penalty"
                                    >
                                        Waive rescheduling penalty
                                    </label>
                                </div>

                                <div class="cyb-form-help mt-1">
                                    The reservation will still be rescheduled, but no rescheduling penalty will be created.
                                </div>
                            </div>

                            <!-- Schedule Selection -->
                            <div class="mt-4">
                                <div class="reservation-section-heading">
                                    <div>
                                        <div class="cyb-form-label mb-1">
                                            Select Schedule
                                        </div>

                                        <div class="cyb-form-help">
                                            Admin booking can use any available schedule for this CYB year regardless of college assignment.
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        availableSchedules.length === 0
                                    "
                                    class="reservation-empty"
                                >
                                    <i class="bi bi-calendar-x"></i>

                                    <strong>
                                        No available schedules
                                    </strong>

                                    <span>
                                        There are currently no schedules with available slots.
                                    </span>
                                </div>

                                <div
                                    v-else
                                    class="schedule-selection-list"
                                >
                                    <button
                                        v-for="schedule in availableSchedules"
                                        :key="schedule.id"
                                        type="button"
                                        class="schedule-option"
                                        :class="{
                                            selected:
                                                selectedPictorialId ===
                                                schedule.id,

                                            current:
                                                schedule
                                                    .is_current_reservation,
                                        }"
                                        :disabled="
                                            schedule
                                                .is_current_reservation
                                        "
                                        @click="
                                            selectedPictorialId =
                                                schedule.id
                                        "
                                    >
                                        <div class="schedule-option-main">
                                            <div class="schedule-option-date">
                                                {{
                                                    schedule.date_label
                                                    ?? formatDate(
                                                        schedule.date,
                                                    )
                                                }}
                                            </div>

                                            <div class="schedule-option-time">
                                                {{
                                                    schedule.time_label
                                                    ?? `${formatTime(
                                                        schedule.start_time,
                                                    )} – ${formatTime(
                                                        schedule.end_time,
                                                    )}`
                                                }}
                                            </div>
                                        </div>

                                        <div class="schedule-option-side">
                                            <span
                                                v-if="
                                                    schedule
                                                        .is_current_reservation
                                                "
                                                class="status-pill status-primary"
                                            >
                                                Current
                                            </span>

                                            <template v-else>
                                                <span class="schedule-slots">
                                                    {{
                                                        schedule.available_slots
                                                    }}
                                                    slot{{
                                                        schedule.available_slots === 1
                                                            ? ''
                                                            : 's'
                                                    }}
                                                    left
                                                </span>

                                                <span
                                                    v-if="schedule.is_delayed"
                                                    class="delayed-schedule-badge"
                                                >
                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                    Delayed · +{{
                                                        formatAmount(
                                                            schedule.delayed_penalty?.amount
                                                            ?? 300,
                                                        )
                                                    }}
                                                </span>
                                            </template>

                                            <i
                                                v-if="
                                                    selectedPictorialId ===
                                                    schedule.id
                                                "
                                                class="bi bi-check-circle-fill schedule-selected-icon"
                                            ></i>
                                        </div>

                                    </button>
                                </div>
                            </div>

                            <!-- Selection Summary -->
                            <div
                                v-if="selectedSchedule"
                                class="selected-schedule-summary"
                            >
                                <div>
                                    <span>
                                        New Schedule
                                    </span>

                                    <strong>
                                        {{
                                            selectedSchedule.date_label
                                        }}
                                    </strong>

                                    <div>
                                        {{
                                            selectedSchedule.time_label
                                        }}
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        selectedSchedule.is_delayed &&
                                        selectedSchedule.delayed_penalty
                                    "
                                >
                                    <span>
                                        Delayed Schedule Penalty
                                    </span>

                                    <strong class="text-danger">
                                        {{
                                            formatAmount(
                                                selectedSchedule
                                                    .delayed_penalty
                                                    .amount,
                                            )
                                        }}
                                    </strong>

                                    <div class="text-muted">
                                        This penalty is applied automatically.
                                    </div>
                                </div>


                                <div
                                    v-if="
                                        currentReservation &&
                                        reschedulePenalty
                                    "
                                >
                                    <span>
                                        Rescheduling Penalty
                                    </span>

                                    <strong
                                        :class="{
                                            'text-decoration-line-through':
                                                waivePenalty,
                                        }"
                                    >
                                        {{
                                            formatAmount(
                                                reschedulePenalty.amount,
                                            )
                                        }}
                                    </strong>

                                    <div
                                        v-if="waivePenalty"
                                        class="text-success"
                                    >
                                        Waived
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-light border"
                            :disabled="isSavingReservation"
                            @click="closeReservationModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            class="btn btn-primary"
                            :disabled="
                                isLoadingSchedules ||
                                isSavingReservation ||
                                !selectedPictorialId
                            "
                            @click="saveReservation"
                        >
                            <span
                                v-if="isSavingReservation"
                                class="spinner-border
                                    spinner-border-sm
                                    me-2"
                            ></span>

                            <i
                                v-else
                                class="bi bi-calendar-check me-2"
                            ></i>

                            {{
                                isSavingReservation
                                    ? 'Saving...'
                                    : currentReservation
                                        ? 'Confirm Reschedule'
                                        : 'Book Schedule'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="reservationModalOpen"
            class="modal-backdrop fade show"
        ></div>


    </div>
</template>

<style scoped>
.student-module {
    font-size: 0.94rem;
}

.detail-header {
    padding: 1.25rem 1.35rem;

    border: 1px solid #e7eaed;
    border-radius: 0.85rem;

    background:
        linear-gradient(
            135deg,
            #ffffff 0%,
            #f8f9fa 100%
        );

    box-shadow:
        0 0.125rem 0.45rem
        rgba(0, 0, 0, 0.035);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;

    color: #6c757d;

    font-size: 0.84rem;
    font-weight: 550;

    text-decoration: none;

    transition:
        color 0.15s ease,
        transform 0.15s ease;
}

.back-link:hover {
    color: #0d6efd;

    transform:
        translateX(-2px);
}

.student-title {
    margin: 0;

    color: #212529;

    font-size: 1.65rem;
    font-weight: 700;

    letter-spacing: -0.025em;
}

.student-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;

    margin-top: 0.5rem;

    color: #6c757d;

    font-size: 0.86rem;
    line-height: 1.5;
}

.meta-divider {
    color: #adb5bd;
}

.status-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    min-height: 26px;
    padding: 0.22rem 0.6rem;
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

.status-primary {
    background: #e8f0fe;
    color: #315ca8;
}

.status-danger {
    background: #fae8e8;
    color: #a33a3a;
}

.status-warning {
    background: #fff4d8;
    color: #87620f;
}

.module-card {
    border: 1px solid #e9ecef;
    border-radius: 0.75rem;
    box-shadow: 0 0.125rem 0.45rem rgba(0, 0, 0, 0.035);
    overflow: hidden;
}

.section-header {
    padding: 1rem 1.25rem;
    background: #fff;
    border-bottom: 1px solid #edf0f2;
}

.section-title {
    margin: 0;
    color: #212529;
    font-size: 1rem;
    font-weight: 650;
}

.section-description {
    margin: 0.2rem 0 0;
    color: #6c757d;
    font-size: 0.8rem;
}

.info-section-label {
    margin-bottom: 1rem;
    color: #6c757d;
    font-size: 0.74rem;
    font-weight: 650;
    letter-spacing: 0.045em;
    text-transform: uppercase;
}

.info-label {
    margin-bottom: 0.25rem;
    color: #6c757d;
    font-size: 0.76rem;
    font-weight: 550;
}

.info-value {
    color: #292d32;
    font-size: 0.92rem;
    line-height: 1.45;
    word-break: break-word;
}

.section-divider {
    height: 1px;
    margin: 1.5rem 0;
    background: #edf0f2;
}

.status-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.85rem 0;
    border-bottom: 1px solid #edf0f2;
}

.status-dot {
    width: 9px;
    height: 9px;
    flex: 0 0 9px;
    border-radius: 50%;
}

.dot-success {
    background: #28a06a;
}

.dot-neutral {
    background: #adb5bd;
}

.secondary-inline {
    margin-left: 0.35rem;
    color: #6c757d;
    font-size: 0.78rem;
}

.action-panel {
    display: flex;
    min-height: 100%;
    flex-direction: column;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.25rem;
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 0.65rem;
    background: #fafbfc;
}

.action-title {
    color: #292d32;
    font-size: 0.92rem;
    font-weight: 600;
}

.action-description {
    max-width: 520px;
    margin: 0.3rem 0 0;
    color: #6c757d;
    font-size: 0.8rem;
    line-height: 1.5;
}

.btn {
    min-height: 39px;
    font-weight: 500;
}

.record-count {
    color: #6c757d;
    font-size: 0.78rem;
    font-weight: 500;
}

.reservation-table thead th {
    padding: 0.85rem 1rem;
    background: #f8f9fa;
    color: #6c757d;
    font-size: 0.75rem;
    font-weight: 650;
    letter-spacing: 0.025em;
    text-transform: uppercase;
    white-space: nowrap;
}


.penalty-table thead th {
    padding: 0.85rem 1rem;

    background: #f8f9fa;

    color: #6c757d;

    font-size: 0.75rem;
    font-weight: 650;

    letter-spacing: 0.025em;

    text-transform: uppercase;

    white-space: nowrap;
}

.penalty-table tbody td {
    padding: 1rem;

    border-color: #edf0f2;
}

.penalty-name {
    color: #292d32;

    font-weight: 600;
}

.penalty-amount {
    color: #b02a37;

    white-space: nowrap;
}


.reservation-table tbody td {
    padding: 1rem;
    border-color: #edf0f2;
}

.schedule-date {
    color: #292d32;
    font-weight: 550;
}

.schedule-time {
    margin-top: 0.2rem;
    color: #6c757d;
    font-size: 0.8rem;
}

.table-value {
    color: #343a40;
}

.empty-state {
    max-width: 520px;
    margin: 0 auto;
    padding: 4rem 1.5rem;
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
    color: #343a40;
    font-size: 1rem;
    font-weight: 600;
}

.empty-state-text {
    margin: 0;
    color: #6c757d;
    font-size: 0.86rem;
}

.reservation-header-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.reservation-loading {
    padding: 3rem 1rem;
    text-align: center;
}



.current-reservation-panel {
    justify-content: flex-start;
}

.current-reservation-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 40px;
    height: 40px;

    flex: 0 0 40px;

    border-radius: 0.6rem;

    background: #e8f0fe;
    color: #315ca8;

    font-size: 1.1rem;
}

.reservation-panel-label {
    margin-bottom: 0.15rem;

    color: #6c757d;

    font-size: 0.74rem;
    font-weight: 600;

    text-transform: uppercase;
}

.reschedule-penalty-panel {
    margin-top: 0.75rem;

    border-color: #ffe69c;

    background: #fff9e6;
}

.penalty-preview-section {
    margin-top: 0.85rem;

    overflow: hidden;

    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;

    background: #fff;
}

.penalty-preview-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 1rem;

    padding: 0.85rem 1rem;

    border-bottom: 1px solid #edf0f2;

    background: #f8f9fa;
}

.penalty-preview-title {
    color: #292d32;

    font-size: 0.9rem;
    font-weight: 650;
}

.penalty-preview-subtitle {
    margin-top: 0.15rem;

    color: #6c757d;

    font-size: 0.76rem;
}

.penalty-preview-heading > i {
    color: #d39e00;

    font-size: 1.1rem;
}

.penalty-preview-list {
    display: flex;
    flex-direction: column;
}

.penalty-preview-item {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 1.5rem;

    padding: 0.95rem 1rem;

    border-bottom: 1px solid #edf0f2;
}

.penalty-preview-item:last-child {
    border-bottom: 0;
}

.penalty-preview-delayed {
    background: #fff9f9;
}

.penalty-preview-content {
    flex: 1 1 auto;
}

.penalty-preview-label {
    margin-bottom: 0.15rem;

    color: #6c757d;

    font-size: 0.7rem;
    font-weight: 650;

    letter-spacing: 0.035em;

    text-transform: uppercase;
}

.penalty-preview-name {
    display: block;

    color: #292d32;

    font-size: 0.9rem;
}

.penalty-preview-description {
    margin-top: 0.2rem;

    color: #6c757d;

    font-size: 0.78rem;
    line-height: 1.45;
}

.penalty-preview-amount {
    display: flex;
    flex-direction: column;
    align-items: flex-end;

    flex: 0 0 auto;

    gap: 0.15rem;
}

.penalty-preview-amount strong {
    color: #b02a37;

    font-size: 1.05rem;

    white-space: nowrap;
}

.penalty-waived-label {
    color: #198754;

    font-size: 0.72rem;
    font-weight: 650;
}


.delayed-schedule-badge {
    display: inline-flex;
    align-items: center;

    padding: 0.25rem 0.5rem;

    border-radius: 999px;

    background: #fff3cd;
    color: #856404;

    font-size: 0.72rem;
    font-weight: 650;

    white-space: nowrap;
}



.waive-penalty-panel {
    margin-top: 0.75rem;

    padding: 0.85rem 1rem;

    border: 1px dashed #ced4da;
    border-radius: 0.65rem;

    background: #fff;
}

.reservation-section-heading {
    display: flex;
    justify-content: space-between;

    margin-bottom: 0.75rem;
}

.schedule-selection-list {
    display: flex;
    flex-direction: column;

    overflow-y: auto;

    max-height: 360px;

    border: 1px solid #e1e5e9;
    border-radius: 0.7rem;
}

.schedule-option {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 1rem;

    width: 100%;

    padding: 0.85rem 1rem;

    border: 0;
    border-bottom: 1px solid #edf0f2;

    background: #fff;

    text-align: left;

    transition:
        background-color 0.15s ease,
        box-shadow 0.15s ease;
}

.schedule-option:last-child {
    border-bottom: 0;
}

.schedule-option:hover:not(:disabled) {
    background: #f8f9fa;
}

.schedule-option.selected {
    background: #eef5ff;

    box-shadow:
        inset 3px 0 0 #0d6efd;
}

.schedule-option.current {
    cursor: default;

    background: #f8f9fa;

    opacity: 0.8;
}

.schedule-option-date {
    color: #292d32;

    font-weight: 650;
}

.schedule-option-time {
    margin-top: 0.15rem;

    color: #6c757d;

    font-size: 0.8rem;
}

.schedule-option-side {
    display: flex;
    align-items: center;

    gap: 0.65rem;

    flex: 0 0 auto;
}

.schedule-slots {
    color: #6c757d;

    font-size: 0.78rem;

    white-space: nowrap;
}

.schedule-selected-icon {
    color: #0d6efd;

    font-size: 1.05rem;
}

.reservation-empty {
    display: flex;
    flex-direction: column;
    align-items: center;

    gap: 0.4rem;

    padding: 2.5rem 1rem;

    border: 1px dashed #dee2e6;
    border-radius: 0.7rem;

    color: #6c757d;

    text-align: center;
}

.reservation-empty > i {
    margin-bottom: 0.25rem;

    font-size: 1.5rem;
}

.reservation-empty strong {
    color: #343a40;
}

.reservation-empty span {
    font-size: 0.82rem;
}

.selected-schedule-summary {
    display: flex;
    justify-content: space-between;
    gap: 1rem;

    margin-top: 1rem;

    padding: 0.9rem 1rem;

    border: 1px solid #b6d4fe;
    border-radius: 0.65rem;

    background: #f3f8ff;
}

.selected-schedule-summary > div {
    display: flex;
    flex-direction: column;

    gap: 0.15rem;
}

.selected-schedule-summary span {
    color: #6c757d;

    font-size: 0.72rem;
}

.selected-schedule-summary strong {
    color: #212529;
}

.student-toast {
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

    border-radius: 0.75rem;

    box-shadow:
        0 0.5rem 1.25rem
        rgba(0, 0, 0, 0.12);
}

.student-toast-success {
    border: 1px solid #badbcc;

    background: #d1e7dd;
    color: #0f5132;
}

.student-toast-error {
    border: 1px solid #f5c2c7;

    background: #f8d7da;
    color: #842029;
}

.student-toast-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 1.75rem;
    height: 1.75rem;

    flex: 0 0 1.75rem;

    border-radius: 50%;

    background: currentColor;
}

.student-toast-icon i {
    color: #fff;
}

.student-toast-message {
    margin-top: 0.15rem;

    font-size: 0.86rem;
}

@media (max-width: 767.98px) {
    .student-title {
        font-size: 1.45rem;
    }

    .reservation-table {
        min-width: 680px;
    }

    .detail-header {
        padding: 1rem;
    }

    .penalty-table {
        min-width: 620px;
    }

    .reservation-header-actions {
        align-items: flex-end;
        flex-direction: column;
    }

    .current-reservation-panel,
    .reschedule-penalty-panel,
    .selected-schedule-summary {
        align-items: flex-start;
        flex-direction: column;
    }

    .schedule-option {
        align-items: flex-start;
    }

    .schedule-option-side {
        align-items: flex-end;
        flex-direction: column;
    }

    .reschedule-penalty-amount {
        font-size: 1rem;
    }    

    .delayed-penalty-panel {
        align-items: flex-start;
        flex-direction: column;
    }

    .delayed-penalty-amount {
        font-size: 1rem;
    }

    .penalty-preview-item {
        align-items: flex-start;
        flex-direction: column;

        gap: 0.6rem;
    }

    .penalty-preview-amount {
        align-items: flex-start;
    }

    .student-toast {
        top: 0.75rem;
        right: 0.75rem;
        left: 0.75rem;

        width: auto;
    }    

}
</style>