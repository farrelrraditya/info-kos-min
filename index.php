<?php
/**
 * InfoKosMin - Public Landing Page & Catalog
 *
 * This is the main entry point for guest users.
 * Displays the hero section, search form, and paginated kost catalog.
 * Queries: view_available_boarding_houses
 */

// Define base URL for all asset/link references
define('BASE_URL', '');

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
// Using view columns but joining directly for filter flexibility
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
$paginationBase = 'index.php' . ($queryParts ? '?' . implode('&', $queryParts) : '');

$pageTitle = 'Beranda';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- ================================================================
     HERO SECTION
     Covers: Landing Page requirement, Hero Section, Bootstrap Grid
     ================================================================ -->
<section class="hero-section" id="home">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <h1 class="mb-3">
                    <i class="bi bi-house-heart-fill me-2"></i>InfoKosMin
                </h1>
                <p class="lead mb-4">
                    Platform katalog kos pintar untuk mempermudah pencarian hunian.
                    Temukan kos terbaik dengan informasi lengkap, foto, dan kontak pemilik.
                </p>
                <a href="#catalog" class="btn btn-light btn-lg me-2">
                    <i class="bi bi-search me-1"></i>Cari Kos Sekarang
                </a>
                <a href="pages/kost/index.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-grid me-1"></i>Lihat Semua
                </a>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <i class="bi bi-buildings" style="font-size: 8rem; opacity: 0.4;"></i>
            </div>
        </div>
    </div>
</section>

<!-- ================================================================
     INFO STRIP
     ================================================================ -->
<section class="bg-white border-bottom py-3">
    <div class="container">
        <div class="row text-center g-3">
            <div class="col-6 col-md-3">
                <div class="fw-bold text-primary fs-4"><?= $totalRows ?>+</div>
                <div class="text-muted small">Kos Tersedia</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fw-bold text-primary fs-4"><?= count($districts) ?>+</div>
                <div class="text-muted small">Kecamatan</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fw-bold text-primary fs-4">3</div>
                <div class="text-muted small">Tipe Kos</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fw-bold text-primary fs-4">100%</div>
                <div class="text-muted small">Info Terverifikasi</div>
            </div>
        </div>
    </div>
</section>

<main class="main-content" id="catalog">
    <div class="container">

        <?php showFlash(); ?>

        <!-- ============================================================
             SEARCH & FILTER
             Covers: Search feature, DOM manipulation via search.js
             ============================================================ -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form id="filterForm" method="GET" action="index.php">
                    <div class="row g-2">
                        <!-- Search input -->
                        <div class="col-12 col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="searchInput"
                                    name="search"
                                    placeholder="Cari nama kos atau kecamatan..."
                                    value="<?= h($search) ?>"
                                    autocomplete="off"
                                >
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary <?= $search ? '' : 'd-none' ?>"
                                    id="clearSearchBtn"
                                    title="Hapus pencarian"
                                >
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>

                        <!-- District filter -->
                        <div class="col-6 col-md-2">
                            <select class="form-select" name="district" id="districtFilter">
                                <option value="">Semua Kecamatan</option>
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?= h($d) ?>" <?= ($filterDistrict === $d) ? 'selected' : '' ?>>
                                        <?= h($d) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Gender filter -->
                        <div class="col-6 col-md-2">
                            <select class="form-select" name="gender" id="genderFilter">
                                <option value="">Semua Tipe</option>
                                <option value="male"   <?= ($filterGender === 'male')   ? 'selected' : '' ?>>Putra</option>
                                <option value="female" <?= ($filterGender === 'female') ? 'selected' : '' ?>>Putri</option>
                                <option value="mixed"  <?= ($filterGender === 'mixed')  ? 'selected' : '' ?>>Campur</option>
                            </select>
                        </div>

                        <!-- Max price filter -->
                        <div class="col-6 col-md-2">
                            <select class="form-select" name="max_price" id="maxPriceFilter">
                                <option value="0">Semua Harga</option>
                                <option value="500000"  <?= ($filterMax === 500000)  ? 'selected' : '' ?>>≤ Rp 500rb</option>
                                <option value="800000"  <?= ($filterMax === 800000)  ? 'selected' : '' ?>>≤ Rp 800rb</option>
                                <option value="1000000" <?= ($filterMax === 1000000) ? 'selected' : '' ?>>≤ Rp 1jt</option>
                                <option value="1500000" <?= ($filterMax === 1500000) ? 'selected' : '' ?>>≤ Rp 1,5jt</option>
                                <option value="2000000" <?= ($filterMax === 2000000) ? 'selected' : '' ?>>≤ Rp 2jt</option>
                            </select>
                        </div>

                        <!-- Submit -->
                        <div class="col-6 col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                            <?php if ($search || $filterGender || $filterMax || $filterDistrict): ?>
                                <a href="index.php" class="btn btn-outline-secondary" title="Reset filter">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div><!-- /.row -->
                </form>
            </div>
        </div>
        <!-- /.search & filter -->

        <!-- Live search indicator (DOM manipulation target) -->
        <div id="result-count" class="text-muted small mb-3 d-none"></div>
        <div id="filterLoading" class="text-muted small mb-3 d-none">
            <span class="spinner-border spinner-border-sm me-1"></span>Memfilter...
        </div>

        <!-- Results header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <?php if ($search || $filterGender || $filterMax || $filterDistrict): ?>
                    Hasil Pencarian
                    <span class="badge bg-primary ms-2"><?= $totalRows ?> kos</span>
                <?php else: ?>
                    Kos Tersedia
                    <span class="badge bg-success ms-2"><?= $totalRows ?> kos</span>
                <?php endif; ?>
            </h5>
            <small class="text-muted">Halaman <?= $pager['currentPage'] ?> dari <?= $pager['totalPages'] ?: 1 ?></small>
        </div>

        <!-- ============================================================
             CATALOG GRID
             Covers: Bootstrap Card component, Grid layout
             ============================================================ -->
        <?php if (empty($kosts)): ?>
            <div class="alert alert-info text-center py-5">
                <i class="bi bi-search display-4 d-block mb-3"></i>
                <h5>Tidak ada kos ditemukan</h5>
                <p class="mb-3">Coba ubah kata kunci atau filter pencarian Anda.</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-1"></i>Lihat Semua Kos
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($kosts as $kost): ?>
                    <div class="col-12 col-sm-6 col-xl-4">
                        <div class="card h-100 kost-card shadow-sm">

                            <!-- Cover Photo -->
                            <?php if ($kost['cover_photo']): ?>
                                <img
                                    src="<?= BASE_URL ?>/uploads/kost/<?= h($kost['cover_photo']) ?>"
                                    class="card-img-top"
                                    alt="<?= h($kost['kost_name']) ?>"
                                    loading="lazy"
                                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                                >
                                <div class="card-img-placeholder" style="display:none;">
                                    <i class="bi bi-image"></i>
                                </div>
                            <?php else: ?>
                                <div class="card-img-placeholder">
                                    <i class="bi bi-building"></i>
                                </div>
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column">
                                <!-- Status badges -->
                                <div class="mb-2 d-flex flex-wrap gap-1">
                                    <span class="badge <?= statusBadgeClass($kost['availability_status']) ?>">
                                        <?= statusLabel($kost['availability_status']) ?>
                                    </span>
                                    <span class="badge <?= genderBadgeClass($kost['gender_type']) ?>">
                                        <?= genderLabel($kost['gender_type']) ?>
                                    </span>
                                    <?php if ($kost['is_furnished']): ?>
                                        <span class="badge bg-info text-dark">Furnished</span>
                                    <?php endif; ?>
                                </div>

                                <h6 class="card-title fw-bold mb-1"><?= h($kost['kost_name']) ?></h6>

                                <p class="text-muted small mb-2">
                                    <i class="bi bi-geo-alt me-1"></i><?= h($kost['district']) ?>
                                </p>

                                <div class="price-tag mb-2">
                                    <?= formatRupiah($kost['monthly_price']) ?>
                                    <span class="text-muted fw-normal fs-6">/bulan</span>
                                </div>

                                <div class="text-muted small mb-3 d-flex flex-wrap gap-2">
                                    <?php if ($kost['room_size']): ?>
                                        <span><i class="bi bi-aspect-ratio me-1"></i><?= h($kost['room_size']) ?> m</span>
                                    <?php endif; ?>
                                    <span><i class="bi bi-lightning me-1"></i><?= ($kost['electricity_type'] === 'token') ? 'Token' : 'Fixed' ?></span>
                                    <span><i class="bi bi-stars me-1"></i><?= $kost['facility_count'] ?> fasilitas</span>
                                </div>

                                <a
                                    href="pages/kost/detail.php?id=<?= $kost['id_kost'] ?>"
                                    class="btn btn-primary btn-sm mt-auto"
                                >
                                    <i class="bi bi-eye me-1"></i>Lihat Detail
                                </a>
                            </div><!-- /.card-body -->

                        </div><!-- /.card -->
                    </div><!-- /.col -->
                <?php endforeach; ?>
            </div><!-- /.row -->

            <!-- Pagination -->
            <div class="mt-4">
                <?php renderPagination($pager, $paginationBase); ?>
            </div>
        <?php endif; ?>

        <!-- ============================================================
             ABOUT SECTION
             Covers: Project Description requirement on landing page
             ============================================================ -->
        <section class="mt-5 pt-4 border-top" id="about">
            <div class="row g-4">
                <div class="col-12">
                    <h3 class="mb-3">Tentang InfoKosMin</h3>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 bg-white shadow-sm text-center p-3">
                        <i class="bi bi-shield-check text-primary fs-1 mb-3"></i>
                        <h5>Terverifikasi</h5>
                        <p class="text-muted small">Setiap kos disurvei langsung oleh tim InfoKosMin sebelum ditampilkan.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 bg-white shadow-sm text-center p-3">
                        <i class="bi bi-camera text-primary fs-1 mb-3"></i>
                        <h5>Foto Lengkap</h5>
                        <p class="text-muted small">Foto kamar, kamar mandi, dapur, dan area parkir tersedia per kategori.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 bg-white shadow-sm text-center p-3">
                        <i class="bi bi-whatsapp text-primary fs-1 mb-3"></i>
                        <h5>Kontak Langsung</h5>
                        <p class="text-muted small">Hubungi pemilik kos langsung via WhatsApp tanpa perantara.</p>
                    </div>
                </div>
            </div>
        </section>

    </div><!-- /.container -->
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
