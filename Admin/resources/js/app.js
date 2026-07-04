import Alpine from 'alpinejs';

import { createApp } from 'vue';
import GenericWriteupsApp from './components/generic-writeups/GenericWriteupsApp.vue';

window.Alpine = Alpine;

Alpine.start();

const genericWriteupsRoot = document.getElementById('generic-writeups-app');

if (genericWriteupsRoot) {
    createApp(GenericWriteupsApp, {
        initialWriteups: JSON.parse(genericWriteupsRoot.dataset.writeups),
        years: JSON.parse(genericWriteupsRoot.dataset.years),
        colleges: JSON.parse(genericWriteupsRoot.dataset.colleges),
        selectedYear: genericWriteupsRoot.dataset.selectedYear || '',
        selectedCollegeId: genericWriteupsRoot.dataset.selectedCollegeId || '',
        currentCount: Number(genericWriteupsRoot.dataset.currentCount || 0),
    }).mount('#generic-writeups-app');
}