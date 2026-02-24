import './bootstrap';

import Alpine from 'alpinejs';
import jQuery from 'jquery';
import DataTable from 'datatables.net-dt';
import 'datatables.net-fixedcolumns-dt';
import 'datatables.net-responsive-dt';
import FirebaseNotificationManager from './firebase';

window.$ = window.jQuery = jQuery;
window.DataTable = DataTable;

window.Alpine = Alpine;

const dispatchAppEvent = (name, detail = {}) => {
	window.dispatchEvent(new CustomEvent(name, { detail }));
};

window.toast = (payload, type = 'info', timeout = 4000) => {
	if (!payload) return;
	const detail = typeof payload === 'string' ? { text: payload, type, timeout } : payload;
	dispatchAppEvent('toast', detail);
};

window.confirmDialog = (options = {}) => {
	dispatchAppEvent('confirm-show', options);
};

// Initialize Firebase for notifications if user is authenticated
document.addEventListener('DOMContentLoaded', async () => {
    // Notifications are staff-only in web routes; skip initialization for other roles.
    const isStaffUser = document.querySelector('meta[name="user-role"][content="staff"]') !== null;
    if (!isStaffUser) {
        return;
    }

    // Check if user is authenticated (look for CSRF token)
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        try {
            const firebaseManager = new FirebaseNotificationManager();
            window.firebaseManager = firebaseManager; // Make it globally available
            await firebaseManager.initialize();
        } catch (error) {
            // Silent fail
        }
    }
});

Alpine.start();
