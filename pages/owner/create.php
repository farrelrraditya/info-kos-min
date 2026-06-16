<?php
/**
 * InfoKosMin - Create Owner
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ownerName   = sanitizeInput($_POST['owner_name'] ?? '');
    $phoneNumber = sanitizeInput($_POST['phone_number'] ?? '');
    $email       = sanitizeInput($_POST['email'] ?? '');

    // Server-side validation
    if ($ownerName === '') {
        $errors['owner_name'] = 'Nama pemilik tidak boleh kosong.';
    } elseif (strlen($ownerName) < 2) {
        $errors['owner_name'] = 'Nama pemilik minimal 2 karakter.';
    }

    $phoneVal = preg_replace('/[\s\-]/', '', $phoneNumber);
    if ($phoneVal === '') {
        $errors['phone_number'] = 'Nomor telepon tidak boleh kosong.';
    } elseif (!preg_match('/^[0-9+]{10,15}$/', $phoneVal)) {
        $errors['phone_number'] = 'Nomor telepon tidak valid (10-15 digit angka).';
    }

    if ($email !== '') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO owners (owner_name, phone_number, email) VALUES (?, ?, ?)");
            $stmt->execute([$ownerName, $phoneNumber, $email ?: null]);

            setFlash('Pemilik "' . $ownerName . '" berhasil ditambahkan!', 'success');
            redirect(BASE_URL . '/pages/owner/index.php');
        } catch (PDOException $e) {
            $errors['general'] = 'Gagal menyimpan ke database: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Tambah Pemilik Baru';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header bg-white border-bottom py-3 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 col-12">
                <h2 class="mb-0 text-dark"><i class="bi bi-person-plus me-2 text-primary"></i>Tambah Pemilik</h2>
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
                    <form id="ownerForm" method="POST" action="create.php" novalidate>
                        
                        <!-- Owner Name -->
                        <div class="mb-3">
                            <label for="owner_name" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control <?= isset($errors['owner_name']) ? 'is-invalid' : '' ?>"
                                id="owner_name"
                                name="owner_name"
                                value="<?= h($_POST['owner_name'] ?? '') ?>"
                                required
                            >
                            <div id="error-owner_name" class="text-danger small mt-1 <?= isset($errors['owner_name']) ? '' : 'd-none' ?>">
                                <?= h($errors['owner_name'] ?? '') ?>
                            </div>
                        </div>

                        <!-- Phone Number -->
                        <div class="mb-3">
                            <label for="phone_number" class="form-label fw-semibold">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control <?= isset($errors['phone_number']) ? 'is-invalid' : '' ?>"
                                id="phone_number"
                                name="phone_number"
                                value="<?= h($_POST['phone_number'] ?? '') ?>"
                                placeholder="Contoh: 081234567890"
                                required
                            >
                            <div id="error-phone_number" class="text-danger small mt-1 <?= isset($errors['phone_number']) ? '' : 'd-none' ?>">
                                <?= h($errors['phone_number'] ?? '') ?>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">Alamat Email (Opsional)</label>
                            <input
                                type="email"
                                class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                id="email"
                                name="email"
                                value="<?= h($_POST['email'] ?? '') ?>"
                                placeholder="Contoh: pemilik@email.com"
                            >
                            <div id="error-email" class="text-danger small mt-1 <?= isset($errors['email']) ? '' : 'd-none' ?>">
                                <?= h($errors['email'] ?? '') ?>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i>Simpan Pemilik
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
