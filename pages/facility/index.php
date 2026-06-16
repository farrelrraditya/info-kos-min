<?php
/**
 * InfoKosMin - Manage Facilities (Admin List)
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();

// Fetch all facilities and count of kosts using each
$sql = "
    SELECT f.*, 
           (SELECT COUNT(*) FROM kost_facilities kf WHERE kf.id_facility = f.id_facility) AS kost_count
    FROM facilities f
    ORDER BY f.facility_name ASC
";
$facilities = $pdo->query($sql)->fetchAll();

$pageTitle = 'Kelola Fasilitas';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header bg-white border-bottom py-3 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 col-12">
                <h2 class="mb-0 text-dark"><i class="bi bi-grid me-2 text-primary"></i>Kelola Fasilitas</h2>
            </div>
            <div class="col-md-6 col-12 text-md-end mt-2 mt-md-0">
                <a href="create.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Fasilitas Baru
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <?php showFlash(); ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 80px;" class="text-center">No</th>
                            <th>Nama Fasilitas</th>
                            <th class="text-center" style="width: 200px;">Jumlah Kos Menggunakan</th>
                            <th class="text-end" style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($facilities)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Belum ada fasilitas terdaftar.</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $no = 1;
                            foreach ($facilities as $row): 
                            ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="fw-bold text-dark"><?= h($row['facility_name']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-pill px-3 py-2">
                                            <?= $row['kost_count'] ?> Kos
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Edit -->
                                            <a href="edit.php?id=<?= $row['id_facility'] ?>" class="btn btn-outline-primary" title="Edit Fasilitas">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <!-- Delete -->
                                            <button
                                                type="button"
                                                class="btn btn-outline-danger btn-delete"
                                                data-name="<?= h($row['facility_name']) ?>"
                                                data-action="<?= BASE_URL ?>/pages/facility/delete.php?id=<?= $row['id_facility'] ?>"
                                                title="Hapus Fasilitas"
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
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
