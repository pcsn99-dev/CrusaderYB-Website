<template>
    <div class="row g-3">
        <div class="col-12 col-xl-3">
            <div class="card mb-3">
                <GenericWriteupFilters
                    :years="years"
                    :colleges="colleges"
                    :year="filterYear"
                    :college-id="filterCollegeId"
                    @update:year="filterYear = $event"
                    @update:collegeId="filterCollegeId = $event"
                />
            </div>

            <GenericWriteupCountCard
                :current-count="currentCount"
                :has-selected-group="hasSelectedGroup"
            />


        </div>

        <div class="col-12 col-xl-9">
            <GenericWriteupList
                :writeups="writeups"
                :has-selected-group="hasSelectedGroup"
                :current-count="currentCount"
                @create="openCreateModal"
                @edit="openEditModal"
                @delete="deleteWriteup"
            />
        </div>


        <GenericWriteupModal
            :visible="showCreateModal"
            title="New Generic Writeup"
            button-text="Save Writeup"
            :form="createForm"
            :years="years"
            :colleges="colleges"
            :is-saving="isSaving"
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
    initialWriteups: Array,
    years: Array,
    colleges: Array,
    selectedYear: String,
    selectedCollegeId: String,
    currentCount: Number,
});

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const writeups = ref(props.initialWriteups || []);
const filterYear = ref(props.selectedYear || '');
const filterCollegeId = ref(props.selectedCollegeId || '');
const isSaving = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const hasSelectedGroup = computed(() => {
    return Boolean(filterYear.value && filterCollegeId.value);
});
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

async function sendRequest(url, method, data = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
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
    if (!hasSelectedGroup.value) return;

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
    errorMessage.value = '';
    successMessage.value = '';
    isSaving.value = true;

    try {
        const result = await sendRequest('/writeups/generic', 'POST', {
            year: createForm.value.year,
            college_id: createForm.value.college_id,
            content: createForm.value.content,
            is_active: createForm.value.is_active,
        });

        writeups.value.unshift(result.genericWriteup);
        successMessage.value = result.message;
        showCreateModal.value = false;
    } catch (error) {
        errorMessage.value = error.message;
    } finally {
        isSaving.value = false;
    }
}

async function submitEdit() {
    errorMessage.value = '';
    successMessage.value = '';
    isSaving.value = true;

    try {
        const result = await sendRequest(`/writeups/generic/${editForm.value.id}`, 'PUT', {
            year: editForm.value.year,
            college_id: editForm.value.college_id,
            content: editForm.value.content,
            is_active: editForm.value.is_active,
        });

        const index = writeups.value.findIndex(writeup => writeup.id === result.genericWriteup.id);

        if (index !== -1) {
            writeups.value[index] = result.genericWriteup;
        }

        successMessage.value = result.message;
        showEditModal.value = false;
    } catch (error) {
        errorMessage.value = error.message;
    } finally {
        isSaving.value = false;
    }
}

async function deleteWriteup(writeup) {
    if (!confirm('Delete this generic writeup?')) return;

    errorMessage.value = '';
    successMessage.value = '';
    isSaving.value = true;

    try {
        const result = await sendRequest(`/writeups/generic/${writeup.id}`, 'DELETE');

        writeups.value = writeups.value.filter(item => item.id !== writeup.id);
        successMessage.value = result.message;
    } catch (error) {
        errorMessage.value = error.message;
    } finally {
        isSaving.value = false;
    }
}
</script>