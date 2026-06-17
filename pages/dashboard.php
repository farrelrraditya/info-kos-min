<?php
/**
 * InfoKosMin - Admin Dashboard
 * 
 * Includes statistics, views (view_kost_summary), and Complex Queries 2 & 3.
 */

define('BASE_URL', '..');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();

// ─── Fetch Statistics ────────────────────────────────────────────────
$countKosts = $pdo->query("SELECT COUNT(*) FROM boarding_houses")->fetchColumn();
$countOwners = $pdo->query("SELECT COUNT(*) FROM owners")->fetchColumn();
$countFacilities = $pdo->query("SELECT COUNT(*) FROM facilities")->fetchColumn();
$countAvailable = $pdo->query("SELECT COUNT(*) FROM boarding_houses WHERE availability_status = 'available'")->fetchColumn();

// ─── Query 2: Ranked by Facility Count (Complex Query) ────────────────
$facilityRankSql = "
    SELECT bh.kost_name, COUNT(kf.id_facility) AS total_facilities
    FROM boarding_houses bh
    LEFT JOIN kost_facilities kf ON bh.id_kost = kf.id_kost
    GROUP BY bh.id_kost, bh.kost_name
    ORDER BY total_facilities DESC
";
$stmtRank = $pdo->query($facilityRankSql);
$facilityRanks = $stmtRank->fetchAll();

// ─── Query 3: District Analysis (Complex Query) ─────────────────────
$districtSql = "
    SELECT district, COUNT(*) AS total_available
    FROM boarding_houses
    WHERE availability_status = 'available'
    GROUP BY district
    HAVING COUNT(*) > 0
";
$stmtDistrict = $pdo->query($districtSql);
$districts = $stmtDistrict->fetchAll();

// ─── View 2: view_kost_summary ────────────────────────────────────────
$summarySql = "SELECT * FROM view_kost_summary ORDER BY created_at DESC";
$stmtSummary = $pdo->query($summarySql);
$summaries = $stmtSummary->fetchAll();

$pageTitle = 'Dashboard Admin';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header bg-white border-bottom py-3 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="mb-0 text-dark"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h2>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="text-muted">Halo, <strong><?= h($_SESSION['username']) ?></strong>! Selamat datang kembali.</span>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <?php showFlash(); ?>

    <!-- ================================================================
         STATISTICS CARDS
         ================================================================ -->
    <div class="row g-3 mb-4">
        <!-- Total Kost -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card-premium stat-primary">
                <div class="stat-card-info">
                    <h6>Total Kos</h6>
                    <div class="counter"><?= $countKosts ?></div>
                </div>
                <div class="stat-card-icon-box"><i class="bi bi-building"></i></div>
            </div>
        </div>

        <!-- Available Kost -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card-premium stat-success">
                <div class="stat-card-info">
                    <h6>Kos Tersedia</h6>
                    <div class="counter"><?= $countAvailable ?></div>
                </div>
                <div class="stat-card-icon-box"><i class="bi bi-check-circle"></i></div>
            </div>
        </div>

        <!-- Total Owners -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card-premium stat-warning">
                <div class="stat-card-info">
                    <h6>Pemilik Kos</h6>
                    <div class="counter"><?= $countOwners ?></div>
                </div>
                <div class="stat-card-icon-box"><i class="bi bi-people"></i></div>
            </div>
        </div>

        <!-- Total Facilities -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card-premium stat-danger">
                <div class="stat-card-info">
                    <h6>Fasilitas Master</h6>
                    <div class="counter"><?= $countFacilities ?></div>
                </div>
                <div class="stat-card-icon-box"><i class="bi bi-grid"></i></div>
            </div>
        </div>
    </div>

    <!-- ================================================================
         COMPLEX QUERIES SECTIONS
         ================================================================ -->
    <div class="row g-4 mb-4">
        <!-- Query 2: Facility Rank Table -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-award me-2 text-primary"></i>Peringkat Kos Berdasarkan Fasilitas (Query 2)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">No</th>
                                    <th>Nama Kos</th>
                                    <th class="text-center" style="width: 150px;">Jumlah Fasilitas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($facilityRanks)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">Tidak ada data kos.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($facilityRanks as $index => $rank): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td class="fw-semibold text-dark"><?= h($rank['kost_name']) ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-primary rounded-pill px-3 py-2">
                                                    <?= $rank['total_facilities'] ?> Fasilitas
                                                </span>
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

        <!-- Query 3: District Analysis Table -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-geo-alt me-2 text-primary"></i>Analisis Ketersediaan per Kecamatan (Query 3)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">No</th>
                                    <th>Kecamatan</th>
                                    <th class="text-center" style="width: 150px;">Kos Tersedia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($districts)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">Tidak ada kecamatan dengan kos tersedia.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($districts as $index => $dist): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td class="fw-semibold text-dark"><?= h($dist['district']) ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-success rounded-pill px-3 py-2">
                                                    <?= $dist['total_available'] ?> Tersedia
                                                </span>
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
    </div>

    <!-- ================================================================
         VIEW 2: view_kost_summary
         ================================================================ -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h5 class="mb-0 text-dark fw-bold">
                <i class="bi bi-table me-2 text-primary"></i>Ringkasan Informasi Kos (view_kost_summary)
            </h5>
            <a href="<?= BASE_URL ?>/pages/kost/index.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil-square me-1"></i>Kelola Kos
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Nama Kos</th>
                            <th>Pemilik</th>
                            <th>Kecamatan</th>
                            <th>Harga Bulanan</th>
                            <th class="text-center">Tipe</th>
                            <th class="text-center">Fasilitas</th>
                            <th class="text-center">Foto</th>
                            <th>Status</th>
                            <th>Tanggal Survei Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($summaries)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada data kos yang terdaftar.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($summaries as $row): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?= h($row['kost_name']) ?></div>
                                    </td>
                                    <td>
                                        <div><?= h($row['owner_name']) ?></div>
                                        <small class="text-muted"><?= h($row['phone_number']) ?></small>
                                    </td>
                                    <td><?= h($row['district']) ?></td>
                                    <td class="price-tag"><?= formatRupiah($row['monthly_price']) ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= genderBadgeClass($row['gender_type']) ?>">
                                            <?= genderLabel($row['gender_type']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-pill"><?= $row['facility_count'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info text-dark rounded-pill"><?= $row['photo_count'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= statusBadgeClass($row['availability_status']) ?>">
                                            <?= statusLabel($row['availability_status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <?= $row['last_surveyed'] ? date('d-m-Y', strtotime($row['last_surveyed'])) : 'Belum disurvei' ?>
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
require_once __DIR__ . '/../includes/footer.php';
?>
