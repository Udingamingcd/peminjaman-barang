// script.js

document.addEventListener('DOMContentLoaded', function() {
    // Validasi form register superadmin
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordStrengthText = document.getElementById('passwordStrength');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        
        // Fungsi untuk mengecek kekuatan password
        function checkPasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            return strength;
        }
        
        // Update kekuatan password saat mengetik
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            
            let strengthText = '';
            let barColor = '';
            let barWidth = '0%';
            
            switch(strength) {
                case 0:
                case 1:
                    strengthText = 'Lemah';
                    barColor = '#dc3545';
                    barWidth = '20%';
                    break;
                case 2:
                    strengthText = 'Cukup';
                    barColor = '#ffc107';
                    barWidth = '40%';
                    break;
                case 3:
                    strengthText = 'Baik';
                    barColor = '#17a2b8';
                    barWidth = '60%';
                    break;
                case 4:
                    strengthText = 'Kuat';
                    barColor = '#28a745';
                    barWidth = '80%';
                    break;
                case 5:
                    strengthText = 'Sangat Kuat';
                    barColor = '#28a745';
                    barWidth = '100%';
                    break;
            }
            
            passwordStrengthText.textContent = strengthText;
            passwordStrengthBar.style.width = barWidth;
            passwordStrengthBar.style.backgroundColor = barColor;
            passwordStrengthText.style.color = barColor;
        });
        
        // Cek kecocokan password
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const matchText = document.getElementById('passwordMatch');
            
            if (confirmPassword === '') {
                matchText.textContent = '';
                matchText.style.color = '';
            } else if (password === confirmPassword) {
                matchText.textContent = '✓ Password cocok';
                matchText.style.color = '#28a745';
            } else {
                matchText.textContent = '✗ Password tidak cocok';
                matchText.style.color = '#dc3545';
            }
        }
        
        passwordInput.addEventListener('input', checkPasswordMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        
        // Validasi sebelum submit
        registerForm.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                confirmPasswordInput.focus();
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                passwordInput.focus();
                return false;
            }
            
            // Konfirmasi sebelum mendaftarkan superadmin
            if (!confirm('Apakah Anda yakin ingin mendaftarkan akun superadmin?\n\nPerhatian: Ini hanya bisa dilakukan sekali!')) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    }
    
    // Animasi untuk tombol
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Auto focus pada input pertama di form login
    const loginUsername = document.getElementById('username');
    if (loginUsername) {
        loginUsername.focus();
    }
    
    // Fitur show/hide password (opsional - bisa ditambahkan nanti)
    console.log('Sistem Peminjaman Barang siap digunakan!');
});