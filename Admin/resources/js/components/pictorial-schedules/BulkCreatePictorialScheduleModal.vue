<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

interface College {
    id: number;
    college_name: string;
}

interface GeneratedSchedule {
    date: string;
    date_label: string;
    start_time: string;
    end_time: string;
    time_label: string;
}

interface BulkPreview {
    year: string;
    total: number;
    conflict_count: number;
    can_create: boolean;
    schedules: GeneratedSchedule[];
    conflicts: GeneratedSchedule[];
}

interface BulkPayload {
    date_from: string;
    date_to: string;

    daily_start_time: string;
    daily_end_time: string;

    slot_duration_minutes: number;

    no_of_slots: number;

    include_saturday: boolean;
    include_sunday: boolean;

    is_delayed: boolean;

    college_id: number | null;

    allowed_college_ids: number[];
}

interface Props {
    colleges: College[];
    activeYear: string;
}

defineProps<Props>();

const emit = defineEmits<{
    close: [];
    created: [message: string];
}>();

const dateFrom = ref('');
const dateTo = ref('');

const dailyStartTime = ref('');
const dailyEndTime = ref('');

const slotDurationMinutes = ref(30);

const noOfSlots = ref(1);

const includeSaturday = ref(false);
const includeSunday = ref(false);

const isDelayed = ref(false);

const collegeId = ref<number | null>(null);

const allowedCollegeIds = ref<number[]>([]);

const preview = ref<BulkPreview | null>(null);

const errors = ref<Record<string, string[]>>({});

const isPreviewing = ref(false);
const isCreating = ref(false);

const canSubmit = computed(() => {
    return (
        preview.value !== null &&
        preview.value.can_create &&
        !isCreating.value
    );
});

const payload = (): BulkPayload => ({
    date_from: dateFrom.value,
    date_to: dateTo.value,

    daily_start_time: dailyStartTime.value,
    daily_end_time: dailyEndTime.value,

    slot_duration_minutes:
        Number(slotDurationMinutes.value),

    no_of_slots:
        Number(noOfSlots.value),

    include_saturday:
        includeSaturday.value,

    include_sunday:
        includeSunday.value,

    is_delayed:
        isDelayed.value,

    college_id:
        isDelayed.value
            ? null
            : collegeId.value,

    allowed_college_ids:
        isDelayed.value
            ? allowedCollegeIds.value
            : [],
});

const csrfToken = (): string => {
    return (
        document
            .querySelector<HTMLMetaElement>(
                'meta[name="csrf-token"]',
            )
            ?.getAttribute('content') ?? ''
    );
};

const handleJsonResponse = async (
    response: Response,
): Promise<any> => {
    const contentType =
        response.headers.get('content-type') ?? '';

    if (!contentType.includes('application/json')) {
        throw new Error(
            `Server returned an unexpected response (${response.status}).`,
        );
    }

    return await response.json();
};

const previewSchedules = async (): Promise<void> => {
    isPreviewing.value = true;
    errors.value = {};
    preview.value = null;

    try {
        const response = await fetch(
            '/settings/pictorial-schedules/bulk/preview',
            {
                method: 'POST',

                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },

                credentials: 'same-origin',

                body: JSON.stringify(
                    payload(),
                ),
            },
        );

        const result =
            await handleJsonResponse(response);

        if (response.status === 422) {
            errors.value =
                result.errors ?? {};

            return;
        }

        if (!response.ok) {
            throw new Error(
                result.message ??
                    'Unable to preview schedules.',
            );
        }

        preview.value = result.data;
    } catch (error) {
        errors.value = {
            schedule: [
                error instanceof Error
                    ? error.message
                    : 'Unable to preview schedules.',
            ],
        };
    } finally {
        isPreviewing.value = false;
    }
};

const createSchedules = async (): Promise<void> => {
    if (!preview.value?.can_create) {
        return;
    }

    isCreating.value = true;
    errors.value = {};

    try {
        const response = await fetch(
            '/settings/pictorial-schedules/bulk',
            {
                method: 'POST',

                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },

                credentials: 'same-origin',

                body: JSON.stringify(
                    payload(),
                ),
            },
        );

        const result =
            await handleJsonResponse(response);

        if (response.status === 422) {
            errors.value =
                result.errors ?? {};

            preview.value = null;

            return;
        }

        if (!response.ok) {
            throw new Error(
                result.message ??
                    'Unable to create schedules.',
            );
        }

        emit(
            'created',
            result.message ??
                'Pictorial schedules created successfully.',
        );
    } catch (error) {
        errors.value = {
            schedule: [
                error instanceof Error
                    ? error.message
                    : 'Unable to create schedules.',
            ],
        };
    } finally {
        isCreating.value = false;
    }
};

const resetPreview = (): void => {
    preview.value = null;
};

const toggleCollege = (
    id: number,
): void => {
    resetPreview();

    if (
        allowedCollegeIds.value.includes(id)
    ) {
        allowedCollegeIds.value =
            allowedCollegeIds.value.filter(
                (collegeId) =>
                    collegeId !== id,
            );

        return;
    }

    allowedCollegeIds.value = [
        ...allowedCollegeIds.value,
        id,
    ];
};

const selectAllColleges = (
    colleges: College[],
): void => {
    resetPreview();

    allowedCollegeIds.value =
        colleges.map(
            (college) => college.id,
        );
};

const clearColleges = (): void => {
    resetPreview();
    allowedCollegeIds.value = [];
};

onMounted(() => {
    document.body.classList.add(
        'modal-open',
    );
});

onBeforeUnmount(() => {
    document.body.classList.remove(
        'modal-open',
    );
});
</script>

<template>
    <Teleport to="body">
        <div
            class="modal fade show d-block"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            @mousedown.self="
                !isCreating &&
                emit('close')
            "
        >
            <div
                class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"
            >
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">
                                Bulk Create Pictorial Schedules
                            </h5>

                            <div class="text-muted small mt-1">
                                Generate multiple schedules for
                                CYB {{ activeYear }}.
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            :disabled="
                                isPreviewing ||
                                isCreating
                            "
                            @click="emit('close')"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <div
                            v-if="errors.schedule"
                            class="cyb-notice cyb-notice-danger rounded-3 mb-4"
                        >
                            <i class="bi bi-exclamation-circle"></i>

                            <div>
                                {{ errors.schedule[0] }}
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="cyb-form-label">
                                    Schedule Type
                                </label>

                                <div class="d-flex gap-3 flex-wrap">
                                    <div class="form-check">
                                        <input
                                            id="bulk-regular"
                                            v-model="isDelayed"
                                            class="form-check-input"
                                            type="radio"
                                            :value="false"
                                            @change="resetPreview"
                                        >

                                        <label
                                            for="bulk-regular"
                                            class="form-check-label"
                                        >
                                            Regular
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input
                                            id="bulk-delayed"
                                            v-model="isDelayed"
                                            class="form-check-input"
                                            type="radio"
                                            :value="true"
                                            @change="resetPreview"
                                        >

                                        <label
                                            for="bulk-delayed"
                                            class="form-check-label"
                                        >
                                            Delayed Pictorial
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="!isDelayed"
                                class="col-12"
                            >
                                <label class="cyb-form-label">
                                    College
                                </label>

                                <select
                                    v-model="collegeId"
                                    class="form-select cyb-form-control"
                                    @change="resetPreview"
                                >
                                    <option :value="null">
                                        Select college
                                    </option>

                                    <option
                                        v-for="college in colleges"
                                        :key="college.id"
                                        :value="college.id"
                                    >
                                        {{ college.college_name }}
                                    </option>
                                </select>

                                <div
                                    v-if="errors.college_id"
                                    class="text-danger small mt-1"
                                >
                                    {{ errors.college_id[0] }}
                                </div>
                            </div>

                            <div
                                v-else
                                class="col-12"
                            >
                                <div
                                    class="d-flex align-items-center justify-content-between gap-2 mb-2"
                                >
                                    <label class="cyb-form-label mb-0">
                                        Allowed Colleges
                                    </label>

                                    <div class="d-flex gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-light border"
                                            @click="
                                                selectAllColleges(
                                                    colleges,
                                                )
                                            "
                                        >
                                            Select All
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-light border"
                                            @click="clearColleges"
                                        >
                                            Clear
                                        </button>
                                    </div>
                                </div>

                                <div class="bulk-college-grid">
                                    <label
                                        v-for="college in colleges"
                                        :key="college.id"
                                        class="bulk-college-option"
                                    >
                                        <input
                                            type="checkbox"
                                            class="form-check-input"
                                            :checked="
                                                allowedCollegeIds.includes(
                                                    college.id,
                                                )
                                            "
                                            @change="
                                                toggleCollege(
                                                    college.id,
                                                )
                                            "
                                        >

                                        {{ college.college_name }}
                                    </label>
                                </div>

                                <div
                                    v-if="
                                        errors.allowed_college_ids
                                    "
                                    class="text-danger small mt-2"
                                >
                                    {{
                                        errors
                                            .allowed_college_ids[0]
                                    }}
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="cyb-form-label">
                                    Start Date
                                </label>

                                <input
                                    v-model="dateFrom"
                                    type="date"
                                    class="form-control cyb-form-control"
                                    @change="resetPreview"
                                >
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="cyb-form-label">
                                    End Date
                                </label>

                                <input
                                    v-model="dateTo"
                                    type="date"
                                    class="form-control cyb-form-control"
                                    @change="resetPreview"
                                >
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="cyb-form-label">
                                    Daily Start Time
                                </label>

                                <input
                                    v-model="dailyStartTime"
                                    type="time"
                                    class="form-control cyb-form-control"
                                    @change="resetPreview"
                                >
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="cyb-form-label">
                                    Daily End Time
                                </label>

                                <input
                                    v-model="dailyEndTime"
                                    type="time"
                                    class="form-control cyb-form-control"
                                    @change="resetPreview"
                                >
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="cyb-form-label">
                                    Slot Duration
                                </label>

                                <div class="input-group">
                                    <input
                                        v-model.number="
                                            slotDurationMinutes
                                        "
                                        type="number"
                                        min="5"
                                        max="480"
                                        class="form-control cyb-form-control"
                                        @input="resetPreview"
                                    >

                                    <span class="input-group-text">
                                        minutes
                                    </span>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="cyb-form-label">
                                    Capacity Per Slot
                                </label>

                                <input
                                    v-model.number="noOfSlots"
                                    type="number"
                                    min="1"
                                    max="500"
                                    class="form-control cyb-form-control"
                                    @input="resetPreview"
                                >
                            </div>

                            <div class="col-12 col-md-8">
                                <label class="cyb-form-label">
                                    Weekend Scheduling
                                </label>

                                <div
                                    class="d-flex flex-wrap gap-4 pt-2"
                                >
                                    <div class="form-check">
                                        <input
                                            id="include-saturday"
                                            v-model="includeSaturday"
                                            class="form-check-input"
                                            type="checkbox"
                                            @change="resetPreview"
                                        >

                                        <label
                                            for="include-saturday"
                                            class="form-check-label"
                                        >
                                            Include Saturdays
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input
                                            id="include-sunday"
                                            v-model="includeSunday"
                                            class="form-check-input"
                                            type="checkbox"
                                            @change="resetPreview"
                                        >

                                        <label
                                            for="include-sunday"
                                            class="form-check-label"
                                        >
                                            Include Sundays
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="cyb-form-actions mt-4">
                            <button
                                type="button"
                                class="btn btn-primary"
                                :disabled="
                                    isPreviewing ||
                                    isCreating
                                "
                                @click="previewSchedules"
                            >
                                <span
                                    v-if="isPreviewing"
                                    class="spinner-border spinner-border-sm me-2"
                                ></span>

                                <i
                                    v-else
                                    class="bi bi-eye me-2"
                                ></i>

                                {{
                                    isPreviewing
                                        ? 'Generating Preview...'
                                        : 'Preview Schedules'
                                }}
                            </button>
                        </div>

                        <div
                            v-if="preview"
                            class="mt-4"
                        >
                            <div
                                class="cyb-card preview-card"
                            >
                                <div class="cyb-card-header">
                                    <div>
                                        <h6 class="cyb-section-title">
                                            Bulk Preview
                                        </h6>

                                        <p class="cyb-section-description">
                                            Review the generated schedules before creating them.
                                        </p>
                                    </div>

                                    <span
                                        class="cyb-pill"
                                        :class="
                                            preview.can_create
                                                ? 'cyb-pill-success'
                                                : 'cyb-pill-danger'
                                        "
                                    >
                                        {{ preview.total }}
                                        schedules
                                    </span>
                                </div>

                                <div class="cyb-card-body">
                                    <div class="row g-3">
                                        <div class="col-6 col-md-3">
                                            <div class="preview-stat">
                                                <strong>
                                                    {{ preview.total }}
                                                </strong>

                                                <span>
                                                    Generated
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-6 col-md-3">
                                            <div class="preview-stat">
                                                <strong>
                                                    {{
                                                        preview.conflict_count
                                                    }}
                                                </strong>

                                                <span>
                                                    Conflicts
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="
                                            preview.conflict_count > 0
                                        "
                                        class="cyb-notice cyb-notice-danger rounded-3 mt-4"
                                    >
                                        <i class="bi bi-exclamation-triangle"></i>

                                        <div>
                                            <strong>
                                                Bulk creation is blocked.
                                            </strong>

                                            <div class="mt-1">
                                                Adjust the dates or times so the generated schedules do not overlap existing schedules.
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="table-responsive mt-4 bulk-preview-table"
                                    >
                                        <table class="table cyb-table">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <tr
                                                    v-for="schedule in preview.schedules"
                                                    :key="
                                                        `${schedule.date}-${schedule.start_time}`
                                                    "
                                                >
                                                    <td>
                                                        {{
                                                            schedule.date_label
                                                        }}
                                                    </td>

                                                    <td>
                                                        {{
                                                            schedule.time_label
                                                        }}
                                                    </td>

                                                    <td>
                                                        <span
                                                            v-if="
                                                                preview.conflicts.some(
                                                                    (
                                                                        conflict,
                                                                    ) =>
                                                                        conflict.date ===
                                                                            schedule.date &&
                                                                        conflict.start_time ===
                                                                            schedule.start_time,
                                                                )
                                                            "
                                                            class="cyb-pill cyb-pill-danger"
                                                        >
                                                            Conflict
                                                        </span>

                                                        <span
                                                            v-else
                                                            class="cyb-pill cyb-pill-success"
                                                        >
                                                            Ready
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

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-light border"
                            :disabled="isCreating"
                            @click="emit('close')"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            class="btn btn-primary"
                            :disabled="!canSubmit"
                            @click="createSchedules"
                        >
                            <span
                                v-if="isCreating"
                                class="spinner-border spinner-border-sm me-2"
                            ></span>

                            <i
                                v-else
                                class="bi bi-plus-lg me-2"
                            ></i>

                            {{
                                isCreating
                                    ? 'Creating...'
                                    : `Create ${preview?.total ?? 0} Schedules`
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-backdrop fade show"></div>
    </Teleport>
</template>

<style scoped>
.bulk-college-grid {
    display: grid;
    max-height: 220px;
    grid-template-columns:
        repeat(2, minmax(0, 1fr));
    gap: 0.5rem;
    overflow-y: auto;
    padding: 0.75rem;
    border: 1px solid #dfe3e7;
    border-radius: 0.65rem;
}

.bulk-college-option {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.55rem 0.65rem;
    border-radius: 0.5rem;
    cursor: pointer;
}

.bulk-college-option:hover {
    background: #f8f9fa;
}

.preview-stat {
    display: flex;
    flex-direction: column;
}

.preview-stat strong {
    font-size: 1.4rem;
    line-height: 1.2;
}

.preview-stat span {
    margin-top: 0.15rem;
    color: #6c757d;
    font-size: 0.75rem;
}

.bulk-preview-table {
    max-height: 330px;
}

@media (max-width: 575.98px) {
    .bulk-college-grid {
        grid-template-columns: 1fr;
    }
}
</style>