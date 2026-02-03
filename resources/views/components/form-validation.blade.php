@props(['type' => 'teacher', 'totalSteps' => 7, 'form' => []])

<script>
window.createProfileFormData = function(profileType, totalSteps, initialForm) {
    return {
        step: 1,
        total: totalSteps,
        form: initialForm,
        errors: {},
        
        validateStep(stepNumber) {
            this.errors = {};
            let isValid = true;
            
            // Define validation rules for each profile type and step
            const validationRules = {
                teacher: {
                    1: ['name'], // Step 1: Basic Information
                    2: ['nrc'],  // Step 2: Personal Details  
                    7: ['email', 'phone'] // Step 7: Portal Registration
                },
                student: {
                    1: ['name'], // Step 1: Basic Information
                    2: ['nrc'],  // Step 2: Personal Details
                    6: ['email', 'phone'] // Step 6: Portal Registration
                },
                staff: {
                    1: ['name'], // Step 1: Basic Information
                    2: ['nrc'],  // Step 2: Personal Details
                    6: ['email', 'phone'] // Step 6: Portal Registration
                }
            };
            
            const fieldsToValidate = validationRules[profileType]?.[stepNumber] || [];
            
            fieldsToValidate.forEach(field => {
                const value = this.form[field];
                
                if (!value || value.toString().trim() === '') {
                    this.errors[field] = this.getFieldMessage(field);
                    isValid = false;
                } else if (field === 'email' && !this.isValidEmail(value)) {
                    this.errors[field] = 'Please enter a valid email address';
                    isValid = false;
                }
            });
            
            return isValid;
        },
        
        nextStep() {
            if (this.validateStep(this.step)) {
                this.step = Math.min(this.total, this.step + 1);
            } else {
                this.showNotification('Please fill in all required fields before proceeding', 'error');
            }
        },
        
        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },
        
        getFieldMessage(field) {
            const messages = {
                name: 'Name is required',
                nrc: 'NRC is required', 
                email: 'Email is required',
                phone: 'Phone number is required'
            };
            return messages[field] || `${field} is required`;
        },
        
        showNotification(message, type) {
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
        },

        getFieldErrorClass(fieldName) {
            return this.errors[fieldName] ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : '';
        }
    };
};
</script>