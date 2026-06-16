<?php
/**
 * InfoKosMin - Survey Logs (Trigger 1 Viewer)
 * 
 * Shows survey logs populated automatically by trg_after_kost_insert and allows editing.
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();

$idKost = (int)($_GET['id_kost'] ?? 0);

if ($idKost > 0) {
    // Filter by specific kost
    $kostStmt = $pdo->prepare("SELECT kost_name FROM boarding_houses WHERE id_kost = ?");
    $kostStmt->execute([$idKost]);
    $kostName = $kostStmt->fetchColumn();

    if (!$kostName) {
        setFlash('ID Kos tidak valid.', 'danger');
        redirect(BASE_URL . '/pages/kost/index.php');
    }

    $sql = "
        SELECT sl.*, bh.kost_name
        FROM survey_logs sl
        INNER JOIN boarding_houses bh ON sl.id_kost = bh.id_kost
        WHERE sl.id_kost = ?
        ORDER BY sl.survey_date DESC, sl.created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idKost]);
} else {
    // Show all survey logs
    $kostName = null;
    $sql = "
        SELECT sl.*, bh.kost_name
        FROM survey_logs sl
        INNER JOIN boarding_houses bh ON sl.id_kost = bh.id_kost
        ORDER BY sl.survey_date DESC, sl.created_at DESC
    ";
    $stmt = $pdo->query($sql);
}

$logs = $stmt->fetchAll();

$pageTitle = 'Log Survei Kos';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header bg-white border-bottom py-3 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 col-12">
                <h2 class="mb-0 text-dark"><i class="bi bi-journal-check me-2 text-primary"></i>Log Survei</h2>
                <?php if ($kostName): ?>
                    <p class="text-muted mb-0">Kos: <strong><?= h($kostName) ?></strong></p>
                <?php endif; ?>
            </div>
            <div class="col-md-6 col-12 text-md-end mt-2 mt-md-0">
                <a href="<?= BASE_URL ?>/pages/kost/index.php" class="btn btn-outline-secondary">
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
                <i class="bi bi-clipboard-data text-primary me-2"></i>Log Survei Terdaftar (Trigger 1)
            </h5>
            <span class="badge bg-secondary rounded-pill px-3 py-2">
                Total Log: <?= count($logs) ?>
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 80px;" class="text-center">No</th>
                            <th>Nama Kos</th>
                            <th style="width: 150px;">Tanggal Survei</th>
                            <th>Catatan Surveyor</th>
                            <th style="width: 200px;">Diinput Pada</th>
                            <th class="text-end" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-info-circle me-1"></i>Belum ada log survei terdaftar.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $no = 1;
                            foreach ($logs as $row): 
                            ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="fw-bold text-dark"><?= h($row['kost_name']) ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            <?= date('d-m-Y', strtotime($row['survey_date'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="mb-0 small text-muted text-wrap" style="max-width: 400px;">
                                            <?= $row['surveyor_note'] ? h($row['surveyor_note']) : '<em class="text-muted small">Tidak ada catatan</em>' ?>
                                        </p>
                                    </td>
                                    <td class="text-muted small">
                                        <?= date('d-m-Y H:i', strtotime($row['created_at'])) ?> WIB
                                    </td>
                                    <td class="text-end">
                                        <a href="edit.php?id=<?= $row['id_log'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                        </a>
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
