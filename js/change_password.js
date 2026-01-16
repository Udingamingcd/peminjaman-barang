// js/change_password.js

document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const toggleCurrentPassword = document.getElementById('toggleCurrentPassword');
    const currentPasswordInput = document.getElementById('currentPassword');
    
    const toggleNewPassword = document.getElementById('toggleNewPassword');
    const newPasswordInput = document.getElementById('newPassword');
    
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    
    // Function to toggle password visibility
    function togglePasswordVisibility(toggleBtn, inputField) {
        if (toggleBtn && inputField) {
            toggleBtn.addEventListener('click', function() {
                const type = inputField.getAttribute('type') === 'password' ? 'text' : 'password';
                inputField.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }
    }
    
    // Initialize toggles
    togglePasswordVisibility(toggleCurrentPassword, currentPasswordInput);
    togglePasswordVisibility(toggleNewPassword, newPasswordInput);
    togglePasswordVisibility(toggleConfirmPassword, confirmPasswordInput);
    
    // Password strength checker
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            checkPasswordMatch();
        });
    }
    
    // Password match checker
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
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
    }
    
    function checkPasswordMatch() {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const matchAlert = document.getElementById('matchAlert');
        const matchText = document.getElementById('matchText');
        const matchIcon = matchAlert.querySelector('i');
        
        if (!confirmPassword) {
            matchText.textContent = 'Menunggu input password';
            matchIcon.className = 'fas fa-info-circle me-3 text-info';
            matchAlert.className = 'alert alert-light border';
            return;
        }
        
        if (newPassword === confirmPassword) {
            matchText.textContent = 'Password cocok ✓';
            matchIcon.className = 'fas fa-check-circle me-3 text-success';
            matchAlert.className = 'alert alert-success border-success';
            
            // Animate success
            matchAlert.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
                matchAlert.classList.remove('animate__animated', 'animate__pulse');
            }, 500);
        } else {
            matchText.textContent = 'Password tidak cocok ✗';
            matchIcon.className = 'fas fa-times-circle me-3 text-danger';
            matchAlert.className = 'alert alert-danger border-danger';
            
            // Shake animation for mismatch
            confirmPasswordInput.classList.add('animate__animated', 'animate__shakeX');
            matchAlert.classList.add('animate__animated', 'animate__shakeX');
            setTimeout(() => {
                confirmPasswordInput.classList.remove('animate__animated', 'animate__shakeX');
                matchAlert.classList.remove('animate__animated', 'animate__shakeX');
            }, 1000);
        }
    }
    
    // Show/hide password rules
    const showPasswordRulesCheckbox = document.getElementById('showPasswordRules');
    const passwordRulesDiv = document.getElementById('passwordRules');
    
    if (showPasswordRulesCheckbox && passwordRulesDiv) {
        showPasswordRulesCheckbox.addEventListener('change', function() {
            if (this.checked) {
                passwordRulesDiv.classList.remove('d-none');
                passwordRulesDiv.classList.add('animate__animated', 'animate__fadeIn');
            } else {
                passwordRulesDiv.classList.add('d-none');
            }
        });
    }
    
    // Form validation
    const changePasswordForm = document.getElementById('changePasswordForm');
    
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function(event) {
            const currentPassword = currentPasswordInput.value.trim();
            const newPassword = newPasswordInput.value.trim();
            const confirmPassword = confirmPasswordInput.value.trim();
            
            let isValid = true;
            let errorMessages = [];
            
            // Validate current password
            if (!currentPassword) {
                errorMessages.push('Password saat ini harus diisi');
                isValid = false;
                highlightError(currentPasswordInput);
            }
            
            // Validate new password
            if (!newPassword) {
                errorMessages.push('Password baru harus diisi');
                isValid = false;
                highlightError(newPasswordInput);
            } else if (newPassword.length < 6) {
                errorMessages.push('Password baru minimal 6 karakter');
                isValid = false;
                highlightError(newPasswordInput);
            }
            
            // Validate confirm password
            if (!confirmPassword) {
                errorMessages.push('Konfirmasi password harus diisi');
                isValid = false;
                highlightError(confirmPasswordInput);
            } else if (newPassword !== confirmPassword) {
                errorMessages.push('Password baru dan konfirmasi password tidak cocok');
                isValid = false;
                highlightError(confirmPasswordInput);
            }
            
            if (!isValid) {
                event.preventDefault();
                
                // Show error alert
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Gagal mengubah password:</strong>
                    <ul class="mb-0 mt-2">
                        ${errorMessages.map(msg => `<li>${msg}</li>`).join('')}
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                // Insert alert at the top of the form
                const cardBody = document.querySelector('.card-body');
                if (cardBody) {
                    const form = cardBody.querySelector('form');
                    if (form) {
                        cardBody.insertBefore(alertDiv, form);
                    } else {
                        cardBody.prepend(alertDiv);
                    }
                }
                
                // Scroll to the top of the form
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        });
    }
    
    function highlightError(inputElement) {
        inputElement.classList.add('is-invalid');
        inputElement.focus();
        
        // Remove error class after 3 seconds
        setTimeout(() => {
            inputElement.classList.remove('is-invalid');
        }, 3000);
    }
    
    // Auto dismiss alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(event) {
        // Ctrl + S to submit form
        if (event.ctrlKey && event.key === 's') {
            event.preventDefault();
            if (changePasswordForm) {
                changePasswordForm.dispatchEvent(new Event('submit'));
            }
        }
        
        // Escape to clear form
        if (event.key === 'Escape') {
            if (changePasswordForm) {
                changePasswordForm.reset();
                checkPasswordStrength('');
                checkPasswordMatch();
                
                // Show reset toast
                showToast('Form telah direset!', 'info');
            }
        }
    });
    
    // Helper function to show toast
    function showToast(message, type = 'info') {
        const toastContainer = document.querySelector('.toast-container') || createToastContainer();
        const toastId = 'toast-' + Date.now();
        
        const typeIcons = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };
        
        const typeClasses = {
            'success': 'bg-success text-white',
            'error': 'bg-danger text-white',
            'warning': 'bg-warning',
            'info': 'bg-info text-white'
        };
        
        const toastHTML = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header ${typeClasses[type]}">
                    <i class="fas ${typeIcons[type] || 'fa-info-circle'} me-2"></i>
                    <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
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
        
        toastElement.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    }
    
    function createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
        return container;
    }
});