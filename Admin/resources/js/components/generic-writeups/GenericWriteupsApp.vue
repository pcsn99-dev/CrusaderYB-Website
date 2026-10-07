<template>
    <div class="row g-4 generic-writeups-layout">

        <!-- Left Side -->
        <aside class="col-12 col-xl-3">
            <div class="generic-writeups-sidebar">
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
        <section class="col-12 col-xl-9">
            <div class="generic-writeups-main">

                <!-- Success -->
                <div
                    v-if="successMessage"
                    class="alert alert-success generic-writeups-alert"
                    role="alert"
                >
                    <i class="bi bi-check2-circle"></i>

                    <span>
                        {{ successMessage }}
                    </span>
                </div>

                <!-- Error -->
                <div
                    v-if="errorMessage"
                    class="alert alert-danger generic-writeups-alert"
                    role="alert"
                >
                    <i class="bi bi-exclamation-circle"></i>

                    <span>
                        {{ errorMessage }}
                    </span>
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

        <!-- Create Modal -->
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

        <!-- Edit Modal -->
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

<script setup lang="ts">
import { computed, ref } from 'vue';

import GenericWriteupList from './GenericWriteupList.vue';
import GenericWriteupModal from './GenericWriteupModal.vue';
import GenericWriteupFilters from './GenericWriteupFilters.vue';
import GenericWriteupCountCard from './GenericWriteupCountCard.vue';

interface College {
    id: number | string;
    college_name: string;
}

interface Creator {
    name?: string | null;
}

interface GenericWriteup {
    id: number | string;
    year: string | number;
    college_id: number | string;
    content: string;
    is_active: boolean;
    updated_at?: string | null;

    college?: College | null;
    creator?: Creator | null;
}

interface GenericWriteupForm {
    year: string;
    college_id: string;
    content: string;
    is_active: boolean;
}

interface GenericWriteupEditForm extends GenericWriteupForm {
    id: number | string | null;
}

interface GenericWriteupApiResponse {
    message?: string;

    genericWriteup?: GenericWriteup;

    currentCount?: number;
    current_count?: number;

    errors?: Record<string, string[]>;
}

const props = withDefaults(
    defineProps<{
        initialWriteups?: GenericWriteup[];
        years?: Array<string | number>;
        colleges?: College[];
        selectedYear?: string;
        selectedCollegeId?: string;
        currentCount?: number;
    }>(),
    {
        initialWriteups: () => [],
        years: () => [],
        colleges: () => [],
        selectedYear: '',
        selectedCollegeId: '',
        currentCount: 0,
    },
);

const maxGenericWriteups = 20;
const maxCharacters = 300;

const csrfToken =
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content')
    || '';

const writeups = ref<GenericWriteup[]>(
    props.initialWriteups || [],
);

const currentCount = ref(
    props.currentCount || 0,
);

const filterYear = ref(
    props.selectedYear || '',
);

const filterCollegeId = ref(
    props.selectedCollegeId || '',
);

const isSaving = ref(false);

const errorMessage = ref('');
const successMessage = ref('');

const showCreateModal = ref(false);
const showEditModal = ref(false);

const createForm = ref<GenericWriteupForm>({
    year: props.selectedYear || '',
    college_id: props.selectedCollegeId || '',
    content: '',
    is_active: true,
});

const editForm = ref<GenericWriteupEditForm>({
    id: null,
    year: '',
    college_id: '',
    content: '',
    is_active: true,
});

const hasSelectedGroup = computed(() => {
    return Boolean(
        filterYear.value
        && filterCollegeId.value,
    );
});

const hasReachedLimit = computed(() => {
    return currentCount.value >= maxGenericWriteups;
});

function belongsToSelectedGroup(
    writeup: GenericWriteup,
): boolean {
    return (
        String(writeup?.year || '')
            === String(filterYear.value || '')
        && String(writeup?.college_id || '')
            === String(filterCollegeId.value || '')
    );
}

function clearMessages(): void {
    errorMessage.value = '';
    successMessage.value = '';
}

function showSuccess(message: string): void {
    successMessage.value = message;
    errorMessage.value = '';

    window.setTimeout(() => {
        if (successMessage.value === message) {
            successMessage.value = '';
        }
    }, 3500);
}

function showError(message: string): void {
    errorMessage.value = message;
    successMessage.value = '';
}

function applyReturnedCount(
    result: GenericWriteupApiResponse,
    fallback: () => void,
): void {
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

async function sendRequest(
    url: string,
    method: string,
    data: Record<string, unknown> | null = null,
): Promise<GenericWriteupApiResponse> {
    const options: RequestInit = {
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

    const response = await fetch(
        url,
        options,
    );

    let result: GenericWriteupApiResponse;

    try {
        result = await response.json();
    } catch {
        throw new Error(
            'The server returned an invalid response.',
        );
    }

    if (!response.ok) {
        const firstError = result.errors
            ? Object.values(result.errors).flat()[0]
            : result.message || 'Something went wrong.';

        throw new Error(firstError);
    }

    return result;
}

function openCreateModal(): void {
    clearMessages();

    if (!hasSelectedGroup.value) {
        showError(
            'Select a school year and college first.',
        );

        return;
    }

    if (hasReachedLimit.value) {
        showError(
            'Maximum generic writeups reached for this year and college.',
        );

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

function closeCreateModal(): void {
    if (isSaving.value) {
        return;
    }

    showCreateModal.value = false;
}

function openEditModal(
    writeup: GenericWriteup,
): void {
    clearMessages();

    editForm.value = {
        id: writeup.id,
        year: String(writeup.year || ''),
        college_id: String(writeup.college_id || ''),
        content: writeup.content || '',
        is_active: Boolean(writeup.is_active),
    };

    showEditModal.value = true;
}

function closeEditModal(): void {
    if (isSaving.value) {
        return;
    }

    showEditModal.value = false;
}

async function submitCreate(): Promise<void> {
    clearMessages();
    isSaving.value = true;

    try {
        const result = await sendRequest(
            '/writeups/generic',
            'POST',
            {
                year: createForm.value.year,
                college_id: createForm.value.college_id,
                content: createForm.value.content,
                is_active: createForm.value.is_active,
            },
        );

        const createdWriteup =
            result.genericWriteup;

        if (
            createdWriteup
            && belongsToSelectedGroup(createdWriteup)
        ) {
            writeups.value.unshift(
                createdWriteup,
            );

            applyReturnedCount(
                result,
                () => {
                    currentCount.value += 1;
                },
            );
        } else {
            applyReturnedCount(
                result,
                () => {},
            );
        }

        showSuccess(
            result.message
            || 'Generic writeup created.',
        );

        showCreateModal.value = false;
    } catch (error) {
        showError(
            getErrorMessage(error),
        );
    } finally {
        isSaving.value = false;
    }
}

async function submitEdit(): Promise<void> {
    clearMessages();

    if (editForm.value.id === null) {
        showError(
            'No generic writeup was selected for editing.',
        );

        return;
    }

    isSaving.value = true;

    try {
        const oldWriteup =
            writeups.value.find((writeup) => {
                return Number(writeup.id)
                    === Number(editForm.value.id);
            });

        const oldBelongs =
            oldWriteup
                ? belongsToSelectedGroup(oldWriteup)
                : false;

        const result = await sendRequest(
            `/writeups/generic/${editForm.value.id}`,
            'PUT',
            {
                year: editForm.value.year,
                college_id: editForm.value.college_id,
                content: editForm.value.content,
                is_active: editForm.value.is_active,
            },
        );

        const updatedWriteup =
            result.genericWriteup;

        if (!updatedWriteup) {
            throw new Error(
                'Updated writeup was not returned by the server.',
            );
        }

        const newBelongs =
            belongsToSelectedGroup(updatedWriteup);

        if (oldBelongs && newBelongs) {
            const index =
                writeups.value.findIndex(
                    (writeup) => {
                        return Number(writeup.id)
                            === Number(updatedWriteup.id);
                    },
                );

            if (index !== -1) {
                writeups.value[index] =
                    updatedWriteup;
            }

            applyReturnedCount(
                result,
                () => {},
            );
        }

        if (oldBelongs && !newBelongs) {
            writeups.value =
                writeups.value.filter(
                    (writeup) => {
                        return Number(writeup.id)
                            !== Number(updatedWriteup.id);
                    },
                );

            applyReturnedCount(
                result,
                () => {
                    currentCount.value =
                        Math.max(
                            currentCount.value - 1,
                            0,
                        );
                },
            );
        }

        if (!oldBelongs && newBelongs) {
            writeups.value.unshift(
                updatedWriteup,
            );

            applyReturnedCount(
                result,
                () => {
                    currentCount.value += 1;
                },
            );
        }

        showSuccess(
            result.message
            || 'Generic writeup updated.',
        );

        showEditModal.value = false;
    } catch (error) {
        showError(
            getErrorMessage(error),
        );
    } finally {
        isSaving.value = false;
    }
}

async function deleteWriteup(
    writeup: GenericWriteup,
): Promise<void> {
    if (
        !window.confirm(
            'Delete this generic writeup?',
        )
    ) {
        return;
    }

    clearMessages();
    isSaving.value = true;

    try {
        const result = await sendRequest(
            `/writeups/generic/${writeup.id}`,
            'DELETE',
        );

        const deletedBelongs =
            belongsToSelectedGroup(writeup);

        writeups.value =
            writeups.value.filter(
                (item) => {
                    return Number(item.id)
                        !== Number(writeup.id);
                },
            );

        if (deletedBelongs) {
            applyReturnedCount(
                result,
                () => {
                    currentCount.value =
                        Math.max(
                            currentCount.value - 1,
                            0,
                        );
                },
            );
        }

        showSuccess(
            result.message
            || 'Generic writeup deleted.',
        );
    } catch (error) {
        showError(
            getErrorMessage(error),
        );
    } finally {
        isSaving.value = false;
    }
}

function getErrorMessage(
    error: unknown,
): string {
    if (error instanceof Error) {
        return error.message;
    }

    return 'Something went wrong.';
}
</script>

<style scoped>
.generic-writeups-layout {
    align-items: flex-start;
}

.generic-writeups-sidebar,
.generic-writeups-main {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.generic-writeups-alert {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    margin: 0;
    font-size: 0.82rem;
}

.generic-writeups-alert > i {
    flex: 0 0 auto;
    margin-top: 0.05rem;
}

@media (min-width: 1200px) {
    .generic-writeups-sidebar {
        position: sticky;
        top: 1rem;
    }
}
</style>