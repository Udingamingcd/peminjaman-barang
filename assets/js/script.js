// JavaScript untuk Sistem Peminjaman Barang

document.addEventListener('DOMContentLoaded', function() {
    // ========== VALIDASI FORM ==========
    // Validasi form register superadmin
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password.length < 8) {
                e.preventDefault();
                showAlert('danger', 'Password minimal 8 karakter!');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showAlert('danger', 'Password dan konfirmasi password tidak sama!');
                return false;
            }
            
            // Konfirmasi akhir
            if (!confirm('Apakah Anda yakin ingin mendaftarkan akun superadmin?\nIni hanya bisa dilakukan sekali!')) {
                e.preventDefault();
                return false;
            }
        });
    }
    
    // Validasi form mahasiswa
    const formMahasiswa = document.getElementById('formMahasiswa');
    if (formMahasiswa) {
        formMahasiswa.addEventListener('submit', function(e) {
            const nim = document.getElementById('nim').value;
            const nama = document.getElementById('nama').value;
            const angkatan = document.getElementById('angkatan').value;
            
            // Validasi NIM
            if (!/^[0-9]{8,20}$/.test(nim)) {
                e.preventDefault();
                showAlert('danger', 'NIM harus berupa angka (8-20 digit)!');
                document.getElementById('nim').focus();
                return false;
            }
            
            // Validasi nama
            if (nama.trim().length < 2) {
                e.preventDefault();
                showAlert('danger', 'Nama harus minimal 2 karakter!');
                document.getElementById('nama').focus();
                return false;
            }
            
            // Validasi angkatan
            const currentYear = new Date().getFullYear();
            if (angkatan < 2000 || angkatan > currentYear + 5) {
                e.preventDefault();
                showAlert('danger', `Angkatan harus antara 2000 dan ${currentYear + 5}!`);
                document.getElementById('angkatan').focus();
                return false;
            }
            
            return true;
        });
    }
    
    // Validasi form umum
    const formValidations = document.querySelectorAll('form[id^="form"]');
    formValidations.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            let errorMessage = '';
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    errorMessage = 'Harap isi semua field yang wajib diisi!';
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showAlert('danger', errorMessage);
                return false;
            }
        });
    });
    
    // ========== AUTO-HIDE ALERTS ==========
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // ========== PASSWORD VISIBILITY TOGGLE ==========
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(function(input) {
        const wrapper = input.parentElement;
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'btn btn-sm btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2';
        toggleBtn.innerHTML = '<i class="bi bi-eye"></i>';
        toggleBtn.style.zIndex = '5';
        
        wrapper.style.position = 'relative';
        input.style.paddingRight = '40px';
        
        toggleBtn.addEventListener('click', function() {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
        });
        
        wrapper.appendChild(toggleBtn);
    });
    
    // ========== REAL-TIME SEARCH ==========
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(function(input) {
        input.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = this.closest('.card-body').querySelector('table');
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(function(row) {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            }
        });
    });
    
    // ========== CONFIRM DELETE ==========
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            const itemName = this.getAttribute('data-item-name') || 'data ini';
            
            if (confirm(`Apakah Anda yakin ingin menghapus ${itemName}? Tindakan ini tidak dapat dibatalkan.`)) {
                window.location.href = url;
            }
        });
    });
    
    // ========== REAL-TIME CLOCK ==========
    function updateClock() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            timeZone: 'Asia/Jakarta'
        };
        const timeString = now.toLocaleDateString('id-ID', options);
        
        const clockElements = document.querySelectorAll('.real-time-clock');
        clockElements.forEach(function(element) {
            element.textContent = timeString;
        });
    }
    
    // Update clock every second
    setInterval(updateClock, 1000);
    updateClock(); // Initial call
    
    // ========== TOAST NOTIFICATIONS ==========
    window.showAlert = function(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container-fluid, .container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
        }
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentElement) {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();
            }
        }, 5000);
    };
    
    // ========== FORM AUTO-SAVE (OPTIONAL) ==========
    const autoSaveForms = document.querySelectorAll('.auto-save-form');
    autoSaveForms.forEach(function(form) {
        const inputs = form.querySelectorAll('input, textarea, select');
        const formId = form.id || 'form_' + Math.random().toString(36).substr(2, 9);
        
        // Load saved data
        inputs.forEach(function(input) {
            const savedValue = localStorage.getItem(`${formId}_${input.name}`);
            if (savedValue && !input.value) {
                input.value = savedValue;
            }
        });
        
        // Save on input change
        inputs.forEach(function(input) {
            input.addEventListener('input', function() {
                localStorage.setItem(`${formId}_${this.name}`, this.value);
                showAutoSaveNotification();
            });
        });
        
        // Clear on submit
        form.addEventListener('submit', function() {
            inputs.forEach(function(input) {
                localStorage.removeItem(`${formId}_${input.name}`);
            });
        });
    });
    
    function showAutoSaveNotification() {
        const notification = document.getElementById('auto-save-notification');
        if (notification) {
            notification.classList.remove('d-none');
            setTimeout(() => {
                notification.classList.add('d-none');
            }, 2000);
        }
    }
    
    // ========== CHARACTER COUNTER ==========
    const textareas = document.querySelectorAll('textarea[maxlength]');
    textareas.forEach(function(textarea) {
        const maxLength = textarea.getAttribute('maxlength');
        const counter = document.createElement('small');
        counter.className = 'text-muted float-end';
        counter.textContent = `0/${maxLength}`;
        
        textarea.parentElement.appendChild(counter);
        
        textarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            counter.textContent = `${currentLength}/${maxLength}`;
            
            if (currentLength > maxLength * 0.9) {
                counter.classList.remove('text-muted');
                counter.classList.add('text-warning');
            } else if (currentLength > maxLength) {
                counter.classList.remove('text-warning');
                counter.classList.add('text-danger');
                this.value = this.value.substring(0, maxLength);
            } else {
                counter.classList.remove('text-warning', 'text-danger');
                counter.classList.add('text-muted');
            }
        });
        
        // Trigger initial count
        textarea.dispatchEvent(new Event('input'));
    });
    
    // ========== IMAGE PREVIEW ==========
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(function(input) {
        const previewId = input.getAttribute('data-preview');
        const previewElement = document.getElementById(previewId);
        
        if (previewElement) {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    // Validasi ukuran file (max 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        showAlert('danger', 'Ukuran file maksimal 2MB!');
                        this.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewElement.src = e.target.result;
                        previewElement.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
    
    // ========== AUTO-GENERATE KODE ==========
    const generateButtons = document.querySelectorAll('.btn-generate');
    generateButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                // Generate random code
                const timestamp = Date.now().toString().slice(-6);
                const random = Math.random().toString(36).substr(2, 4).toUpperCase();
                const generatedCode = `PMJ-${timestamp}-${random}`;
                
                targetElement.value = generatedCode;
                showAlert('success', 'Kode berhasil digenerate!');
            }
        });
    });
    
    // ========== DATE PICKER ENHANCEMENT ==========
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        // Set min date to today for future dates
        if (input.id.includes('pinjam') || input.id.includes('tanggal')) {
            const today = new Date().toISOString().split('T')[0];
            input.setAttribute('min', today);
        }
        
        // Set max date to today for past dates
        if (input.id.includes('kembali') || input.id.includes('pengembalian')) {
            const today = new Date().toISOString().split('T')[0];
            input.setAttribute('max', today);
        }
    });
    
    // ========== PHONE NUMBER FORMATTING ==========
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            // Remove non-numeric characters
            this.value = this.value.replace(/\D/g, '');
            
            // Format as Indonesian phone number
            if (this.value.length > 0) {
                if (this.value.startsWith('0')) {
                    this.value = '62' + this.value.substring(1);
                }
            }
        });
    });
    
    // ========== BULK ACTIONS ==========
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.select-item');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    }
    
    // ========== LOADING STATES ==========
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            if (this.form.checkValidity()) {
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Memproses...';
                this.disabled = true;
            }
        });
    });
    
    // ========== TOOLTIP INITIALIZATION ==========
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// ========== HELPER FUNCTIONS ==========
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
}

function generateRandomString(length) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[0-9]{10,15}$/;
    return re.test(phone);
}

// Export for use in console if needed
window.SPBTools = {
    formatDate,
    formatDateTime,
    formatRupiah,
    generateRandomString,
    validateEmail,
    validatePhone
};

// ========== GLOBAL ERROR HANDLING ==========
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    // You can send this to your error tracking service
});

// ========== OFFLINE DETECTION ==========
window.addEventListener('online', function() {
    showAlert('success', 'Koneksi internet telah pulih.');
});

window.addEventListener('offline', function() {
    showAlert('warning', 'Anda sedang offline. Beberapa fitur mungkin tidak tersedia.');
});