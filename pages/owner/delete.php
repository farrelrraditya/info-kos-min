<?php
/**
 * InfoKosMin - Delete Owner
 * 
 * POST-only route. Blocks deletion if owner has active boarding houses.
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmed']) && $_POST['confirmed'] == 1) {
    $idOwner = (int)($_GET['id'] ?? 0);
    
    if ($idOwner <= 0) {
        setFlash('ID Pemilik tidak valid.', 'danger');
        redirect(BASE_URL . '/pages/owner/index.php');
    }

    try {
        // Programmatic pre-check (Blocked if kost exists)
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM boarding_houses WHERE id_owner = ?");
        $stmtCheck->execute([$idOwner]);
        $kostCount = (int)$stmtCheck->fetchColumn();

        if ($kostCount > 0) {
            setFlash('Gagal menghapus pemilik: Pemilik masih terikat dengan ' . $kostCount . ' data kos. Hapus atau pindahkan kos tersebut terlebih dahulu.', 'danger');
            redirect(BASE_URL . '/pages/owner/index.php');
        }

        // Execute delete
        $deleteStmt = $pdo->prepare("DELETE FROM owners WHERE id_owner = ?");
        $deleteStmt->execute([$idOwner]);

        setFlash('Data pemilik berhasil dihapus.', 'success');
    } catch (PDOException $e) {
        setFlash('Gagal menghapus pemilik karena batasan database: ' . $e->getMessage(), 'danger');
    }
} else {
    setFlash('Metode akses tidak valid.', 'danger');
}

redirect(BASE_URL . '/pages/owner/index.php');
