<?php
/**
 * InfoKosMin - Manage Owners (Admin List)
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();

// ─── Pagination Setup ────────────────────────────────────────────────
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$perPage     = 10;

$countSQL = "SELECT COUNT(*) FROM owners";
$totalRows = (int)$pdo->query($countSQL)->fetchColumn();
$pager = paginate($totalRows, $currentPage, $perPage);

// ─── Fetch Owners ────────────────────────────────────────────────────
$sql = "
    SELECT o.*, 
           (SELECT COUNT(*) FROM boarding_houses bh WHERE bh.id_owner = o.id_owner) AS kost_count
    FROM owners o
    ORDER BY o.owner_name ASC
    LIMIT {$pager['perPage']} OFFSET {$pager['offset']}
";
$owners = $pdo->query($sql)->fetchAll();

$pageTitle = 'Kelola Pemilik';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header bg-white border-bottom py-3 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 col-12">
                <h2 class="mb-0 text-dark"><i class="bi bi-people me-2 text-primary"></i>Kelola Pemilik</h2>
            </div>
            <div class="col-md-6 col-12 text-md-end mt-2 mt-md-0">
                <a href="create.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Pemilik Baru
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <?php showFlash(); ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 80px;" class="text-center">No</th>
                            <th>Nama Pemilik</th>
                            <th>No. Telepon</th>
                            <th>Email</th>
                            <th class="text-center" style="width: 150px;">Jumlah Kos</th>
                            <th class="text-end" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($owners)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada data pemilik terdaftar.</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $startNo = $pager['offset'] + 1;
                            foreach ($owners as $row): 
                            ?>
                                <tr>
                                    <td class="text-center"><?= $startNo++ ?></td>
                                    <td class="fw-bold text-dark"><?= h($row['owner_name']) ?></td>
                                    <td>
                                        <a href="tel:<?= h($row['phone_number']) ?>" class="text-decoration-none text-dark">
                                            <i class="bi bi-telephone me-1 text-muted"></i><?= h($row['phone_number']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($row['email']): ?>
                                            <a href="mailto:<?= h($row['email']) ?>" class="text-decoration-none text-muted">
                                                <i class="bi bi-envelope me-1"></i><?= h($row['email']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-pill px-3 py-2"><?= $row['kost_count'] ?> Kos</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Edit -->
                                            <a href="edit.php?id=<?= $row['id_owner'] ?>" class="btn btn-outline-primary" title="Edit Pemilik">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <!-- Delete -->
                                            <button
                                                type="button"
                                                class="btn btn-outline-danger btn-delete"
                                                data-name="<?= h($row['owner_name']) ?>"
                                                data-action="<?= BASE_URL ?>/pages/owner/delete.php?id=<?= $row['id_owner'] ?>"
                                                title="Hapus Pemilik"
                                            >
                                                <i class="bi bi-trash"></i> Hapus
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
        <?php renderPagination($pager, 'index.php'); ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
