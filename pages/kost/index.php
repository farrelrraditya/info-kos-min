<?php
/**
 * InfoKosMin - Manage Boarding Houses (Admin List)
 * 
 * Includes searching, filtering, and pagination.
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();

// ─── Filter Parameters ───────────────────────────────────────────────
$search       = sanitizeInput($_GET['search'] ?? '');
$filterGender = sanitizeInput($_GET['gender'] ?? '');
$filterStatus = sanitizeInput($_GET['status'] ?? '');
$currentPage  = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 10;

// ─── Build WHERE query ───────────────────────────────────────────────
$where = [];
$params = [];

if ($search !== '') {
    $where[] = '(bh.kost_name LIKE ? OR bh.district LIKE ? OR o.owner_name LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($filterGender !== '') {
    $where[] = 'bh.gender_type = ?';
    $params[] = $filterGender;
}
if ($filterStatus !== '') {
    $where[] = 'bh.availability_status = ?';
    $params[] = $filterStatus;
}

$whereSQL = '';
if (!empty($where)) {
    $whereSQL = 'WHERE ' . implode(' AND ', $where);
}

// ─── Pagination ──────────────────────────────────────────────────────
$countSQL = "
    SELECT COUNT(*) 
    FROM boarding_houses bh
    INNER JOIN owners o ON bh.id_owner = o.id_owner
    $whereSQL
";
$countStmt = $pdo->prepare($countSQL);
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$pager = paginate($totalRows, $currentPage, $perPage);

// ─── Fetch Rows ──────────────────────────────────────────────────────
$sql = "
    SELECT bh.*, o.owner_name, o.phone_number,
           (SELECT COUNT(*) FROM kost_facilities kf WHERE kf.id_kost = bh.id_kost) AS facility_count,
           (SELECT COUNT(*) FROM photos p WHERE p.id_kost = bh.id_kost) AS photo_count
    FROM boarding_houses bh
    INNER JOIN owners o ON bh.id_owner = o.id_owner
    $whereSQL
    ORDER BY bh.created_at DESC
    LIMIT {$pager['perPage']} OFFSET {$pager['offset']}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$kosts = $stmt->fetchAll();

// ─── Query string for pagination links ───────────────────────────────
$queryParts = [];
if ($search !== '') $queryParts[] = 'search=' . urlencode($search);
if ($filterGender !== '') $queryParts[] = 'gender=' . urlencode($filterGender);
if ($filterStatus !== '') $queryParts[] = 'status=' . urlencode($filterStatus);
$paginationBase = 'index.php' . ($queryParts ? '?' . implode('&', $queryParts) : '');

$pageTitle = 'Kelola Kos';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header bg-white border-bottom py-3 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 col-12">
                <h2 class="mb-0 text-dark"><i class="bi bi-building me-2 text-primary"></i>Kelola Kos</h2>
            </div>
            <div class="col-md-6 col-12 text-md-end mt-2 mt-md-0">
                <a href="create.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Kos Baru
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <?php showFlash(); ?>

    <!-- Search and Filter Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-2">
                <!-- Search input -->
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input
                            type="text"
                            class="form-control"
                            name="search"
                            placeholder="Cari nama kos, kecamatan, atau pemilik..."
                            value="<?= h($search) ?>"
                        >
                    </div>
                </div>

                <!-- Gender filter -->
                <div class="col-6 col-md-2">
                    <select class="form-select" name="gender">
                        <option value="">Semua Tipe</option>
                        <option value="male" <?= $filterGender === 'male' ? 'selected' : '' ?>>Putra</option>
                        <option value="female" <?= $filterGender === 'female' ? 'selected' : '' ?>>Putri</option>
                        <option value="mixed" <?= $filterGender === 'mixed' ? 'selected' : '' ?>>Campur</option>
                    </select>
                </div>

                <!-- Status filter -->
                <div class="col-6 col-md-2">
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="available" <?= $filterStatus === 'available' ? 'selected' : '' ?>>Tersedia</option>
                        <option value="full" <?= $filterStatus === 'full' ? 'selected' : '' ?>>Penuh</option>
                        <option value="unavailable" <?= $filterStatus === 'unavailable' ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-12 col-md-3 d-grid gap-2 d-md-flex">
                    <button type="submit" class="btn btn-outline-primary flex-fill">
                        <i class="bi bi-filter me-1"></i>Filter
                    </button>
                    <?php if ($search !== '' || $filterGender !== '' || $filterStatus !== ''): ?>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th>Nama Kos</th>
                            <th>Kecamatan</th>
                            <th>Harga Bulanan</th>
                            <th class="text-center">Tipe</th>
                            <th class="text-center">Fasilitas</th>
                            <th class="text-center">Foto</th>
                            <th class="text-center">Status</th>
                            <th class="text-end" style="min-width: 240px; width: 30%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($kosts)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Tidak ada data kos ditemukan.</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $startNo = $pager['offset'] + 1;
                            foreach ($kosts as $row): 
                            ?>
                                <tr>
                                    <td class="text-center"><?= $startNo++ ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= h($row['kost_name']) ?></div>
                                        <small class="text-muted">Pemilik: <?= h($row['owner_name']) ?></small>
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
                                        <a href="<?= BASE_URL ?>/pages/photo/index.php?id_kost=<?= $row['id_kost'] ?>" class="btn btn-sm btn-outline-info rounded-pill" title="Kelola Foto">
                                            <i class="bi bi-images me-1"></i><?= $row['photo_count'] ?>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= statusBadgeClass($row['availability_status']) ?>">
                                            <?= statusLabel($row['availability_status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Public Detail Link -->
                                            <a href="detail.php?id=<?= $row['id_kost'] ?>" class="btn btn-outline-secondary" title="Detail Publik">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <!-- Survey Log Link -->
                                            <a href="<?= BASE_URL ?>/pages/survey/index.php?id_kost=<?= $row['id_kost'] ?>" class="btn btn-outline-success" title="Log Survei">
                                                <i class="bi bi-journal-check"></i>
                                            </a>
                                            <!-- History Link -->
                                            <a href="history.php?id=<?= $row['id_kost'] ?>" class="btn btn-outline-warning" title="Riwayat Status">
                                                <i class="bi bi-clock-history"></i>
                                            </a>
                                            <!-- Edit Link -->
                                            <a href="edit.php?id=<?= $row['id_kost'] ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <!-- Delete Button -->
                                            <button
                                                type="button"
                                                class="btn btn-outline-danger btn-delete"
                                                data-name="<?= h($row['kost_name']) ?>"
                                                data-action="<?= BASE_URL ?>/pages/kost/delete.php?id=<?= $row['id_kost'] ?>"
                                                title="Hapus"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mb-4">
        <?php renderPagination($pager, $paginationBase); ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
