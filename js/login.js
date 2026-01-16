// js/login.js - Versi Profesional

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            delay: { show: 300, hide: 100 }
        });
    });
    
    // Initialize toast notification
    const welcomeToastEl = document.getElementById('welcomeToast');
    if (welcomeToastEl) {
        const welcomeToast = new bootstrap.Toast(welcomeToastEl, { 
            delay: 4000,
            animation: true
        });
        setTimeout(() => welcomeToast.show(), 1500);
    }
    
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon with animation
            const icon = this.querySelector('i');
            icon.classList.add('animate__animated', 'animate__flipInY');
            
            if (type === 'password') {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.setAttribute('title', 'Tampilkan password');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.setAttribute('title', 'Sembunyikan password');
            }
            
            // Update tooltip
            const tooltipInstance = bootstrap.Tooltip.getInstance(this);
            if (tooltipInstance) {
                tooltipInstance.hide();
                tooltipInstance.setContent({ '.tooltip-inner': this.getAttribute('title') });
            }
            
            // Remove animation class after animation completes
            setTimeout(() => {
                icon.classList.remove('animate__animated', 'animate__flipInY');
            }, 500);
        });
    }
    
    // Password strength indicator
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
    }
    
    function checkPasswordStrength(password) {
        const strengthDiv = document.getElementById('passwordStrength');
        const progressBar = document.querySelector('.progress-bar');
        const strengthText = document.getElementById('strengthText');
        
        if (!password) {
            strengthDiv.classList.add('d-none');
            return;
        }
        
        strengthDiv.classList.remove('d-none');
        
        let strength = 0;
        
        // Length check
        if (password.length >= 6) strength += 20;
        if (password.length >= 8) strength += 15;
        if (password.length >= 12) strength += 15;
        
        // Complexity checks
        if (/[A-Z]/.test(password)) strength += 20;
        if (/[a-z]/.test(password)) strength += 20;
        if (/[0-9]/.test(password)) strength += 20;
        if (/[^A-Za-z0-9]/.test(password)) strength += 20;
        
        // Cap at 100
        strength = Math.min(strength, 100);
        
        // Update progress bar
        progressBar.style.width = `${strength}%`;
        
        // Update color and text based on strength
        let color = 'bg-danger';
        let text = 'Sangat Lemah';
        let textColor = 'text-danger';
        
        if (strength >= 40) {
            color = 'bg-warning';
            text = 'Lemah';
            textColor = 'text-warning';
        }
        if (strength >= 60) {
            color = 'bg-info';
            text = 'Cukup';
            textColor = 'text-info';
        }
        if (strength >= 80) {
            color = 'bg-success';
            text = 'Kuat';
            textColor = 'text-success';
        }
        if (strength >= 95) {
            text = 'Sangat Kuat';
        }
        
        progressBar.className = `progress-bar ${color}`;
        strengthText.textContent = text;
        strengthText.className = `fw-semibold ${textColor}`;
        
        // Add animation to progress bar
        progressBar.classList.add('animate__animated', 'animate__pulse');
        setTimeout(() => {
            progressBar.classList.remove('animate__animated', 'animate__pulse');
        }, 500);
    }
    
    // Real-time form validation
    const usernameInput = document.getElementById('username');
    const loginForm = document.getElementById('loginForm');
    
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            validateUsername(this.value);
        });
    }
    
    function validateUsername(username) {
        if (!username.trim()) {
            usernameInput.classList.add('is-invalid');
            usernameInput.classList.remove('is-valid');
        } else {
            usernameInput.classList.remove('is-invalid');
            usernameInput.classList.add('is-valid');
        }
    }
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            if (!this.value.trim()) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    }
    
    // Form submission with loading state
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const username = usernameInput.value.trim();
            const password = passwordInput.value.trim();
            const loginButton = document.getElementById('loginButton');
            const buttonText = loginButton.querySelector('.button-text');
            const spinner = loginButton.querySelector('.spinner-border');
            
            let isValid = true;
            
            // Validate inputs
            if (!username) {
                usernameInput.classList.add('is-invalid');
                isValid = false;
                shakeElement(usernameInput);
            }
            
            if (!password) {
                passwordInput.classList.add('is-invalid');
                isValid = false;
                shakeElement(passwordInput);
            }
            
            if (!isValid) {
                showErrorToast('Harap isi semua field yang diperlukan!');
                return;
            }
            
            // Show loading state
            buttonText.classList.add('d-none');
            spinner.classList.remove('d-none');
            loginButton.disabled = true;
            loginButton.classList.add('btn-loading');
            
            // Simulate loading and then submit
            setTimeout(() => {
                // In real application, this would be AJAX or form submission
                // For now, we'll simulate a successful login
                loginForm.submit();
            }, 2000);
        });
    }
    
    // Animate registration button
    const registerBtn = document.querySelector('.btn-register');
    if (registerBtn) {
        registerBtn.addEventListener('mouseenter', function() {
            this.classList.add('animate__animated', 'animate__tada');
        });
        
        registerBtn.addEventListener('mouseleave', function() {
            this.classList.remove('animate__animated', 'animate__tada');
        });
        
        registerBtn.addEventListener('click', function(e) {
            this.classList.add('animate__animated', 'animate__rubberBand');
            setTimeout(() => {
                this.classList.remove('animate__animated', 'animate__rubberBand');
            }, 1000);
        });
    }
    
    // Simulate system statistics
    const userCountElement = document.getElementById('userCount');
    const itemCountElement = document.getElementById('itemCount');
    const loanCountElement = document.getElementById('loanCount');
    
    if (userCountElement) {
        animateCounter(userCountElement, Math.floor(Math.random() * 50) + 20, 1000);
    }
    
    if (itemCountElement) {
        animateCounter(itemCountElement, Math.floor(Math.random() * 100) + 50, 1200);
    }
    
    if (loanCountElement) {
        animateCounter(loanCountElement, Math.floor(Math.random() * 200) + 100, 1500);
    }
    
    function animateCounter(element, target, duration) {
        let start = 0;
        const increment = target / (duration / 20);
        const timer = setInterval(() => {
            start += increment;
            if (start >= target) {
                element.textContent = target;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(start);
            }
        }, 20);
    }
    
    // Add hover effect to card
    const card = document.querySelector('.login-card');
    if (card) {
        card.addEventListener('mouseenter', () => {
            card.classList.add('animate__animated', 'animate__pulse');
        });
        
        card.addEventListener('mouseleave', () => {
            card.classList.remove('animate__animated', 'animate__pulse');
        });
    }
    
    // Utility functions
    function shakeElement(element) {
        element.classList.add('animate__animated', 'animate__shakeX');
        setTimeout(() => {
            element.classList.remove('animate__animated', 'animate__shakeX');
        }, 1000);
    }
    
    function showErrorToast(message) {
        const toastContainer = document.querySelector('.toast-container');
        const toastId = 'errorToast-' + Date.now();
        
        const toastHTML = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-danger text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong class="me-auto">Login Error</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { 
            delay: 5000,
            animation: true
        });
        toast.show();
        
        toastElement.addEventListener('hidden.bs.toast', function () {
            this.remove();
        });
    }
    
    // Add floating labels effect
    const formControls = document.querySelectorAll('.form-control');
    formControls.forEach(control => {
        control.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        control.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });
    
    // Auto dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 20,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl + Enter to submit form
        if (e.ctrlKey && e.key === 'Enter') {
            if (loginForm) {
                loginForm.dispatchEvent(new Event('submit'));
            }
        }
        
        // Escape to clear form
        if (e.key === 'Escape') {
            if (loginForm) {
                loginForm.reset();
                usernameInput.classList.remove('is-valid', 'is-invalid');
                passwordInput.classList.remove('is-valid', 'is-invalid');
                document.getElementById('passwordStrength').classList.add('d-none');
            }
        }
    });
    
    // Add form auto-focus on page load
    if (usernameInput) {
        setTimeout(() => {
            usernameInput.focus();
        }, 300);
    }
});