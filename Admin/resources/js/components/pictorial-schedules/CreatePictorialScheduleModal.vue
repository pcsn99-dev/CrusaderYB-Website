<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

interface College {
    id: number;
    college_name: string;
}

interface Props {
    colleges: College[];
    activeYear: string;
    saving: boolean;
    errors: Record<string, string[]>;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    close: [];
    save: [
        payload: {
            date: string;
            start_time: string;
            end_time: string;
            no_of_slots: number;
            is_delayed: boolean;
            college_id: number | null;
            allowed_college_ids: number[];
        },
    ];
}>();

const date = ref('');
const startTime = ref('');
const endTime = ref('');
const noOfSlots = ref(1);

const isDelayed = ref(false);

const collegeId = ref<number | null>(null);
const allowedCollegeIds = ref<number[]>([]);

const title = computed(() =>
    isDelayed.value
        ? 'Create Delayed Pictorial Schedule'
        : 'Create Pictorial Schedule',
);

const submit = (): void => {
    emit('save', {
        date: date.value,
        start_time: startTime.value,
        end_time: endTime.value,
        no_of_slots: Number(noOfSlots.value),
        is_delayed: isDelayed.value,

        college_id: isDelayed.value
            ? null
            : collegeId.value,

        allowed_college_ids: isDelayed.value
            ? allowedCollegeIds.value
            : [],
    });
};

const toggleCollege = (collegeId: number): void => {
    if (allowedCollegeIds.value.includes(collegeId)) {
        allowedCollegeIds.value =
            allowedCollegeIds.value.filter(
                (id) => id !== collegeId,
            );

        return;
    }

    allowedCollegeIds.value = [
        ...allowedCollegeIds.value,
        collegeId,
    ];
};

const selectAllColleges = (): void => {
    allowedCollegeIds.value =
        props.colleges.map((college) => college.id);
};

const clearColleges = (): void => {
    allowedCollegeIds.value = [];
};

onMounted(() => {
    document.body.classList.add('modal-open');
});

onBeforeUnmount(() => {
    document.body.classList.remove('modal-open');
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
                !saving && emit('close')
            "
        >
            <div
                class="modal-dialog modal-lg modal-dialog-centered"
            >
                <div class="modal-content">
                    <form @submit.prevent="submit">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">
                                    {{ title }}
                                </h5>

                                <div class="text-muted small mt-1">
                                    This schedule will be created for
                                    CYB {{ activeYear }}.
                                </div>
                            </div>

                            <button
                                type="button"
                                class="btn-close"
                                :disabled="saving"
                                aria-label="Close"
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

                                    <div class="d-flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            class="schedule-type-option"
                                            :class="{
                                                'schedule-type-option-active':
                                                    !isDelayed,
                                            }"
                                            @click="isDelayed = false"
                                        >
                                            <i class="bi bi-building"></i>

                                            <span>
                                                <strong>Regular</strong>

                                                <small>
                                                    Available to one college
                                                </small>
                                            </span>
                                        </button>

                                        <button
                                            type="button"
                                            class="schedule-type-option"
                                            :class="{
                                                'schedule-type-option-active':
                                                    isDelayed,
                                            }"
                                            @click="isDelayed = true"
                                        >
                                            <i class="bi bi-clock-history"></i>

                                            <span>
                                                <strong>Delayed</strong>

                                                <small>
                                                    Open to selected colleges
                                                </small>
                                            </span>
                                        </button>
                                    </div>
                                </div>

                                <div
                                    v-if="!isDelayed"
                                    class="col-12"
                                >
                                    <label
                                        for="schedule-college"
                                        class="cyb-form-label"
                                    >
                                        College
                                    </label>

                                    <select
                                        id="schedule-college"
                                        v-model="collegeId"
                                        class="form-select cyb-form-control"
                                        :class="{
                                            'is-invalid':
                                                errors.college_id,
                                        }"
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
                                        class="invalid-feedback"
                                    >
                                        {{ errors.college_id[0] }}
                                    </div>
                                </div>

                                <div
                                    v-else
                                    class="col-12"
                                >
                                    <div
                                        class="d-flex align-items-center justify-content-between gap-3 mb-2"
                                    >
                                        <label class="cyb-form-label mb-0">
                                            Allowed Colleges
                                        </label>

                                        <div class="d-flex gap-2">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-light border"
                                                @click="selectAllColleges"
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

                                    <div
                                        class="college-selection"
                                        :class="{
                                            'college-selection-invalid':
                                                errors.allowed_college_ids,
                                        }"
                                    >
                                        <label
                                            v-for="college in colleges"
                                            :key="college.id"
                                            class="college-option"
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

                                            <span>
                                                {{ college.college_name }}
                                            </span>
                                        </label>
                                    </div>

                                    <div
                                        v-if="errors.allowed_college_ids"
                                        class="text-danger small mt-2"
                                    >
                                        {{
                                            errors
                                                .allowed_college_ids[0]
                                        }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label
                                        for="schedule-date"
                                        class="cyb-form-label"
                                    >
                                        Date
                                    </label>

                                    <input
                                        id="schedule-date"
                                        v-model="date"
                                        type="date"
                                        class="form-control cyb-form-control"
                                        :class="{
                                            'is-invalid': errors.date,
                                        }"
                                        required
                                    >

                                    <div
                                        v-if="errors.date"
                                        class="invalid-feedback"
                                    >
                                        {{ errors.date[0] }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label
                                        for="schedule-capacity"
                                        class="cyb-form-label"
                                    >
                                        Capacity
                                    </label>

                                    <input
                                        id="schedule-capacity"
                                        v-model.number="noOfSlots"
                                        type="number"
                                        min="1"
                                        max="500"
                                        class="form-control cyb-form-control"
                                        :class="{
                                            'is-invalid':
                                                errors.no_of_slots,
                                        }"
                                        required
                                    >

                                    <div
                                        v-if="errors.no_of_slots"
                                        class="invalid-feedback"
                                    >
                                        {{ errors.no_of_slots[0] }}
                                    </div>

                                    <div class="cyb-form-help">
                                        Maximum number of students who can reserve this schedule.
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label
                                        for="schedule-start"
                                        class="cyb-form-label"
                                    >
                                        Start Time
                                    </label>

                                    <input
                                        id="schedule-start"
                                        v-model="startTime"
                                        type="time"
                                        class="form-control cyb-form-control"
                                        :class="{
                                            'is-invalid':
                                                errors.start_time,
                                        }"
                                        required
                                    >

                                    <div
                                        v-if="errors.start_time"
                                        class="invalid-feedback"
                                    >
                                        {{ errors.start_time[0] }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label
                                        for="schedule-end"
                                        class="cyb-form-label"
                                    >
                                        End Time
                                    </label>

                                    <input
                                        id="schedule-end"
                                        v-model="endTime"
                                        type="time"
                                        class="form-control cyb-form-control"
                                        :class="{
                                            'is-invalid':
                                                errors.end_time,
                                        }"
                                        required
                                    >

                                    <div
                                        v-if="errors.end_time"
                                        class="invalid-feedback"
                                    >
                                        {{ errors.end_time[0] }}
                                    </div>
                                </div>
                            </div>

                            <div
                                class="cyb-notice cyb-notice-info rounded-3 mt-4"
                            >
                                <i class="bi bi-info-circle"></i>

                                <div>
                                    The system will prevent overlapping
                                    schedules that are available to the
                                    same college.
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-light border"
                                :disabled="saving"
                                @click="emit('close')"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="saving"
                            >
                                <span
                                    v-if="saving"
                                    class="spinner-border spinner-border-sm me-2"
                                ></span>

                                <i
                                    v-else
                                    class="bi bi-plus-lg me-2"
                                ></i>

                                {{
                                    saving
                                        ? 'Creating...'
                                        : 'Create Schedule'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal-backdrop fade show"></div>
    </Teleport>
</template>

<style scoped>
.schedule-type-option {
    display: flex;
    flex: 1 1 240px;
    align-items: center;
    gap: 0.8rem;
    min-height: 72px;
    padding: 0.9rem 1rem;
    border: 1px solid #dfe3e7;
    border-radius: 0.7rem;
    background: #fff;
    color: #343a40;
    text-align: left;
}

.schedule-type-option:hover {
    background: #f8f9fa;
}

.schedule-type-option-active {
    border-color: #86a9d1;
    background: #f1f6fb;
}

.schedule-type-option > i {
    font-size: 1.2rem;
}

.schedule-type-option span {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.schedule-type-option small {
    color: #6c757d;
    font-size: 0.75rem;
}

.college-selection {
    display: grid;
    max-height: 240px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.5rem;
    overflow-y: auto;
    padding: 0.75rem;
    border: 1px solid #dfe3e7;
    border-radius: 0.65rem;
}

.college-selection-invalid {
    border-color: #dc3545;
}

.college-option {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    margin: 0;
    padding: 0.55rem 0.65rem;
    border-radius: 0.5rem;
    cursor: pointer;
}

.college-option:hover {
    background: #f8f9fa;
}

@media (max-width: 575.98px) {
    .college-selection {
        grid-template-columns: 1fr;
    }
}
</style>