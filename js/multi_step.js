// Multi-step form functionality with integrated validation
class MultiStepForm {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 4;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.updateProgressBar();
        this.setupRealTimeValidation();
    }

    setupEventListeners() {
        // Next buttons
        document.querySelectorAll('.btn-next').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const nextStep = parseInt(e.target.dataset.next);
                if (this.validateCurrentStep()) {
                    this.goToStep(nextStep);
                }
            });
        });

        // Previous buttons
        document.querySelectorAll('.btn-prev').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const prevStep = parseInt(e.target.dataset.prev);
                this.goToStep(prevStep);
            });
        });

        // Form submission
        const form = document.getElementById('register-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                if (!this.validateAllSteps()) {
                    e.preventDefault();
                    this.showStepError("Please correct all errors before submitting the form.");
                }
            });
        }
    }

    setupRealTimeValidation() {
        // Set up blur validation for all fields
        document.querySelectorAll('.form-step input, .form-step select').forEach(input => {
            // Skip buttons and hidden fields
            if (input.type === 'submit' || input.type === 'button' || input.type === 'hidden') {
                return;
            }

            input.addEventListener('blur', () => {
                this.validateSingleField(input);
            });

            // Clear errors when user starts typing
            input.addEventListener('input', () => {
                this.clearFieldError(input);
            });
        });
    }

    validateCurrentStep() {
        const currentStepElement = document.querySelector(`.form-step[data-step="${this.currentStep}"]`);
        const inputs = currentStepElement.querySelectorAll('input, select');
        
        let isValid = true;
        let errorCount = 0;
        let firstInvalidField = null;

        // Clear previous step errors
        this.clearStepError();

        inputs.forEach(input => {
            // Skip buttons and hidden fields
            if (input.type === 'submit' || input.type === 'button' || input.type === 'hidden') {
                return;
            }

            const fieldValidation = this.validateField(input);
            if (!fieldValidation.isValid) {
                isValid = false;
                errorCount++;
                if (!firstInvalidField) {
                    firstInvalidField = input;
                }
                
                // Show field error
                this.showFieldError(input, fieldValidation.errors[0]);
            } else {
                this.clearFieldError(input);
                this.showFieldSuccess(input);
            }
        });

        if (!isValid) {
            // Show step-level error message
            this.showStepError(`Please correct the ${errorCount} error${errorCount > 1 ? 's' : ''} before proceeding to the next step.`);
            
            // Scroll to first error
            if (firstInvalidField) {
                firstInvalidField.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                firstInvalidField.focus();
            }
        } else {
            this.clearStepError();
        }

        return isValid;
    }

    validateSingleField(input) {
        const fieldValidation = this.validateField(input);
        if (!fieldValidation.isValid) {
            this.showFieldError(input, fieldValidation.errors[0]);
        } else {
            this.clearFieldError(input);
            this.showFieldSuccess(input);
        }
        return fieldValidation.isValid;
    }

    validateField(input) {
        const value = input.value.trim();
        const fieldName = this.getFieldDisplayName(input.name);
        const errors = [];

        // Skip validation for empty optional fields
        if (!input.hasAttribute('required') && value === '') {
            return { isValid: true, errors: [] };
        }

        // Required field validation
        if (input.hasAttribute('required') && !value) {
            errors.push(`${fieldName} is required`);
        }

        // Name field validation (using your Validation class rules)
        if (input.name.includes('name') && !input.name.includes('security_answer') && value) {
            if (!/^[A-Z]/.test(value)) {
                errors.push('First letter must be capital');
            }
            if (/^[A-Z\s]+$/.test(value)) {
                errors.push('All capital letters are not allowed');
            }
            if (!/^[A-Z][a-z]*(\s[A-Z][a-z]*)*$/.test(value)) {
                errors.push('Name format should be: First Letter Capital, rest lowercase');
            }
            if (/[^a-zA-Z\s]/.test(value)) {
                errors.push('Special characters and numbers are not allowed');
            }
            if (/([a-zA-Z])\1\1/.test(value)) {
                errors.push('Three consecutive identical letters are not allowed');
            }
        }

        // Email validation
        if (input.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                errors.push('Please enter a valid email address');
            }
        }

        // ID Number validation
        if (input.name === 'id_number' && value) {
            if (!/^\d{4}-\d{4}$/.test(value)) {
                errors.push('ID format must be xxxx-xxxx (numbers only)');
            }
        }

        // Zip code validation
        if (input.name === 'zip_code' && value) {
            if (!/^\d{4,10}$/.test(value.replace(/\D/g, ''))) {
                errors.push('Zip code must be 4-10 digits only');
            }
        }

        // Password validation for step 3
        if (input.name === 'password' && this.currentStep === 3 && value) {
            if (value.length < 8) {
                errors.push('Password must be at least 8 characters long');
            }
            if (!/[A-Z]/.test(value)) {
                errors.push('Password must contain at least one uppercase letter');
            }
            if (!/[a-z]/.test(value)) {
                errors.push('Password must contain at least one lowercase letter');
            }
            if (!/[0-9]/.test(value)) {
                errors.push('Password must contain at least one number');
            }
            if (!/[^A-Za-z0-9]/.test(value)) {
                errors.push('Password must contain at least one special character');
            }
        }

        // Password match validation
        if (input.name === 'confirm_password' && this.currentStep === 3 && value) {
            const password = document.querySelector('input[name="password"]');
            if (password && value !== password.value) {
                errors.push('Passwords do not match');
            }
        }

        // Security answers match validation
        if (input.name.includes('security_answer') && input.name.includes('_repeat') && value) {
            const originalFieldName = input.name.replace('_repeat', '');
            const originalInput = document.querySelector(`input[name="${originalFieldName}"]`);
            if (originalInput && value !== originalInput.value.trim()) {
                errors.push('Security answers do not match');
            }
        }

        // Age validation (for birthdate field)
        if (input.name === 'birthdate' && value) {
            const age = this.calculateAge(value);
            if (age < 18) {
                errors.push('Must be at least 18 years old');
            }
        }

        // Username validation
        if (input.name === 'username' && value) {
            if (!/^[a-zA-Z0-9_]{3,20}$/.test(value)) {
                errors.push('Username must be 3-20 characters (letters, numbers, underscore only)');
            }
        }

        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    calculateAge(birthdate) {
        const today = new Date();
        const birthDate = new Date(birthdate);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }

    getFieldDisplayName(fieldName) {
        const names = {
            'first_name': 'First Name',
            'last_name': 'Last Name', 
            'middle_name': 'Middle Name',
            'extension_name': 'Extension Name',
            'id_number': 'ID Number',
            'birthdate': 'Birthdate',
            'email': 'Email Address',
            'sex': 'Sex',
            'purok_street': 'Purok/Street',
            'barangay': 'Barangay',
            'city_municipality': 'City/Municipality',
            'province': 'Province',
            'country': 'Country',
            'zip_code': 'Zip Code',
            'username': 'Username',
            'password': 'Password',
            'confirm_password': 'Confirm Password'
        };
        return names[fieldName] || fieldName.replace(/_/g, ' ');
    }

    showFieldError(input, message) {
        // Remove any existing success styling
        input.classList.remove('valid-field');
        input.classList.add('error-field');
        
        // Create or update error message
        let errorDiv = input.parentNode.querySelector('.field-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            input.parentNode.appendChild(errorDiv);
        }
        
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        errorDiv.style.display = 'block';
        
        // Visual feedback on input
        input.style.borderColor = '#dc3545';
        input.style.boxShadow = '0 0 0 2px rgba(220, 53, 69, 0.1)';
    }

    showFieldSuccess(input) {
        input.classList.remove('error-field');
        input.classList.add('valid-field');
        
        // Hide error message if exists
        const errorDiv = input.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
        
        // Visual feedback
        input.style.borderColor = '#28a745';
        input.style.boxShadow = '0 0 0 2px rgba(40, 167, 69, 0.1)';
    }

    clearFieldError(input) {
        input.classList.remove('error-field', 'valid-field');
        input.style.borderColor = '';
        input.style.boxShadow = '';
        
        const errorDiv = input.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    }

    showStepError(message) {
        // Remove any existing step error
        this.clearStepError();
        
        // Create error message at the top of the current step
        const currentStepElement = document.querySelector(`.form-step[data-step="${this.currentStep}"]`);
        const errorDiv = document.createElement('div');
        errorDiv.className = 'step-error-message';
        errorDiv.innerHTML = `
            <div class="error-alert">
                <i class="fas fa-exclamation-triangle"></i>
                <span>${message}</span>
            </div>
        `;
        
        // Insert after the progress steps or at the top
        const progressSteps = document.querySelector('.progress-steps');
        if (progressSteps && currentStepElement.contains(progressSteps)) {
            progressSteps.parentNode.insertBefore(errorDiv, progressSteps.nextSibling);
        } else {
            currentStepElement.insertBefore(errorDiv, currentStepElement.firstChild);
        }
    }

    clearStepError() {
        const existingError = document.querySelector('.step-error-message');
        if (existingError) {
            existingError.remove();
        }
    }

    validateAllSteps() {
        let allValid = true;

        for (let step = 1; step <= this.totalSteps; step++) {
            this.currentStep = step;
            if (!this.validateCurrentStep()) {
                allValid = false;
                break;
            }
        }

        return allValid;
    }

    goToStep(step) {
        // Hide current step
        const currentStepElement = document.querySelector(`.form-step[data-step="${this.currentStep}"]`);
        const currentStepIndicator = document.querySelector(`.step[data-step="${this.currentStep}"]`);
        
        if (currentStepElement) currentStepElement.classList.remove('active');
        if (currentStepIndicator) currentStepIndicator.classList.remove('active');

        // Show new step
        const newStepElement = document.querySelector(`.form-step[data-step="${step}"]`);
        const newStepIndicator = document.querySelector(`.step[data-step="${step}"]`);
        
        if (newStepElement) newStepElement.classList.add('active');
        if (newStepIndicator) newStepIndicator.classList.add('active');

        // Mark previous steps as completed
        for (let i = 1; i < step; i++) {
            const stepElement = document.querySelector(`.step[data-step="${i}"]`);
            if (stepElement) {
                stepElement.classList.add('completed');
            }
        }

        // Remove completed class from future steps
        for (let i = step + 1; i <= this.totalSteps; i++) {
            const stepElement = document.querySelector(`.step[data-step="${i}"]`);
            if (stepElement) {
                stepElement.classList.remove('completed');
            }
        }

        this.currentStep = step;
        this.updateProgressBar();
        
        // Clear any step errors when moving to a new step
        this.clearStepError();
    }

    updateProgressBar() {
        const progress = ((this.currentStep - 1) / (this.totalSteps - 1)) * 100;
        const progressFill = document.querySelector('.progress-fill');
        if (progressFill) {
            progressFill.style.width = `${progress}%`;
        }
    }
}

// Global functions
window.togglePassword = function(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const passwordIcon = document.getElementById(fieldId + '-icon');
    
    if (!passwordInput || !passwordIcon) return;
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        passwordIcon.className = 'fas fa-eye';
    }
}

window.calculateAge = function() {
    const birthdate = document.getElementById('birthdate');
    const ageDisplay = document.getElementById('age-display');
    
    if (birthdate && birthdate.value && ageDisplay) {
        const today = new Date();
        const birthDate = new Date(birthdate.value);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        ageDisplay.textContent = `${age} years old`;
        
        // Age validation feedback
        if (age < 18) {
            ageDisplay.style.color = '#dc3545';
        } else {
            ageDisplay.style.color = '#28a745';
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new MultiStepForm();
    calculateAge();
});