<?php
/**
 * InfoKosMin - Delete Facility
 * 
 * POST-only route. Blocks deletion if facility is currently assigned to any boarding houses.
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmed']) && $_POST['confirmed'] == 1) {
    $idFacility = (int)($_GET['id'] ?? 0);
    
    if ($idFacility <= 0) {
        setFlash('ID Fasilitas tidak valid.', 'danger');
        redirect(BASE_URL . '/pages/facility/index.php');
    }

    try {
        // Pre-check (Blocked if assigned)
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM kost_facilities WHERE id_facility = ?");
        $stmtCheck->execute([$idFacility]);
        $kostCount = (int)$stmtCheck->fetchColumn();

        if ($kostCount > 0) {
            setFlash('Gagal menghapus fasilitas: Fasilitas masih digunakan oleh ' . $kostCount . ' data kos. Hapus kaitan fasilitas tersebut terlebih dahulu.', 'danger');
            redirect(BASE_URL . '/pages/facility/index.php');
        }

        // Execute delete
        $deleteStmt = $pdo->prepare("DELETE FROM facilities WHERE id_facility = ?");
        $deleteStmt->execute([$idFacility]);

        setFlash('Fasilitas berhasil dihapus.', 'success');
    } catch (PDOException $e) {
        setFlash('Gagal menghapus fasilitas karena batasan database: ' . $e->getMessage(), 'danger');
    }
} else {
    setFlash('Metode akses tidak valid.', 'danger');
}

redirect(BASE_URL . '/pages/facility/index.php');
