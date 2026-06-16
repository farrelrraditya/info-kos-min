<?php
/**
 * InfoKosMin - Manage Photos (Admin Gallery)
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();

$idKost = (int)($_GET['id_kost'] ?? 0);
if ($idKost <= 0) {
    setFlash('ID Kos tidak valid.', 'danger');
    redirect(BASE_URL . '/pages/kost/index.php');
}

// Fetch kost info
$kostStmt = $pdo->prepare("SELECT kost_name FROM boarding_houses WHERE id_kost = ?");
$kostStmt->execute([$idKost]);
$kostName = $kostStmt->fetchColumn();

if (!$kostName) {
    setFlash('Data kos tidak ditemukan.', 'danger');
    redirect(BASE_URL . '/pages/kost/index.php');
}

// Fetch all photos for this kost
$photoStmt = $pdo->prepare("SELECT * FROM photos WHERE id_kost = ? ORDER BY uploaded_at DESC");
$photoStmt->execute([$idKost]);
$photos = $photoStmt->fetchAll();

// Group photos by category
$categories = [
    'bedroom'  => 'Kamar Tidur',
    'bathroom' => 'Kamar Mandi',
    'parking'  => 'Area Parkir',
    'kitchen'  => 'Dapur',
    'exterior' => 'Eksterior / Depan'
];

$groupedPhotos = [];
foreach (array_keys($categories) as $cat) {
    $groupedPhotos[$cat] = [];
}

foreach ($photos as $p) {
    $groupedPhotos[$p['photo_category']][] = $p;
}

$pageTitle = 'Kelola Foto - ' . $kostName;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header bg-white border-bottom py-3 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 col-12">
                <h2 class="mb-0 text-dark"><i class="bi bi-images me-2 text-primary"></i>Kelola Foto</h2>
                <p class="text-muted mb-0">Kos: <strong><?= h($kostName) ?></strong></p>
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

    <div class="row g-4">
        <!-- Left: Photo Upload Form -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 80px; z-index: 10;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-cloud-upload me-2 text-primary"></i>Unggah Foto Baru</h5>
                </div>
                <div class="card-body">
                    <form action="upload.php" method="POST" enctype="multipart/form-data" id="uploadPhotoForm">
                        <input type="hidden" name="id_kost" value="<?= $idKost ?>">
                        
                        <!-- File Input -->
                        <div class="mb-3">
                            <label for="photoFile" class="form-label fw-semibold">Pilih File Foto <span class="text-danger">*</span></label>
                            <input 
                                type="file" 
                                class="form-control" 
                                id="photoFile" 
                                name="photo" 
                                accept="image/jpeg,image/png,image/jpg" 
                                required
                            >
                            <div class="form-text small">Format yang didukung: JPG, JPEG, PNG. Maksimal 2MB.</div>
                        </div>

                        <!-- Category Selection -->
                        <div class="mb-4">
                            <label for="photoCategory" class="form-label fw-semibold">Kategori Foto <span class="text-danger">*</span></label>
                            <select class="form-select" id="photoCategory" name="photo_category" required>
                                <?php foreach ($categories as $value => $label): ?>
                                    <option value="<?= $value ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-cloud-upload-fill me-1"></i>Mulai Unggah
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Photo Gallery Grid grouped by category -->
        <div class="col-lg-8">
            <?php if (empty($photos)): ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="bi bi-images" style="font-size: 4rem;"></i>
                        <h5 class="mt-3">Galeri Foto Kosong</h5>
                        <p class="mb-0 small">Belum ada foto yang diunggah untuk kos ini. Silakan gunakan panel di sebelah kiri untuk mengunggah.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $catKey => $catLabel): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-folder-fill me-2 text-warning"></i><?= $catLabel ?>
                            </h5>
                            <span class="badge bg-secondary rounded-pill px-3 py-1">
                                <?= count($groupedPhotos[$catKey]) ?> Foto
                            </span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($groupedPhotos[$catKey])): ?>
                                <p class="text-muted small mb-0">Belum ada foto untuk kategori ini.</p>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach ($groupedPhotos[$catKey] as $p): ?>
                                        <div class="col-6 col-sm-4 col-md-3">
                                            <div class="card h-100 border overflow-hidden position-relative group-hover-actions">
                                                <img 
                                                    src="<?= BASE_URL ?>/uploads/kost/<?= h($p['photo_path']) ?>" 
                                                    class="photo-thumb" 
                                                    alt="<?= h($catLabel) ?>"
                                                    style="height: 120px; object-fit: cover;"
                                                >
                                                <div class="card-body p-2 text-center bg-light border-top">
                                                    <!-- Delete button trigger modal -->
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-sm btn-outline-danger w-100 btn-delete"
                                                        data-name="Foto <?= $catLabel ?> (ID: <?= $p['id_photo'] ?>)"
                                                        data-action="delete.php?id=<?= $p['id_photo'] ?>&id_kost=<?= $idKost ?>"
                                                    >
                                                        <i class="bi bi-trash small"></i> Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
