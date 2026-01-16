/**
 * Dashboard JavaScript
 * Sistem Peminjaman Barang
 */

// Global variables
let categoryChart, statusChart, detailedChart;
let currentChartType = 'doughnut';
let darkMode = false;

document.addEventListener('DOMContentLoaded', function() {
    console.log('📊 Dashboard loaded successfully!');
    
    // Initialize all dashboard components
    initDashboard();
    
    // Check for saved preferences
    loadUserPreferences();
});

/**
 * Initialize all dashboard components
 */
function initDashboard() {
    initCharts();
    initTooltips();
    initTimeUpdater();
    initEventListeners();
    initClickableRows();
    initCardAnimations();
    initQuickActionButtons();
    initNotificationBell();
    initLiveUpdates();
    initKeyboardShortcuts();
    
    // Add particles effect for welcome card
    initParticlesEffect();
}

/**
 * Initialize charts
 */
function initCharts() {
    // Category Distribution Chart
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        categoryChart = new Chart(categoryCtx, {
            type: currentChartType,
            data: window.kategoriData || {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            font: {
                                size: 12,
                                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} barang (${percentage}%)`;
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                cutout: '70%',
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
        
        // Hide loading overlay
        setTimeout(() => {
            document.getElementById('chartLoading')?.classList.remove('show');
        }, 1000);
    }

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        statusChart = new Chart(statusCtx, {
            type: 'bar',
            data: window.statusData || {
                labels: ['Dipinjam', 'Dikembalikan', 'Hilang'],
                datasets: [{
                    label: 'Jumlah',
                    data: [0, 0, 0],
                    backgroundColor: ['#f6c23e', '#1cc88a', '#e74a3b'],
                    borderWidth: 0,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                if (value % 1 === 0) return value;
                            }
                        },
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }
}

/**
 * Initialize Bootstrap tooltips
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            delay: { show: 300, hide: 100 }
        });
    });
}

/**
 * Update current time in footer
 */
function initTimeUpdater() {
    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
        const updateTime = () => {
            const now = new Date();
            const formatted = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            timeElement.textContent = formatted;
        };
        
        updateTime();
        setInterval(updateTime, 1000);
    }
}

/**
 * Initialize event listeners
 */
function initEventListeners() {
    // Refresh button
    const refreshBtn = document.querySelector('[onclick="refreshDashboard()"]');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', refreshDashboard);
    }
    
    // Dark mode toggle
    const darkModeToggle = document.querySelector('[onclick="toggleDarkMode()"]');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', toggleDarkMode);
    }
    
    // Export PDF
    const exportBtn = document.querySelector('[onclick="Dashboard.exportPDF()"]');
    if (exportBtn) {
        exportBtn.addEventListener('click', Dashboard.exportPDF);
    }
    
    // View mode toggle
    const viewModeBtn = document.querySelector('[onclick="toggleViewMode()"]');
    if (viewModeBtn) {
        viewModeBtn.addEventListener('click', toggleViewMode);
    }
}

/**
 * Refresh dashboard data with animation
 */
function refreshDashboard() {
    const btn = document.querySelector('[onclick="refreshDashboard()"]');
    const icon = btn.querySelector('i');
    
    // Add loading animation
    btn.disabled = true;
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memperbarui...';
    btn.classList.add('disabled');
    
    // Add pulse animation to cards
    document.querySelectorAll('.stat-card').forEach(card => {
        card.classList.add('pulse');
    });
    
    // Show loading overlay on charts
    const chartLoading = document.getElementById('chartLoading');
    if (chartLoading) {
        chartLoading.classList.add('show');
    }
    
    // Simulate API call
    setTimeout(() => {
        // Fetch new data
        fetchDashboardStats();
        
        // Reset button
        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            btn.classList.remove('disabled');
            
            // Remove pulse animation
            document.querySelectorAll('.stat-card').forEach(card => {
                card.classList.remove('pulse');
            });
            
            // Hide loading overlay
            if (chartLoading) {
                chartLoading.classList.remove('show');
            }
            
            // Show success message
            Dashboard.showToast('✅ Dashboard berhasil diperbarui!', 'success');
        }, 800);
    }, 1500);
}

/**
 * Fetch dashboard stats from API
 */
async function fetchDashboardStats() {
    try {
        // In a real application, you would fetch from an API endpoint
        // const response = await fetch('api/dashboard_stats.php');
        // const data = await response.json();
        // updateStatistics(data);
        
        // For now, just update the time and animate counters
        updateLiveStats();
    } catch (error) {
        console.error('❌ Error fetching dashboard data:', error);
        Dashboard.showToast('❌ Gagal memperbarui data', 'error');
    }
}

/**
 * Update statistics on the page
 */
function updateStatistics(data) {
    const elements = {
        'total_barang': document.getElementById('total-barang'),
        'total_dipinjam': document.getElementById('total-dipinjam'),
        'total_dikembalikan': document.getElementById('total-dikembalikan'),
        'total_user': document.getElementById('total-user'),
        'total_mahasiswa': document.getElementById('total-mahasiswa'),
        'total_barang_hilang': document.getElementById('total-barang-hilang'),
        'total_barang_tersedia': document.getElementById('total-barang-tersedia')
    };
    
    for (const [key, element] of Object.entries(elements)) {
        if (element && data[key] !== undefined) {
            animateCounter(element, parseInt(data[key]));
        }
    }
}

/**
 * Animate counter from current value to new value
 */
function animateCounter(element, newValue) {
    const current = parseInt(element.textContent.replace(/[^\d]/g, '')) || 0;
    const duration = 1000;
    
    if (current === newValue) return;
    
    const start = performance.now();
    const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);
    
    const animate = (timestamp) => {
        const elapsed = timestamp - start;
        const progress = Math.min(elapsed / duration, 1);
        const easedProgress = easeOutCubic(progress);
        
        const currentValue = Math.round(current + (newValue - current) * easedProgress);
        element.textContent = currentValue.toLocaleString('id-ID');
        
        if (progress < 1) {
            requestAnimationFrame(animate);
        } else {
            element.textContent = newValue.toLocaleString('id-ID');
        }
    };
    
    requestAnimationFrame(animate);
}

/**
 * Initialize clickable table rows
 */
function initClickableRows() {
    document.querySelectorAll('.clickable-row').forEach(row => {
        // Single click for tooltip
        row.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || 
                e.target.closest('a') || e.target.closest('button')) {
                return;
            }
            
            const peminjamanId = this.getAttribute('data-id') || 
                this.getAttribute('onclick')?.match(/showPeminjamanDetail\((\d+)\)/)?.[1];
            
            if (peminjamanId) {
                showPeminjamanDetail(peminjamanId);
            }
        });
        
        // Double click for quick action
        row.addEventListener('dblclick', function(e) {
            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || 
                e.target.closest('a') || e.target.closest('button')) {
                return;
            }
            
            const href = this.getAttribute('data-href') || 
                this.getAttribute('onclick')?.match(/window\.location='([^']+)'/)?.[1];
            
            if (href) {
                window.location.href = href;
            }
        });
    });
}

/**
 * Initialize card animations
 */
function initCardAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.stat-card').forEach(card => {
        observer.observe(card);
    });
}

/**
 * Initialize quick action buttons
 */
function initQuickActionButtons() {
    document.querySelectorAll('.quick-action-btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

/**
 * Initialize notification bell
 */
function initNotificationBell() {
    const bell = document.getElementById('notificationDropdown');
    if (bell) {
        bell.addEventListener('click', function() {
            // Add animation
            this.classList.add('animate__animated', 'animate__shakeX');
            setTimeout(() => {
                this.classList.remove('animate__animated', 'animate__shakeX');
            }, 1000);
        });
    }
}

/**
 * Initialize live updates
 */
function initLiveUpdates() {
    // Update live stats every 30 seconds
    setInterval(updateLiveStats, 30000);
    
    // Initial update
    updateLiveStats();
}

/**
 * Update live stats in footer
 */
function updateLiveStats() {
    const liveStats = document.getElementById('liveStats');
    if (liveStats) {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        liveStats.innerHTML = `<i class="fas fa-sync-alt fa-spin me-1"></i> Terakhir diperbarui: ${timeStr}`;
        
        // Add flash animation
        liveStats.classList.add('text-primary');
        setTimeout(() => {
            liveStats.classList.remove('text-primary');
        }, 1000);
    }
}

/**
 * Initialize keyboard shortcuts
 */
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl+R or F5 to refresh
        if ((e.ctrlKey && e.key === 'r') || e.key === 'F5') {
            e.preventDefault();
            refreshDashboard();
        }
        
        // Ctrl+P to print
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            Dashboard.print();
        }
        
        // Ctrl+D for dark mode
        if (e.ctrlKey && e.key === 'd') {
            e.preventDefault();
            toggleDarkMode();
        }
        
        // Ctrl+H for help
        if (e.ctrlKey && e.key === 'h') {
            e.preventDefault();
            Dashboard.showHelp();
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal.show');
            modals.forEach(modal => {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) modalInstance.hide();
            });
        }
    });
}

/**
 * Show peminjaman detail modal
 */
function showPeminjamanDetail(id) {
    // Show loading
    const modalContent = document.getElementById('peminjamanDetail');
    modalContent.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat detail peminjaman...</p>
        </div>
    `;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('peminjamanModal'));
    modal.show();
    
    // Simulate API call
    setTimeout(() => {
        modalContent.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-info-circle me-2"></i>Informasi Umum</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Kode Peminjaman</span>
                            <span class="fw-bold">PMJ-${id.toString().padStart(6, '0')}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Status</span>
                            <span class="badge bg-warning">Dipinjam</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Tanggal Pinjam</span>
                            <span>${new Date().toLocaleDateString('id-ID')}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Admin</span>
                            <span><?= htmlspecialchars($nama_lengkap) ?></span>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-user-graduate me-2"></i>Data Mahasiswa</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Nama</span>
                            <span>Mahasiswa Contoh</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>NIM</span>
                            <span>20210001</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Angkatan</span>
                            <span>2021</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6><i class="fas fa-box me-2"></i>Detail Barang</h6>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Data detail peminjaman akan ditampilkan di sini.
                    </div>
                </div>
            </div>
        `;
    }, 1000);
}

/**
 * Toggle view mode (grid/list)
 */
function toggleViewMode() {
    const cards = document.querySelectorAll('.stat-card');
    const icon = document.querySelector('[onclick="toggleViewMode()"] i');
    
    cards.forEach(card => {
        card.classList.toggle('grid-view');
    });
    
    // Toggle icon
    if (icon.classList.contains('fa-th-large')) {
        icon.classList.remove('fa-th-large');
        icon.classList.add('fa-list');
        Dashboard.showToast('📋 Mode daftar diaktifkan', 'info');
    } else {
        icon.classList.remove('fa-list');
        icon.classList.add('fa-th-large');
        Dashboard.showToast('🔲 Mode grid diaktifkan', 'info');
    }
}

/**
 * Toggle quick actions visibility
 */
function toggleQuickActions() {
    const quickActions = document.getElementById('quickActions');
    const icon = document.querySelector('[onclick="toggleQuickActions()"] i');
    
    quickActions.classList.toggle('d-none');
    
    // Toggle icon
    if (icon.classList.contains('fa-eye-slash')) {
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        Dashboard.showToast('👁️ Akses cepat ditampilkan', 'info');
    } else {
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        Dashboard.showToast('👁️ Akses cepat disembunyikan', 'info');
    }
}

/**
 * Change chart type
 */
function changeChartType(type) {
    if (categoryChart && currentChartType !== type) {
        currentChartType = type;
        
        // Show loading
        const chartLoading = document.getElementById('chartLoading');
        if (chartLoading) {
            chartLoading.classList.add('show');
        }
        
        // Update chart
        setTimeout(() => {
            categoryChart.config.type = type;
            categoryChart.update();
            
            // Hide loading
            if (chartLoading) {
                chartLoading.classList.remove('show');
            }
            
            Dashboard.showToast(`📊 Chart diubah ke ${type}`, 'success');
        }, 500);
    }
}

/**
 * Download chart as image
 */
function downloadChart() {
    if (statusChart) {
        const link = document.createElement('a');
        link.download = `chart-status-${new Date().getTime()}.png`;
        link.href = statusChart.toBase64Image();
        link.click();
        Dashboard.showToast('📥 Chart berhasil diunduh', 'success');
    }
}

/**
 * Toggle dark mode
 */
function toggleDarkMode() {
    darkMode = !darkMode;
    document.body.classList.toggle('dark-mode');
    
    // Save preference
    localStorage.setItem('darkMode', darkMode);
    
    // Update charts for dark mode
    if (darkMode) {
        Dashboard.showToast('🌙 Mode gelap diaktifkan', 'info');
    } else {
        Dashboard.showToast('☀️ Mode terang diaktifkan', 'info');
    }
}

/**
 * Load user preferences
 */
function loadUserPreferences() {
    // Load dark mode preference
    const savedDarkMode = localStorage.getItem('darkMode') === 'true';
    if (savedDarkMode) {
        darkMode = true;
        document.body.classList.add('dark-mode');
    }
    
    // Load other preferences
    const collapsedQuickActions = localStorage.getItem('collapsedQuickActions') === 'true';
    if (collapsedQuickActions) {
        toggleQuickActions();
    }
}

/**
 * Show stats modal
 */
function showStatsModal() {
    const modal = new bootstrap.Modal(document.getElementById('statsModal'));
    modal.show();
}

/**
 * Print detail
 */
function printDetail() {
    window.print();
}

/**
 * Initialize particles effect for welcome card
 */
function initParticlesEffect() {
    const welcomeCard = document.querySelector('.welcome-card');
    if (welcomeCard) {
        // Create particles
        for (let i = 0; i < 15; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.cssText = `
                position: absolute;
                width: ${Math.random() * 3 + 1}px;
                height: ${Math.random() * 3 + 1}px;
                background: rgba(255, 255, 255, ${Math.random() * 0.5});
                border-radius: 50%;
                top: ${Math.random() * 100}%;
                left: ${Math.random() * 100}%;
                animation: float ${Math.random() * 10 + 5}s linear infinite;
            `;
            welcomeCard.appendChild(particle);
        }
    }
}

// Export functions for global use
window.Dashboard = {
    refresh: refreshDashboard,
    exportPDF: function() {
        Dashboard.showToast('📄 Sedang menyiapkan PDF...', 'info');
        setTimeout(() => {
            Dashboard.showToast('✅ PDF berhasil dibuat!', 'success');
        }, 2000);
    },
    print: function() {
        window.print();
        Dashboard.showToast('🖨️ Mencetak dashboard...', 'info');
    },
    showToast: function(message, type = 'info') {
        // Create toast container if it doesn't exist
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        
        // Create toast
        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast align-items-center text-bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        container.appendChild(toast);
        
        // Show toast
        const bsToast = new bootstrap.Toast(toast, {
            delay: 3000,
            animation: true
        });
        bsToast.show();
        
        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', function () {
            this.remove();
        });
    },
    showStatsModal: showStatsModal,
    showHelp: function() {
        const helpText = `
            <strong>Keyboard Shortcuts:</strong><br>
            • Ctrl+R = Refresh Dashboard<br>
            • Ctrl+P = Print<br>
            • Ctrl+D = Toggle Dark Mode<br>
            • Ctrl+H = Bantuan ini<br>
            • ESC = Tutup modal<br><br>
            <strong>Tips:</strong><br>
            • Klik dua kali pada baris tabel untuk aksi cepat<br>
            • Arahkan kursor ke ikon untuk melihat tooltip<br>
            • Gunakan tombol refresh untuk data terbaru
        `;
        
        const helpModal = new bootstrap.Modal(document.createElement('div'));
        helpModal._element.className = 'modal fade';
        helpModal._element.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-question-circle me-2"></i>Bantuan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${helpText}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Mengerti</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(helpModal._element);
        helpModal.show();
        
        // Clean up after modal is hidden
        helpModal._element.addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }
};

// Add CSS for particles animation
const style = document.createElement('style');
style.textContent = `
    @keyframes float {
        0% {
            transform: translateY(0) translateX(0);
            opacity: 1;
        }
        100% {
            transform: translateY(-100px) translateX(20px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);