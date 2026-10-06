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

const attendanceBadge = (
    status: AttendanceStatus,
): string => {
    switch (status) {
        case 'present':
            return 'text-bg-success';

        case 'absent':
            return 'text-bg-danger';

        case 'late':
            return 'text-bg-warning';

        default:
            return 'text-bg-secondary';
    }
};

const getCsrfToken = (): string => {
    const element = document.querySelector<HTMLMetaElement>(
        'meta[name="csrf-token"]',
    );

    return element?.content ?? '';
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
                'The server updated the record but returned an invalid response.',
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
                'The server updated the record but returned an invalid response.',
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


</script>

<template>
    <div class="container-fluid">
        <div
            class="d-flex flex-column flex-md-row
                   justify-content-between
                   align-items-md-start gap-3 mb-4"
        >
            <div>
                <a
                    href="/student-accounts"
                    class="text-decoration-none"
                >
                    <i class="bi bi-arrow-left me-1"></i>
                    Student Accounts
                </a>

                <h1 class="h3 mt-2 mb-1">
                    {{ studentData.full_name }}
                </h1>

                <div class="text-muted">
                    {{ studentData.university_id }}
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <span
                    v-if="studentData.is_subscribe"
                    class="badge text-bg-success fs-6"
                >
                    Subscribed
                </span>

                <span
                    v-else
                    class="badge text-bg-secondary fs-6"
                >
                    Not Subscribed
                </span>

                <span
                    v-if="studentData.is_third_party"
                    class="badge text-bg-info fs-6"
                >
                    Third-Party Photo
                </span>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0">
                            Basic Information
                        </h2>
                    </div>

                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">
                                Student ID
                            </dt>

                            <dd class="col-sm-7">
                                {{ studentData.university_id || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                SLMIS ID
                            </dt>

                            <dd class="col-sm-7">
                                {{ studentData.slmis_id ?? '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Full Name
                            </dt>

                            <dd class="col-sm-7">
                                {{ studentData.full_name }}
                            </dd>

                            <dt class="col-sm-5">
                                Email
                            </dt>

                            <dd class="col-sm-7">
                                {{ studentData.email || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Contact Number
                            </dt>

                            <dd class="col-sm-7">
                                {{ studentData.contact_number || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Current Address
                            </dt>

                            <dd class="col-sm-7">
                                {{ studentData.current_address || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Permanent Address
                            </dt>

                            <dd class="col-sm-7">
                                {{ studentData.permanent_address || '—' }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0">
                            Academic Information
                        </h2>
                    </div>

                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">
                                College
                            </dt>

                            <dd class="col-sm-7">
                                {{ studentData.college || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Program
                            </dt>

                            <dd class="col-sm-7">
                                {{ studentData.program || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Major
                            </dt>

                            <dd class="col-sm-7">
                                {{ studentData.major || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Graduation Year
                            </dt>

                            <dd class="col-sm-7">
                                {{ studentData.graduation_year || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Expected Graduation
                            </dt>

                            <dd class="col-sm-7">
                                {{
                                    studentData.expected_graduation_date
                                        || '—'
                                }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0">
                            CYB Status
                        </h2>
                    </div>

                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="text-muted small">
                                    Subscription
                                </div>

                                <div class="fw-semibold mt-1">
                                    {{
                                        studentData.is_subscribe
                                            ? 'Subscribed'
                                            : 'Not Subscribed'
                                    }}
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="text-muted small">
                                    Subscription Date
                                </div>

                                <div class="fw-semibold mt-1">
                                    {{
                                        formatDate(
                                            studentData.subscribe_date,
                                        )
                                    }}
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="text-muted small">
                                    Unsubscribe Date
                                </div>

                                <div class="fw-semibold mt-1">
                                    {{
                                        formatDate(
                                            studentData.unsubscribe_date,
                                        )
                                    }}
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="text-muted small">
                                    Contract Agreement
                                </div>

                                <div class="fw-semibold mt-1">
                                    {{
                                        studentData.is_agree_contract
                                            ? 'Agreed'
                                            : 'Not Agreed'
                                    }}
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="text-muted small">
                                    Photo Source
                                </div>

                                <div class="fw-semibold mt-1">
                                    {{
                                        studentData.is_third_party
                                            ? 'Third-Party Photo'
                                            : 'CYB Pictorial'
                                    }}
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="text-muted small">
                                    Picture Claimed
                                </div>

                                <div class="fw-semibold mt-1">
                                    {{
                                        studentData.claim_pic
                                            ? 'Yes'
                                            : 'No'
                                    }}
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="text-muted small">
                                    Picture Claim Date
                                </div>

                                <div class="fw-semibold mt-1">
                                    {{
                                        formatDate(
                                            studentData.claim_pic_date,
                                        )
                                    }}
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
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0">
                            Admin Actions
                        </h2>

                        <small class="text-muted">
                            Changes made here are recorded in the audit log.
                        </small>
                    </div>

                    <div class="card-body">
                        <div
                            v-if="actionMessage"
                            class="alert alert-success"
                        >
                            {{ actionMessage }}
                        </div>

                        <div
                            v-if="actionError"
                            class="alert alert-danger"
                        >
                            {{ actionError }}
                        </div>

                        <div class="row g-4">
                            <div
                                v-if="
                                    studentData.permissions
                                        .manage_subscription
                                "
                                class="col-12 col-lg-6"
                            >
                                <div class="border rounded p-3 h-100">
                                    <div
                                        class="d-flex
                                            justify-content-between
                                            align-items-start
                                            gap-3"
                                    >
                                        <div>
                                            <h3 class="h6 mb-1">
                                                Subscription Status
                                            </h3>

                                            <p class="text-muted small mb-0">
                                                Controls whether the student
                                                is currently subscribed to CYB.
                                            </p>
                                        </div>

                                        <span
                                            class="badge"
                                            :class="
                                                studentData.is_subscribe
                                                    ? 'text-bg-success'
                                                    : 'text-bg-secondary'
                                            "
                                        >
                                            {{
                                                studentData.is_subscribe
                                                    ? 'Subscribed'
                                                    : 'Not Subscribed'
                                            }}
                                        </span>
                                    </div>

                                    <div class="mt-3">
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
                                            <span
                                                v-if="
                                                    isUpdatingSubscription
                                                "
                                                class="
                                                    spinner-border
                                                    spinner-border-sm
                                                    me-1
                                                "
                                            ></span>

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
                                            <span
                                                v-if="
                                                    isUpdatingSubscription
                                                "
                                                class="
                                                    spinner-border
                                                    spinner-border-sm
                                                    me-1
                                                "
                                            ></span>

                                            Unsubscribe Student
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="
                                    studentData.permissions
                                        .manage_third_party
                                "
                                class="col-12 col-lg-6"
                            >
                                <div class="border rounded p-3 h-100">
                                    <div
                                        class="d-flex
                                            justify-content-between
                                            align-items-start
                                            gap-3"
                                    >
                                        <div>
                                            <h3 class="h6 mb-1">
                                                Photo Source
                                            </h3>

                                            <p class="text-muted small mb-0">
                                                Identify students who will
                                                provide a photo outside the
                                                CYB pictorial process.
                                            </p>
                                        </div>

                                        <span
                                            class="badge"
                                            :class="
                                                studentData.is_third_party
                                                    ? 'text-bg-info'
                                                    : 'text-bg-primary'
                                            "
                                        >
                                            {{
                                                studentData.is_third_party
                                                    ? 'Third-Party'
                                                    : 'CYB'
                                            }}
                                        </span>
                                    </div>

                                    <div class="mt-3">
                                        <button
                                            v-if="
                                                !studentData.is_third_party
                                            "
                                            type="button"
                                            class="btn btn-info"
                                            :disabled="
                                                isUpdatingThirdParty
                                            "
                                            @click="
                                                updateThirdParty(true)
                                            "
                                        >
                                            <span
                                                v-if="
                                                    isUpdatingThirdParty
                                                "
                                                class="
                                                    spinner-border
                                                    spinner-border-sm
                                                    me-1
                                                "
                                            ></span>

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
                                            <span
                                                v-if="
                                                    isUpdatingThirdParty
                                                "
                                                class="
                                                    spinner-border
                                                    spinner-border-sm
                                                    me-1
                                                "
                                            ></span>

                                            Remove Third-Party Status
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-12">
                <div class="card shadow-sm">
                    <div
                        class="card-header bg-white
                               d-flex justify-content-between
                               align-items-center"
                    >
                        <div>
                            <h2 class="h5 mb-0">
                                Pictorial Reservations
                            </h2>

                            <small class="text-muted">
                                Reservation and attendance history
                            </small>
                        </div>

                        <span
                            class="badge text-bg-light"
                        >
                            {{ studentData.reservations.length }}
                            reservation{{
                                studentData.reservations.length === 1
                                    ? ''
                                    : 's'
                            }}
                        </span>
                    </div>

                    <div
                        v-if="studentData.reservations.length === 0"
                        class="card-body py-5 text-center"
                    >
                        <i
                            class="bi bi-calendar-x
                                   fs-1 text-muted"
                        ></i>

                        <h3 class="h6 mt-3">
                            No pictorial reservation
                        </h3>

                        <p class="text-muted mb-0">
                            This student does not currently have
                            any reservation records.
                        </p>
                    </div>

                    <div
                        v-else
                        class="table-responsive"
                    >
                        <table
                            class="table table-hover
                                   align-middle mb-0"
                        >
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
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
                                        {{
                                            reservation.pictorial
                                                ? formatDate(
                                                    reservation
                                                        .pictorial
                                                        .date,
                                                )
                                                : '—'
                                        }}
                                    </td>

                                    <td class="text-nowrap">
                                        <template
                                            v-if="
                                                reservation.pictorial
                                            "
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
                                        </template>

                                        <template v-else>
                                            —
                                        </template>
                                    </td>

                                    <td>
                                        <span
                                            v-if="
                                                reservation
                                                    .is_rescheduled
                                            "
                                            class="badge
                                                   text-bg-secondary"
                                        >
                                            Rescheduled
                                        </span>

                                        <span
                                            v-else
                                            class="badge
                                                   text-bg-primary"
                                        >
                                            Current
                                        </span>
                                    </td>

                                    <td>
                                        <span
                                            class="badge"
                                            :class="
                                                attendanceBadge(
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
                                        {{
                                            formatDate(
                                                reservation
                                                    .reschedule_date,
                                            )
                                        }}
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