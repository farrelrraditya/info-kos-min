<?php
/**
 * InfoKosMin - Create Boarding House
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();

// Fetch owners for dropdown
$owners = $pdo->query("SELECT id_owner, owner_name FROM owners ORDER BY owner_name ASC")->fetchAll();

// Fetch facilities for checkboxes
$facilities = $pdo->query("SELECT id_facility, facility_name FROM facilities ORDER BY facility_name ASC")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and read fields
    $kostName           = sanitizeInput($_POST['kost_name'] ?? '');
    $idOwner            = (int)($_POST['id_owner'] ?? 0);
    $address            = sanitizeInput($_POST['address'] ?? '');
    $district           = sanitizeInput($_POST['district'] ?? '');
    $monthlyPrice       = (float)($_POST['monthly_price'] ?? 0);
    $roomSize           = sanitizeInput($_POST['room_size'] ?? '');
    $genderType         = sanitizeInput($_POST['gender_type'] ?? '');
    $curfew             = sanitizeInput($_POST['curfew'] ?? '');
    $isFurnished        = isset($_POST['is_furnished']) ? 1 : 0;
    $electricityType    = sanitizeInput($_POST['electricity_type'] ?? '');
    $description        = sanitizeInput($_POST['description'] ?? '');
    $availabilityStatus = sanitizeInput($_POST['availability_status'] ?? 'available');
    
    $selectedFacilities = $_POST['facilities'] ?? [];

    // Server-side validation (matches validation.js rules)
    if ($kostName === '') {
        $errors['kost_name'] = 'Nama kos tidak boleh kosong.';
    } elseif (strlen($kostName) < 3) {
        $errors['kost_name'] = 'Nama kos minimal 3 karakter.';
    }

    if ($monthlyPrice <= 0) {
        $errors['monthly_price'] = 'Harga sewa harus lebih dari 0.';
    }

    if ($address === '') {
        $errors['address'] = 'Alamat tidak boleh kosong.';
    } elseif (strlen($address) < 10) {
        $errors['address'] = 'Alamat terlalu pendek (minimal 10 karakter).';
    }

    if ($district === '') {
        $errors['district'] = 'Kecamatan tidak boleh kosong.';
    }

    if ($idOwner <= 0) {
        $errors['id_owner'] = 'Pilih pemilik kos.';
    }

    if (!in_array($genderType, ['male', 'female', 'mixed'])) {
        $errors['gender_type'] = 'Pilih tipe penghuni.';
    }

    if (!in_array($electricityType, ['token', 'fixed'])) {
        $errors['electricity_type'] = 'Pilih tipe listrik.';
    }

    if (!in_array($availabilityStatus, ['available', 'full', 'unavailable'])) {
        $errors['availability_status'] = 'Pilih status ketersediaan.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $sql = "
                INSERT INTO boarding_houses (
                    id_owner, kost_name, address, district, monthly_price, room_size,
                    gender_type, curfew, is_furnished, electricity_type, description, availability_status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $idOwner, $kostName, $address, $district, $monthlyPrice, $roomSize ?: null,
                $genderType, $curfew ?: null, $isFurnished, $electricityType, $description ?: null, $availabilityStatus
            ]);

            $idKost = $pdo->lastInsertId();

            // Insert facilities
            if (!empty($selectedFacilities)) {
                $facSql = "INSERT INTO kost_facilities (id_kost, id_facility) VALUES (?, ?)";
                $facStmt = $pdo->prepare($facSql);
                foreach ($selectedFacilities as $idFac) {
                    $facStmt->execute([$idKost, (int)$idFac]);
                }
            }

            $pdo->commit();
            setFlash('Kos "' . $kostName . '" berhasil ditambahkan! Log survei awal telah dibuat secara otomatis.', 'success');
            redirect(BASE_URL . '/pages/kost/index.php');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors['general'] = 'Gagal menyimpan data ke database: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Tambah Kos Baru';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header bg-white border-bottom py-3 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 col-12">
                <h2 class="mb-0 text-dark"><i class="bi bi-plus-lg me-2 text-primary"></i>Tambah Kos</h2>
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

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form id="kostForm" method="POST" action="create.php" novalidate>
                        
                        <!-- Kost Name -->
                        <div class="mb-3">
                            <label for="kost_name" class="form-label fw-semibold">Nama Kos <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control <?= isset($errors['kost_name']) ? 'is-invalid' : '' ?>"
                                id="kost_name"
                                name="kost_name"
                                value="<?= h($_POST['kost_name'] ?? '') ?>"
                                required
                            >
                            <div id="error-kost_name" class="text-danger small mt-1 <?= isset($errors['kost_name']) ? '' : 'd-none' ?>">
                                <?= h($errors['kost_name'] ?? '') ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <!-- Owner Selection -->
                            <div class="col-md-6">
                                <label for="id_owner" class="form-label fw-semibold">Pemilik Kos <span class="text-danger">*</span></label>
                                <select
                                    class="form-select <?= isset($errors['id_owner']) ? 'is-invalid' : '' ?>"
                                    id="id_owner"
                                    name="id_owner"
                                    required
                                >
                                    <option value="">-- Pilih Pemilik --</option>
                                    <?php foreach ($owners as $owner): ?>
                                        <option value="<?= $owner['id_owner'] ?>" <?= (isset($_POST['id_owner']) && $_POST['id_owner'] == $owner['id_owner']) ? 'selected' : '' ?>>
                                            <?= h($owner['owner_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="error-id_owner" class="text-danger small mt-1 <?= isset($errors['id_owner']) ? '' : 'd-none' ?>">
                                    <?= h($errors['id_owner'] ?? '') ?>
                                </div>
                            </div>

                            <!-- Monthly Price -->
                            <div class="col-md-6">
                                <label for="monthly_price" class="form-label fw-semibold">Harga Sewa Bulanan (Rp) <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    class="form-control <?= isset($errors['monthly_price']) ? 'is-invalid' : '' ?>"
                                    id="monthly_price"
                                    name="monthly_price"
                                    value="<?= h($_POST['monthly_price'] ?? '') ?>"
                                    min="1"
                                    required
                                >
                                <div id="error-monthly_price" class="text-danger small mt-1 <?= isset($errors['monthly_price']) ? '' : 'd-none' ?>">
                                    <?= h($errors['monthly_price'] ?? '') ?>
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea
                                class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>"
                                id="address"
                                name="address"
                                rows="3"
                                required
                            ><?= h($_POST['address'] ?? '') ?></textarea>
                            <div id="error-address" class="text-danger small mt-1 <?= isset($errors['address']) ? '' : 'd-none' ?>">
                                <?= h($errors['address'] ?? '') ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <!-- District -->
                            <div class="col-md-6">
                                <label for="district" class="form-label fw-semibold">Kecamatan <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control <?= isset($errors['district']) ? 'is-invalid' : '' ?>"
                                    id="district"
                                    name="district"
                                    value="<?= h($_POST['district'] ?? '') ?>"
                                    required
                                >
                                <div id="error-district" class="text-danger small mt-1 <?= isset($errors['district']) ? '' : 'd-none' ?>">
                                    <?= h($errors['district'] ?? '') ?>
                                </div>
                            </div>

                            <!-- Room Size -->
                            <div class="col-md-6">
                                <label for="room_size" class="form-label fw-semibold">Ukuran Kamar (contoh: 3x4)</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="room_size"
                                    name="room_size"
                                    value="<?= h($_POST['room_size'] ?? '') ?>"
                                    placeholder="e.g. 3x4"
                                >
                            </div>
                        </div>

                        <div class="row mb-3">
                            <!-- Gender Type -->
                            <div class="col-md-6">
                                <label for="gender_type" class="form-label fw-semibold">Tipe Kos / Penghuni <span class="text-danger">*</span></label>
                                <select
                                    class="form-select <?= isset($errors['gender_type']) ? 'is-invalid' : '' ?>"
                                    id="gender_type"
                                    name="gender_type"
                                    required
                                >
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="male" <?= (isset($_POST['gender_type']) && $_POST['gender_type'] === 'male') ? 'selected' : '' ?>>Putra</option>
                                    <option value="female" <?= (isset($_POST['gender_type']) && $_POST['gender_type'] === 'female') ? 'selected' : '' ?>>Putri</option>
                                    <option value="mixed" <?= (isset($_POST['gender_type']) && $_POST['gender_type'] === 'mixed') ? 'selected' : '' ?>>Campur</option>
                                </select>
                                <div id="error-gender_type" class="text-danger small mt-1 <?= isset($errors['gender_type']) ? '' : 'd-none' ?>">
                                    <?= h($errors['gender_type'] ?? '') ?>
                                </div>
                            </div>

                            <!-- Electricity Type -->
                            <div class="col-md-6">
                                <label for="electricity_type" class="form-label fw-semibold">Tipe Listrik <span class="text-danger">*</span></label>
                                <select
                                    class="form-select <?= isset($errors['electricity_type']) ? 'is-invalid' : '' ?>"
                                    id="electricity_type"
                                    name="electricity_type"
                                    required
                                >
                                    <option value="">-- Pilih Tipe Listrik --</option>
                                    <option value="token" <?= (isset($_POST['electricity_type']) && $_POST['electricity_type'] === 'token') ? 'selected' : '' ?>>Token (Pulsa)</option>
                                    <option value="fixed" <?= (isset($_POST['electricity_type']) && $_POST['electricity_type'] === 'fixed') ? 'selected' : '' ?>>Fixed (Termasuk Sewa)</option>
                                </select>
                                <div id="error-electricity_type" class="text-danger small mt-1 <?= isset($errors['electricity_type']) ? '' : 'd-none' ?>">
                                    <?= h($errors['electricity_type'] ?? '') ?>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <!-- Curfew -->
                            <div class="col-md-6">
                                <label for="curfew" class="form-label fw-semibold">Jam Malam</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="curfew"
                                    name="curfew"
                                    value="<?= h($_POST['curfew'] ?? '') ?>"
                                    placeholder="e.g. 22:00 WIB atau Bebas"
                                >
                            </div>

                            <!-- Availability Status -->
                            <div class="col-md-6">
                                <label for="availability_status" class="form-label fw-semibold">Status Ketersediaan <span class="text-danger">*</span></label>
                                <select
                                    class="form-select <?= isset($errors['availability_status']) ? 'is-invalid' : '' ?>"
                                    id="availability_status"
                                    name="availability_status"
                                    required
                                >
                                    <option value="available" <?= (isset($_POST['availability_status']) && $_POST['availability_status'] === 'available') ? 'selected' : '' ?>>Tersedia</option>
                                    <option value="full" <?= (isset($_POST['availability_status']) && $_POST['availability_status'] === 'full') ? 'selected' : '' ?>>Penuh</option>
                                    <option value="unavailable" <?= (isset($_POST['availability_status']) && $_POST['availability_status'] === 'unavailable') ? 'selected' : '' ?>>Tidak Aktif</option>
                                </select>
                                <div id="error-availability_status" class="text-danger small mt-1 <?= isset($errors['availability_status']) ? '' : 'd-none' ?>">
                                    <?= h($errors['availability_status'] ?? '') ?>
                                </div>
                            </div>
                        </div>

                        <!-- Furnished Checkbox -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="is_furnished"
                                    name="is_furnished"
                                    value="1"
                                    <?= (isset($_POST['is_furnished']) && $_POST['is_furnished'] == 1) ? 'checked' : '' ?>
                                >
                                <label class="form-check-label fw-semibold" for="is_furnished">
                                    Sudah Termasuk Furnitur (Kasur, Lemari, dll.)
                                </label>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">Deskripsi Tambahan</label>
                            <textarea
                                class="form-control"
                                id="description"
                                name="description"
                                rows="4"
                                placeholder="Tuliskan info pendukung, misal dekat dengan halte bus, minimarket, dll..."
                            ><?= h($_POST['description'] ?? '') ?></textarea>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i>Simpan Kos
                            </button>
                            <a href="index.php" class="btn btn-light border px-4">Batal</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Facility List Sidebar -->
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-grid me-2 text-primary"></i>Pilih Fasilitas</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Pilih fasilitas yang tersedia untuk kos ini. Fasilitas dapat diubah kapan saja.</p>
                    <hr>
                    <div class="row g-2">
                        <?php if (empty($facilities)): ?>
                            <div class="col-12 text-center text-muted">
                                Belum ada fasilitas master. Silakan <a href="<?= BASE_URL ?>/pages/facility/create.php">tambah fasilitas baru</a>.
                            </div>
                        <?php else: ?>
                            <?php foreach ($facilities as $f): ?>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="facilities[]"
                                            value="<?= $f['id_facility'] ?>"
                                            id="facility_<?= $f['id_facility'] ?>"
                                            form="kostForm"
                                            <?= (isset($_POST['facilities']) && in_array($f['id_facility'], $_POST['facilities'])) ? 'checked' : '' ?>
                                        >
                                        <label class="form-check-label text-dark" for="facility_<?= $f['id_facility'] ?>">
                                            <?= h($f['facility_name']) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
