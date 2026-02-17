/**
 * Profile Form Validation Utility
 * Provides common validation functions for multi-step profile forms
 */

window.ProfileFormValidation = {
    /**
     * Common validation rules for different profile types
     */
    validationRules: {
        teacher: {
            1: ['name'], // Step 1: Basic Information
            2: ['nrc'],  // Step 2: Personal Details
            7: ['email', 'phone'] // Step 7: Portal Registration
        },
        student: {
            1: ['name'], // Step 1: Basic Information
            2: [],  // Step 2: Personal Details
            3: ['dob', 'previous_grade', 'previous_class'], // Step 3: School Placement
            4: ['previous_school_name', 'previous_school_address'], // Step 4: Previous School
            5: ['father_name', 'father_nrc', 'father_religious', 'father_occupation', 'father_address', 
                'mother_name', 'mother_nrc', 'mother_religious', 'mother_occupation', 'mother_address'], // Step 5: Family
            6: [] // Step 6: Portal Registration (students have 6 steps)
        },
        staff: {
            1: ['name'], // Step 1: Basic Information
            2: ['nrc'],  // Step 2: Personal Details
            6: ['email', 'phone'] // Step 6: Portal Registration
        }
    },

    /**
     * Validation messages
     */
    messages: {
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
        mother_address: 'Mother Address is required',
        invalidEmail: 'Please enter a valid email address',
        fillRequired: 'Please fill in the following required fields:'
    },

    /**
     * Create Alpine.js data object with validation
     */
    createAlpineData(profileType, totalSteps, initialForm = {}) {
        return {
            step: 1,
            total: totalSteps,
            form: initialForm,
            errors: {},
            
            validateStep(stepNumber) {
                this.errors = {};
                let isValid = true;
                
                const rules = ProfileFormValidation.validationRules[profileType];
                const fieldsToValidate = rules[stepNumber] || [];
                
                fieldsToValidate.forEach(field => {
                    const value = this.form[field];
                    
                    if (!value || value.toString().trim() === '') {
                        this.errors[field] = ProfileFormValidation.getFieldMessage(field);
                        isValid = false;
                    } else if (field === 'email' && !this.isValidEmail(value)) {
                        this.errors[field] = ProfileFormValidation.messages.invalidEmail;
                        isValid = false;
                    }
                });
                
                return isValid;
            },
            
            nextStep() {
                if (this.validateStep(this.step)) {
                    this.step = Math.min(this.total, this.step + 1);
                } else {
                    this.showNotification(ProfileFormValidation.messages.fillRequired, 'error', this.errors);
                }
            },
            
            isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            },
            
            showNotification(message, type) {
                // Remove existing notifications
                const existingNotifications = document.querySelectorAll('.profile-form-notification');
                existingNotifications.forEach(notification => notification.remove());
                
                // Build error list if errors object is provided
                let errorList = '';
                if (type === 'error' && this.errors && Object.keys(this.errors).length > 0) {
                    errorList = '<ul class="mt-2 ml-6 list-disc text-sm">';
                    Object.values(this.errors).forEach(error => {
                        errorList += `<li>${error}</li>`;
                    });
                    errorList += '</ul>';
                }
                
                // Create notification element
                const notification = document.createElement('div');
                notification.className = `profile-form-notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-md ${type === 'error' ? 'bg-red-500' : 'bg-green-500'} text-white transform transition-all duration-300 translate-x-full`;
                notification.innerHTML = `
                    <div class='flex items-start'>
                        <div class='flex-1'>
                            <div class='flex items-center'>
                                <i class='fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'} mr-2'></i>
                                <span class="font-semibold">${message}</span>
                            </div>
                            ${errorList}
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-3 text-white hover:text-gray-200 flex-shrink-0">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                // Animate in
                setTimeout(() => {
                    notification.classList.remove('translate-x-full');
                }, 100);
                
                // Remove after 8 seconds
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.classList.add('translate-x-full');
                        setTimeout(() => {
                            if (notification.parentNode) {
                                notification.parentNode.removeChild(notification);
                            }
                        }, 300);
                    }
                }, 8000);
            },

            // Helper method to get field-specific error class
            getFieldErrorClass(fieldName) {
                return this.errors[fieldName] ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : '';
            }
        };
    },

    /**
     * Get localized message for field
     */
    getFieldMessage(field) {
        return this.messages[field] || `${field} is required`;
    },

    /**
     * Add autocomplete attributes based on field type
     */
    getAutocompleteAttribute(fieldName) {
        const autocompleteMap = {
            name: 'name',
            email: 'email',
            phone: 'tel',
            password: 'current-password',
            nrc: 'off'
        };
        
        return autocompleteMap[fieldName] || 'off';
    }
};

/**
 * Initialize form validation on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add CSS for smooth transitions
    const style = document.createElement('style');
    style.textContent = `
        .profile-form-notification {
            transition: transform 0.3s ease-in-out;
        }
        
        .field-error {
            border-color: #ef4444 !important;
        }
        
        .field-error:focus {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 1px #ef4444 !important;
        }
    `;
    document.head.appendChild(style);
});