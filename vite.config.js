import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny, fontsource } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                fontsource('IBM Plex Sans', {
                    weights: [400, 500, 600, 700],
                }),
                fontsource('IBM Plex Mono', {
                    weights: [500, 600],
                }),
                bunny('Cairo', {
                    weights: [400, 500, 600, 700, 800],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
