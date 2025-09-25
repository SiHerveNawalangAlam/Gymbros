// Login attempt lockout timer functionality
class LoginTimer {
    constructor() {
        this.lockoutTime = parseInt(document.getElementById('lockout_time')?.value) || 0;
        this.loginAttempts = parseInt(document.getElementById('login_attempts')?.value) || 0;
        this.timerInterval = null;
        
        this.init();
    }

    init() {
        if (this.lockoutTime > 0) {
            this.startTimer();
            this.disableForm();
        }
    }

    startTimer() {
        let timeLeft = this.lockoutTime;
        const countdownElement = document.getElementById('timer');
        const loginText = document.getElementById('login-text');
        const countdownContainer = document.getElementById('countdown');
        const loginBtn = document.getElementById('login-btn');
        const registerLink = document.getElementById('register-link');

        if (countdownElement && loginText && countdownContainer) {
            loginText.style.display = 'none';
            countdownContainer.style.display = 'inline';
            
            this.updateTimerDisplay(timeLeft, countdownElement);
            
            this.timerInterval = setInterval(() => {
                timeLeft--;
                this.updateTimerDisplay(timeLeft, countdownElement);
                
                if (timeLeft <= 0) {
                    this.stopTimer();
                    this.enableForm();
                    loginText.style.display = 'inline';
                    countdownContainer.style.display = 'none';
                    
                    if (loginBtn) loginBtn.disabled = false;
                    if (registerLink) registerLink.style.pointerEvents = 'auto';
                }
            }, 1000);
        }
    }

    stopTimer() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
            this.timerInterval = null;
        }
    }

    updateTimerDisplay(time, element) {
        if (element) {
            element.textContent = time;
        }
    }

    disableForm() {
        const loginBtn = document.getElementById('login-btn');
        const registerLink = document.getElementById('register-link');
        const formInputs = document.querySelectorAll('#login-form input');
        
        if (loginBtn) loginBtn.disabled = true;
        if (registerLink) registerLink.style.pointerEvents = 'none';
        
        formInputs.forEach(input => {
            input.readOnly = true;
        });
    }

    enableForm() {
        const loginBtn = document.getElementById('login-btn');
        const registerLink = document.getElementById('register-link');
        const formInputs = document.querySelectorAll('#login-form input');
        
        if (loginBtn) loginBtn.disabled = false;
        if (registerLink) registerLink.style.pointerEvents = 'auto';
        
        formInputs.forEach(input => {
            input.readOnly = false;
        });
    }
}

// Initialize timer when page loads
document.addEventListener('DOMContentLoaded', function() {
    new LoginTimer();
});