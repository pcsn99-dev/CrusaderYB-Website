<template>
    <Teleport to="body">
        <div v-if="visible">
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <form class="modal-content" @submit.prevent="$emit('submit')">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ title }}</h5>
                            <button type="button" class="btn-close" @click="$emit('close')"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">School Year</label>
                                    <select
                                        class="form-select"
                                        :value="form.year"
                                        @input="updateField('year', $event.target.value)"
                                        required
                                    >
                                        <option value="">Select Year</option>
                                        <option v-for="year in years" :key="year" :value="year">
                                            {{ year }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">College</label>
                                    <select
                                        class="form-select"
                                        :value="form.college_id"
                                        @input="updateField('college_id', $event.target.value)"
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

                                <div class="col-12">
                                    <label class="form-label">Writeup Content</label>
                                    <textarea
                                        rows="6"
                                        maxlength="300"
                                        class="form-control"
                                        :value="form.content"
                                        @input="updateField('content', $event.target.value)"
                                        required
                                    ></textarea>

                                    <div class="form-text">
                                        {{ form.content.length }} / 300 characters
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input
                                            type="checkbox"
                                            class="form-check-input"
                                            id="generic_writeup_is_active"
                                            :checked="form.is_active"
                                            @change="updateField('is_active', $event.target.checked)"
                                        >

                                        <label class="form-check-label" for="generic_writeup_is_active">
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" @click="$emit('close')">
                                Cancel
                            </button>

                            <button type="submit" class="btn btn-primary" :disabled="isSaving">
                                {{ isSaving ? 'Saving...' : buttonText }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
const props = defineProps({
    visible: Boolean,
    title: String,
    buttonText: String,
    form: {
        type: Object,
        required: true,
    },
    years: Array,
    colleges: Array,
    isSaving: Boolean,
});

const emit = defineEmits(['close', 'submit', 'update:form']);

function updateField(field, value) {
    emit('update:form', {
        ...props.form,
        [field]: value,
    });
}
</script>