import { createApp } from 'vue';
import StudentAccountsPage from './pages/student-accounts/StudentAccountsPage.vue';

const element = document.getElementById('student-accounts-app');

if (element) {
    const colleges = JSON.parse(element.dataset.colleges ?? '[]');
    const graduationYears = JSON.parse(
        element.dataset.graduationYears ?? '[]',
    );

    createApp(StudentAccountsPage, {
        colleges,
        graduationYears,
    }).mount(element);
}