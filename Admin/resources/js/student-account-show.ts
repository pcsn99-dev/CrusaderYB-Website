import { createApp } from 'vue';
import StudentAccountShow from './pages/student-accounts/StudentAccountShow.vue';

const element = document.getElementById(
    'student-account-show-app',
);

if (element) {
    const student = JSON.parse(
        element.dataset.student ?? '{}',
    );

    const subscriptionPackages = JSON.parse(
        element.dataset.subscriptionPackages ?? '[]',
    );

    createApp(StudentAccountShow, {
        student,
        subscriptionPackages,
    }).mount(element);
}