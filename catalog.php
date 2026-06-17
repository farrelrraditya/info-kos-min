<?php
/**
 * InfoKosMin - Catalog Search Page
 *
 * Dedicated page for searching and listing available boarding houses.
 */

// Define base URL dynamically for all asset/link references
$base_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', $base_dir === '/' ? '' : $base_dir);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = getDB();

// ─── Search & Filter Parameters (from GET) ───────────────────────
$search       = sanitizeInput($_GET['search']      ?? '');
$filterGender = sanitizeInput($_GET['gender']      ?? '');
$filterMax    = (int)($_GET['max_price']           ?? 0);
$filterDistrict = sanitizeInput($_GET['district']  ?? '');
$currentPage  = max(1, (int)($_GET['page']         ?? 1));
$perPage      = 9;

// ─── Build dynamic WHERE clause ──────────────────────────────────
$where  = ['bh.availability_status = ?'];
$params = ['available'];

if ($search !== '') {
    $where[]  = '(bh.kost_name LIKE ? OR bh.district LIKE ? OR bh.address LIKE ?)';
    $like     = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($filterGender !== '') {
    $where[]  = 'bh.gender_type = ?';
    $params[] = $filterGender;
}
if ($filterMax > 0) {
    $where[]  = 'bh.monthly_price <= ?';
    $params[] = $filterMax;
}
if ($filterDistrict !== '') {
    $where[]  = 'bh.district LIKE ?';
    $params[] = '%' . $filterDistrict . '%';
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

// ─── Count total for pagination ───────────────────────────────────
$countSQL  = "SELECT COUNT(*) FROM boarding_houses bh $whereSQL";
$countStmt = $pdo->prepare($countSQL);
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$pager     = paginate($totalRows, $currentPage, $perPage);

// ─── Fetch catalog rows ───────────────────────────────────────────
$sql = "
    SELECT
        bh.id_kost,
        bh.kost_name,
        bh.district,
        bh.monthly_price,
        bh.gender_type,
        bh.room_size,
        bh.is_furnished,
        bh.electricity_type,
        bh.availability_status,
        o.owner_name,
        o.phone_number,
        (SELECT p.photo_path FROM photos p WHERE p.id_kost = bh.id_kost ORDER BY p.uploaded_at ASC LIMIT 1) AS cover_photo,
        (SELECT COUNT(*) FROM kost_facilities kf WHERE kf.id_kost = bh.id_kost) AS facility_count
    FROM boarding_houses bh
    INNER JOIN owners o ON bh.id_owner = o.id_owner
    $whereSQL
    ORDER BY bh.created_at DESC
    LIMIT {$pager['perPage']} OFFSET {$pager['offset']}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$kosts = $stmt->fetchAll();

// ─── District list for filter dropdown ───────────────────────────
$distStmt = $pdo->query("SELECT DISTINCT district FROM boarding_houses WHERE availability_status='available' ORDER BY district");
$districts = $distStmt->fetchAll(PDO::FETCH_COLUMN);

// ─── Build base URL for pagination links ─────────────────────────
$queryParts = [];
if ($search       !== '') $queryParts[] = 'search='       . urlencode($search);
if ($filterGender !== '') $queryParts[] = 'gender='       . urlencode($filterGender);
if ($filterMax     > 0)   $queryParts[] = 'max_price='    . $filterMax;
if ($filterDistrict !== '') $queryParts[] = 'district='   . urlencode($filterDistrict);

$paginationBase = 'catalog.php' . (empty($queryParts) ? '' : '?' . implode('&', $queryParts));

$pageTitle = 'Cari Kos';
require_once __DIR__ . '/includes/header.php';
?>

<main class="main-content py-5" id="catalog" style="background-color: #f8fafc; min-height: 80vh;">
    <div class="container py-4">

        <!-- Page Header -->
        <div class="text-center mb-5">
            <span class="section-tag mb-2">Pencarian Kamar</span>
            <h1 class="fw-bold text-dark display-5 mb-2">Cari Kos Terbaik</h1>
            <p class="text-muted mx-auto" style="max-width: 600px;">
                Temukan kost idaman Anda di Yogyakarta dengan filter pencarian nama, tipe hunian, lokasi kecamatan, dan kisaran harga bulanan.
            </p>
        </div>

        <!-- Floating Search & Filter Panel -->
        <div class="filter-card-floating mb-5" style="margin-top: 0;">
            <form id="filterForm" method="GET" action="catalog.php">
                <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2 gap-lg-3">
                    <!-- Search input -->
                    <div class="flex-lg-grow-1" style="min-width: 240px;">
                        <div class="filter-input-group h-100">
                            <div class="icon-wrap"><i class="bi bi-search"></i></div>
                            <input
                                type="text"
                                id="searchInput"
                                name="search"
                                placeholder="Cari nama kos atau kecamatan..."
                                value="<?= h($search) ?>"
                                autocomplete="off"
                                class="w-100"
                            >
                            <button
                                type="button"
                                class="btn btn-sm btn-light border-0 <?= $search ? '' : 'd-none' ?>"
                                id="clearSearchBtn"
                                title="Hapus pencarian"
                            >
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- District Filter -->
                    <div style="min-width: 175px;">
                        <div class="filter-input-group h-100">
                            <div class="icon-wrap"><i class="bi bi-geo-alt"></i></div>
                            <select name="district" id="districtFilter" class="form-select border-0 bg-transparent w-100">
                                <option value="">Semua Kecamatan</option>
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?= h($d) ?>" <?= ($filterDistrict === $d) ? 'selected' : '' ?>>
                                        <?= h($d) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Gender Filter -->
                    <div style="min-width: 130px;">
                        <div class="filter-input-group h-100">
                            <div class="icon-wrap"><i class="bi bi-people"></i></div>
                            <select name="gender" id="genderFilter" class="form-select border-0 bg-transparent w-100">
                                <option value="">Semua Tipe</option>
                                <option value="male"   <?= ($filterGender === 'male')   ? 'selected' : '' ?>>Putra</option>
                                <option value="female" <?= ($filterGender === 'female') ? 'selected' : '' ?>>Putri</option>
                                <option value="mixed"  <?= ($filterGender === 'mixed')  ? 'selected' : '' ?>>Campur</option>
                            </select>
                        </div>
                    </div>

                    <!-- Max Price Filter -->
                    <div style="min-width: 150px;">
                        <div class="filter-input-group h-100">
                            <div class="icon-wrap"><i class="bi bi-cash-stack"></i></div>
                            <select name="max_price" id="maxPriceFilter" class="form-select border-0 bg-transparent w-100">
                                <option value="0">Semua Harga</option>
                                <option value="500000"  <?= ($filterMax === 500000)  ? 'selected' : '' ?>>≤ Rp 500rb</option>
                                <option value="800000"  <?= ($filterMax === 800000)  ? 'selected' : '' ?>>≤ Rp 800rb</option>
                                <option value="1000000" <?= ($filterMax === 1000000) ? 'selected' : '' ?>>≤ Rp 1jt</option>
                                <option value="1500000" <?= ($filterMax === 1500000) ? 'selected' : '' ?>>≤ Rp 1,5jt</option>
                                <option value="2000000" <?= ($filterMax === 2000000) ? 'selected' : '' ?>>≤ Rp 2jt</option>
                            </select>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2" style="min-width: 125px;">
                        <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <?php if ($search || $filterGender || $filterMax || $filterDistrict): ?>
                            <a href="catalog.php" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;" title="Reset filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Live search indicator -->
        <div id="result-count" class="alert alert-light border small py-2 mb-3 d-none"></div>
        <div id="filterLoading" class="text-muted small mb-3 d-none">
            <span class="spinner-border spinner-border-sm me-1"></span>Memfilter...
        </div>

        <!-- Results header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 fw-bold fs-4 text-dark">
                <?php if ($search || $filterGender || $filterMax || $filterDistrict): ?>
                    Hasil Pencarian
                    <span class="badge bg-primary rounded-pill ms-2"><?= $totalRows ?> kos</span>
                <?php else: ?>
                    Semua Katalog Kos
                    <span class="badge bg-success rounded-pill ms-2"><?= $totalRows ?> kos</span>
                <?php endif; ?>
            </h5>
            <small class="text-muted">Halaman <?= $pager['currentPage'] ?> dari <?= $pager['totalPages'] ?: 1 ?></small>
        </div>

        <!-- Catalog Grid -->
        <?php if (empty($kosts)): ?>
            <div class="alert alert-info text-center py-5 border-0 rounded-4">
                <i class="bi bi-search display-4 d-block mb-3 text-muted"></i>
                <h5 class="fw-bold text-dark">Tidak ada kos ditemukan</h5>
                <p class="text-muted mb-3">Coba ubah kata kunci pencarian atau reset filter Anda.</p>
                <a href="catalog.php" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i>Lihat Semua Kos
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($kosts as $kost): ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="kost-card-custom">
                            <div class="kost-card-img-wrapper">
                                <div class="kost-card-badge-container">
                                    <span class="kost-badge-status status-<?= h($kost['availability_status']) ?>">
                                        <?= statusLabel($kost['availability_status']) ?>
                                    </span>
                                    <span class="kost-badge-gender gender-<?= h($kost['gender_type']) ?>">
                                        <?= genderLabel($kost['gender_type']) ?>
                                    </span>
                                </div>
                                <?php if ($kost['cover_photo']): ?>
                                    <img
                                        src="<?= BASE_URL ?>/uploads/kost/<?= h($kost['cover_photo']) ?>"
                                        alt="<?= h($kost['kost_name']) ?>"
                                        loading="lazy"
                                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                                    >
                                    <div class="w-100 h-100 d-none align-items-center justify-content-center bg-light text-muted" style="font-size: 3rem;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-muted" style="font-size: 3rem;">
                                        <i class="bi bi-building"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?= h($kost['kost_name']) ?>"><?= h($kost['kost_name']) ?></h5>
                                <p class="text-muted small mb-3"><i class="bi bi-geo-alt me-1 text-danger"></i>Kec. <?= h($kost['district']) ?></p>
                                
                                <div class="text-muted small mb-4 d-flex flex-wrap gap-3 border-top border-bottom py-2">
                                    <?php if ($kost['room_size']): ?>
                                        <span class="kost-card-spec-item"><i class="bi bi-aspect-ratio"></i> <?= h($kost['room_size']) ?> m</span>
                                    <?php endif; ?>
                                    <span class="kost-card-spec-item"><i class="bi bi-lightning"></i> <?= ($kost['electricity_type'] === 'token') ? 'Token' : 'Fixed' ?></span>
                                    <span class="kost-card-spec-item"><i class="bi bi-stars"></i> <?= $kost['facility_count'] ?> Fasilitas</span>
                                </div>

                                <div class="kost-card-price mt-auto mb-3">
                                    <?= formatRupiah($kost['monthly_price']) ?> <span>/bulan</span>
                                </div>

                                <a href="pages/kost/detail.php?id=<?= $kost['id_kost'] ?>" class="btn btn-primary rounded-pill w-100 py-2">
                                    Lihat Detail <i class="bi bi-eye ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="mt-5">
                <?php renderPagination($pager, $paginationBase); ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
