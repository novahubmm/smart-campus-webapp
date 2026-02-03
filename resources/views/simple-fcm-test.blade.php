<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Simple FCM Test</title>
</head>
<body>
    <h1>Simple FCM Test</h1>
    <button onclick="testFCM()">Test FCM</button>
    <div id="result"></div>

    <script type="module">
        async function testFCM() {
            const result = document.getElementById('result');
            result.innerHTML = 'Testing...';

            try {
                // Import Firebase
                const { initializeApp } = await import('https://www.gstatic.com/firebasejs/9.0.0/firebase-app.js');
                const { getMessaging, getToken } = await import('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging.js');

                // Firebase config
                const firebaseConfig = {
                    apiKey: "AIzaSyAPj9lP2Ho1IIoL_zG9lxkBRMiu1Ps-pj8",
                    authDomain: "smart-campus-dafc9.firebaseapp.com",
                    projectId: "smart-campus-dafc9",
                    storageBucket: "smart-campus-dafc9.firebasestorage.app",
                    messagingSenderId: "1009023794548",
                    appId: "1:1009023794548:web:797784460f6b1be58b8021",
                    measurementId: "G-RK0GRZHR28"
                };

                // Initialize
                const app = initializeApp(firebaseConfig);
                const messaging = getMessaging(app);

                // Request permission
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    result.innerHTML = 'Permission denied';
                    return;
                }

                // Register SW
                await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                await navigator.serviceWorker.ready;

                // Get token without VAPID first
                try {
                    log('Trying to get token without VAPID...', 'info');
                    const token = await getToken(messaging);
                    result.innerHTML = `Success! Token: ${token.substring(0, 50)}...`;
                    
                    // Save token
                    await fetch('/api/save-fcm-token', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ token })
                    });
                    
                } catch (error) {
                    log('Failed without VAPID, trying with VAPID...', 'info');
                    // Try with VAPID
                    const vapidKey = "BEo-JqQVLbjqITH3wyIILkqlr8T-0Qgw8jWGj47bk8P6TgObJju8rOYllYcrR5B8zHTdix36pidb2cOc9mwyvcU";
                    const token = await getToken(messaging, { vapidKey });
                    result.innerHTML = `Success with VAPID! Token: ${token.substring(0, 50)}...`;
                    
                    // Save token
                    await fetch('/api/save-fcm-token', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ token })
                    });
                }

            } catch (error) {
                result.innerHTML = `Error: ${error.message}`;
                console.error('Full error:', error);
            }
        }

        window.testFCM = testFCM;
    </script>
</body>
</html>