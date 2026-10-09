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

    createApp(PictorialSchedules, {
        activeYear,
        colleges,
    }).mount(element);
}