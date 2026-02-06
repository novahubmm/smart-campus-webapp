import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/pwa.css',
                'resources/js/app.js',
                'public/css/academic-management.css',
                'public/js/academic-management.js',
                'public/css/pwa.css',
                'public/css/salary-payroll.css',
                'public/js/pwa.js',
                'public/css/select2.min.css',
                'public/js/select2.min.js'
            ],
            refresh: true,
        }),
    ],
});
