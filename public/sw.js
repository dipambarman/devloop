// DevLoop Service Worker for PWA
const CACHE_NAME = 'devloop-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Default network passthrough
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});
