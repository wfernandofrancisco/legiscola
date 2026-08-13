import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin-reports.js'],
            refresh: true,
        }),
    ],
    server: {
        /**
         * Nunca deixe o Vite anunciar http://0.0.0.0:5173 no ficheiro public/hot — o browser não resolve 0.0.0.0
         * (e extensões podem bloquear). Com origin explícito, o @vite do Laravel injeta 127.0.0.1:5173.
         * CORS: true permite carregar assets a partir de APP_URL (ex.: *.lvh.me:8000).
         */
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        origin: 'http://127.0.0.1:5173',
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
