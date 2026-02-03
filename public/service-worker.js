const CACHE_NAME = 'nova-hub-v4';
const RUNTIME_CACHE = 'nova-hub-runtime-v4';

// Assets to cache on install (only URLs that definitely exist)
const STATIC_CACHE_URLS = [
  '/',
  '/offline',
  '/manifest.json'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('Service Worker: Installing...');

  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Service Worker: Caching static assets');
        // Cache URLs that exist, ignore failures for others
        return Promise.allSettled(
          STATIC_CACHE_URLS.map(url => 
            cache.add(new Request(url, { cache: 'reload' }))
              .catch(err => console.log('Failed to cache:', url, err))
          )
        );
      })
      .then(() => {
        console.log('Service Worker: Installation complete');
        return self.skipWaiting();
      })
      .catch((error) => {
        console.error('Service Worker: Install failed', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('Service Worker: Activating...');

  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames
            .filter((cacheName) => cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE)
            .map((cacheName) => {
              console.log('Service Worker: Deleting old cache', cacheName);
              return caches.delete(cacheName);
            })
        );
      })
      .then(() => self.clients.claim())
  );
});

// Fetch event - Network First with Cache Fallback strategy
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip cross-origin requests
  if (url.origin !== location.origin) {
    return;
  }

  // Skip POST, PUT, DELETE, PATCH requests (only cache GET requests)
  if (request.method !== 'GET') {
    event.respondWith(fetch(request));
    return;
  }

  // Skip API requests for now (they need network)
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(
      fetch(request)
        .catch(() => {
          return new Response(
            JSON.stringify({ error: 'Offline', message: 'You are currently offline' }),
            {
              headers: { 'Content-Type': 'application/json' },
              status: 503
            }
          );
        })
    );
    return;
  }

  // For navigation requests (HTML pages)
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then((response) => {
          // Clone the response
          const responseClone = response.clone();

          // Cache the page
          caches.open(RUNTIME_CACHE)
            .then((cache) => cache.put(request, responseClone));

          return response;
        })
        .catch(() => {
          // Try to return cached version
          return caches.match(request)
            .then((cachedResponse) => {
              if (cachedResponse) {
                return cachedResponse;
              }
              // Return offline page
              return caches.match('/offline');
            });
        })
    );
    return;
  }

  // For other requests (CSS, JS, images, etc.) - Cache First strategy
  event.respondWith(
    caches.match(request)
      .then((cachedResponse) => {
        if (cachedResponse) {
          // Return cached version and update cache in background
          fetch(request)
            .then((response) => {
              caches.open(RUNTIME_CACHE)
                .then((cache) => cache.put(request, response));
            })
            .catch(() => {}); // Ignore fetch errors for background updates

          return cachedResponse;
        }

        // Not in cache, fetch from network
        return fetch(request)
          .then((response) => {
            // Don't cache non-successful responses
            if (!response || response.status !== 200 || response.type === 'error') {
              return response;
            }

            // Clone the response
            const responseClone = response.clone();

            // Cache the new response
            caches.open(RUNTIME_CACHE)
              .then((cache) => cache.put(request, responseClone));

            return response;
          })
          .catch(() => {
            // If it's an image, return a placeholder
            if (request.destination === 'image') {
              return new Response(
                '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"><rect fill="#f0f0f0" width="200" height="200"/><text fill="#999" x="50%" y="50%" text-anchor="middle" dy=".3em">Offline</text></svg>',
                { headers: { 'Content-Type': 'image/svg+xml' } }
              );
            }
          });
      })
  );
});

// Background sync for form submissions when back online
self.addEventListener('sync', (event) => {
  console.log('Service Worker: Background sync', event.tag);

  if (event.tag === 'sync-forms') {
    event.waitUntil(
      // Implement your sync logic here
      Promise.resolve()
    );
  }
});

// Push notification support
self.addEventListener('push', (event) => {
  console.log('Service Worker: Push notification received');

  let data = {
    title: 'Nova Hub',
    body: 'You have a new notification',
    icon: '/images/icons/icon-192x192.png',
    badge: '/images/icons/icon-72x72.png',
    vibrate: [200, 100, 200],
  };

  if (event.data) {
    try {
      data = event.data.json();
    } catch (e) {
      data.body = event.data.text();
    }
  }

  const options = {
    body: data.body,
    icon: data.icon || '/images/icons/icon-192x192.png',
    badge: data.badge || '/images/icons/icon-72x72.png',
    vibrate: data.vibrate || [200, 100, 200],
    tag: data.tag || 'default-notification',
    data: {
      url: data.url || '/',
      ...data
    },
    actions: data.actions || [
      {
        action: 'open',
        title: 'Open'
      },
      {
        action: 'close',
        title: 'Close'
      }
    ],
    requireInteraction: data.requireInteraction || false,
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'Nova Hub', options)
  );
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
  console.log('Service Worker: Notification clicked', event.action);

  event.notification.close();

  if (event.action === 'close') {
    return;
  }

  // Get the URL from notification data
  const urlToOpen = event.notification.data?.url || '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true })
      .then((clientList) => {
        // Check if there's already a window open
        for (const client of clientList) {
          if (client.url === urlToOpen && 'focus' in client) {
            return client.focus();
          }
        }
        // Otherwise open a new window
        if (clients.openWindow) {
          return clients.openWindow(urlToOpen);
        }
      })
  );
});
