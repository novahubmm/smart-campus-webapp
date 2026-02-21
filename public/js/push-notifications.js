/**
 * Push Notification Manager for Admin
 * Handles subscription, permission requests, and notification display
 */

class PushNotificationManager {
    constructor() {
        this.swRegistration = null;
        this.isSubscribed = false;
        this.publicKey = null;
    }

    /**
     * Initialize push notifications
     */
    async init() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.warn('Push notifications not supported');
            return false;
        }

        try {
            // Wait for service worker to be ready
            this.swRegistration = await navigator.serviceWorker.ready;

            // Get VAPID public key from server
            await this.getPublicKey();

            // Check current subscription status
            await this.checkSubscription();

            // Auto-subscribe for admin users if not subscribed
            if (!this.isSubscribed && this.isAdmin()) {
                await this.requestPermission();
            }

            return true;
        } catch (error) {
            console.error('Push notification initialization failed:', error);
            return false;
        }
    }

    /**
     * Get VAPID public key from server
     */
    async getPublicKey() {
        try {
            const response = await fetch('/api/push/public-key');
            const data = await response.json();
            this.publicKey = data.publicKey;
        } catch (error) {
            console.error('Failed to get public key:', error);
            throw error;
        }
    }

    /**
     * Check if user is admin
     */
    isAdmin() {
        // Check if user has admin role or manage settings permission
        const userRole = document.querySelector('meta[name="user-role"]');
        const userPermissions = document.querySelector('meta[name="user-permissions"]');

        return userRole?.content === 'admin' ||
               userPermissions?.content.includes('manage settings');
    }

    /**
     * Check current subscription status
     */
    async checkSubscription() {
        try {
            const subscription = await this.swRegistration.pushManager.getSubscription();
            this.isSubscribed = subscription !== null;

            if (subscription) {
                // Already subscribed
            }

            return this.isSubscribed;
        } catch (error) {
            console.error('Error checking subscription:', error);
            return false;
        }
    }

    /**
     * Request notification permission
     */
    async requestPermission() {
        try {
            const permission = await Notification.requestPermission();

            if (permission === 'granted') {
                await this.subscribe();
                return true;
            } else if (permission === 'denied') {
                console.warn('Notification permission denied');
                return false;
            } else {
                return false;
            }
        } catch (error) {
            console.error('Error requesting permission:', error);
            return false;
        }
    }

    /**
     * Subscribe to push notifications
     */
    async subscribe() {
        try {
            if (!this.publicKey) {
                await this.getPublicKey();
            }

            const subscription = await this.swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.publicKey)
            });

            // Send subscription to server
            const response = await fetch('/api/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(subscription.toJSON())
            });

            const data = await response.json();

            if (data.success) {
                this.isSubscribed = true;
                this.showToast('Push notifications enabled!', 'success');
            } else {
                console.error('Subscription failed:', data.message);
                this.showToast('Failed to enable push notifications', 'error');
            }

            return data.success;
        } catch (error) {
            console.error('Error subscribing:', error);
            this.showToast('Failed to enable push notifications', 'error');
            return false;
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    async unsubscribe() {
        try {
            const subscription = await this.swRegistration.pushManager.getSubscription();

            if (subscription) {
                // Unsubscribe from browser
                await subscription.unsubscribe();

                // Remove subscription from server
                await fetch('/api/push/unsubscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(subscription.toJSON())
                });

                this.isSubscribed = false;
                this.showToast('Push notifications disabled', 'info');
            }
        } catch (error) {
            console.error('Error unsubscribing:', error);
        }
    }

    /**
     * Send test notification
     */
    async sendTest() {
        try {
            const response = await fetch('/api/push/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showToast('Test notification sent!', 'success');
            } else {
                this.showToast('Failed to send test notification', 'error');
            }
        } catch (error) {
            console.error('Error sending test notification:', error);
            this.showToast('Failed to send test notification', 'error');
        }
    }

    /**
     * Convert base64 string to Uint8Array
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        if (typeof showToast === 'function') {
            showToast(message, type);
        }
    }
}

// Create global instance
window.pushManager = new PushNotificationManager();

// Auto-initialize for admin users when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (window.pushManager.isAdmin()) {
            window.pushManager.init();
        }
    });
} else {
    if (window.pushManager.isAdmin()) {
        window.pushManager.init();
    }
}
