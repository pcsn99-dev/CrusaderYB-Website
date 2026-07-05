<template>
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
        <!-- Left Side -->
        <aside class="xl:col-span-3">
            <div class="space-y-4">
                <GenericWriteupFilters
                    :years="years"
                    :colleges="colleges"
                    :year="filterYear"
                    :college-id="filterCollegeId"
                    @update:year="filterYear = $event"
                    @update:collegeId="filterCollegeId = $event"
                />

                <GenericWriteupCountCard
                    :current-count="currentCount"
                    :has-selected-group="hasSelectedGroup"
                    :max-limit="maxGenericWriteups"
                />
            </div>
        </aside>

        <!-- Main Content -->
        <section class="xl:col-span-9">
            <div class="space-y-3">
                <div
                    v-if="successMessage"
                    class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700"
                >
                    <i class="bi bi-check2-circle me-1"></i>
                    {{ successMessage }}
                </div>

                <div
                    v-if="errorMessage"
                    class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"
                >
                    <i class="bi bi-exclamation-circle me-1"></i>
                    {{ errorMessage }}
                </div>

                <GenericWriteupList
                    :writeups="writeups"
                    :has-selected-group="hasSelectedGroup"
                    :current-count="currentCount"
                    :max-limit="maxGenericWriteups"
                    @create="openCreateModal"
                    @edit="openEditModal"
                    @delete="deleteWriteup"
                />
            </div>
        </section>

        <GenericWriteupModal
            :visible="showCreateModal"
            title="New Generic Writeup"
            button-text="Save Writeup"
            :form="createForm"
            :years="years"
            :colleges="colleges"
            :is-saving="isSaving"
            :max-characters="maxCharacters"
            @close="closeCreateModal"
            @submit="submitCreate"
            @update:form="createForm = $event"
        />

        <GenericWriteupModal
            :visible="showEditModal"
            title="Edit Generic Writeup"
            button-text="Update Writeup"
            :form="editForm"
            :years="years"
            :colleges="colleges"
            :is-saving="isSaving"
            :max-characters="maxCharacters"
            @close="closeEditModal"
            @submit="submitEdit"
            @update:form="editForm = $event"
        />
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import GenericWriteupList from './GenericWriteupList.vue';
import GenericWriteupModal from './GenericWriteupModal.vue';
import GenericWriteupFilters from './GenericWriteupFilters.vue';
import GenericWriteupCountCard from './GenericWriteupCountCard.vue';

const props = defineProps({
    initialWriteups: {
        type: Array,
        default: () => [],
    },
    years: {
        type: Array,
        default: () => [],
    },
    colleges: {
        type: Array,
        default: () => [],
    },
    selectedYear: {
        type: String,
        default: '',
    },
    selectedCollegeId: {
        type: String,
        default: '',
    },
    currentCount: {
        type: Number,
        default: 0,
    },
});

const maxGenericWriteups = 20;
const maxCharacters = 300;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const writeups = ref(props.initialWriteups || []);
const currentCount = ref(props.currentCount || 0);

const filterYear = ref(props.selectedYear || '');
const filterCollegeId = ref(props.selectedCollegeId || '');

const isSaving = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const showCreateModal = ref(false);
const showEditModal = ref(false);

const createForm = ref({
    year: props.selectedYear || '',
    college_id: props.selectedCollegeId || '',
    content: '',
    is_active: true,
});

const editForm = ref({
    id: null,
    year: '',
    college_id: '',
    content: '',
    is_active: true,
});

const hasSelectedGroup = computed(() => {
    return Boolean(filterYear.value && filterCollegeId.value);
});

const hasReachedLimit = computed(() => {
    return currentCount.value >= maxGenericWriteups;
});

function belongsToSelectedGroup(writeup) {
    return String(writeup?.year || '') === String(filterYear.value || '')
        && String(writeup?.college_id || '') === String(filterCollegeId.value || '');
}

function clearMessages() {
    errorMessage.value = '';
    successMessage.value = '';
}

function showSuccess(message) {
    successMessage.value = message;
    errorMessage.value = '';

    window.setTimeout(() => {
        successMessage.value = '';
    }, 3500);
}

function showError(message) {
    errorMessage.value = message;
    successMessage.value = '';
}

function applyReturnedCount(result, fallback) {
    if (typeof result.currentCount === 'number') {
        currentCount.value = result.currentCount;
        return;
    }

    if (typeof result.current_count === 'number') {
        currentCount.value = result.current_count;
        return;
    }

    fallback();
}

async function sendRequest(url, method, data = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
    };

    if (data) {
        options.body = JSON.stringify(data);
    }

    const response = await fetch(url, options);
    const result = await response.json();

    if (!response.ok) {
        const firstError = result.errors
            ? Object.values(result.errors).flat()[0]
            : result.message || 'Something went wrong.';

        throw new Error(firstError);
    }

    return result;
}

function openCreateModal() {
    clearMessages();

    if (!hasSelectedGroup.value) {
        showError('Select a school year and college first.');
        return;
    }

    if (hasReachedLimit.value) {
        showError('Maximum generic writeups reached for this year and college.');
        return;
    }

    createForm.value = {
        year: filterYear.value,
        college_id: filterCollegeId.value,
        content: '',
        is_active: true,
    };

    showCreateModal.value = true;
}

function closeCreateModal() {
    showCreateModal.value = false;
}

function openEditModal(writeup) {
    clearMessages();

    editForm.value = {
        id: writeup.id,
        year: writeup.year || '',
        college_id: String(writeup.college_id || ''),
        content: writeup.content || '',
        is_active: Boolean(writeup.is_active),
    };

    showEditModal.value = true;
}

function closeEditModal() {
    showEditModal.value = false;
}

async function submitCreate() {
    clearMessages();
    isSaving.value = true;

    try {
        const result = await sendRequest('/writeups/generic', 'POST', {
            year: createForm.value.year,
            college_id: createForm.value.college_id,
            content: createForm.value.content,
            is_active: createForm.value.is_active,
        });

        const createdWriteup = result.genericWriteup;

        if (createdWriteup && belongsToSelectedGroup(createdWriteup)) {
            writeups.value.unshift(createdWriteup);

            applyReturnedCount(result, () => {
                currentCount.value += 1;
            });
        } else {
            applyReturnedCount(result, () => {});
        }

        showSuccess(result.message || 'Generic writeup created.');
        showCreateModal.value = false;
    } catch (error) {
        showError(error.message);
    } finally {
        isSaving.value = false;
    }
}

async function submitEdit() {
    clearMessages();
    isSaving.value = true;

    try {
        const oldWriteup = writeups.value.find((writeup) => {
            return Number(writeup.id) === Number(editForm.value.id);
        });

        const oldBelongs = oldWriteup ? belongsToSelectedGroup(oldWriteup) : false;

        const result = await sendRequest(`/writeups/generic/${editForm.value.id}`, 'PUT', {
            year: editForm.value.year,
            college_id: editForm.value.college_id,
            content: editForm.value.content,
            is_active: editForm.value.is_active,
        });

        const updatedWriteup = result.genericWriteup;

        if (!updatedWriteup) {
            throw new Error('Updated writeup was not returned by the server.');
        }

        const newBelongs = belongsToSelectedGroup(updatedWriteup);

        if (oldBelongs && newBelongs) {
            const index = writeups.value.findIndex((writeup) => {
                return Number(writeup.id) === Number(updatedWriteup.id);
            });

            if (index !== -1) {
                writeups.value[index] = updatedWriteup;
            }

            applyReturnedCount(result, () => {});
        }

        if (oldBelongs && !newBelongs) {
            writeups.value = writeups.value.filter((writeup) => {
                return Number(writeup.id) !== Number(updatedWriteup.id);
            });

            applyReturnedCount(result, () => {
                currentCount.value = Math.max(currentCount.value - 1, 0);
            });
        }

        if (!oldBelongs && newBelongs) {
            writeups.value.unshift(updatedWriteup);

            applyReturnedCount(result, () => {
                currentCount.value += 1;
            });
        }

        showSuccess(result.message || 'Generic writeup updated.');
        showEditModal.value = false;
    } catch (error) {
        showError(error.message);
    } finally {
        isSaving.value = false;
    }
}

async function deleteWriteup(writeup) {
    if (!window.confirm('Delete this generic writeup?')) {
        return;
    }

    clearMessages();
    isSaving.value = true;

    try {
        const result = await sendRequest(`/writeups/generic/${writeup.id}`, 'DELETE');

        const deletedBelongs = belongsToSelectedGroup(writeup);

        writeups.value = writeups.value.filter((item) => {
            return Number(item.id) !== Number(writeup.id);
        });

        if (deletedBelongs) {
            applyReturnedCount(result, () => {
                currentCount.value = Math.max(currentCount.value - 1, 0);
            });
        }

        showSuccess(result.message || 'Generic writeup deleted.');
    } catch (error) {
        showError(error.message);
    } finally {
        isSaving.value = false;
    }
}
</script>