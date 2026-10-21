import { createApp } from 'vue';
import PenaltyManagement from './components/PenaltyManagement.vue';

const element = document.getElementById(
    'penalty-management-app',
);

if (element) {
    const years = element.dataset.years
        ? JSON.parse(element.dataset.years)
        : [];

    const canManage =
        element.dataset.canManage === 'true';

    createApp(PenaltyManagement, {
        years,
        canManage,
    }).mount(element);
}