// Filemai.my Service Worker (PWA)
const CACHE_NAME = 'filemai-cache-v1';
const STATIC_ASSETS = [
    '/',
    '/assets/css/style.css?v=3',
    '/assets/icons/icon-192.png',
    '/assets/icons/icon-512.png',
    '/manifest.json'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Bypass caching for streaming video files, HLS chunks & playlists, and API endpoints
    if (url.pathname.endsWith('.mp4') || 
        url.pathname.endsWith('.ts') || 
        url.pathname.endsWith('.m3u8') || 
        url.pathname.startsWith('/uploads/videos/') || 
        url.pathname.startsWith('/api/')) {
        return;
    }

    // HTML Navigation requests: Network-first with offline cache fallback
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(event.request).then(res => res || caches.match('/')))
        );
        return;
    }

    // Static Assets (CSS, JS, Images): Stale-while-revalidate
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            const fetchPromise = fetch(event.request).then((networkResponse) => {
                if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return networkResponse;
            }).catch(() => cachedResponse);

            return cachedResponse || fetchPromise;
        })
    );
});

