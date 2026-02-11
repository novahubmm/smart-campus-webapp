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

@once
<script>
    // Alert dialog notification system - replaces toast with centered modal
    function showToast(message, type = 'info', title = null) {
        // Dispatch alert-show event to trigger the alert dialog
        window.dispatchEvent(new CustomEvent('alert-show', {
            detail: {
                message: message,
                text: message, // Support both message and text
                type: type,
                title: title
            }
        }));
    }

    // Show initial messages from session
    document.addEventListener('DOMContentLoaded', function() {
        const initialMessages = @json($messages);
        initialMessages.forEach(function(msg, index) {
            setTimeout(function() {
                showToast(msg.text, msg.type);
            }, index * 300); // Stagger by 300ms to avoid overlapping dialogs
        });
    });

    // Listen for toast events (single listener to avoid duplicates)
    let toastListenerAdded = false;
    function addToastListener() {
        if (toastListenerAdded) return;
        toastListenerAdded = true;
        window.addEventListener('toast', (event) => {
            if (event.detail && (event.detail.text || event.detail.message)) {
                showToast(
                    event.detail.text || event.detail.message, 
                    event.detail.type || 'info',
                    event.detail.title || null
                );
            }
        });
    }
    addToastListener();
</script>
@endonce
