import { createApp } from 'vue';
import PictorialAttendance from './components/PictorialAttendance.vue';

const element = document.getElementById(
    'pictorial-attendance-app',
);

if (element) {
    const activeYear = element.dataset.activeYear
        ? JSON.parse(element.dataset.activeYear)
        : null;

    const canManage =
        element.dataset.canManage === 'true';

    createApp(PictorialAttendance, {
        activeYear,
        canManage,
    }).mount(element);
}