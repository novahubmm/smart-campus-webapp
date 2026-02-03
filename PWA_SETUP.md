# Progressive Web App (PWA) Setup

> Status: PWA/service worker and push notifications are **disabled for now** while we focus on school management features. Keep registration/scripts off in layouts. Use this guide when we are ready to re-enable.

Smart Campus includes PWA assets (manifest, service worker, icons) ready to be turned back on when the school modules are stable.

## Features Implemented

These activate once we turn PWA back on (service worker + manifest).

✅ **Installable** - Users can install the app on desktop and mobile devices
✅ **Offline Support** - Service worker caches assets and pages for offline access
✅ **App-like Experience** - Runs in standalone mode without browser UI
✅ **Fast Loading** - Cached assets load instantly
✅ **Responsive** - Works on all screen sizes
✅ **Push Notifications** - Ready for push notifications (backend implementation needed)
✅ **Background Sync** - Queues actions when offline and syncs when online

## Files Added

### Core PWA Files

-   `public/manifest.json` - PWA manifest with app metadata and icons
-   `public/service-worker.js` - Service worker for caching and offline support
-   `public/js/pwa.js` - PWA installation and offline detection logic
-   `public/css/pwa.css` - Styles for offline banner and install prompt
-   `resources/views/offline.blade.php` - Offline fallback page
-   `public/images/icons/` - PWA icons (72x72 to 512x512)

### Modified Files

-   `resources/views/layouts/app.blade.php` - Added PWA meta tags and scripts
-   `routes/web.php` - Added /offline route

## Generate Icons

1. Open `public/images/icons/generate-icons.html` in your browser
2. It will automatically download all required icon sizes
3. Save them in the `public/images/icons/` directory

**Or** use your own logo/icon and create these sizes:

-   72x72, 96x96, 128x128, 144x144, 152x152, 192x192, 384x384, 512x512

## Testing PWA

### Desktop (Chrome/Edge)

1. Visit `http://127.0.0.1:8000`
2. Look for install icon in address bar (⊕)
3. Click to install
4. App opens in standalone window

### Mobile (Chrome/Safari)

1. Visit your site on mobile
2. **Chrome**: Tap "Add to Home Screen" banner or menu
3. **Safari**: Tap Share → Add to Home Screen
4. App icon appears on home screen

### Test Offline Mode

1. Open DevTools → Network tab
2. Select "Offline" from throttling dropdown
3. Refresh page - should still work with cached content
4. Navigate to previously visited pages - they load from cache
5. You'll see an orange "You are offline" banner at the top

## Caching Strategy

### Static Assets (CSS, JS, Images)

-   **Strategy**: Cache First with background update
-   **Behavior**: Loads from cache instantly, updates cache in background

### Pages (HTML)

-   **Strategy**: Network First with cache fallback
-   **Behavior**: Tries network first, falls back to cache if offline

### API Requests

-   **Strategy**: Network only (with offline error handling)
-   **Behavior**: Returns offline error message when no connection

## Customization

### Change App Colors

Edit `public/manifest.json`:

```json
{
    "theme_color": "#4d46e5",
    "background_color": "#ffffff"
}
```

### Modify Cache Strategy

Edit `public/service-worker.js` - change the fetch event handler logic

### Add Pages to Cache

Edit `public/service-worker.js`:

```javascript
const STATIC_CACHE_URLS = [
    "/",
    "/login",
    "/dashboard", // Add more pages
];
```

### Customize Offline Page

Edit `resources/views/offline.blade.php`

## Production Deployment

### HTTPS Required

PWA requires HTTPS in production (service workers won't register on HTTP except localhost)

### Update Service Worker Version

When making changes, update cache version in `service-worker.js`:

```javascript
const CACHE_NAME = "nova-hub-v2"; // Increment version
```

### Clear Old Caches

Users will automatically get new version on next visit

## Browser Support

✅ Chrome/Edge (Desktop & Mobile) - Full support
✅ Safari (iOS 11.3+) - Full support  
✅ Firefox - Full support
✅ Samsung Internet - Full support
⚠️ IE - No support (PWA features gracefully degrade)

## Troubleshooting

### Service Worker Not Registering

-   Check browser console for errors
-   Ensure HTTPS (or localhost)
-   Clear browser cache and hard reload

### Icons Not Showing

-   Generate all required icon sizes
-   Check manifest.json paths are correct
-   Clear browser cache

### Offline Mode Not Working

-   Check service worker is active (DevTools → Application → Service Workers)
-   Verify cache contains expected files
-   Check network requests in DevTools

### Install Prompt Not Showing

-   Already installed apps won't show prompt
-   Some browsers require user engagement first
-   Check manifest.json is valid

## Future Enhancements

-   [ ] Push notifications backend implementation
-   [ ] Background sync for form submissions
-   [ ] Periodic background sync for data updates
-   [ ] Share API integration
-   [ ] File handling
-   [ ] Shortcuts in manifest

## Resources

-   [MDN: Progressive Web Apps](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps)
-   [Web.dev: PWA](https://web.dev/progressive-web-apps/)
-   [Workbox (Advanced Service Worker Library)](https://developers.google.com/web/tools/workbox)

---

**Note**: The app is now PWA-ready! Users can install it and use core features offline. Test thoroughly before deploying to production.
