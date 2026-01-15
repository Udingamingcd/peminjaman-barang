<?php
// includes/footer.php
?>
        </div>
    </div>
    
    <!-- Settings Modal (untuk superadmin) -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'superadmin'): ?>
    <div class="modal fade" id="settingsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pengaturan Sistem</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-database me-2"></i> Backup Database
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-history me-2"></i> Log Aktivitas
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-shield me-2"></i> Keamanan Sistem
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    
    <!-- Custom Script -->
    <script>
    $(document).ready(function() {
        // Initialize DataTables
        $('.datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
            },
            responsive: true
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Confirm before delete
        $('.btn-delete').on('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                e.preventDefault();
            }
        });
        
        // Format numbers
        function formatNumber(num) {
            return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
        }
        
        // Update stats in dashboard
        updateDashboardStats();
    });
    
    function updateDashboardStats() {
        $.ajax({
            url: 'ajax/get_stats.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if(data.success) {
                    $('#total-barang').text(data.total_barang);
                    $('#total-mahasiswa').text(data.total_mahasiswa);
                    $('#total-peminjaman').text(data.total_peminjaman);
                    $('#total-admin').text(data.total_admin);
                }
            }
        });
    }
    </script>
</body>
</html>