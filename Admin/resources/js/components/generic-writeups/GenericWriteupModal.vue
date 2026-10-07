<template>
    <Teleport to="body">
        <template v-if="visible">
            <div
                class="modal fade show d-block generic-writeup-modal"
                tabindex="-1"
                role="dialog"
                aria-modal="true"
                @mousedown.self="$emit('close')"
            >
                <div
                    class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"
                >
                    <form
                        class="modal-content generic-writeup-modal-content"
                        @submit.prevent="$emit('submit')"
                    >
                        <!-- Header -->
                        <div class="modal-header generic-writeup-modal-header">
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <span class="cyb-pill cyb-pill-primary">
                                        <i class="bi bi-card-text"></i>
                                        Generic Writeup
                                    </span>
                                </div>

                                <h5 class="modal-title">
                                    {{ title }}
                                </h5>

                                <p class="generic-writeup-modal-description">
                                    Create or update reusable content for a specific
                                    school year and college.
                                </p>
                            </div>

                            <button
                                type="button"
                                class="btn-close"
                                aria-label="Close"
                                @click="$emit('close')"
                            ></button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body generic-writeup-modal-body">
                            <div class="row g-4">

                                <!-- School Year -->
                                <div class="col-12 col-md-6">
                                    <label
                                        for="generic_writeup_year"
                                        class="cyb-form-label"
                                    >
                                        School Year
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        id="generic_writeup_year"
                                        class="form-select cyb-form-control"
                                        :value="form.year"
                                        required
                                        @change="updateYear"
                                    >
                                        <option value="">
                                            Select Year
                                        </option>

                                        <option
                                            v-for="year in years"
                                            :key="year"
                                            :value="String(year)"
                                        >
                                            {{ year }}
                                        </option>
                                    </select>
                                </div>

                                <!-- College -->
                                <div class="col-12 col-md-6">
                                    <label
                                        for="generic_writeup_college"
                                        class="cyb-form-label"
                                    >
                                        College
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        id="generic_writeup_college"
                                        class="form-select cyb-form-control"
                                        :value="form.college_id"
                                        required
                                        @change="updateCollege"
                                    >
                                        <option value="">
                                            Select College
                                        </option>

                                        <option
                                            v-for="college in colleges"
                                            :key="college.id"
                                            :value="String(college.id)"
                                        >
                                            {{ college.college_name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Selected Context -->
                                <div
                                    v-if="selectedYear || selectedCollegeName"
                                    class="col-12"
                                >
                                    <div class="generic-writeup-context">
                                        <span
                                            v-if="selectedYear"
                                            class="cyb-pill cyb-pill-primary"
                                        >
                                            <i class="bi bi-calendar3"></i>
                                            SY {{ selectedYear }}
                                        </span>

                                        <span
                                            v-if="selectedCollegeName"
                                            class="cyb-pill cyb-pill-info generic-writeup-context-college"
                                            :title="selectedCollegeName"
                                        >
                                            <i class="bi bi-building"></i>

                                            <span>
                                                {{ selectedCollegeName }}
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="col-12">
                                    <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                                        <label
                                            for="generic_writeup_content"
                                            class="cyb-form-label mb-0"
                                        >
                                            Writeup Content
                                            <span class="text-danger">*</span>
                                        </label>

                                        <span
                                            class="generic-writeup-character-count"
                                            :class="{
                                                'text-danger': characterCount >= maxCharacters,
                                                'text-warning': isNearLimit && characterCount < maxCharacters,
                                                'text-muted': !isNearLimit,
                                            }"
                                        >
                                            {{ characterCount }} / {{ maxCharacters }}
                                        </span>
                                    </div>

                                    <textarea
                                        id="generic_writeup_content"
                                        rows="7"
                                        :maxlength="maxCharacters"
                                        class="form-control cyb-form-control generic-writeup-modal-textarea"
                                        :value="form.content"
                                        placeholder="Enter the generic writeup here..."
                                        required
                                        @input="updateContent"
                                    ></textarea>

                                    <div
                                        class="generic-writeup-content-notice"
                                        :class="noticeStateClass"
                                    >
                                        <i
                                            class="bi"
                                            :class="contentNoticeIcon"
                                        ></i>

                                        <span>
                                            {{ contentNotice }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Active Status -->
                                <div class="col-12">
                                    <div class="generic-writeup-active">
                                        <div class="form-check generic-writeup-active-check">
                                            <input
                                                id="generic_writeup_active"
                                                type="checkbox"
                                                class="form-check-input"
                                                :checked="form.is_active"
                                                @change="updateActive"
                                            >

                                            <label
                                                for="generic_writeup_active"
                                                class="form-check-label"
                                            >
                                                <span class="generic-writeup-active-title">
                                                    Active
                                                </span>

                                                <span class="generic-writeup-active-description">
                                                    Active generic writeups are available
                                                    when assigning writeups to students.
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer generic-writeup-modal-footer">
                            <button
                                type="button"
                                class="btn btn-light border"
                                :disabled="isSaving"
                                @click="$emit('close')"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="isSaving || !canSubmit"
                            >
                                <i
                                    class="bi me-1"
                                    :class="isSaving
                                        ? 'bi-arrow-repeat generic-writeup-saving-icon'
                                        : 'bi-check2-circle'"
                                ></i>

                                {{ isSaving ? 'Saving...' : buttonText }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div
                class="modal-backdrop fade show generic-writeup-backdrop"
            ></div>
        </template>
    </Teleport>
</template>

<script setup lang="ts">
import {
    computed,
    onBeforeUnmount,
    watch,
} from 'vue';

interface College {
    id: number | string;
    college_name: string;
}

interface GenericWriteupForm {
    year: string;
    college_id: string;
    content: string;
    is_active: boolean;
}

const props = withDefaults(
    defineProps<{
        visible?: boolean;
        title?: string;
        buttonText?: string;
        form: GenericWriteupForm;
        years?: Array<string | number>;
        colleges?: College[];
        isSaving?: boolean;
        maxCharacters?: number;
    }>(),
    {
        visible: false,
        title: 'Generic Writeup',
        buttonText: 'Save Writeup',
        years: () => [],
        colleges: () => [],
        isSaving: false,
        maxCharacters: 300,
    },
);

const emit = defineEmits<{
    close: [];
    submit: [];
    'update:form': [form: GenericWriteupForm];
}>();

const characterCount = computed(() => {
    return props.form.content?.length || 0;
});

const remainingCharacters = computed(() => {
    return Math.max(
        props.maxCharacters - characterCount.value,
        0,
    );
});

const isNearLimit = computed(() => {
    return remainingCharacters.value <= 20;
});

const canSubmit = computed(() => {
    return Boolean(
        props.form.year
        && props.form.college_id
        && props.form.content?.trim()
        && characterCount.value <= props.maxCharacters
    );
});

const selectedYear = computed(() => {
    return props.form.year || '';
});

const selectedCollegeName = computed(() => {
    if (!props.form.college_id) {
        return '';
    }

    const selected = props.colleges.find((college) => {
        return String(college.id)
            === String(props.form.college_id);
    });

    return selected?.college_name || '';
});

const noticeStateClass = computed(() => {
    if (characterCount.value >= props.maxCharacters) {
        return 'is-danger';
    }

    if (isNearLimit.value) {
        return 'is-warning';
    }

    return 'is-success';
});

const contentNoticeIcon = computed(() => {
    if (characterCount.value >= props.maxCharacters) {
        return 'bi-exclamation-triangle';
    }

    if (isNearLimit.value) {
        return 'bi-exclamation-circle';
    }

    return 'bi-check2-circle';
});

const contentNotice = computed(() => {
    if (characterCount.value >= props.maxCharacters) {
        return 'Character limit reached.';
    }

    if (isNearLimit.value) {
        return `${remainingCharacters.value} characters remaining.`;
    }

    return `Within the ${props.maxCharacters}-character limit.`;
});

watch(
    () => props.visible,
    (isVisible) => {
        document.body.classList.toggle(
            'modal-open',
            isVisible,
        );

        if (isVisible) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.removeProperty('overflow');
        }
    },
);

onBeforeUnmount(() => {
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
});

function updateField<K extends keyof GenericWriteupForm>(
    field: K,
    value: GenericWriteupForm[K],
): void {
    emit('update:form', {
        ...props.form,
        [field]: value,
    });
}

function updateYear(event: Event): void {
    const target = event.target as HTMLSelectElement;

    updateField('year', target.value);
}

function updateCollege(event: Event): void {
    const target = event.target as HTMLSelectElement;

    updateField('college_id', target.value);
}

function updateContent(event: Event): void {
    const target = event.target as HTMLTextAreaElement;

    updateField('content', target.value);
}

function updateActive(event: Event): void {
    const target = event.target as HTMLInputElement;

    updateField('is_active', target.checked);
}
</script>

<style scoped>
.generic-writeup-modal {
    z-index: 1060;
}

.generic-writeup-backdrop {
    z-index: 1055;
}

.generic-writeup-modal-content {
    overflow: hidden;
    border: 0;
    border-radius: 0.8rem;
    box-shadow: 0 1.25rem 3.5rem rgba(0, 0, 0, 0.18);
}

.generic-writeup-modal-header {
    align-items: flex-start;
    padding: 1.1rem 1.25rem;
    border-bottom: 1px solid var(--cyb-border-soft, #edf0f2);
    background: #fff;
}

.generic-writeup-modal-header .modal-title {
    margin: 0;
    color: var(--cyb-text, #212529);
    font-size: 1rem;
    font-weight: 650;
}

.generic-writeup-modal-description {
    margin: 0.2rem 0 0;
    color: var(--cyb-muted, #6c757d);
    font-size: 0.76rem;
    line-height: 1.45;
}

.generic-writeup-modal-body {
    padding: 1.25rem;
}

.generic-writeup-context {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    padding: 0.7rem 0.75rem;
    border: 1px solid var(--cyb-border, #e7eaed);
    border-radius: 0.6rem;
    background: #f8f9fa;
}

.generic-writeup-context-college {
    max-width: 100%;
}

.generic-writeup-context-college span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.generic-writeup-character-count {
    flex: 0 0 auto;
    font-size: 0.72rem;
    font-weight: 600;
}

.generic-writeup-modal-textarea {
    min-height: 180px;
    line-height: 1.65;
    resize: vertical;
}

.generic-writeup-content-notice {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.6rem;
    padding: 0.65rem 0.75rem;
    border: 1px solid;
    border-radius: 0.55rem;
    font-size: 0.73rem;
    line-height: 1.4;
}

.generic-writeup-content-notice.is-success {
    border-color: #cce7d8;
    background: #f1faf5;
    color: #24724d;
}

.generic-writeup-content-notice.is-warning {
    border-color: #ead8a8;
    background: #fff9e7;
    color: #7d641c;
}

.generic-writeup-content-notice.is-danger {
    border-color: #edc6c6;
    background: #fdf2f2;
    color: #9a3d3d;
}

.generic-writeup-active {
    padding: 0.9rem 1rem;
    border: 1px solid var(--cyb-border, #e7eaed);
    border-radius: 0.65rem;
    background: #f8f9fa;
}

.generic-writeup-active-check {
    display: flex;
    align-items: flex-start;
    gap: 0.7rem;
    margin: 0;
    padding: 0;
}

.generic-writeup-active-check .form-check-input {
    width: 1.05rem;
    height: 1.05rem;
    flex: 0 0 1.05rem;
    margin: 0.15rem 0 0;
    float: none;
    cursor: pointer;
}

.generic-writeup-active-check .form-check-label {
    cursor: pointer;
}

.generic-writeup-active-title {
    display: block;
    color: var(--cyb-text, #212529);
    font-size: 0.82rem;
    font-weight: 600;
}

.generic-writeup-active-description {
    display: block;
    margin-top: 0.15rem;
    color: var(--cyb-muted, #6c757d);
    font-size: 0.74rem;
    line-height: 1.45;
}

.generic-writeup-modal-footer {
    padding: 0.9rem 1.25rem;
    border-top: 1px solid var(--cyb-border-soft, #edf0f2);
    background: #fafbfc;
}

.generic-writeup-saving-icon {
    animation: generic-writeup-spin 0.8s linear infinite;
}

@keyframes generic-writeup-spin {
    to {
        transform: rotate(360deg);
    }
}

@media (max-width: 575.98px) {
    .generic-writeup-modal .modal-dialog {
        margin: 0.75rem;
    }

    .generic-writeup-modal-header,
    .generic-writeup-modal-body,
    .generic-writeup-modal-footer {
        padding-right: 1rem;
        padding-left: 1rem;
    }
}
</style>