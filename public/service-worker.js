const CACHE_NAME = 'laravel-app-v1.2';
const STATIC_CACHE = 'static-v1.2';
const DYNAMIC_CACHE = 'dynamic-v1.2';

// Assets to cache on install
const STATIC_ASSETS = [
    '/',
    '/dresses',
    '/offline.html',
    '/manifest.json',
    // Build assets will be added dynamically
];

// Install event - cache static assets
self.addEventListener('install', function(event) {
    event.waitUntil(
        Promise.all([
            // Cache static assets
            caches.open(STATIC_CACHE).then(function(cache) {
                return cache.addAll(STATIC_ASSETS);
            }),
            // Cache build assets if available
            caches.open(CACHE_NAME).then(async function(cache) {
                try {
                    // Try to cache built assets
                    const buildAssets = [
                        '/build/assets/app.css',
                        '/build/assets/app.js',
                    ];
                    return cache.addAll(buildAssets);
                } catch (error) {
                    console.log('Build assets not available during install');
                }
            })
        ]).then(() => {
            // Force activation of new service worker
            return self.skipWaiting();
        })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheName !== CACHE_NAME && 
                        cacheName !== STATIC_CACHE && 
                        cacheName !== DYNAMIC_CACHE) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            return self.clients.claim();
        })
    );
});

// Fetch event - serve from cache with network fallback
self.addEventListener('fetch', function(event) {
    const { request } = event;
    const url = new URL(request.url);

    // Handle different types of requests
    if (request.method === 'GET') {
        event.respondWith(
            caches.match(request).then(function(cachedResponse) {
                // Return cached version if available
                if (cachedResponse) {
                    return cachedResponse;
                }

                // For navigation requests, try network first
                if (request.mode === 'navigate') {
                    return fetch(request).then(function(response) {
                        // Cache successful navigation responses
                        if (response.status === 200) {
                            const responseClone = response.clone();
                            caches.open(DYNAMIC_CACHE).then(function(cache) {
                                cache.put(request, responseClone);
                            });
                        }
                        return response;
                    }).catch(function() {
                        // Fallback to offline page for navigation
                        return caches.match('/offline.html');
                    });
                }

                // For static assets (CSS, JS, images, fonts)
                if (request.destination === 'style' || 
                    request.destination === 'script' || 
                    request.destination === 'image' ||
                    request.destination === 'font') {
                    return fetch(request).then(function(response) {
                        // Cache successful asset responses
                        if (response.status === 200) {
                            const responseClone = response.clone();
                            caches.open(STATIC_CACHE).then(function(cache) {
                                cache.put(request, responseClone);
                            });
                        }
                        return response;
                    }).catch(function() {
                        // Return cached version or fail gracefully
                        return caches.match(request);
                    });
                }

                // For API requests, try network first with cache fallback
                if (url.pathname.startsWith('/api/')) {
                    return fetch(request).then(function(response) {
                        // Only cache successful API responses
                        if (response.status === 200) {
                            const responseClone = response.clone();
                            caches.open(DYNAMIC_CACHE).then(function(cache) {
                                cache.put(request, responseClone);
                            });
                        }
                        return response;
                    }).catch(function() {
                        return caches.match(request);
                    });
                }

                // Default: network first
                return fetch(request).catch(function() {
                    return caches.match(request);
                });
            })
        );
    }
});

// Background sync for better offline experience
self.addEventListener('sync', function(event) {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

function doBackgroundSync() {
    // Implement background sync logic here
    console.log('Background sync triggered');
}
