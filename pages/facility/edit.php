<?php
/**
 * InfoKosMin - Edit Facility
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();

$idFacility = (int)($_GET['id'] ?? 0);
if ($idFacility <= 0) {
    setFlash('ID Fasilitas tidak valid.', 'danger');
    redirect(BASE_URL . '/pages/facility/index.php');
}

// Fetch existing facility data
$stmt = $pdo->prepare("SELECT * FROM facilities WHERE id_facility = ?");
$stmt->execute([$idFacility]);
$facility = $stmt->fetch();

if (!$facility) {
    setFlash('Data fasilitas tidak ditemukan.', 'danger');
    redirect(BASE_URL . '/pages/facility/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $facilityName = sanitizeInput($_POST['facility_name'] ?? '');

    // Server-side validation
    if ($facilityName === '') {
        $errors['facility_name'] = 'Nama fasilitas tidak boleh kosong.';
    } elseif (strlen($facilityName) < 2) {
        $errors['facility_name'] = 'Nama fasilitas minimal 2 karakter.';
    }

    if (empty($errors)) {
        try {
            // Check for uniqueness under other IDs
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM facilities WHERE facility_name = ? AND id_facility <> ?");
            $checkStmt->execute([$facilityName, $idFacility]);
            if ((int)$checkStmt->fetchColumn() > 0) {
                $errors['facility_name'] = 'Nama fasilitas ini sudah terdaftar.';
            } else {
                $stmtUpdate = $pdo->prepare("UPDATE facilities SET facility_name = ? WHERE id_facility = ?");
                $stmtUpdate->execute([$facilityName, $idFacility]);

                setFlash('Fasilitas berhasil diperbarui menjadi "' . $facilityName . '"!', 'success');
                redirect(BASE_URL . '/pages/facility/index.php');
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Gagal menyimpan perubahan ke database: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Edit Data Fasilitas';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header bg-white border-bottom py-3 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 col-12">
                <h2 class="mb-0 text-dark"><i class="bi bi-pencil me-2 text-primary"></i>Edit Fasilitas</h2>
            </div>
            <div class="col-md-6 col-12 text-md-end mt-2 mt-md-0">
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
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
                    <form id="facilityForm" method="POST" action="edit.php?id=<?= $idFacility ?>" novalidate>
                        
                        <!-- Facility Name -->
                        <div class="mb-4">
                            <label for="facility_name" class="form-label fw-semibold">Nama Fasilitas <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control <?= isset($errors['facility_name']) ? 'is-invalid' : '' ?>"
                                id="facility_name"
                                name="facility_name"
                                value="<?= h($_POST['facility_name'] ?? $facility['facility_name']) ?>"
                                required
                            >
                            <div id="error-facility_name" class="text-danger small mt-1 <?= isset($errors['facility_name']) ? '' : 'd-none' ?>">
                                <?= h($errors['facility_name'] ?? '') ?>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i>Simpan Perubahan
                            </button>
                            <a href="index.php" class="btn btn-light border px-4">Batal</a>
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
