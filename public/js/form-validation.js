/**
 * Multi-step Form Validation
 * Provides validation for profile forms (teacher, student, staff)
 */

// Add validation styles
const validationStyles = `
    .field-error {
        border-color: #ef4444 !important;
    }
    .field-error:focus {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 1px #ef4444 !important;
    }
    .form-notification {
        transition: all 0.3s ease-in-out;
    }
`;

// Inject styles
if (!document.getElementById('form-validation-styles')) {
    const style = document.createElement('style');
    style.id = 'form-validation-styles';
    style.textContent = validationStyles;
    document.head.appendChild(style);
}

// Global validation function
window.validateFormStep = function(form, step, profileType = 'teacher') {
    const errors = {};
    let isValid = true;
    
    // Define validation rules for each profile type
    const validationRules = {
        teacher: {
            1: ['name'],
            2: ['nrc'],
            7: ['email', 'phone']
        },
        student: {
            1: ['name'],
            2: ['nrc'],
            6: ['email', 'phone']
        },
        staff: {
            1: ['name'],
            2: ['nrc'],
            6: ['email', 'phone']
        }
    };
    
    const fieldsToValidate = validationRules[profileType]?.[step] || [];
    
    fieldsToValidate.forEach(field => {
        const value = form[field];
        
        if (!value || value.toString().trim() === '') {
            errors[field] = getFieldMessage(field);
            isValid = false;
        } else if (field === 'email' && !isValidEmail(value)) {
            errors[field] = 'Please enter a valid email address';
            isValid = false;
        }
    });
    
    return { isValid, errors };
};

// Helper functions
function getFieldMessage(field) {
    const messages = {
        name: 'Name is required',
        nrc: 'NRC is required',
        email: 'Email is required',
        phone: 'Phone number is required'
    };
    return messages[field] || `${field} is required`;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Show notification function
window.showFormNotification = function(message, type = 'error') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.form-notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `form-notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${type === 'error' ? 'bg-red-500' : 'bg-green-500'} text-white`;
    notification.innerHTML = `
        <div class='flex items-center justify-between'>
            <div class='flex items-center'>
                <i class='fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'} mr-2'></i>
                <span>${message}</span>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-3 text-white hover:text-gray-200">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
};

// Update field error classes
window.updateFieldErrorClasses = function(errors) {
    // Remove all existing error classes
    document.querySelectorAll('.field-error').forEach(field => {
        field.classList.remove('field-error');
    });
    
    // Add error classes to fields with errors
    Object.keys(errors).forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"], [x-model*="${fieldName}"]`);
        if (field) {
            field.classList.add('field-error');
        }
    });
};