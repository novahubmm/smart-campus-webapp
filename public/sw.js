self.addEventListener('install', (event) => {
    console.log('Service Worker: Installed');
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
    console.log('Service Worker: Activated');
    event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Basic pass-through fetch for now. 
    // We can implement caching strategies here later.
    event.respondWith(fetch(event.request));
});
