const CACHE = 'tipping-pool-v1';
const SHELL = [
    '/',
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE).then(c => c.addAll(SHELL))
    );
    self.skipWaiting();
});

self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', e => {
    if (!e.request.url.startsWith('http')) return;

    // Only handle GET requests
    if (e.request.method !== 'GET') return;

    // Network-first for API calls — always get fresh data
    if (e.request.url.includes('/api/')) {
        e.respondWith(
            fetch(e.request).catch(() =>
                caches.match(e.request)
            )
        );
        return;
    }

    // Cache-first for static assets
    if (e.request.destination === 'image' ||
        e.request.destination === 'style' ||
        e.request.destination === 'script') {
        e.respondWith(
            caches.match(e.request).then(cached =>
                cached || fetch(e.request).then(res => {
                    const clone = res.clone();
                    caches.open(CACHE).then(c => c.put(e.request, clone));
                    return res;
                })
            )
        );
        return;
    }

    // Network-first for everything else (HTML pages)
    e.respondWith(
        fetch(e.request).catch(() => caches.match('/'))
    );
});
