<?php
/**
 * InfoKosMin - Edit Survey Log
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();

$idLog = (int)($_GET['id'] ?? 0);
if ($idLog <= 0) {
    setFlash('ID Log Survei tidak valid.', 'danger');
    redirect(BASE_URL . '/pages/survey/index.php');
}

// Fetch existing survey log data
$stmt = $pdo->prepare("
    SELECT sl.*, bh.kost_name 
    FROM survey_logs sl 
    INNER JOIN boarding_houses bh ON sl.id_kost = bh.id_kost 
    WHERE sl.id_log = ?
");
$stmt->execute([$idLog]);
$log = $stmt->fetch();

if (!$log) {
    setFlash('Data log survei tidak ditemukan.', 'danger');
    redirect(BASE_URL . '/pages/survey/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $surveyDate   = sanitizeInput($_POST['survey_date'] ?? '');
    $surveyorNote = sanitizeInput($_POST['surveyor_note'] ?? '');

    // Server-side validation (matches validation.js rule)
    if ($surveyDate === '') {
        $errors['survey_date'] = 'Tanggal survei tidak boleh kosong.';
    }

    if (empty($errors)) {
        try {
            $stmtUpdate = $pdo->prepare("UPDATE survey_logs SET survey_date = ?, surveyor_note = ? WHERE id_log = ?");
            $stmtUpdate->execute([$surveyDate, $surveyorNote ?: null, $idLog]);

            setFlash('Log survei berhasil diperbarui!', 'success');
            // Redirect back to logs list (preserve kost filter if possible, otherwise list all)
            redirect(BASE_URL . '/pages/survey/index.php?id_kost=' . $log['id_kost']);
        } catch (PDOException $e) {
            $errors['general'] = 'Gagal menyimpan data ke database: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Edit Log Survei';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header bg-white border-bottom py-3 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 col-12">
                <h2 class="mb-0 text-dark"><i class="bi bi-pencil me-2 text-primary"></i>Edit Log Survei</h2>
                <p class="text-muted mb-0">Kos: <strong><?= h($log['kost_name']) ?></strong></p>
            </div>
            <div class="col-md-6 col-12 text-md-end mt-2 mt-md-0">
                <a href="index.php?id_kost=<?= $log['id_kost'] ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Batal
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= h($errors['general']) ?>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form id="surveyForm" method="POST" action="edit.php?id=<?= $idLog ?>" novalidate>
                        
                        <!-- Survey Date -->
                        <div class="mb-3">
                            <label for="survey_date" class="form-label fw-semibold">Tanggal Survei <span class="text-danger">*</span></label>
                            <input
                                type="date"
                                class="form-control <?= isset($errors['survey_date']) ? 'is-invalid' : '' ?>"
                                id="survey_date"
                                name="survey_date"
                                value="<?= h($_POST['survey_date'] ?? $log['survey_date']) ?>"
                                required
                            >
                            <div id="error-survey_date" class="text-danger small mt-1 <?= isset($errors['survey_date']) ? '' : 'd-none' ?>">
                                <?= h($errors['survey_date'] ?? '') ?>
                            </div>
                        </div>

                        <!-- Surveyor Note -->
                        <div class="mb-4">
                            <label for="surveyor_note" class="form-label fw-semibold">Catatan Kondisi / Hasil Survei</label>
                            <textarea
                                class="form-control"
                                id="surveyor_note"
                                name="surveyor_note"
                                rows="5"
                                placeholder="Masukkan catatan kondisi fasilitas, kebersihan, kepatuhan jam malam, dll..."
                            ><?= h($_POST['surveyor_note'] ?? $log['surveyor_note']) ?></textarea>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i>Simpan Perubahan
                            </button>
                            <a href="index.php?id_kost=<?= $log['id_kost'] ?>" class="btn btn-light border px-4">Batal</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
