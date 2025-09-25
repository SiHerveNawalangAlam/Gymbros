// Real-time validation functions
class Validation {
    static formatID(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length > 4) {
            value = value.substring(0, 4) + '-' + value.substring(4, 8);
        }
        input.value = value;
        return value;
    }

    static validateName(input, fieldName) {
        const value = input.value.trim();
        const errors = [];
        
        // Remove double spaces
        input.value = value.replace(/\s{2,}/g, ' ');
        
        if (value === '') return { isValid: false, errors: [`${fieldName} is required`] };
        
        // Check three consecutive identical letters
        if (/([a-zA-Z])\1\1/.test(value)) {
            errors.push('Three consecutive identical letters are not allowed');
        }
        
        // Check all capital letters
        if (/^[A-Z\s]+$/.test(value)) {
            errors.push('All capital letters are not allowed');
        }
        
        // Check first letter is capital
        if (!/^[A-Z]/.test(value)) {
            errors.push('First letter must be capital');
        }
        
        // Check rest should be lowercase (simple validation)
        if (!/^[A-Z][a-z]*(\s[A-Z][a-z]*)*$/.test(value)) {
            errors.push('Name format should be: First Letter Capital, rest lowercase');
        }
        
        // No special characters or numbers
        if (/[^a-zA-Z\s]/.test(value)) {
            errors.push('Special characters and numbers are not allowed');
        }
        
        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    static validatePassword(password) {
        const strength = {
            score: 0,
            feedback: []
        };
        
        if (password.length >= 8) strength.score++;
        else strength.feedback.push("At least 8 characters");
        
        if (/[A-Z]/.test(password)) strength.score++;
        else strength.feedback.push("One uppercase letter");
        
        if (/[a-z]/.test(password)) strength.score++;
        else strength.feedback.push("One lowercase letter");
        
        if (/[0-9]/.test(password)) strength.score++;
        else strength.feedback.push("One number");
        
        if (/[^a-zA-Z0-9]/.test(password)) strength.score++;
        else strength.feedback.push("One special character");
        
        let strengthLevel = 'weak';
        if (strength.score >= 4) strengthLevel = 'strong';
        else if (strength.score >= 3) strengthLevel = 'medium';
        
        return {
            level: strengthLevel,
            score: strength.score,
            feedback: strength.feedback
        };
    }

    static calculateAge(birthdate) {
        const today = new Date();
        const birthDate = new Date(birthdate);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }

    static validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    static validateZipCode(zipCode) {
        return /^\d{4,10}$/.test(zipCode.replace(/\D/g, ''));
    }
}

// Real-time event listeners
document.addEventListener('DOMContentLoaded', function() {
    // ID Number formatting
    const idInputs = document.querySelectorAll('input[placeholder*="XXXX-XXXX"]');
    idInputs.forEach(input => {
        input.addEventListener('input', function() {
            Validation.formatID(this);
        });
    });

    // Name validation
    const nameInputs = document.querySelectorAll('input[placeholder*="name" i], input[placeholder*="Name" i]');
    nameInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const fieldName = this.previousElementSibling?.textContent || 'This field';
            const result = Validation.validateName(this, fieldName);
            
            this.setCustomValidity(result.isValid ? '' : result.errors[0]);
            
            // Visual feedback
            if (this.value.trim() !== '') {
                if (result.isValid) {
                    this.style.borderColor = '#28a745';
                } else {
                    this.style.borderColor = '#dc3545';
                    // Show error tooltip
                    this.title = result.errors.join(', ');
                }
            }
        });
    });

    // Password strength indicator
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        if (input.placeholder.toLowerCase().includes('password')) {
            const strengthDiv = document.createElement('div');
            strengthDiv.className = 'password-strength';
            input.parentNode.appendChild(strengthDiv);
            
            input.addEventListener('input', function() {
                const result = Validation.validatePassword(this.value);
                strengthDiv.textContent = `Strength: ${result.level}`;
                strengthDiv.className = `password-strength strength-${result.level}`;
                
                if (this.value.length > 0) {
                    strengthDiv.style.display = 'block';
                } else {
                    strengthDiv.style.display = 'none';
                }
            });
        }
    });

    // Age calculation
    const birthdateInputs = document.querySelectorAll('input[type="date"][id*="birthdate" i]');
    birthdateInputs.forEach(input => {
        input.addEventListener('change', function() {
            const ageDisplay = document.getElementById('age-display');
            if (ageDisplay && this.value) {
                const age = Validation.calculateAge(this.value);
                ageDisplay.textContent = `${age} years old`;
                
                // Validate legal age
                if (age < 18) {
                    this.setCustomValidity('Must be at least 18 years old');
                    ageDisplay.style.color = '#dc3545';
                } else {
                    this.setCustomValidity('');
                    ageDisplay.style.color = '#28a745';
                }
            }
        });
    });

    // Email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !Validation.validateEmail(this.value)) {
                this.setCustomValidity('Please enter a valid email address');
                this.style.borderColor = '#dc3545';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = this.value ? '#28a745' : '';
            }
        });
    });
});

 // Password strength indicator for change password form
    document.getElementById('new_password').addEventListener('input', function () {
      const strengthDiv = document.getElementById('password-strength');
      const password = this.value;

      if (password.length === 0) {
        strengthDiv.style.display = 'none';
        return;
      }

      let strength = 0;
      if (password.length >= 8) strength++;
      if (/[A-Z]/.test(password)) strength++;
      if (/[a-z]/.test(password)) strength++;
      if (/[0-9]/.test(password)) strength++;
      if (/[^A-Za-z0-9]/.test(password)) strength++;

      let level = 'weak';
      let color = '#dc3545';
      if (strength >= 4) {
        level = 'strong';
        color = '#28a745';
      } else if (strength >= 3) {
        level = 'medium';
        color = '#ffc107';
      }

      strengthDiv.textContent = `Password strength: ${level}`;
      strengthDiv.className = `password-strength strength-${level}`;
      strengthDiv.style.display = 'block';
      strengthDiv.style.color = color;
    });