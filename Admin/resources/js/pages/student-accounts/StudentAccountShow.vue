<script setup lang="ts">
import { ref } from 'vue';

import type {
    AttendanceStatus,
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

const actionMessage = ref<string | null>(null);
const actionError = ref<string | null>(null);

const formatDate = (
    value: string | null,
): string => {
    if (!value) {
        return '—';
    }

    const date = new Date(`${value}T00:00:00`);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(date);
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
</script>

<template>
    <div class="student-module container-fluid px-0">
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

        <div
            v-if="actionMessage"
            class="alert alert-success"
        >
            <i class="bi bi-check-circle me-2"></i>
            {{ actionMessage }}
        </div>

        <div
            v-if="actionError"
            class="alert alert-danger"
        >
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ actionError }}
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

                        <div class="record-count">
                            {{ studentData.reservations.length }}
                            record{{
                                studentData.reservations.length === 1
                                    ? ''
                                    : 's'
                            }}
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
        </div>
    </div>
</template>

<style scoped>
.student-module {
    font-size: 0.94rem;
}

.detail-header {
    padding-bottom: 0.25rem;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: #6c757d;
    font-size: 0.86rem;
    font-weight: 500;
    text-decoration: none;
}

.back-link:hover {
    color: #0d6efd;
}

.student-title {
    margin: 0;
    color: #212529;
    font-size: 1.7rem;
    font-weight: 650;
    letter-spacing: -0.025em;
}

.student-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-top: 0.45rem;
    color: #6c757d;
    font-size: 0.87rem;
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

@media (max-width: 767.98px) {
    .student-title {
        font-size: 1.45rem;
    }

    .reservation-table {
        min-width: 680px;
    }
}
</style>