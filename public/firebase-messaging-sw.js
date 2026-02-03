// Import Firebase scripts
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

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

// Initialize Firebase
firebase.initializeApp(firebaseConfig);

// Initialize Firebase Cloud Messaging
const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage(function(payload) {
    console.log('Received background message ', payload);
    
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/smart-campus-logo.svg',
        badge: '/smart-campus-logo.svg',
        tag: 'staff-notification',
        requireInteraction: false,
        actions: [
            {
                action: 'view',
                title: 'View Notifications'
            }
        ]
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', function(event) {
    console.log('Notification click received.');
    
    event.notification.close();
    
    if (event.action === 'view') {
        // Open notifications page
        event.waitUntil(
            clients.openWindow('/staff/notifications')
        );
    } else {
        // Default action - open notifications page
        event.waitUntil(
            clients.openWindow('/staff/notifications')
        );
    }
});