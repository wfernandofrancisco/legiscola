/**
 * Service worker da área do aluno (Legiscola).
 *
 * Estratégia simples: HTML tenta a rede e cai no offline; imagens/ícones PWA
 * ficam em cache. Não cacheia respostas autenticadas de forma agressiva.
 */
const CACHE_NAME = 'legiscola-pwa-v2';
const PRECACHE = [
    '/offline.html',
    '/img/pwa-icon-192.png',
    '/img/pwa-icon-512.png',
    '/img/pwa-maskable-512.png',
    '/img/logo-curto.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) {
        return;
    }

    // Navegação: rede primeiro; sem rede mostra a página offline.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => response)
                .catch(() => caches.match('/offline.html'))
        );
        return;
    }

    // Assets estáticos do PWA / img pública: cache primeiro.
    if (url.pathname.startsWith('/img/') || url.pathname === '/offline.html') {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) {
                    return cached;
                }

                return fetch(request).then((response) => {
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }

                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));

                    return response;
                });
            })
        );
    }
});
