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
                    2: [],  // Step 2: Personal Details
                    3: ['dob', 'previous_grade', 'previous_class'], // Step 3: School Placement
                    4: ['previous_school_name', 'previous_school_address'], // Step 4: Previous School
                    5: ['father_name', 'father_nrc', 'father_religious', 'father_occupation', 'father_address', 
                        'mother_name', 'mother_nrc', 'mother_religious', 'mother_occupation', 'mother_address'], // Step 5: Family
                    6: [] // Step 6: Portal Registration
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
                this.showNotification('Please fill in the following required fields:', 'error');
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
        },
        
        showNotification(message, type) {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.form-notification');
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
            
            // Remove after 8 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 8000);
        },

        getFieldErrorClass(fieldName) {
            return this.errors[fieldName] ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : '';
        }
    };
};
</script>