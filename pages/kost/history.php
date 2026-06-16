<?php
/**
 * InfoKosMin - Boarding House Status History Log (Trigger 2 Viewer)
 * 
 * Shows audit log of availability status changes populated by trg_after_kost_status_update.
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();

$idKost = (int)($_GET['id'] ?? 0);

// Fetch history records
if ($idKost > 0) {
    // Fetch specifically for one kost
    $kostStmt = $pdo->prepare("SELECT kost_name FROM boarding_houses WHERE id_kost = ?");
    $kostStmt->execute([$idKost]);
    $kostName = $kostStmt->fetchColumn();

    if (!$kostName) {
        setFlash('ID Kos tidak valid.', 'danger');
        redirect('index.php');
    }

    $sql = "
        SELECT sh.*, bh.kost_name
        FROM status_history sh
        INNER JOIN boarding_houses bh ON sh.id_kost = bh.id_kost
        WHERE sh.id_kost = ?
        ORDER BY sh.changed_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idKost]);
} else {
    // Fetch all logs
    $kostName = null;
    $sql = "
        SELECT sh.*, bh.kost_name
        FROM status_history sh
        INNER JOIN boarding_houses bh ON sh.id_kost = bh.id_kost
        ORDER BY sh.changed_at DESC
    ";
    $stmt = $pdo->query($sql);
}

$historyLogs = $stmt->fetchAll();

$pageTitle = 'Riwayat Status Ketersediaan';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header bg-white border-bottom py-3 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 col-12">
                <h2 class="mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Status</h2>
                <?php if ($kostName): ?>
                    <p class="text-muted mb-0">Kos: <strong><?= h($kostName) ?></strong></p>
                <?php endif; ?>
            </div>
            <div class="col-md-6 col-12 text-md-end mt-2 mt-md-0">
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke Kelola Kos
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <?php showFlash(); ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="bi bi-shield-check text-primary me-2"></i>Log Perubahan Status (Trigger 2)
            </h5>
            <span class="badge bg-secondary rounded-pill px-3 py-2">
                Total Log: <?= count($historyLogs) ?>
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 80px;" class="text-center">No</th>
                            <th>Nama Kos</th>
                            <th class="text-center" style="width: 200px;">Status Lama</th>
                            <th class="text-center" style="width: 200px;">Status Baru</th>
                            <th style="width: 250px;">Waktu Perubahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historyLogs)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-info-circle me-1"></i>Belum ada riwayat perubahan status terdaftar.
                                    <br><small class="text-muted">Untuk memicu log, silakan edit status ketersediaan pada data kos.</small>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $no = 1;
                            foreach ($historyLogs as $log): 
                            ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="fw-bold text-dark">
                                        <a href="detail.php?id=<?= $log['id_kost'] ?>" class="text-decoration-none text-dark">
                                            <?= h($log['kost_name']) ?>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= statusBadgeClass($log['old_status']) ?> px-3 py-2">
                                            <?= statusLabel($log['old_status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= statusBadgeClass($log['new_status']) ?> px-3 py-2">
                                            <?= statusLabel($log['new_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            <?= date('d-m-Y H:i:s', strtotime($log['changed_at'])) ?> WIB
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
