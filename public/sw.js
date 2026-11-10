const CACHE_NAME = 'wholesale-offline-v1';
const STATIC_ASSETS = [
    '/offline',
    '/manifest.json',
    '/invenco.png',
    '/favicon.ico',
];

const OFFLINE_ROUTES = [
    '/dashboard',
    '/sales',
    '/sales/create',
    '/pos',
    '/payments',
    '/payments/create',
    '/reports/account-ledger',
    '/reports/party-ledger',
];

// 1. Install event: pre-cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch((err) => {
                console.warn('Service worker pre-caching non-fatal warning:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// 2. Activate event: clean up outdated caches
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
        }).then(() => self.clients.claim())
    );
});

// 3. Fetch event: Network-First with Cache Fallback for navigation and pages
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle GET requests for same origin or fonts/cdn
    if (request.method !== 'GET') {
        return;
    }

    // Do not cache API sync or ping endpoints
    if (url.pathname.startsWith('/api/offline/')) {
        return;
    }

    // Navigation requests (HTML pages)
    if (request.mode === 'navigate' || request.destination === 'document') {
        event.respondWith(
            fetch(request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, copy);
                        });
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    // Try to match specific requested route in cache
                    const cachedResponse = await caches.match(request);
                    if (cachedResponse) {
                        return cachedResponse;
                    }

                    // Try clean pathname without query params
                    const cleanMatch = await caches.match(url.pathname);
                    if (cleanMatch) {
                        return cleanMatch;
                    }

                    // Fallback to offline page
                    const offlineFallback = await caches.match('/offline');
                    return offlineFallback || new Response('Offline - No connection available.', {
                        status: 503,
                        headers: { 'Content-Type': 'text/plain' }
                    });
                })
        );
        return;
    }

    // Static assets (CSS, JS, Fonts, Images): Stale-While-Revalidate
    if (
        request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'font' ||
        request.destination === 'image' ||
        url.pathname.startsWith('/build/')
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                const fetchPromise = fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, copy);
                        });
                    }
                    return networkResponse;
                }).catch(() => null);

                return cachedResponse || fetchPromise;
            })
        );
        return;
    }
});
