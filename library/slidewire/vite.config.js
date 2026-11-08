import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import designTokens from './bin/design-tokens.cjs';

export default defineConfig({
    plugins: [
        { name: 'slidewire-design-tokens', buildStart() { designTokens.buildDesignTokens(); } },
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/catalogue.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
