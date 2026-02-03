<!-- PWA & Notification Permission Modal -->
<div x-data="{
    showModal: false,
    dontShowAgain: false,

    init() {
        const dismissed = localStorage.getItem('pwa-permissions-dismissed');
        const hasPermission = 'Notification' in window && Notification.permission === 'granted';

        // Don't show if already dismissed OR if permission already granted
        if (dismissed === 'true' || hasPermission) {
            return;
        }

        setTimeout(() => {
            this.showModal = true;
        }, 2000);
    },

    async enableAll() {
        try {
            let permissionGranted = false;

            if ('Notification' in window && window.pushManager && typeof window.pushManager.isAdmin === 'function' && window.pushManager.isAdmin()) {
                permissionGranted = await window.pushManager.requestPermission();

                if (permissionGranted) {
                    if (typeof showToast === 'function') {
                        showToast('Notifications enabled successfully!', 'success');
                    }
                    // Mark as completed - don't show again
                    localStorage.setItem('pwa-permissions-dismissed', 'true');
                } else {
                    if (typeof showToast === 'function') {
                        showToast('Notification permission was denied. You can enable it later in your browser settings.', 'warning');
                    }
                    
                }
            } else if ('Notification' in window) {
                const permission = await Notification.requestPermission();

                if (permission === 'granted') {
                    permissionGranted = true;
                    if (typeof showToast === 'function') {
                        showToast('Notifications enabled!', 'success');
                    }
                    // Mark as completed - don't show again
                    localStorage.setItem('pwa-permissions-dismissed', 'true');
                } else if (permission === 'denied') {
                    if (typeof showToast === 'function') {
                        showToast('Notification permission was denied. You can enable it later in your browser settings.', 'warning');
                    }
                    
                }
            }

            // Show PWA install banner if available
            if (window.deferredPrompt) {
                const banner = document.getElementById('pwa-install-banner');
                if (banner) {
                    banner.style.display = 'block';
                }
            }

            // Close modal regardless of permission outcome
            this.close();
        } catch (error) {
            console.error('Error enabling features:', error);
            if (typeof showToast === 'function') {
                showToast('Failed to enable some features', 'error');
            }
        }
    },

    dismiss() {
        
        if (this.dontShowAgain) {
            localStorage.setItem('pwa-permissions-dismissed', 'true');
            
        }
        this.close();
    },

    close() {
        this.showModal = false;
        
    }
}"
     x-show="showModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true">
    <!-- Backdrop -->
    <div x-show="showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
         @click="dismiss()"></div>

    <!-- Modal -->
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">

            <!-- Icon -->
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0M3.124 7.5A8.969 8.969 0 015.292 3m13.416 0a8.969 8.969 0 012.168 4.5" />
                    </svg>
                </div>

                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left flex-1">
                    <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">
                        Enable Notifications & Install App
                    </h3>

                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Get the most out of Smart Campus by enabling notifications and installing the app on your device.
                        </p>
                    </div>

                    <!-- Features List -->
                    <div class="mt-4 space-y-3">
                        <!-- Notifications -->
                        <div class="flex items-start gap-3 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Instant Notifications</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    Get notified immediately when new applications are submitted
                                </p>
                            </div>
                        </div>

                        <!-- Offline Access -->
                        <div class="flex items-start gap-3 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Work Offline</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    Access cached pages even without internet connection
                                </p>
                            </div>
                        </div>

                        <!-- Install App -->
                        <div class="flex items-start gap-3 p-3 rounded-lg bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Install as App</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    Quick access from your desktop or home screen
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                <button type="button"
                        @click="enableAll()"
                        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:w-auto transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Enable All
                </button>
                <button type="button"
                        @click="dismiss()"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto transition-colors">
                    Maybe Later
                </button>
            </div>

            <!-- Don't show again -->
            <div class="mt-4 text-center">
                <label class="inline-flex items-center text-xs text-gray-500 dark:text-gray-400 cursor-pointer">
                    <input type="checkbox" x-model="dontShowAgain" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700 mr-2">
                    Don't show this again
                </label>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] {
    display: none !important;
}
</style>
