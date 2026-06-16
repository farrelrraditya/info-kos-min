<?php
/**
 * InfoKosMin - Delete Boarding House
 * 
 * POST-only route. Removes associated physical uploads and performs cascade database deletion.
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmed']) && $_POST['confirmed'] == 1) {
    $idKost = (int)($_GET['id'] ?? 0);
    
    if ($idKost <= 0) {
        setFlash('ID Kos tidak valid.', 'danger');
        redirect(BASE_URL . '/pages/kost/index.php');
    }

    try {
        $pdo->beginTransaction();

        // 1. Fetch all associated photo records to delete physical files
        $photoStmt = $pdo->prepare("SELECT photo_path FROM photos WHERE id_kost = ?");
        $photoStmt->execute([$idKost]);
        $photos = $photoStmt->fetchAll();

        $uploadDir = __DIR__ . '/../../uploads/kost/';
        foreach ($photos as $photo) {
            $filePath = $uploadDir . $photo['photo_path'];
            if (file_exists($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }
        }

        // 2. Delete the boarding house record (MySQL cascade constraints will handle the database rows)
        $deleteStmt = $pdo->prepare("DELETE FROM boarding_houses WHERE id_kost = ?");
        $deleteStmt->execute([$idKost]);

        $pdo->commit();
        setFlash('Data kos dan seluruh data terkait (foto, log survei, riwayat) berhasil dihapus.', 'success');
    } catch (PDOException $e) {
        $pdo->rollBack();
        setFlash('Gagal menghapus kos dari database: ' . $e->getMessage(), 'danger');
    }
} else {
    setFlash('Metode akses tidak valid.', 'danger');
}

redirect(BASE_URL . '/pages/kost/index.php');
