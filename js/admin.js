// js/admin.js

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar Toggle for Mobile
    const sidebarToggle = document.createElement('button');
    sidebarToggle.className = 'btn btn-primary d-md-none sidebar-toggle';
    sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
    sidebarToggle.style.position = 'fixed';
    sidebarToggle.style.top = '10px';
    sidebarToggle.style.left = '10px';
    sidebarToggle.style.zIndex = '1001';
    
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('show');
        mainContent.style.marginLeft = sidebar.classList.contains('show') ? '250px' : '0';
    });
    
    document.body.appendChild(sidebarToggle);
    
    // Initialize all tooltips
    initializeTooltips();
    
    // Initialize scroll functionality for peminjaman table
    initializePeminjamanScroll();
    
    // Initialize scroll functionality for laporan table
    initializeLaporanScroll();
    
    // Dashboard cards animation on load
    animateStatCards();
    
    // Auto-hide sidebar on mobile when clicking outside
    setupSidebarAutoHide();
    
    // Add CSS for animations and styles
    addCustomStyles();
    
    // Initialize table row animations
    animateTableRows();
});

// Fungsi untuk menginisialisasi tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Fungsi scroll horizontal untuk tabel peminjaman
function initializePeminjamanScroll() {
    const tableContainer = document.getElementById('peminjamanTableContainer');
    const scrollLeftBtn = document.getElementById('scrollLeft');
    const scrollRightBtn = document.getElementById('scrollRight');
    
    if (tableContainer && scrollLeftBtn && scrollRightBtn) {
        const scrollAmount = 300;
        
        // Scroll left function
        scrollLeftBtn.addEventListener('click', function() {
            tableContainer.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
            updateScrollButtons(tableContainer, scrollLeftBtn, scrollRightBtn);
        });
        
        // Scroll right function
        scrollRightBtn.addEventListener('click', function() {
            tableContainer.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
            updateScrollButtons(tableContainer, scrollLeftBtn, scrollRightBtn);
        });
        
        // Touch/swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        
        tableContainer.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, {passive: true});
        
        tableContainer.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe(tableContainer, scrollLeftBtn, scrollRightBtn, scrollAmount);
        }, {passive: true});
        
        function handleSwipe(container, leftBtn, rightBtn, amount) {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left
                    container.scrollBy({
                        left: amount,
                        behavior: 'smooth'
                    });
                } else {
                    // Swipe right
                    container.scrollBy({
                        left: -amount,
                        behavior: 'smooth'
                    });
                }
            }
            updateScrollButtons(container, leftBtn, rightBtn);
        }
        
        // Update scroll buttons visibility
        function updateScrollButtons(container, leftBtn, rightBtn) {
            const scrollLeft = container.scrollLeft;
            const maxScrollLeft = container.scrollWidth - container.clientWidth;
            
            // Fade out buttons when at edges
            if (scrollLeft <= 10) {
                leftBtn.style.opacity = '0.5';
                leftBtn.disabled = true;
                leftBtn.classList.remove('pulse-animation');
            } else {
                leftBtn.style.opacity = '1';
                leftBtn.disabled = false;
                leftBtn.classList.add('pulse-animation');
            }
            
            if (scrollLeft >= maxScrollLeft - 10) {
                rightBtn.style.opacity = '0.5';
                rightBtn.disabled = true;
                rightBtn.classList.remove('pulse-animation');
            } else {
                rightBtn.style.opacity = '1';
                rightBtn.disabled = false;
                rightBtn.classList.add('pulse-animation');
            }
        }
        
        // Initialize scroll buttons
        updateScrollButtons(tableContainer, scrollLeftBtn, scrollRightBtn);
        
        // Listen for scroll events
        tableContainer.addEventListener('scroll', function() {
            updateScrollButtons(tableContainer, scrollLeftBtn, scrollRightBtn);
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            setTimeout(function() {
                updateScrollButtons(tableContainer, scrollLeftBtn, scrollRightBtn);
            }, 100);
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            
            if (e.key === 'ArrowLeft') {
                scrollLeftBtn.click();
                e.preventDefault();
            } else if (e.key === 'ArrowRight') {
                scrollRightBtn.click();
                e.preventDefault();
            }
        });
    }
}

// Fungsi scroll horizontal untuk tabel laporan
function initializeLaporanScroll() {
    const laporanTableContainer = document.getElementById('laporanTableContainer');
    const laporanScrollLeftBtn = document.getElementById('laporanScrollLeft');
    const laporanScrollRightBtn = document.getElementById('laporanScrollRight');
    
    // Jika tombol dengan ID khusus tidak ditemukan, coba tombol umum
    const scrollLeftBtn = laporanScrollLeftBtn || document.getElementById('scrollLeft');
    const scrollRightBtn = laporanScrollRightBtn || document.getElementById('scrollRight');
    
    // Inisialisasi DataTable untuk tabel laporan HANYA JIKA BELUM DIINISIALISASI
    if (typeof $ !== 'undefined' && $('#laporanTable').length && !$.fn.dataTable.isDataTable('#laporanTable')) {
        $('#laporanTable').DataTable({
            scrollX: true,
            scrollCollapse: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            order: [[5, 'desc']],
            columnDefs: [
                { orderable: false, targets: [0, 8, 10] }
            ],
            initComplete: function() {
                // Re-attach scroll event after DataTables initialization
                if (laporanTableContainer) {
                    const leftBtn = document.getElementById('scrollLeft');
                    const rightBtn = document.getElementById('scrollRight');
                    
                    laporanTableContainer.addEventListener('scroll', function() {
                        updateLaporanScrollButtons(laporanTableContainer, leftBtn, rightBtn);
                    });
                    
                    updateLaporanScrollButtons(laporanTableContainer, leftBtn, rightBtn);
                }
            }
        });
    }
    
    if (laporanTableContainer && scrollLeftBtn && scrollRightBtn) {
        const scrollAmount = 300;
        
        // Scroll left function
        scrollLeftBtn.addEventListener('click', function() {
            laporanTableContainer.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
            updateLaporanScrollButtons(laporanTableContainer, scrollLeftBtn, scrollRightBtn);
        });
        
        // Scroll right function
        scrollRightBtn.addEventListener('click', function() {
            laporanTableContainer.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
            updateLaporanScrollButtons(laporanTableContainer, scrollLeftBtn, scrollRightBtn);
        });
        
        // Touch/swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        
        laporanTableContainer.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, {passive: true});
        
        laporanTableContainer.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleLaporanSwipe(laporanTableContainer, scrollLeftBtn, scrollRightBtn, scrollAmount);
        }, {passive: true});
        
        function handleLaporanSwipe(container, leftBtn, rightBtn, amount) {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left
                    container.scrollBy({
                        left: amount,
                        behavior: 'smooth'
                    });
                } else {
                    // Swipe right
                    container.scrollBy({
                        left: -amount,
                        behavior: 'smooth'
                    });
                }
            }
            updateLaporanScrollButtons(container, leftBtn, rightBtn);
        }
        
        // Update scroll buttons visibility
        function updateLaporanScrollButtons(container, leftBtn, rightBtn) {
            const scrollLeft = container.scrollLeft;
            const maxScrollLeft = container.scrollWidth - container.clientWidth;
            
            // Fade out buttons when at edges
            if (scrollLeft <= 10) {
                leftBtn.style.opacity = '0.5';
                leftBtn.disabled = true;
                leftBtn.classList.remove('pulse-animation');
            } else {
                leftBtn.style.opacity = '1';
                leftBtn.disabled = false;
                leftBtn.classList.add('pulse-animation');
            }
            
            if (scrollLeft >= maxScrollLeft - 10) {
                rightBtn.style.opacity = '0.5';
                rightBtn.disabled = true;
                rightBtn.classList.remove('pulse-animation');
            } else {
                rightBtn.style.opacity = '1';
                rightBtn.disabled = false;
                rightBtn.classList.add('pulse-animation');
            }
        }
        
        // Initialize scroll buttons
        updateLaporanScrollButtons(laporanTableContainer, scrollLeftBtn, scrollRightBtn);
        
        // Listen for scroll events
        laporanTableContainer.addEventListener('scroll', function() {
            updateLaporanScrollButtons(laporanTableContainer, scrollLeftBtn, scrollRightBtn);
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            setTimeout(function() {
                updateLaporanScrollButtons(laporanTableContainer, scrollLeftBtn, scrollRightBtn);
            }, 100);
        });
        
        // Keyboard navigation for laporan table
        document.addEventListener('keydown', function(e) {
            // Only activate if we're on laporan page
            if (window.location.href.indexOf('laporan.php') > -1) {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                
                if (e.key === 'ArrowLeft') {
                    scrollLeftBtn.click();
                    e.preventDefault();
                } else if (e.key === 'ArrowRight') {
                    scrollRightBtn.click();
                    e.preventDefault();
                }
            }
        });
    }
}

// Fungsi untuk animasi kartu statistik
function animateStatCards() {
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });
}

// Fungsi untuk auto-hide sidebar di mobile
function setupSidebarAutoHide() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const mainContent = document.querySelector('.main-content');
    
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 768) {
            if (sidebar && sidebarToggle && mainContent) {
                if (!sidebar.contains(e.target) && 
                    !sidebarToggle.contains(e.target) && 
                    sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                    mainContent.style.marginLeft = '0';
                }
            }
        }
    });
}

// Fungsi untuk animasi baris tabel
function animateTableRows() {
    const tableRows = document.querySelectorAll('#peminjamanTable tbody tr, #laporanTable tbody tr');
    tableRows.forEach((row, index) => {
        row.style.animationDelay = `${index * 0.05}s`;
        row.classList.add('fade-in');
    });
}

// Fungsi untuk menambahkan style kustom
function addCustomStyles() {
    const style = document.createElement('style');
    style.textContent = `
        /* Animasi dasar */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
            opacity: 0;
            animation-fill-mode: forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Tombol scroll */
        .scroll-btn {
            transition: all 0.3s ease;
            margin: 0 2px;
            position: relative;
            z-index: 100;
        }
        
        .scroll-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .scroll-btn:disabled {
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
        
        .pulse-animation {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(52, 152, 219, 0); }
            100% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0); }
        }
        
        /* Container tabel */
        #peminjamanTableContainer, #laporanTableContainer {
            overflow-x: auto;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 5px;
            position: relative;
        }
        
        /* Indikator scroll */
        .scroll-indicator {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 50;
        }
        
        #peminjamanTableContainer:hover .scroll-indicator,
        #laporanTableContainer:hover .scroll-indicator {
            opacity: 1;
        }
        
        /* Tabel */
        #peminjamanTable, #laporanTable {
            min-width: 800px;
        }
        
        /* Kontrol tabel */
        .table-controls {
            display: flex;
            gap: 5px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        
        /* Responsif untuk mobile */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                position: fixed;
                height: 100vh;
                z-index: 1000;
            }
            
            .sidebar.show {
                transform: translateX(0);
                box-shadow: 3px 0 20px rgba(0, 0, 0, 0.3);
            }
            
            .main-content {
                transition: margin-left 0.3s ease;
                width: 100%;
            }
            
            #peminjamanTable, #laporanTable {
                min-width: 1000px;
            }
            
            .scroll-btn {
                padding: 10px 15px;
                font-size: 14px;
            }
            
            .table-scroll-controls {
                flex-direction: column;
                gap: 10px;
            }
        }
        
        /* Animasi untuk kartu statistik */
        .stat-card {
            transform-origin: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        /* Loading animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0,0,0,.1);
            border-radius: 50%;
            border-top-color: #3498db;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Badge animasi */
        .badge {
            transition: all 0.3s ease;
        }
        
        .badge:hover {
            transform: scale(1.1);
        }
        
        /* Tooltip kustom */
        .custom-tooltip {
            position: relative;
            display: inline-block;
        }
        
        .custom-tooltip .tooltip-text {
            visibility: hidden;
            width: 200px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -100px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .custom-tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
        
        /* Hover efek untuk baris tabel */
        .table-hover tbody tr {
            transition: background-color 0.2s;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,0.02);
        }
        
        /* Scrollbar kustom */
        #peminjamanTableContainer::-webkit-scrollbar,
        #laporanTableContainer::-webkit-scrollbar {
            height: 8px;
        }
        
        #peminjamanTableContainer::-webkit-scrollbar-track,
        #laporanTableContainer::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        #peminjamanTableContainer::-webkit-scrollbar-thumb,
        #laporanTableContainer::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        #peminjamanTableContainer::-webkit-scrollbar-thumb:hover,
        #laporanTableContainer::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    `;
    document.head.appendChild(style);
}

// Fungsi helper untuk format tanggal
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
}

// Fungsi helper untuk format uang
function formatCurrency(amount) {
    return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Fungsi untuk view detail peminjaman
function setupViewDetailButtons() {
    const viewDetailButtons = document.querySelectorAll('.view-detail');
    viewDetailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const peminjamanId = this.getAttribute('data-id');
            const peminjamanKode = this.getAttribute('data-kode');
            
            // Tampilkan modal atau redirect ke halaman detail
            showDetailModal(peminjamanId, peminjamanKode);
        });
    });
}

// Fungsi untuk menampilkan modal detail
function showDetailModal(id, kode) {
    // Buat modal dinamis
    const modalHTML = `
        <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailModalLabel">Detail Peminjaman: ${kode}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="detailContent">
                            <div class="text-center">
                                <div class="loading"></div>
                                <p>Memuat data...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" onclick="printDetail(${id})">
                            <i class="fas fa-print me-2"></i>Cetak Detail
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Tambahkan modal ke body jika belum ada
    if (!document.getElementById('detailModal')) {
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
    
    // Tampilkan modal
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();
    
    // Load data detail via AJAX
    loadDetailData(id);
}

// Fungsi untuk load data detail via AJAX
function loadDetailData(id) {
    // Simulasi AJAX request
    setTimeout(function() {
        const detailContent = document.getElementById('detailContent');
        if (detailContent) {
            detailContent.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informasi Peminjaman</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%">Kode Peminjaman</td>
                                <td><strong>PMN-${id}</strong></td>
                            </tr>
                            <tr>
                                <td>Tanggal Pinjam</td>
                                <td>${formatDate(new Date())}</td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td><span class="badge bg-warning">Dipinjam</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Informasi Mahasiswa</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%">NIM</td>
                                <td>12345678</td>
                            </tr>
                            <tr>
                                <td>Nama</td>
                                <td>Mahasiswa Contoh</td>
                            </tr>
                            <tr>
                                <td>Angkatan</td>
                                <td>2022</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Informasi Barang</h6>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Kode Barang</th>
                                    <th>Kondisi</th>
                                    <th>Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Laptop Dell</td>
                                    <td>LP-001</td>
                                    <td><span class="badge bg-success">Baik</span></td>
                                    <td>Lab Komputer 1</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Riwayat Status</h6>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <small>${formatDate(new Date())}</small>
                                    <p>Peminjaman diajukan</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <small>${formatDate(new Date())}</small>
                                    <p>Peminjaman disetujui oleh Admin</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
    }, 1000);
}

// Fungsi untuk mencetak detail
function printDetail(id) {
    window.open(`cetak_detail.php?id=${id}`, '_blank');
}

// Fungsi untuk export laporan ke Excel
function exportToExcel(tableId, filename = 'laporan') {
    if (typeof $ !== 'undefined' && $(`#${tableId}`).length) {
        // Jika menggunakan DataTables, export data yang difilter
        const table = $(`#${tableId}`).DataTable();
        const data = table.rows({ filter: 'applied' }).data();
        
        // Buat array untuk data Excel
        let excelData = [];
        
        // Tambahkan header
        const headers = [];
        $(`#${tableId} thead th`).each(function() {
            headers.push($(this).text().trim());
        });
        excelData.push(headers);
        
        // Tambahkan data
        data.each(function(value, index) {
            const row = [];
            for (let i = 0; i < value.length; i++) {
                // Hapus tag HTML dan ambil teks saja
                const cellText = $(value[i]).text().trim();
                row.push(cellText);
            }
            excelData.push(row);
        });
        
        // Convert to CSV
        let csvContent = "data:text/csv;charset=utf-8,";
        excelData.forEach(function(rowArray) {
            let row = rowArray.map(cell => `"${cell}"`).join(",");
            csvContent += row + "\r\n";
        });
        
        // Download file
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `${filename}_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } else {
        // Fallback jika tidak menggunakan DataTables
        alert('Fitur export Excel memerlukan DataTables. Data akan diexport dalam format CSV.');
    }
}

// Fungsi untuk menampilkan notifikasi
function showNotification(message, type = 'info') {
    const types = {
        'success': { icon: 'check-circle', color: 'success' },
        'error': { icon: 'exclamation-triangle', color: 'danger' },
        'warning': { icon: 'exclamation-circle', color: 'warning' },
        'info': { icon: 'info-circle', color: 'info' }
    };
    
    const notifType = types[type] || types.info;
    
    // Buat notifikasi element
    const notification = document.createElement('div');
    notification.className = `alert alert-${notifType.color} alert-dismissible fade show`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideInRight 0.3s ease;
    `;
    
    notification.innerHTML = `
        <i class="fas fa-${notifType.icon} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Tambahkan style untuk animasi
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    
    // Tambahkan ke body
    document.body.appendChild(notification);
    
    // Auto-hide setelah 5 detik
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Fungsi untuk validasi form
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Fungsi untuk reset form
function resetForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
    }
}

// Fungsi untuk konfirmasi aksi
function confirmAction(message = 'Apakah Anda yakin?') {
    return new Promise((resolve) => {
        // Buat modal konfirmasi
        const modalHTML = `
            <div class="modal fade" id="confirmModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Konfirmasi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" id="confirmCancel">Batal</button>
                            <button type="button" class="btn btn-primary" id="confirmOk">Ya, Lanjutkan</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Tambahkan modal jika belum ada
        if (!document.getElementById('confirmModal')) {
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }
        
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        modal.show();
        
        // Setup event listeners
        document.getElementById('confirmOk').onclick = function() {
            modal.hide();
            resolve(true);
        };
        
        document.getElementById('confirmCancel').onclick = function() {
            modal.hide();
            resolve(false);
        };
        
        // Auto-resolve false jika modal ditutup
        document.getElementById('confirmModal').addEventListener('hidden.bs.modal', function() {
            resolve(false);
        });
    });
}

// Event listeners global
document.addEventListener('click', function(e) {
    // Handle export buttons
    if (e.target.closest('[data-export]')) {
        const button = e.target.closest('[data-export]');
        const tableId = button.getAttribute('data-table') || 'laporanTable';
        const filename = button.getAttribute('data-filename') || 'laporan';
        exportToExcel(tableId, filename);
    }
    
    // Handle print buttons
    if (e.target.closest('[data-print]')) {
        const button = e.target.closest('[data-print]');
        const contentId = button.getAttribute('data-content');
        printContent(contentId);
    }
    
    // Handle confirm buttons
    if (e.target.closest('[data-confirm]')) {
        e.preventDefault();
        const button = e.target.closest('[data-confirm]');
        const message = button.getAttribute('data-message') || 'Apakah Anda yakin?';
        const form = button.closest('form');
        const href = button.getAttribute('href');
        
        confirmAction(message).then(confirmed => {
            if (confirmed) {
                if (form) {
                    form.submit();
                } else if (href) {
                    window.location.href = href;
                }
            }
        });
    }
});

// Fungsi untuk print content
function printContent(contentId) {
    const content = document.getElementById(contentId);
    if (!content) {
        window.print();
        return;
    }
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Print Document</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    @media print {
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                ${content.innerHTML}
                <div class="no-print" style="margin-top: 20px;">
                    <button onclick="window.print()">Print</button>
                    <button onclick="window.close()">Close</button>
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
}

// Ekspor fungsi untuk digunakan global
window.adminJS = {
    formatDate,
    formatCurrency,
    showNotification,
    validateForm,
    resetForm,
    confirmAction,
    exportToExcel,
    printContent
};

// Inisialisasi tambahan jika jQuery tersedia
if (typeof $ !== 'undefined') {
    $(document).ready(function() {
        // Setup tooltips untuk elemen yang dibuat dinamis
        $(document).on('mouseenter', '[data-bs-toggle="tooltip"]', function() {
            if (!$(this).data('bs.tooltip')) {
                new bootstrap.Tooltip(this);
            }
        });
        
        // Auto-focus pada modal pertama input
        $('.modal').on('shown.bs.modal', function() {
            $(this).find('input:visible:first').focus();
        });
        
        // Format input number dengan pemisah ribuan
        $('input[data-thousands]').on('keyup', function() {
            let value = $(this).val().replace(/[^\d]/g, '');
            if (value) {
                value = parseInt(value).toLocaleString('id-ID');
                $(this).val(value);
            }
        });
    });
}