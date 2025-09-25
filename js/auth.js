// Authentication-related JavaScript functionality
class AuthHelper {
    static togglePassword() {
        const passwordInput = document.getElementById('password');
        const passwordIcon = document.getElementById('password-icon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.className = 'fas fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            passwordIcon.className = 'fas fa-eye';
        }
    }

    static checkUsername(username) {
        if (username.length < 3) return;
        
        const feedbackDiv = document.getElementById('username-feedback');
        if (!feedbackDiv) return;
        
        // Simulate AJAX check (you'll need to implement actual AJAX call)
        fetch('check_username.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `username=${encodeURIComponent(username)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                feedbackDiv.innerHTML = '<span class="feedback error"><i class="fas fa-times"></i> Username already exists</span>';
            } else {
                feedbackDiv.innerHTML = '<span class="feedback success"><i class="fas fa-check"></i> Username available</span>';
            }
        })
        .catch(() => {
            // Fallback client-side validation
            const isValid = /^[a-zA-Z0-9_]{3,20}$/.test(username);
            if (isValid) {
                feedbackDiv.innerHTML = '<span class="feedback success"><i class="fas fa-check"></i> Username format valid</span>';
            } else {
                feedbackDiv.innerHTML = '<span class="feedback error"><i class="fas fa-times"></i> Use 3-20 letters, numbers, or underscore</span>';
            }
        });
    }

    static validatePasswordMatch() {
        const password = document.querySelector('input[name="password"]');
        const confirmPassword = document.querySelector('input[name="confirm_password"]');
        
        if (password && confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (this.value !== password.value) {
                    this.setCustomValidity('Passwords do not match');
                    this.style.borderColor = '#dc3545';
                } else {
                    this.setCustomValidity('');
                    this.style.borderColor = '#28a745';
                }
            });
        }
    }

    static preventBackButton() {
        // Prevent back button after login
        if (window.history && window.history.pushState) {
            window.history.pushState(null, null, window.location.href);
            window.onpopstate = function() {
                window.history.go(1);
            };
        }
    }

    static initSecurityQuestions() {
        const questions = [
            "Who is your best friend in Elementary?",
            "What is the name of your favorite pet?",
            "Who is your favorite teacher in high school?"
        ];
        
        // Randomly assign questions to avoid pattern
        const questionSelects = document.querySelectorAll('select[name^="security_question"]');
        questionSelects.forEach((select, index) => {
            // Shuffle questions for each select
            const shuffled = [...questions].sort(() => Math.random() - 0.5);
            shuffled.forEach(question => {
                const option = document.createElement('option');
                option.value = question;
                option.textContent = question;
                select.appendChild(option);
            });
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', AuthHelper.togglePassword);
    });

    // Username availability check
    const usernameInputs = document.querySelectorAll('input[name="username"]');
    usernameInputs.forEach(input => {
        input.addEventListener('blur', function() {
            AuthHelper.checkUsername(this.value);
        });
    });

    // Password match validation
    AuthHelper.validatePasswordMatch();

    // Security questions initialization
    AuthHelper.initSecurityQuestions();

    // Form submission enhancements
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Additional client-side validation before submission
            if (!this.checkValidity()) {
                e.preventDefault();
                // Highlight invalid fields
                const invalidFields = this.querySelectorAll(':invalid');
                invalidFields.forEach(field => {
                    field.style.borderColor = '#dc3545';
                    field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
                
                // Show first error message
                if (invalidFields.length > 0) {
                    alert('Please correct the errors before submitting.');
                }
            }
        });
    });
});