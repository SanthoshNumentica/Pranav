import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        host: '127.0.0.1',
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        chunkSizeWarningLimit: 1600,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('cornerstone')) {
                            return 'cornerstone';
                        }
                        if (id.includes('jszip')) {
                            return 'jszip';
                        }
                        if (id.includes('lucide-vue-next')) {
                            return 'lucide';
                        }
                        if (id.includes('vue') || id.includes('vue-router')) {
                            return 'vue-core';
                        }
                        return 'vendor';
                    }
                }
            }
        }
    }
});
