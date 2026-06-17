    </main><!-- /.main-content (opened in each page) -->

    <!-- =====================================================
         FOOTER
         ===================================================== -->
    <footer class="rich-footer py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?= BASE_URL ?>/assets/img/Main Logo.png" alt="InfoKosMin Logo" style="height: 36px; object-fit: contain;" class="me-2">
                        <span class="fw-bold text-white fs-5">InfoKosMin</span>
                    </div>
                    <p class="small mb-0" style="line-height: 1.6;">Platform katalog kos pintar untuk mempermudah pencarian hunian terbaik di Sleman & Yogyakarta. Kami menyediakan data kos terverifikasi langsung.</p>
                </div>
                <div class="col-lg-2 col-md-6 mb-3 mb-lg-0">
                    <h5 class="text-white fs-6 fw-semibold mb-3">Navigasi</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php" class="text-decoration-none">Beranda</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/catalog.php" class="text-decoration-none">Cari Kos</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/pages/login.php" class="text-decoration-none">Login Admin</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                    <h5 class="text-white fs-6 fw-semibold mb-3">Kontak</h5>
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2 text-primary"></i>Sleman, DIY</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2 text-primary"></i>+62 812-3456-789</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i>info@infokosmin.ac.id</li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-white fs-6 fw-semibold mb-3">Motto Kami</h5>
                    <p class="small mb-0" style="line-height: 1.6;">"Pilihan Hunian Tepat Untuk Masa Depan Hebat." Partner terpercaya mahasiswa dan pekerja mencari kos idaman.</p>
                </div>
            </div>
            <div class="rich-footer-bottom text-center small">
                &copy; <?= date('Y') ?> InfoKosMin.
            </div>
        </div>
    </footer>

    <!-- =====================================================
         BOOTSTRAP DELETE CONFIRMATION MODAL
         Covers: B3 (Modal component), J2 (confirm before delete)
         Populated dynamically by confirm-delete.js
         ===================================================== -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">Yakin ingin menghapus:</p>
                    <p class="fw-bold fs-5" id="modalDeleteName">—</p>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        Tindakan ini <strong>tidak dapat dibatalkan</strong> dan akan menghapus semua data terkait.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                    <form id="modalDeleteForm" method="POST" style="display:inline;">
                        <input type="hidden" name="confirmed" value="1">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash3 me-1"></i>Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /.deleteModal -->

    <!-- Bootstrap 5.3 JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Application JS (loaded after Bootstrap) -->
    <script src="<?= BASE_URL ?>/assets/js/confirm-delete.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/search.js"></script>

</body>
</html>
