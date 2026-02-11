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
            2: [],
            3: ['dob', 'previous_grade', 'previous_class'],
            4: ['previous_school_name', 'previous_school_address'],
            5: ['father_name', 'father_nrc', 'father_religious', 'father_occupation', 'father_address', 
                'mother_name', 'mother_nrc', 'mother_religious', 'mother_occupation', 'mother_address'],
            6: []
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
        phone: 'Phone number is required',
        dob: 'Date of Birth is required',
        previous_grade: 'Previous Grade is required',
        previous_class: 'Previous Class is required',
        previous_school_name: 'Previous School Name is required',
        previous_school_address: 'Previous School Address is required',
        father_name: 'Father Name is required',
        father_nrc: 'Father NRC is required',
        father_religious: 'Father Religious is required',
        father_occupation: 'Father Occupation is required',
        father_address: 'Father Address is required',
        mother_name: 'Mother Name is required',
        mother_nrc: 'Mother NRC is required',
        mother_religious: 'Mother Religious is required',
        mother_occupation: 'Mother Occupation is required',
        mother_address: 'Mother Address is required'
    };
    return messages[field] || `${field} is required`;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Show notification function
window.showFormNotification = function(message, type = 'error', errors = {}) {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.form-notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Build error list if errors object is provided
    let errorList = '';
    if (type === 'error' && Object.keys(errors).length > 0) {
        errorList = '<ul class="mt-2 ml-6 list-disc text-sm">';
        Object.values(errors).forEach(error => {
            errorList += `<li>${error}</li>`;
        });
        errorList += '</ul>';
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `form-notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-md ${type === 'error' ? 'bg-red-500' : 'bg-green-500'} text-white`;
    notification.innerHTML = `
        <div class='flex items-start justify-between'>
            <div class='flex-1'>
                <div class='flex items-center'>
                    <i class='fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'} mr-2'></i>
                    <span class="font-semibold">${message}</span>
                </div>
                ${errorList}
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-3 text-white hover:text-gray-200 flex-shrink-0">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 8 seconds (longer for error lists)
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 8000);
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