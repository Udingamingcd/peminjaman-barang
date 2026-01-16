// js/register_superadmin.js - Versi Profesional

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            delay: { show: 300, hide: 100 }
        });
    });
    
    // Initialize toast notification
    const registerToastEl = document.getElementById('registerToast');
    if (registerToastEl) {
        const registerToast = new bootstrap.Toast(registerToastEl, { 
            delay: 4000,
            animation: true
        });
        setTimeout(() => registerToast.show(), 1500);
    }
    
    // Toggle password visibility for password field
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
    
    // Toggle password visibility for confirm password field
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (toggleConfirmPassword && confirmPasswordInput) {
        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            
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
            checkPasswordMatch();
        });
    }
    
    function checkPasswordStrength(password) {
        const strengthDiv = document.getElementById('passwordStrength');
        const progressBar = document.querySelector('.progress-bar');
        const strengthText = document.getElementById('strengthText');
        
        if (!password) {
            strengthDiv.style.opacity = '0.5';
            return;
        }
        
        strengthDiv.style.opacity = '1';
        
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
        
        if (strength >= 30) {
            color = 'bg-warning';
            text = 'Lemah';
            textColor = 'text-warning';
        }
        if (strength >= 50) {
            color = 'bg-info';
            text = 'Cukup';
            textColor = 'text-info';
        }
        if (strength >= 70) {
            color = 'bg-success';
            text = 'Kuat';
            textColor = 'text-success';
        }
        if (strength >= 90) {
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
    
    // Password match checking
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const matchAlert = document.getElementById('matchAlert');
        const matchText = document.getElementById('matchText');
        const matchDescription = document.getElementById('matchDescription');
        
        if (!confirmPassword) {
            matchText.textContent = 'Menunggu input password';
            matchDescription.textContent = 'Pastikan kedua password sama untuk keamanan akun';
            matchAlert.className = 'alert alert-light border';
            confirmPasswordInput.classList.remove('is-invalid', 'is-valid');
            return;
        }
        
        if (password === confirmPassword) {
            matchText.textContent = 'Password cocok ✓';
            matchDescription.textContent = 'Password konfirmasi sesuai dengan password yang diinput';
            matchAlert.className = 'alert alert-success border-success';
            confirmPasswordInput.classList.remove('is-invalid');
            confirmPasswordInput.classList.add('is-valid');
            
            // Animate success
            matchAlert.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
                matchAlert.classList.remove('animate__animated', 'animate__pulse');
            }, 500);
        } else {
            matchText.textContent = 'Password tidak cocok ✗';
            matchDescription.textContent = 'Password konfirmasi tidak sesuai dengan password yang diinput';
            matchAlert.className = 'alert alert-danger border-danger';
            confirmPasswordInput.classList.add('is-invalid');
            confirmPasswordInput.classList.remove('is-valid');
            
            // Shake animation for mismatch
            confirmPasswordInput.classList.add('animate__animated', 'animate__shakeX');
            matchAlert.classList.add('animate__animated', 'animate__shakeX');
            setTimeout(() => {
                confirmPasswordInput.classList.remove('animate__animated', 'animate__shakeX');
                matchAlert.classList.remove('animate__animated', 'animate__shakeX');
            }, 1000);
        }
    }
    
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    }
    
    // Real-time form validation
    const namaLengkapInput = document.getElementById('nama_lengkap');
    const usernameInput = document.getElementById('username');
    const registerForm = document.getElementById('registerForm');
    
    // Nama lengkap validation
    if (namaLengkapInput) {
        namaLengkapInput.addEventListener('input', function() {
            if (this.value.trim().length >= 3) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });
    }
    
    // Username validation
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            const username = this.value.trim();
            
            if (username.length >= 3) {
                // Check username availability (simulated)
                checkUsernameAvailability(username);
            } else {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });
    }
    
    // Simulate username availability check
    function checkUsernameAvailability(username) {
        // In a real application, this would be an AJAX call to the server
        setTimeout(() => {
            // Simulate some usernames that are already taken
            const takenUsernames = ['admin', 'superadmin', 'administrator', 'user', 'root'];
            
            if (takenUsernames.includes(username.toLowerCase())) {
                usernameInput.classList.add('is-invalid');
                usernameInput.classList.remove('is-valid');
                showValidationMessage(usernameInput, 'Username sudah terdaftar!');
            } else {
                usernameInput.classList.remove('is-invalid');
                usernameInput.classList.add('is-valid');
                showValidationMessage(usernameInput, 'Username tersedia!');
            }
        }, 500);
    }
    
    function showValidationMessage(input, message) {
        let feedback = input.nextElementSibling?.nextElementSibling;
        if (!feedback || !feedback.classList.contains('custom-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'custom-feedback form-text mt-1';
            input.parentNode.appendChild(feedback);
        }
        feedback.textContent = message;
        feedback.className = `custom-feedback form-text mt-1 ${message.includes('tersedia') ? 'text-success' : 'text-danger'}`;
    }
    
    // Password validation
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            if (this.value.length >= 6) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });
    }
    
    // Form submission with loading state
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            const namaLengkap = namaLengkapInput.value.trim();
            const username = usernameInput.value.trim();
            const password = passwordInput.value.trim();
            const confirmPassword = confirmPasswordInput.value.trim();
            const registerButton = document.getElementById('registerButton');
            const buttonText = registerButton.querySelector('.button-text');
            const spinner = registerButton.querySelector('.spinner-border');
            
            let isValid = true;
            let errorMessages = [];
            
            // Validate inputs
            if (namaLengkap.length < 3) {
                namaLengkapInput.classList.add('is-invalid');
                isValid = false;
                errorMessages.push('Nama lengkap minimal 3 karakter');
                shakeElement(namaLengkapInput);
            }
            
            if (username.length < 3) {
                usernameInput.classList.add('is-invalid');
                isValid = false;
                errorMessages.push('Username minimal 3 karakter');
                shakeElement(usernameInput);
            }
            
            if (password.length < 6) {
                passwordInput.classList.add('is-invalid');
                isValid = false;
                errorMessages.push('Password minimal 6 karakter');
                shakeElement(passwordInput);
            }
            
            if (password !== confirmPassword) {
                confirmPasswordInput.classList.add('is-invalid');
                isValid = false;
                errorMessages.push('Password dan konfirmasi password tidak cocok');
                shakeElement(confirmPasswordInput);
            }
            
            if (!isValid) {
                event.preventDefault();
                showErrorToast('Registrasi gagal: ' + errorMessages.join(', '));
                return;
            }
            
            // Show loading state
            buttonText.classList.add('d-none');
            spinner.classList.remove('d-none');
            registerButton.disabled = true;
            registerButton.classList.add('btn-loading');
            
            // Form will submit normally after validation
            // We don't prevent default to allow form submission
        });
    }
    
    // Animate back button
    const backBtn = document.querySelector('.btn-back');
    if (backBtn) {
        backBtn.addEventListener('mouseenter', function() {
            this.querySelector('i').classList.add('animate__animated', 'animate__bounce');
        });
        
        backBtn.addEventListener('mouseleave', function() {
            this.querySelector('i').classList.remove('animate__animated', 'animate__bounce');
        });
    }
    
    // Animate warning alert items
    const warningItems = document.querySelectorAll('.alert-warning ul li');
    warningItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.2}s`;
    });
    
    // Add hover effect to card
    const card = document.querySelector('.register-card');
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
                    <strong class="me-auto">Registrasi Error</strong>
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
    
    // Add auto-focus on first input
    if (namaLengkapInput) {
        setTimeout(() => {
            namaLengkapInput.focus();
        }, 300);
    }
    
    // Add form reset functionality
    const resetBtn = document.createElement('button');
    resetBtn.type = 'button';
    resetBtn.className = 'btn btn-sm btn-outline-secondary position-absolute';
    resetBtn.style.right = '1rem';
    resetBtn.style.top = '1rem';
    resetBtn.innerHTML = '<i class="fas fa-redo me-1"></i>Reset';
    
    const formContainer = document.querySelector('.card-body');
    if (formContainer) {
        formContainer.style.position = 'relative';
        formContainer.appendChild(resetBtn);
        
        resetBtn.addEventListener('click', function() {
            registerForm.reset();
            namaLengkapInput.classList.remove('is-valid', 'is-invalid');
            usernameInput.classList.remove('is-valid', 'is-invalid');
            passwordInput.classList.remove('is-valid', 'is-invalid');
            confirmPasswordInput.classList.remove('is-valid', 'is-invalid');
            document.getElementById('passwordStrength').style.opacity = '0.5';
            document.querySelector('.progress-bar').style.width = '0%';
            document.getElementById('matchAlert').className = 'alert alert-light border';
            document.getElementById('matchText').textContent = 'Menunggu input password';
            document.getElementById('matchDescription').textContent = 'Pastikan kedua password sama untuk keamanan akun';
            
            // Show reset confirmation
            const resetToast = new bootstrap.Toast(document.createElement('div'), {
                delay: 3000
            });
            showSuccessToast('Form telah direset!');
        });
    }
    
    function showSuccessToast(message) {
        const toastContainer = document.querySelector('.toast-container');
        const toastId = 'successToast-' + Date.now();
        
        const toastHTML = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong class="me-auto">Berhasil</strong>
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
            delay: 3000,
            animation: true
        });
        toast.show();
        
        toastElement.addEventListener('hidden.bs.toast', function () {
            this.remove();
        });
    }
    
    // Auto dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});