    </main><!-- /.main-content (opened in each page) -->

    <!-- =====================================================
         FOOTER
         ===================================================== -->
    <footer class="bg-dark text-white-50 py-4 mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <strong class="text-white">
                        <i class="bi bi-house-heart-fill me-1"></i>InfoKosMin
                    </strong>
                    <p class="mb-0 small">Platform Katalog Kos Pintar &mdash; Temukan kos impianmu.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>&copy; <?= date('Y') ?> InfoKosMin. Proyek Akademik.</small>
                </div>
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
