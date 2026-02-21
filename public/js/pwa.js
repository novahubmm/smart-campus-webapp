// Service Worker Registration
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker
      .register('/service-worker.js')
      .then((registration) => {
        // Service Worker registered successfully

        // Check for updates
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing;

          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              // New service worker available, show update notification
              showUpdateNotification();
            }
          });
        });
      })
      .catch((error) => {
        console.error('Service Worker registration failed:', error);
      });

    // Handle controller change (new service worker activated)
    navigator.serviceWorker.addEventListener('controllerchange', () => {
      window.location.reload();
    });
  });
}

// Show update notification to user
function showUpdateNotification() {
  // You can customize this notification UI
  if (confirm('A new version is available! Reload to update?')) {
    window.location.reload();
  }
}

// Install prompt for PWA
window.deferredPrompt = null;

window.addEventListener('beforeinstallprompt', (e) => {
  // Prevent the mini-infobar from appearing on mobile
  e.preventDefault();

  // Stash the event so it can be triggered later globally
  window.deferredPrompt = e;

  // Show install button/banner (you can customize this UI)
  showInstallPromotion();
});

// Listen for successful app installation
window.addEventListener('appinstalled', (e) => {
  // Clear the deferredPrompt
  window.deferredPrompt = null;

  // Dispatch custom event for components to listen
  window.dispatchEvent(new CustomEvent('pwa-installed'));

  // Show success notification
  if (typeof showToast === 'function') {
    showToast('App installed successfully!', 'success');
  }
});

// Show install promotion
function showInstallPromotion() {
  const installBanner = document.getElementById('pwa-install-banner');
  if (installBanner) {
    installBanner.style.display = 'block';
  }
}

// Install PWA when user clicks install button
window.installPWA = async () => {
  if (!window.deferredPrompt) {
    return;
  }

  // Show the install prompt
  window.deferredPrompt.prompt();

  // Wait for the user to respond to the prompt
  const { outcome } = await window.deferredPrompt.userChoice;

  if (outcome === 'accepted') {
    // User accepted the install prompt
  } else {
    // User dismissed the install prompt
  }

  // Clear the deferredPrompt
  window.deferredPrompt = null;

  // Hide install banner
  const installBanner = document.getElementById('pwa-install-banner');
  if (installBanner) {
    installBanner.style.display = 'none';
  }
};

// Detect if app is running as PWA
function isPWA() {
  return window.matchMedia('(display-mode: standalone)').matches ||
         window.navigator.standalone === true;
}

// Online/Offline detection
window.addEventListener('online', () => {
  document.body.classList.remove('offline');

  // Show online notification
  showToast('You are back online!', 'success');

  // Trigger background sync if available
  if ('serviceWorker' in navigator && 'sync' in ServiceWorkerRegistration.prototype) {
    navigator.serviceWorker.ready.then((registration) => {
      return registration.sync.register('sync-forms');
    });
  }
});

window.addEventListener('offline', () => {
  document.body.classList.add('offline');

  // Show offline notification
  showToast('You are offline. Some features may be limited.', 'warning');
});

// Toast notification helper - uses alert dialog instead of toast
function showToast(message, type = 'info') {
  // Dispatch alert-show event to trigger the alert dialog
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent('alert-show', {
      detail: {
        message: message,
        type: type
      }
    }));
  }
}

// Check if currently offline on page load
if (!navigator.onLine) {
  document.body.classList.add('offline');
}
// PWA status tracking
// PWA Mode: isPWA() ? 'Installed' : 'Browser'
// Online Status: navigator.onLine ? 'Online' : 'Offline'
