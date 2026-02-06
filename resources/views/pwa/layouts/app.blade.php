<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="{{ $themeColor ?? '#26BFFF' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Smart Campus">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('smart-campus-browser-tab.svg') }}">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">
    
    <title>{{ $title ?? 'Smart Campus' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    
    <!-- PWA Styles -->
    @vite(['resources/css/pwa.css'])
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @stack('styles')
    
    <style>
        /* Prevent pull-to-refresh on iOS */
        body {
            overscroll-behavior-y: contain;
        }
        
        /* Hide scrollbar but keep functionality */
        .pwa-content::-webkit-scrollbar {
            display: none;
        }
        .pwa-content {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="theme-{{ $theme ?? 'guardian' }}">
    <div class="pwa-container" x-data="pwaApp()">
        
        <!-- Top Header -->
        @if(!isset($hideHeader) || !$hideHeader)
            <header class="pwa-header">
                <div class="pwa-header-content">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        @if(isset($showBack) && $showBack)
                            <a href="{{ $backUrl ?? 'javascript:history.back()' }}" class="pwa-header-icon">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        @endif
                        <h1 class="pwa-header-title">{{ $headerTitle ?? 'Smart Campus' }}</h1>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 8px;">
                        @if(isset($showNotifications) && $showNotifications)
                            <a href="{{ route('pwa.notifications') }}" class="pwa-header-icon" style="position: relative;">
                                <i class="fas fa-bell"></i>
                                @if(isset($unreadCount) && $unreadCount > 0)
                                    <span class="bottom-nav-badge" style="top: -4px; right: -4px;">
                                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                    </span>
                                @endif
                            </a>
                        @endif
                        
                        @if(isset($headerActions))
                            {!! $headerActions !!}
                        @endif
                    </div>
                </div>
            </header>
        @endif
        
        <!-- Main Content -->
        <main class="pwa-content" id="pwa-content">
            <div class="p-md">
                @yield('content')
            </div>
        </main>
        
        <!-- Bottom Navigation -->
        @if(!isset($hideBottomNav) || !$hideBottomNav)
            @include('pwa.layouts.bottom-nav', ['active' => $activeNav ?? 'home'])
        @endif
        
        <!-- Install Prompt (shown when PWA is installable) -->
        <div x-show="showInstallPrompt" 
             x-cloak
             @click.away="showInstallPrompt = false"
             style="position: fixed; bottom: calc(var(--bottom-nav-height) + 16px); left: 16px; right: 16px; background: white; border-radius: 12px; padding: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <img src="{{ asset('smart-campus-logo.svg') }}" alt="Smart Campus" style="width: 48px; height: 48px;">
                <div style="flex: 1;">
                    <div style="font-weight: 600; font-size: 16px; color: var(--text-primary);">Install Smart Campus</div>
                    <div style="font-size: 14px; color: var(--text-secondary);">Add to home screen for quick access</div>
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                <button @click="showInstallPrompt = false" class="pwa-btn pwa-btn-secondary" style="flex: 1;">
                    Later
                </button>
                <button @click="installPWA()" class="pwa-btn pwa-btn-primary" style="flex: 1;">
                    Install
                </button>
            </div>
        </div>
        
        <!-- Offline Indicator -->
        <div x-show="!isOnline" 
             x-cloak
             style="position: fixed; top: var(--top-header-height); left: 0; right: 0; background: var(--warning); color: white; padding: 8px; text-align: center; font-size: 14px; z-index: 99;">
            <i class="fas fa-wifi-slash"></i> You're offline. Some features may be limited.
        </div>
    </div>
    
    <!-- PWA JavaScript -->
    <script>
        function pwaApp() {
            return {
                isOnline: navigator.onLine,
                showInstallPrompt: false,
                deferredPrompt: null,
                
                init() {
                    // Listen for online/offline events
                    window.addEventListener('online', () => this.isOnline = true);
                    window.addEventListener('offline', () => this.isOnline = false);
                    
                    // Listen for install prompt
                    window.addEventListener('beforeinstallprompt', (e) => {
                        e.preventDefault();
                        this.deferredPrompt = e;
                        // Show install prompt after 3 seconds
                        setTimeout(() => {
                            if (!window.matchMedia('(display-mode: standalone)').matches) {
                                this.showInstallPrompt = true;
                            }
                        }, 3000);
                    });
                    
                    // Register service worker
                    if ('serviceWorker' in navigator) {
                        navigator.serviceWorker.register('/sw.js')
                            .then(reg => console.log('Service Worker registered', reg))
                            .catch(err => console.log('Service Worker registration failed', err));
                    }
                    
                    // Request notification permission
                    if ('Notification' in window && Notification.permission === 'default') {
                        setTimeout(() => {
                            Notification.requestPermission();
                        }, 5000);
                    }
                },
                
                async installPWA() {
                    if (!this.deferredPrompt) return;
                    
                    this.deferredPrompt.prompt();
                    const { outcome } = await this.deferredPrompt.userChoice;
                    
                    if (outcome === 'accepted') {
                        console.log('PWA installed');
                    }
                    
                    this.deferredPrompt = null;
                    this.showInstallPrompt = false;
                }
            }
        }
        
        // Pull to refresh
        let startY = 0;
        let currentY = 0;
        let pulling = false;
        
        const content = document.getElementById('pwa-content');
        
        content.addEventListener('touchstart', (e) => {
            if (content.scrollTop === 0) {
                startY = e.touches[0].pageY;
                pulling = true;
            }
        });
        
        content.addEventListener('touchmove', (e) => {
            if (!pulling) return;
            currentY = e.touches[0].pageY;
            const diff = currentY - startY;
            
            if (diff > 0 && diff < 100) {
                e.preventDefault();
            }
        });
        
        content.addEventListener('touchend', () => {
            if (!pulling) return;
            const diff = currentY - startY;
            
            if (diff > 80) {
                window.location.reload();
            }
            
            pulling = false;
            startY = 0;
            currentY = 0;
        });
    </script>
    
    @stack('scripts')
</body>
</html>
