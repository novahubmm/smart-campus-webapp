@php
    $messages = [];

    if (session('success')) {
        $messages[] = ['type' => 'success', 'text' => session('success')];
    }

    if (session('error')) {
        $messages[] = ['type' => 'error', 'text' => session('error')];
    }

    if (session('warning')) {
        $messages[] = ['type' => 'warning', 'text' => session('warning')];
    }

    if (session('info')) {
        $messages[] = ['type' => 'info', 'text' => session('info')];
    }

    if (session('status')) {
        $statusMap = [
            'profile-updated' => __('Profile updated'),
            'password-updated' => __('Password updated'),
            'recovery-email-sent' => __('Recovery email sent'),
            'otp-verified' => __('OTP verified'),
            'password-reset' => __('Password reset successfully'),
        ];

        $messages[] = ['type' => 'success', 'text' => $statusMap[session('status')] ?? __(session('status'))];
    }

    if (isset($errors) && $errors->any()) {
        foreach ($errors->all() as $error) {
            $messages[] = ['type' => 'error', 'text' => $error];
        }
    }
@endphp

<!-- Toast Container -->
<div id="toastContainer" class="fixed top-5 right-5 z-[10000] flex flex-col gap-3 pointer-events-none"></div>

@once
<script>
    // Toast notification system - matching blade_prototype style
    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const colors = {
            success: { bg: '#10b981', border: '#059669' },
            error: { bg: '#ef4444', border: '#dc2626' },
            warning: { bg: '#f59e0b', border: '#d97706' },
            info: { bg: '#3b82f6', border: '#2563eb' }
        };

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        const color = colors[type] || colors.info;
        const icon = icons[type] || icons.info;

        const toast = document.createElement('div');
        toast.className = 'toast-item pointer-events-auto';
        toast.style.cssText = `
            background: white;
            border-radius: 8px;
            padding: 12px 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 280px;
            max-width: 400px;
            border-left: 4px solid ${color.border};
            animation: slideIn 0.3s ease-out;
        `;

        toast.innerHTML = `
            <div style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: ${color.bg}20; color: ${color.bg}; flex-shrink: 0;">
                <i class="fas ${icon}" style="font-size: 12px;"></i>
            </div>
            <div style="flex: 1; font-size: 14px; color: #1f2937; line-height: 1.4;">${message}</div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-times" style="font-size: 12px;"></i>
            </button>
        `;

        container.appendChild(toast);

        // Auto remove after 4 seconds
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out forwards';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // Add CSS animations
    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }

    // Show initial toasts from session
    document.addEventListener('DOMContentLoaded', function() {
        const initialMessages = @json($messages);
        initialMessages.forEach(function(msg, index) {
            setTimeout(function() {
                showToast(msg.text, msg.type);
            }, index * 100);
        });
    });

    // Listen for toast events (single listener to avoid duplicates)
    let toastListenerAdded = false;
    function addToastListener() {
        if (toastListenerAdded) return;
        toastListenerAdded = true;
        window.addEventListener('toast', (event) => {
            if (event.detail && event.detail.text) {
                showToast(event.detail.text, event.detail.type || 'info');
            }
        });
    }
    addToastListener();
</script>
@endonce
