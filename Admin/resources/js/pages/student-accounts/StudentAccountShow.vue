<script setup lang="ts">
import type {
    AttendanceStatus,
    StudentAccountDetail,
} from '@/types/student-account';

interface Props {
    student: StudentAccountDetail;
}

defineProps<Props>();

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
                    {{ student.full_name }}
                </h1>

                <div class="text-muted">
                    {{ student.university_id }}
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <span
                    v-if="student.is_subscribe"
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
                    v-if="student.is_third_party"
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
                                {{ student.university_id || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                SLMIS ID
                            </dt>

                            <dd class="col-sm-7">
                                {{ student.slmis_id ?? '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Full Name
                            </dt>

                            <dd class="col-sm-7">
                                {{ student.full_name }}
                            </dd>

                            <dt class="col-sm-5">
                                Email
                            </dt>

                            <dd class="col-sm-7">
                                {{ student.email || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Contact Number
                            </dt>

                            <dd class="col-sm-7">
                                {{ student.contact_number || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Current Address
                            </dt>

                            <dd class="col-sm-7">
                                {{ student.current_address || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Permanent Address
                            </dt>

                            <dd class="col-sm-7">
                                {{ student.permanent_address || '—' }}
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
                                {{ student.college || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Program
                            </dt>

                            <dd class="col-sm-7">
                                {{ student.program || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Major
                            </dt>

                            <dd class="col-sm-7">
                                {{ student.major || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Graduation Year
                            </dt>

                            <dd class="col-sm-7">
                                {{ student.graduation_year || '—' }}
                            </dd>

                            <dt class="col-sm-5">
                                Expected Graduation
                            </dt>

                            <dd class="col-sm-7">
                                {{
                                    student.expected_graduation_date
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
                                        student.is_subscribe
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
                                            student.subscribe_date,
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
                                            student.unsubscribe_date,
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
                                        student.is_agree_contract
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
                                        student.is_third_party
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
                                        student.claim_pic
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
                                            student.claim_pic_date,
                                        )
                                    }}
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
                            {{ student.reservations.length }}
                            reservation{{
                                student.reservations.length === 1
                                    ? ''
                                    : 's'
                            }}
                        </span>
                    </div>

                    <div
                        v-if="student.reservations.length === 0"
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
                                        in student.reservations"
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