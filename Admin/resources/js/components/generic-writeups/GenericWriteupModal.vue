<template>
    <Teleport to="body">
        <div v-if="visible">
            <div
                class="modal fade show d-block cyb-generic-modal"
                tabindex="-1"
                role="dialog"
                aria-modal="true"
            >
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <form class="modal-content cyb-generic-modal-content" @submit.prevent="$emit('submit')">

                        <!-- Header -->
                        <div class="modal-header cyb-generic-modal-header">
                            <div class="min-w-0">
                                <div class="cyb-modal-chip">
                                    <i class="bi bi-card-text"></i>
                                    Generic Writeup
                                </div>

                                <h5 class="modal-title">
                                    {{ title }}
                                </h5>

                                <p class="cyb-modal-subtitle">
                                    Create or update a reusable writeup for the selected school year and college.
                                </p>
                            </div>

                            <button
                                type="button"
                                class="btn-close"
                                aria-label="Close modal"
                                @click="$emit('close')"
                            ></button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body cyb-generic-modal-body">
                            <div class="row g-3">

                                <!-- School Year -->
                                <div class="col-12 col-md-6">
                                    <label for="generic_writeup_year" class="cyb-modal-label">
                                        School Year
                                    </label>

                                    <select
                                        id="generic_writeup_year"
                                        class="form-select cyb-modal-control"
                                        :value="form.year"
                                        @change="updateField('year', $event.target.value)"
                                        required
                                    >
                                        <option value="">Select Year</option>

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
                                    <label for="generic_writeup_college" class="cyb-modal-label">
                                        College
                                    </label>

                                    <select
                                        id="generic_writeup_college"
                                        class="form-select cyb-modal-control"
                                        :value="form.college_id"
                                        @change="updateField('college_id', $event.target.value)"
                                        required
                                    >
                                        <option value="">Select College</option>

                                        <option
                                            v-for="college in colleges"
                                            :key="college.id"
                                            :value="String(college.id)"
                                        >
                                            {{ college.college_name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Active Context -->
                                <div v-if="selectedYear || selectedCollegeName" class="col-12">
                                    <div class="cyb-modal-context">
                                        <span v-if="selectedYear" class="cyb-context-pill cyb-context-pill-year">
                                            <i class="bi bi-calendar3"></i>
                                            SY {{ selectedYear }}
                                        </span>

                                        <span v-if="selectedCollegeName" class="cyb-context-pill cyb-context-pill-college">
                                            <i class="bi bi-building"></i>
                                            {{ selectedCollegeName }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="col-12">
                                    <div class="d-flex align-items-center justify-content-between gap-3 mb-1">
                                        <label for="generic_writeup_content" class="cyb-modal-label mb-0">
                                            Writeup Content
                                        </label>

                                        <span
                                            class="cyb-character-count"
                                            :class="characterCount >= maxCharacters ? 'text-danger' : 'text-secondary'"
                                        >
                                            {{ characterCount }} / {{ maxCharacters }}
                                        </span>
                                    </div>

                                    <textarea
                                        id="generic_writeup_content"
                                        rows="7"
                                        :maxlength="maxCharacters"
                                        class="form-control cyb-modal-textarea"
                                        :value="form.content"
                                        @input="updateField('content', $event.target.value)"
                                        placeholder="Enter the generic writeup here..."
                                        required
                                    ></textarea>

                                    <div class="cyb-content-notice" :class="contentNoticeClass">
                                        <i class="bi" :class="contentNoticeIcon"></i>
                                        {{ contentNotice }}
                                    </div>
                                </div>

                                <!-- Active Toggle -->
                                <div class="col-12">
                                    <label class="cyb-active-toggle">
                                        <input
                                            type="checkbox"
                                            class="form-check-input mt-1"
                                            :checked="form.is_active"
                                            @change="updateField('is_active', $event.target.checked)"
                                        >

                                        <span>
                                            <span class="cyb-active-title">
                                                Active
                                            </span>

                                            <span class="cyb-active-help">
                                                Active generic writeups can be used later when assigning writeups to students.
                                            </span>
                                        </span>
                                    </label>
                                </div>

                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer cyb-generic-modal-footer">
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                @click="$emit('close')"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                class="btn btn-primary cyb-save-button"
                                :disabled="isSaving || !canSubmit"
                            >
                                <i class="bi" :class="isSaving ? 'bi-arrow-repeat' : 'bi-save'"></i>
                                {{ isSaving ? 'Saving...' : buttonText }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <div class="modal-backdrop fade show"></div>
        </div>
    </Teleport>
</template>

<script setup>
import { computed, onBeforeUnmount, watch } from 'vue';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Generic Writeup',
    },
    buttonText: {
        type: String,
        default: 'Save Writeup',
    },
    form: {
        type: Object,
        required: true,
    },
    years: {
        type: Array,
        default: () => [],
    },
    colleges: {
        type: Array,
        default: () => [],
    },
    isSaving: {
        type: Boolean,
        default: false,
    },
    maxCharacters: {
        type: Number,
        default: 300,
    },
});

const emit = defineEmits(['close', 'submit', 'update:form']);

const characterCount = computed(() => {
    return props.form.content?.length || 0;
});

const remainingCharacters = computed(() => {
    return Math.max(props.maxCharacters - characterCount.value, 0);
});

const canSubmit = computed(() => {
    return Boolean(
        props.form.year &&
        props.form.college_id &&
        props.form.content?.trim() &&
        characterCount.value <= props.maxCharacters
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
        return String(college.id) === String(props.form.college_id);
    });

    return selected?.college_name || '';
});

const contentNoticeClass = computed(() => {
    if (characterCount.value >= props.maxCharacters) {
        return 'cyb-content-notice-danger';
    }

    if (remainingCharacters.value <= 20) {
        return 'cyb-content-notice-warning';
    }

    return 'cyb-content-notice-success';
});

const contentNoticeIcon = computed(() => {
    if (characterCount.value >= props.maxCharacters) {
        return 'bi-exclamation-triangle';
    }

    if (remainingCharacters.value <= 20) {
        return 'bi-exclamation-circle';
    }

    return 'bi-check2-circle';
});

const contentNotice = computed(() => {
    if (characterCount.value >= props.maxCharacters) {
        return 'Character limit reached.';
    }

    if (remainingCharacters.value <= 20) {
        return `${remainingCharacters.value} characters remaining.`;
    }

    return 'Within the 300-character limit.';
});

watch(
    () => props.visible,
    (isVisible) => {
        document.body.classList.toggle('modal-open', isVisible);
    }
);

onBeforeUnmount(() => {
    document.body.classList.remove('modal-open');
});

function updateField(field, value) {
    emit('update:form', {
        ...props.form,
        [field]: value,
    });
}
</script>

<style scoped>
.cyb-generic-modal {
    z-index: 1060;
}

.cyb-generic-modal-content {
    overflow: hidden;
    border: 0;
    border-radius: 1rem;
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
}

.cyb-generic-modal-header {
    align-items: flex-start;
    padding: 1.25rem 1.35rem;
    border-bottom: 1px solid var(--cyb-border);
    background:
        linear-gradient(135deg, rgba(30, 58, 95, 0.06), rgba(201, 162, 39, 0.08)),
        #ffffff;
}

.cyb-modal-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    width: fit-content;
    padding: 0.35rem 0.6rem;
    border: 1px solid #bfdbfe;
    border-radius: 999px;
    color: var(--cyb-primary);
    background-color: var(--cyb-primary-soft);
    font-size: 0.72rem;
    font-weight: 800;
    line-height: 1;
}

.modal-title {
    margin-top: 0.65rem;
    color: var(--cyb-primary);
    font-size: 1.05rem;
    font-weight: 800;
}

.cyb-modal-subtitle {
    margin: 0.25rem 0 0;
    color: var(--cyb-muted);
    font-size: 0.84rem;
    line-height: 1.45;
}

.cyb-generic-modal-body {
    padding: 1.25rem 1.35rem;
}

.cyb-modal-label {
    display: block;
    margin-bottom: 0.35rem;
    color: var(--cyb-muted);
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.cyb-modal-control,
.cyb-modal-textarea {
    border-color: var(--cyb-border);
    border-radius: 0.75rem;
    color: var(--cyb-text);
    font-size: 0.9rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
}

.cyb-modal-control:focus,
.cyb-modal-textarea:focus {
    border-color: var(--cyb-primary);
    box-shadow: 0 0 0 0.2rem rgba(30, 58, 95, 0.12);
}

.cyb-modal-textarea {
    min-height: 180px;
    line-height: 1.65;
    resize: vertical;
}

.cyb-character-count {
    font-size: 0.78rem;
    font-weight: 800;
}

.cyb-modal-context {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.75rem;
    border: 1px solid var(--cyb-border);
    border-radius: 0.85rem;
    background-color: #f8fafc;
}

.cyb-context-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    max-width: 100%;
    padding: 0.35rem 0.6rem;
    border-radius: 999px;
    background-color: #ffffff;
    font-size: 0.75rem;
    font-weight: 750;
}

.cyb-context-pill-year {
    border: 1px solid #bfdbfe;
    color: var(--cyb-primary);
}

.cyb-context-pill-college {
    border: 1px solid #fbcfe8;
    color: #be185d;
}

.cyb-content-notice {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 0.6rem;
    padding: 0.65rem 0.75rem;
    border: 1px solid;
    border-radius: 0.85rem;
    font-size: 0.78rem;
    font-weight: 700;
}

.cyb-content-notice-success {
    color: #15803d;
    border-color: #bbf7d0;
    background-color: #f0fdf4;
}

.cyb-content-notice-warning {
    color: #92400e;
    border-color: #fde68a;
    background-color: #fffbeb;
}

.cyb-content-notice-danger {
    color: #b91c1c;
    border-color: #fecaca;
    background-color: #fef2f2;
}

.cyb-active-toggle {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    border: 1px solid var(--cyb-border);
    border-radius: 0.9rem;
    background-color: #f8fafc;
    cursor: pointer;
    transition: background-color 120ms ease, border-color 120ms ease;
}

.cyb-active-toggle:hover {
    border-color: #cbd5e1;
    background-color: var(--cyb-primary-soft);
}

.cyb-active-title {
    display: block;
    color: var(--cyb-text);
    font-size: 0.9rem;
    font-weight: 800;
}

.cyb-active-help {
    display: block;
    margin-top: 0.15rem;
    color: var(--cyb-muted);
    font-size: 0.78rem;
    line-height: 1.4;
}

.cyb-generic-modal-footer {
    padding: 1rem 1.35rem;
    border-top: 1px solid var(--cyb-border);
    background-color: #f8fafc;
}

.cyb-save-button {
    background-color: var(--cyb-primary);
    border-color: var(--cyb-primary);
    font-weight: 800;
}

.cyb-save-button:hover,
.cyb-save-button:focus {
    background-color: var(--cyb-primary-dark);
    border-color: var(--cyb-primary-dark);
}

.modal-backdrop {
    z-index: 1055;
}

@media (max-width: 575.98px) {
    .modal-dialog {
        margin: 0.75rem;
    }

    .cyb-generic-modal-header,
    .cyb-generic-modal-body,
    .cyb-generic-modal-footer {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}
</style>