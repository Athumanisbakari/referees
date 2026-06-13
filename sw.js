const CACHE_NAME = 'ilala-referees-v1';

const PRECACHE_URLS = [
    './',
    './login.php',
    './dashboard.php',
    './manifest.php',
    './assets/css/style.css',
    './assets/js/app.js',
    './assets/js/pwa.js',
    './assets/icons/icon.svg',
    './assets/vendor/bootstrap/css/bootstrap.min.css',
    './assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
    './assets/vendor/bootstrap-icons/bootstrap-icons.min.css',
    './assets/vendor/bootstrap-icons/fonts/bootstrap-icons.woff2',
    './assets/vendor/leaflet/leaflet.css',
    './assets/vendor/leaflet/leaflet.js',
    './assets/vendor/chartjs/chart.umd.min.js',
];

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function (cache) {
            return cache.addAll(PRECACHE_URLS);
        }).then(function () {
            return self.skipWaiting();
        })
    );
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(
                keys.filter(function (key) {
                    return key !== CACHE_NAME;
                }).map(function (key) {
                    return caches.delete(key);
                })
            );
        }).then(function () {
            return self.clients.claim();
        })
    );
});

self.addEventListener('fetch', function (event) {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (url.pathname.includes('/assets/')) {
        event.respondWith(
            caches.match(event.request).then(function (cached) {
                if (cached) {
                    return cached;
                }

                return fetch(event.request).then(function (response) {
                    if (response && response.status === 200) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then(function (cache) {
                            cache.put(event.request, copy);
                        });
                    }
                    return response;
                });
            })
        );
        return;
    }

    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(function () {
                return caches.match('./login.php');
            })
        );
    }
});
