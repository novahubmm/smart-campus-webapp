<!-- PWA Install Banner - Bottom Prompt -->
<div x-data="{
    showBanner: false,

    init() {
        // Check if banner was closed in this session
        const bannerClosedThisSession = sessionStorage.getItem('install-banner-closed');

        // Show banner if PWA is not installed and not closed in this session
        setTimeout(() => {
            const isPWA = window.matchMedia('(display-mode: standalone)').matches ||
                         window.navigator.standalone === true;

            // Show banner if not already installed AND not closed in this session
            if (!isPWA && !bannerClosedThisSession) {
                this.showBanner = true;
                
            } else {
                
            }
        }, 3000); // Show 3 seconds after page load

        // Listen for successful installation
        window.addEventListener('pwa-installed', () => {
            
            this.close();
        });

        // Listen for beforeinstallprompt
        window.addEventListener('beforeinstallprompt', (e) => {
            
        });
    },

    async install() {
        if (window.deferredPrompt) {
            try {
                
                // Show the install prompt
                await window.deferredPrompt.prompt();

                // Wait for the user's response
                const { outcome } = await window.deferredPrompt.userChoice;

                

                if (outcome === 'accepted') {
                    
                    if (typeof showToast === 'function') {
                        showToast('Installing app...', 'success');
                    }
                    // Banner will close automatically via 'appinstalled' event
                } else {
                    
                    // Keep banner open if user dismissed
                }

                // Clear the deferredPrompt
                window.deferredPrompt = null;
            } catch (error) {
                console.error('Error showing install prompt:', error);
                alert('Error: ' + error.message);
            }
        } else {
            // Show manual instructions for iOS or if prompt not available
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
            const message = isIOS
                ? 'Tap the Share button (square with arrow) and select Add to Home Screen'
                : 'To install: Open browser menu and select Install app or Add to Home Screen';

            if (typeof showToast === 'function') {
                showToast(message, 'info');
            } else {
                alert(message);
            }

            
        }
    },

    close() {
        // Save to sessionStorage so it doesn't show again in this session
        sessionStorage.setItem('install-banner-closed', 'true');
        this.showBanner = false;
        
    }
}"
     x-show="showBanner"
     x-cloak
     x-transition:enter="transform transition ease-out duration-300"
     x-transition:enter-start="translate-y-full opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transform transition ease-in duration-200"
     x-transition:leave-start="translate-y-0 opacity-100"
     x-transition:leave-end="translate-y-full opacity-0"
     class="fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-gray-800 shadow-lg border-t border-gray-200 dark:border-gray-700">

    <div class="max-w-7xl mx-auto px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <!-- Icon and Message -->
            <div class="flex items-center gap-3 flex-1">
                <!-- App Icon -->
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <!-- Text Content -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        Install Smart Campus App
                    </p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                        Add to Home Screen for quick access and offline support
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <!-- Install Button -->
                <button @click="install()"
                        type="button"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-150 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Install
                </button>

                <!-- Close Button -->
                <button @click="close()"
                        type="button"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150"
                        aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
