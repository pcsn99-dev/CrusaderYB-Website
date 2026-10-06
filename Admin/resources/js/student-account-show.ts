import { createApp } from 'vue';
import StudentAccountShow from './pages/student-accounts/StudentAccountShow.vue';

const element = document.getElementById(
    'student-account-show-app',
);

if (element) {
    const student = JSON.parse(
        element.dataset.student ?? '{}',
    );

    createApp(StudentAccountShow, {
        student,
    }).mount(element);
}