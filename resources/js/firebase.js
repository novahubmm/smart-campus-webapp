// Import Firebase modules
import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage, isSupported } from 'firebase/messaging';

// Real Firebase configuration for smart-campus-dafc9 project
const firebaseConfig = {
    apiKey: "AIzaSyAPj9lP2Ho1IIoL_zG9lxkBRMiu1Ps-pj8",
    authDomain: "smart-campus-dafc9.firebaseapp.com",
    projectId: "smart-campus-dafc9",
    storageBucket: "smart-campus-dafc9.firebasestorage.app",
    messagingSenderId: "1009023794548",
    appId: "1:1009023794548:web:797784460f6b1be58b8021",
    measurementId: "G-RK0GRZHR28"
};

// VAPID key for web push (from Firebase Console > Project Settings > Cloud Messaging > Web Push certificates)
const VAPID_KEY = "BEo-JqQVLbjqITH3wyIILkqlr8T-0Qgw8jWGj47bk8P6TgObJju8rOYllYcrR5B8zHTdix36pidb2cOc9mwyvcU";

class FirebaseNotificationManager {
    constructor() {
        this.messaging = null;
        this.isSupported = false;
        this.token = null;
        this.app = null;
        this.initializationAttempted = false;
        this.serviceWorkerRegistration = null;
    }

    async initialize() {
        if (this.initializationAttempted) {
            return this.isSupported;
        }
        
        this.initializationAttempted = true;

        try {
            // Check if messaging is supported first
            const messagingSupported = await isSupported();
            if (!messagingSupported) {
                return false;
            }

            // Check basic browser support
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                return false;
            }

            // Initialize Firebase app
            this.app = initializeApp(firebaseConfig);

            // Initialize messaging
            this.messaging = getMessaging(this.app);

            // Register service worker
            this.serviceWorkerRegistration = await this.registerServiceWorker();

            // Request permission and get token
            const tokenObtained = await this.requestPermissionAndGetToken();

            if (tokenObtained) {
                // Setup foreground message listener
                this.setupForegroundMessageListener();
                this.isSupported = true;
                return true;
            } else {
                return false;
            }

        } catch (error) {
            return false;
        }
    }

    async registerServiceWorker() {
        try {
            const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js', {
                scope: '/'
            });
            
            // Wait for service worker to be ready
            await navigator.serviceWorker.ready;
            
            return registration;
            
        } catch (error) {
            throw error;
        }
    }

    async requestPermissionAndGetToken() {
        try {
            // Check current permission status
            const currentPermission = Notification.permission;
            
            if (currentPermission === 'denied') {
                return null;
            }
            
            // Request permission if not granted
            if (currentPermission !== 'granted') {
                const permission = await Notification.requestPermission();
                
                if (permission !== 'granted') {
                    return null;
                }
            }
            
            // Get FCM token
            const token = await getToken(this.messaging, { 
                vapidKey: VAPID_KEY,
                serviceWorkerRegistration: this.serviceWorkerRegistration
            });
            
            if (token) {
                this.token = token;
                await this.saveFcmToken(token);
                return token;
            } else {
                return null;
            }
            
        } catch (error) {
            return null;
        }
    }

    async saveFcmToken(token) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                return false;
            }

            const response = await fetch('/staff/notifications/save-fcm-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                },
                body: JSON.stringify({ token })
            });

            if (response.ok) {
                return true;
            } else {
                return false;
            }
        } catch (error) {
            return false;
        }
    }

    setupForegroundMessageListener() {
        onMessage(this.messaging, (payload) => {
            // Dispatch custom event for pages to listen (FIRST - so pages can update)
            window.dispatchEvent(new CustomEvent('fcm-notification-received', { detail: payload }));
            
            // Immediately increment the badge count (optimistic update)
            this.incrementBadgeCount();
            
            // Then fetch the actual count with retry mechanism to ensure DB is synced
            this.updateCountWithRetry(3, 500);

            // Show browser notification for foreground messages
            if (payload.notification) {
                this.showBrowserNotification(payload.notification.title, payload.notification.body);
            }

            // Show toast notification
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    type: 'info',
                    text: payload.notification?.body || 'New notification received'
                }
            }));

            // Add visual pulse effect to the bell
            const badge = document.getElementById('notification-badge');
            if (badge) {
                const bellIcon = badge.parentElement?.querySelector('i');
                if (bellIcon) {
                    bellIcon.classList.add('animate-pulse', 'text-red-500');
                    setTimeout(() => {
                        bellIcon.classList.remove('animate-pulse', 'text-red-500');
                    }, 3000);
                }
            }
        });
    }

    /**
     * Immediately increment the badge count (optimistic update)
     * This ensures the user sees the count change right away
     */
    incrementBadgeCount() {
        const badge = document.getElementById('notification-badge');
        const countElement = document.getElementById('notification-count');
        
        if (badge && countElement) {
            // Get current count
            let currentCount = parseInt(countElement.textContent) || 0;
            if (countElement.textContent === '99+') {
                currentCount = 100;
            }
            
            // Increment
            const newCount = currentCount + 1;
            
            // Update display
            badge.classList.remove('hidden');
            countElement.textContent = newCount > 99 ? '99+' : newCount;
        }
    }

    /**
     * Update notification count with retry mechanism
     * This handles the race condition where FCM arrives before DB commit
     */
    async updateCountWithRetry(maxRetries, delayMs) {
        const badge = document.getElementById('notification-badge');
        const countElement = document.getElementById('notification-count');
        
        if (!badge || !countElement) return;
        
        // Get the optimistic count we just set
        let expectedMinCount = parseInt(countElement.textContent) || 1;
        if (countElement.textContent === '99+') {
            expectedMinCount = 100;
        }
        
        for (let attempt = 1; attempt <= maxRetries; attempt++) {
            // Wait before fetching (give DB time to commit)
            await new Promise(resolve => setTimeout(resolve, delayMs));
            
            try {
                if (typeof window.updateNotificationCount === 'function') {
                    window.updateNotificationCount();
                }
                
                // Small delay to let the fetch complete
                await new Promise(resolve => setTimeout(resolve, 200));
                
                // Check if count was updated
                const newCount = parseInt(countElement.textContent) || 0;
                if (newCount >= expectedMinCount - 1) {
                    return;
                }
            } catch (error) {
                // Silent fail
            }
        }
    }

    showBrowserNotification(title, body) {
        if (Notification.permission === 'granted') {
            new Notification(title, {
                body: body,
                icon: '/smart-campus-logo.svg',
                badge: '/smart-campus-logo.svg',
                tag: 'smart-campus-notification'
            });
        }
    }

    getToken() {
        return this.token;
    }

    isInitialized() {
        return this.isSupported && this.token !== null;
    }
}

// Export for global use
window.FirebaseNotificationManager = FirebaseNotificationManager;

export default FirebaseNotificationManager;
