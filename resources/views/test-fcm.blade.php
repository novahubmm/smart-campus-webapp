<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FCM Test - Smart Campus</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        button { padding: 10px 20px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
        button:disabled { background: #6c757d; cursor: not-allowed; }
        #console { background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin: 10px 0; height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px; }
        .debug-info { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔥 Firebase FCM Debug Page</h1>
    
    <div class="info">
        <strong>Debug Steps:</strong>
        <ol>
            <li>Check browser support and permissions</li>
            <li>Initialize Firebase manually</li>
            <li>Get FCM token</li>
            <li>Test notifications</li>
        </ol>
        
        <strong>Chrome Troubleshooting:</strong>
        <ul>
            <li><strong>Check Chrome flags:</strong> Visit <code>chrome://flags</code> and ensure Service Workers are enabled</li>
            <li><strong>Clear browser data:</strong> Settings → Privacy → Clear browsing data</li>
            <li><strong>Disable extensions:</strong> Try in incognito mode or disable all extensions</li>
            <li><strong>Check enterprise policies:</strong> Visit <code>chrome://policy</code> to see restrictions</li>
        </ul>
    </div>

    <div id="status"></div>

    <div class="debug-info">
        <strong>Browser Support:</strong>
        <div id="browserSupport"></div>
    </div>

    <div>
        <button id="checkSupport">🔍 Check Browser Support</button>
        <button id="initFirebase">🚀 Initialize Firebase</button>
        <button id="requestPermission">🔔 Request Permission</button>
        <button id="checkServiceWorker">⚙️ Check Service Worker</button>
        <button id="getToken">🎫 Get FCM Token</button>
        <button id="testNotification">� Test Local MNotification</button>
        <button id="sendFcmTest" disabled>🔥 Send FCM Test</button>
    </div>

    <div>
        <h3>Debug Console:</h3>
        <div id="console"></div>
    </div>

    <script type="module">
        let firebaseApp = null;
        let messaging = null;
        let userToken = null;

        // Console logging
        function log(message, type = 'info') {
            const console = document.getElementById('console');
            const timestamp = new Date().toLocaleTimeString();
            const color = type === 'error' ? 'red' : type === 'success' ? 'green' : type === 'warning' ? 'orange' : 'black';
            console.innerHTML += `<div style="color: ${color}">[${timestamp}] ${message}</div>`;
            console.scrollTop = console.scrollHeight;
        }

        function showStatus(message, type = 'info') {
            const status = document.getElementById('status');
            status.innerHTML = `<div class="${type}">${message}</div>`;
        }

        // Check browser support
        document.getElementById('checkSupport').addEventListener('click', () => {
            log('Checking browser support...', 'info');
            
            const support = {
                serviceWorker: 'serviceWorker' in navigator,
                pushManager: 'PushManager' in window,
                notification: 'Notification' in window,
                https: location.protocol === 'https:' || location.hostname === 'localhost'
            };
            
            const supportDiv = document.getElementById('browserSupport');
            supportDiv.innerHTML = Object.entries(support)
                .map(([key, value]) => `${key}: ${value ? '✅' : '❌'}`)
                .join('<br>');
            
            log(`Browser support: ${JSON.stringify(support)}`, 'info');
            
            if (!support.serviceWorker) {
                log('⚠️ Service Workers not supported. FCM will not work.', 'warning');
                log('Solutions:', 'info');
                log('1. Use Chrome, Firefox, Safari, or Edge', 'info');
                log('2. Enable HTTPS (required for most browsers)', 'info');
                log('3. Update your browser to the latest version', 'info');
                showStatus('❌ Service Workers not supported - FCM cannot work', 'error');
                return;
            }
            
            if (!support.https) {
                log('⚠️ HTTPS not enabled. FCM may not work properly.', 'warning');
                log('For production, you need HTTPS', 'warning');
            }
            
            if (Object.values(support).every(v => v)) {
                showStatus('✅ Browser fully supports FCM', 'success');
            } else {
                showStatus('⚠️ Browser has limited FCM support', 'error');
            }
        });

        // Initialize Firebase
        document.getElementById('initFirebase').addEventListener('click', async () => {
            try {
                log('Initializing Firebase...', 'info');
                
                // Import Firebase modules from CDN
                const { initializeApp } = await import('https://www.gstatic.com/firebasejs/9.0.0/firebase-app.js');
                const { getMessaging, getToken, onMessage } = await import('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging.js');
                
                // Firebase configuration
                const firebaseConfig = {
                    apiKey: "AIzaSyAPj9lP2Ho1IIoL_zG9lxkBRMiu1Ps-pj8",
                    authDomain: "smart-campus-dafc9.firebaseapp.com",
                    projectId: "smart-campus-dafc9",
                    storageBucket: "smart-campus-dafc9.firebasestorage.app",
                    messagingSenderId: "1009023794548",
                    appId: "1:1009023794548:web:797784460f6b1be58b8021",
                    measurementId: "G-RK0GRZHR28"
                };
                
                log('Firebase config loaded', 'success');
                
                // Initialize Firebase
                firebaseApp = initializeApp(firebaseConfig);
                messaging = getMessaging(firebaseApp);
                
                log('Firebase app initialized', 'success');
                
                // Register service worker
                const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                log(`Service worker registered: ${registration.scope}`, 'success');
                
                // Listen for foreground messages
                onMessage(messaging, (payload) => {
                    log(`Foreground message: ${JSON.stringify(payload)}`, 'success');
                });
                
                showStatus('✅ Firebase initialized successfully!', 'success');
                
            } catch (error) {
                log(`Firebase initialization error: ${error.message}`, 'error');
                showStatus(`❌ Firebase initialization failed: ${error.message}`, 'error');
            }
        });

        // Check Service Worker status
        document.getElementById('checkServiceWorker').addEventListener('click', async () => {
            try {
                log('Checking service worker status...', 'info');
                
                if ('serviceWorker' in navigator) {
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    log(`Found ${registrations.length} service worker registrations`, 'info');
                    
                    registrations.forEach((reg, index) => {
                        log(`Registration ${index + 1}:`, 'info');
                        log(`  Scope: ${reg.scope}`, 'info');
                        log(`  Active: ${reg.active ? '✅' : '❌'}`, reg.active ? 'success' : 'error');
                        log(`  Installing: ${reg.installing ? '⏳' : '❌'}`, reg.installing ? 'warning' : 'info');
                        log(`  Waiting: ${reg.waiting ? '⏳' : '❌'}`, reg.waiting ? 'warning' : 'info');
                        
                        if (reg.active) {
                            log(`  Active SW URL: ${reg.active.scriptURL}`, 'success');
                            log(`  Active SW State: ${reg.active.state}`, 'success');
                        }
                    });
                    
                    // Check if ready
                    const ready = await navigator.serviceWorker.ready;
                    log(`Service Worker ready: ${ready ? '✅' : '❌'}`, ready ? 'success' : 'error');
                    
                    if (ready && ready.active) {
                        log('✅ Service Worker is active and ready for FCM!', 'success');
                        showStatus('✅ Service Worker is ready! You can now get FCM token.', 'success');
                    } else {
                        log('⚠️ Service Worker not fully active yet', 'warning');
                        showStatus('⚠️ Service Worker still activating. Wait a moment and try again.', 'error');
                    }
                } else {
                    log('❌ Service Workers not supported', 'error');
                }
            } catch (error) {
                log(`Service Worker check error: ${error.message}`, 'error');
            }
        });

        // Request permission
        document.getElementById('requestPermission').addEventListener('click', async () => {
            try {
                log('Requesting notification permission...', 'info');
                
                const permission = await Notification.requestPermission();
                log(`Permission result: ${permission}`, permission === 'granted' ? 'success' : 'error');
                
                if (permission === 'granted') {
                    showStatus('✅ Notification permission granted!', 'success');
                } else if (permission === 'denied') {
                    showStatus('❌ Notification permission denied', 'error');
                    log('To fix: Go to browser settings and allow notifications for this site', 'warning');
                } else {
                    showStatus('⚠️ Notification permission dismissed', 'error');
                }
                
            } catch (error) {
                log(`Permission error: ${error.message}`, 'error');
            }
        });

        // Get FCM token
        document.getElementById('getToken').addEventListener('click', async () => {
            if (!messaging) {
                log('Firebase not initialized. Click "Initialize Firebase" first.', 'error');
                return;
            }
            
            try {
                log('Getting FCM token...', 'info');
                
                // Wait for service worker to be active
                log('Waiting for service worker to become active...', 'info');
                
                const registration = await navigator.serviceWorker.ready;
                log('Service worker is ready', 'success');
                
                // Additional wait to ensure it's fully active
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                const vapidKey = "BEo-JqQVLbjqITH3wyIILkqlr8T-0Qgw8jWGj47bk8P6TgObJju8rOYllYcrR5B8zHTdix36pidb2cOc9mwyvcU";
                
                const { getToken } = await import('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging.js');
                const token = await getToken(messaging, { vapidKey });
                
                if (token) {
                    userToken = token;
                    log(`FCM Token received: ${token}`, 'success');
                    
                    // Save token to backend
                    const response = await fetch('/api/save-fcm-token', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ token })
                    });
                    
                    if (response.ok) {
                        log('FCM token saved to backend', 'success');
                        showStatus('✅ FCM token obtained and saved!', 'success');
                        document.getElementById('sendFcmTest').disabled = false;
                    } else {
                        const errorText = await response.text();
                        log(`Failed to save token: ${errorText}`, 'error');
                    }
                } else {
                    log('No FCM token available. Check permissions and try again.', 'error');
                    showStatus('❌ No FCM token available', 'error');
                }
                
            } catch (error) {
                log(`Token error: ${error.message}`, 'error');
                log(`Error details: ${error.stack}`, 'error');
                
                if (error.message.includes('no active Service Worker')) {
                    log('💡 Try waiting a few seconds and clicking "Get FCM Token" again', 'warning');
                    showStatus('⚠️ Service Worker not ready yet. Wait a few seconds and try again.', 'error');
                } else if (error.message.includes('authentication credential')) {
                    log('🔧 Firebase Authentication Issue Detected', 'error');
                    log('Possible solutions:', 'info');
                    log('1. Enable Cloud Messaging API in Firebase Console', 'info');
                    log('2. Check if web app is properly configured for FCM', 'info');
                    log('3. Verify domain is allowed in Firebase settings', 'info');
                    log('4. Try regenerating the web app config', 'info');
                    showStatus('❌ Firebase authentication error. Check Firebase Console settings.', 'error');
                } else if (error.message.includes('token-subscribe-failed')) {
                    log('🔧 FCM Subscription Failed', 'error');
                    log('This usually means:', 'info');
                    log('- Cloud Messaging API is not enabled', 'info');
                    log('- Web app not configured for push notifications', 'info');
                    log('- Domain restrictions in Firebase', 'info');
                    showStatus('❌ FCM subscription failed. Check Firebase project settings.', 'error');
                }
            }
        });

        // Test local notification
        document.getElementById('testNotification').addEventListener('click', () => {
            try {
                log('Testing local notification...', 'info');
                
                if (Notification.permission !== 'granted') {
                    log('Notification permission not granted', 'error');
                    return;
                }
                
                new Notification('Test Notification', {
                    body: 'This is a test notification from Smart Campus!',
                    icon: '/smart-campus-logo.svg'
                });
                
                log('Local notification sent!', 'success');
                
            } catch (error) {
                log(`Local notification error: ${error.message}`, 'error');
            }
        });

        // Send FCM test
        document.getElementById('sendFcmTest').addEventListener('click', async () => {
            if (!userToken) {
                log('No FCM token available', 'error');
                return;
            }

            try {
                log('Sending FCM test notification...', 'info');
                
                const response = await fetch('/api/test-fcm-notification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ token: userToken })
                });

                const result = await response.json();
                
                if (response.ok) {
                    log('FCM test notification sent successfully!', 'success');
                    showStatus('✅ FCM test notification sent! Check your notifications.', 'success');
                } else {
                    log(`FCM test failed: ${result.message}`, 'error');
                    showStatus(`❌ FCM test failed: ${result.message}`, 'error');
                }
            } catch (error) {
                log(`FCM test error: ${error.message}`, 'error');
            }
        });

        // Auto-run browser support check
        document.addEventListener('DOMContentLoaded', () => {
            log('Page loaded', 'info');
            
            // Detailed browser detection
            const userAgent = navigator.userAgent;
            log(`User Agent: ${userAgent}`, 'info');
            
            // Chrome version detection
            const chromeMatch = userAgent.match(/Chrome\/(\d+)/);
            if (chromeMatch) {
                const chromeVersion = parseInt(chromeMatch[1]);
                log(`Chrome version: ${chromeVersion}`, 'info');
                
                if (chromeVersion < 50) {
                    log('⚠️ Chrome version too old for Service Workers (need 50+)', 'warning');
                } else {
                    log('✅ Chrome version supports Service Workers', 'success');
                }
            }
            
            // Check Service Worker registration capability
            if ('serviceWorker' in navigator) {
                log('✅ Service Worker API is available', 'success');
                
                // Try to get existing registrations
                navigator.serviceWorker.getRegistrations().then(registrations => {
                    log(`Found ${registrations.length} existing service worker registrations`, 'info');
                    registrations.forEach((reg, index) => {
                        log(`Registration ${index + 1}: ${reg.scope}`, 'info');
                    });
                }).catch(error => {
                    log(`Error getting registrations: ${error.message}`, 'error');
                });
            } else {
                log('❌ Service Worker API not available', 'error');
                log('Possible causes:', 'info');
                log('- Chrome flags disabled (chrome://flags)', 'info');
                log('- Enterprise policy restrictions', 'info');
                log('- Corrupted Chrome profile', 'info');
                log('- Running in restricted mode', 'info');
            }
            
            // Check other APIs
            log(`Push Manager: ${'PushManager' in window ? '✅' : '❌'}`, 'info');
            log(`Notifications: ${'Notification' in window ? '✅' : '❌'}`, 'info');
            log(`Fetch API: ${'fetch' in window ? '✅' : '❌'}`, 'info');
            
            document.getElementById('checkSupport').click();
        });
    </script>
</body>
</html>