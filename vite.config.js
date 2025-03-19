import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/sass/app.scss'
            ],
            refresh: true,
        }),
    ],
    build: {
        outDir: 'public/build',
        assetsDir: 'assets',
        manifest: 'manifest.json',
    },
});
