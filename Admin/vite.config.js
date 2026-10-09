import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/guest.css',
                'resources/js/app.js',
                'resources/js/student-accounts.ts',
                'resources/js/student-account-show.ts',
                'resources/js/pictorial-schedules.ts',
            ],
            refresh: true,
        }),

        vue(),
    ],
});