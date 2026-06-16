<?php
/**
 * InfoKosMin - Boarding House Detail (Public Page)
 * 
 * Implements Query 1: JOIN 4 tables (boarding_houses, owners, kost_facilities, facilities) 
 * + 2 stored functions (fn_total_facilities, fn_estimated_yearly_cost)
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();

$idKost = (int)($_GET['id'] ?? 0);
if ($idKost <= 0) {
    setFlash('ID Kos tidak valid.', 'danger');
    redirect(BASE_URL . '/index.php');
}

// ─── Query 1: Join 4 tables + 2 stored functions ────────────────────
$sql = "
    SELECT 
        bh.*, 
        o.owner_name, 
        o.phone_number, 
        o.email AS owner_email,
        f.facility_name,
        fn_total_facilities(bh.id_kost) AS total_facilities,
        fn_estimated_yearly_cost(bh.id_kost) AS estimated_yearly_cost
    FROM boarding_houses bh
    INNER JOIN owners o ON bh.id_owner = o.id_owner
    LEFT JOIN kost_facilities kf ON bh.id_kost = kf.id_kost
    LEFT JOIN facilities f ON kf.id_facility = f.id_facility
    WHERE bh.id_kost = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idKost]);
$rows = $stmt->fetchAll();

if (empty($rows)) {
    setFlash('Data kos tidak ditemukan.', 'danger');
    redirect(BASE_URL . '/index.php');
}

// Group facilities from query rows
$kostDetails = $rows[0]; // Take boarding house info from first row
$facilitiesList = [];
foreach ($rows as $row) {
    if (!empty($row['facility_name'])) {
        $facilitiesList[] = $row['facility_name'];
    }
}

// Fetch photos for this boarding house
$photoStmt = $pdo->prepare("SELECT * FROM photos WHERE id_kost = ? ORDER BY uploaded_at ASC");
$photoStmt->execute([$idKost]);
$photos = $photoStmt->fetchAll();

$pageTitle = $kostDetails['kost_name'];
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container my-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Katalog</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= h($kostDetails['kost_name']) ?></li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="row align-items-center mb-4 g-2">
        <div class="col-md-8">
            <h1 class="fw-bold mb-1 text-dark"><?= h($kostDetails['kost_name']) ?></h1>
            <p class="text-muted mb-0">
                <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= h($kostDetails['address']) ?>, Kec. <?= h($kostDetails['district']) ?>
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <span class="badge fs-6 <?= statusBadgeClass($kostDetails['availability_status']) ?> px-3 py-2">
                <?= statusLabel($kostDetails['availability_status']) ?>
            </span>
            <span class="badge fs-6 <?= genderBadgeClass($kostDetails['gender_type']) ?> px-3 py-2">
                Kos <?= genderLabel($kostDetails['gender_type']) ?>
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Photos & Details -->
        <div class="col-lg-8">
            <!-- Photo Gallery Carousel/Grid -->
            <div class="card shadow-sm border-0 overflow-hidden mb-4">
                <?php if (empty($photos)): ?>
                    <div class="bg-light d-flex flex-column align-items-center justify-content-center text-muted" style="height: 400px;">
                        <i class="bi bi-image" style="font-size: 5rem;"></i>
                        <p class="mt-2 mb-0">Belum ada foto yang diunggah.</p>
                    </div>
                <?php else: ?>
                    <div id="kostCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <?php foreach ($photos as $index => $photo): ?>
                                <button 
                                    type="button" 
                                    data-bs-target="#kostCarousel" 
                                    data-bs-slide-to="<?= $index ?>" 
                                    class="<?= $index === 0 ? 'active' : '' ?>"
                                    aria-current="<?= $index === 0 ? 'true' : 'false' ?>"
                                    aria-label="Slide <?= $index + 1 ?>"
                                ></button>
                            <?php endforeach; ?>
                        </div>
                        <div class="carousel-inner" style="height: 400px; background: #000;">
                            <?php foreach ($photos as $index => $photo): ?>
                                <div class="carousel-item h-100 <?= $index === 0 ? 'active' : '' ?>">
                                    <img 
                                        src="<?= BASE_URL ?>/uploads/kost/<?= h($photo['photo_path']) ?>" 
                                        class="d-block w-100 h-100" 
                                        style="object-fit: contain;"
                                        alt="<?= h($photo['photo_category']) ?>"
                                    >
                                    <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded py-1 px-3" style="width: fit-content; margin: 0 auto 20px auto;">
                                        <span class="text-capitalize small">Kategori: <?= h($photo['photo_category']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#kostCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Sebelumnya</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#kostCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Selanjutnya</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Specs Grid -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Spesifikasi Kos</h5>
                    <div class="row g-3">
                        <div class="col-6 col-md-3 text-center border-end">
                            <div class="text-muted small mb-1">Ukuran Kamar</div>
                            <div class="fw-bold text-dark"><i class="bi bi-aspect-ratio me-1"></i><?= $kostDetails['room_size'] ? h($kostDetails['room_size']) : '—' ?></div>
                        </div>
                        <div class="col-6 col-md-3 text-center border-end-md">
                            <div class="text-muted small mb-1">Tipe Listrik</div>
                            <div class="fw-bold text-dark">
                                <i class="bi bi-lightning-charge me-1"></i>
                                <?= $kostDetails['electricity_type'] === 'token' ? 'Token (Pulsa)' : 'Fixed (Free)' ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 text-center border-end">
                            <div class="text-muted small mb-1">Jam Malam</div>
                            <div class="fw-bold text-dark"><i class="bi bi-clock me-1"></i><?= $kostDetails['curfew'] ? h($kostDetails['curfew']) : 'Bebas' ?></div>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-muted small mb-1">Furnitur</div>
                            <div class="fw-bold text-dark">
                                <i class="bi bi-house-door me-1"></i>
                                <?= $kostDetails['is_furnished'] ? 'Furnished' : 'Kosongan' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Deskripsi Lengkap</h5>
                    <p class="text-muted mb-0" style="white-space: pre-line;">
                        <?= $kostDetails['description'] ? h($kostDetails['description']) : 'Tidak ada deskripsi tambahan.' ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Column: Price & Owner Contacts -->
        <div class="col-lg-4">
            <!-- Price Card -->
            <div class="card shadow-sm border-primary mb-4">
                <div class="card-body">
                    <div class="text-muted small mb-1">Harga Sewa</div>
                    <div class="price-tag fs-3 mb-2"><?= formatRupiah($kostDetails['monthly_price']) ?> <span class="text-muted fs-6 fw-normal">/ bulan</span></div>
                    
                    <!-- Stored Function 2 output (estimated_yearly_cost) -->
                    <div class="alert alert-light border small text-dark mb-0 py-2">
                        <i class="bi bi-calculator me-1 text-primary"></i>
                        Estimasi Biaya / Tahun: <strong><?= formatRupiah($kostDetails['estimated_yearly_cost']) ?></strong>
                    </div>
                </div>
            </div>

            <!-- Facilities Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark">Fasilitas</h5>
                    <!-- Stored Function 1 output (total_facilities) -->
                    <span class="badge bg-primary rounded-pill px-2 py-1">
                        <?= $kostDetails['total_facilities'] ?> Fasilitas
                    </span>
                </div>
                <div class="card-body">
                    <?php if (empty($facilitiesList)): ?>
                        <div class="text-muted small">Tidak ada fasilitas terdaftar.</div>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($facilitiesList as $facility): ?>
                                <span class="badge bg-light text-dark border px-3 py-2">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i><?= h($facility) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Owner Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark">Informasi Kontak Pemilik</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-person fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark"><?= h($kostDetails['owner_name']) ?></div>
                            <div class="text-muted small">Pemilik Properti</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Nomor Telepon:</div>
                        <div class="fw-bold"><?= h($kostDetails['phone_number']) ?></div>
                    </div>

                    <?php if (!empty($kostDetails['owner_email'])): ?>
                        <div class="mb-4">
                            <div class="text-muted small">Email:</div>
                            <div class="fw-bold"><?= h($kostDetails['owner_email']) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php
                    // Clean phone number for WhatsApp Link
                    $cleanPhone = preg_replace('/[^0-9]/', '', $kostDetails['phone_number']);
                    if (strpos($cleanPhone, '0') === 0) {
                        $cleanPhone = '62' . substr($cleanPhone, 1);
                    }
                    ?>
                    <div class="d-grid gap-2">
                        <a 
                            href="https://wa.me/<?= $cleanPhone ?>?text=Halo%20<?= urlencode($kostDetails['owner_name']) ?>%2C%20saya%20tertarik%20dengan%20kos%20<?= urlencode($kostDetails['kost_name']) ?>%20yang%20ada%20di%20InfoKosMin." 
                            class="btn btn-success" 
                            target="_blank"
                        >
                            <i class="bi bi-whatsapp me-2"></i>Hubungi via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
