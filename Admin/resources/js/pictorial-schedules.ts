import { createApp } from 'vue';

import PictorialSchedules from '@/components/pictorial-schedules/PictorialSchedules.vue';

const element = document.getElementById('pictorial-schedules-app');

if (element) {
    const activeYear = JSON.parse(
        element.dataset.activeYear ?? 'null',
    );

    const colleges = JSON.parse(
        element.dataset.colleges ?? '[]',
    );

    const canManage =
        element.dataset.canManage === 'true';

    createApp(PictorialSchedules, {
        activeYear,
        colleges,
        canManage,
    }).mount(element);
}