<?php
/**
 * InfoKosMin - Delete Photo Action
 * 
 * POST-only route. Removes physical image from upload folder and deletes database record.
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmed']) && $_POST['confirmed'] == 1) {
    $idPhoto = (int)($_GET['id'] ?? 0);
    $idKost  = (int)($_GET['id_kost'] ?? 0);
    
    if ($idPhoto <= 0 || $idKost <= 0) {
        setFlash('Parameter penghapusan foto tidak valid.', 'danger');
        redirect(BASE_URL . '/pages/kost/index.php');
    }

    try {
        // Fetch photo info to get path
        $stmtPhoto = $pdo->prepare("SELECT photo_path FROM photos WHERE id_photo = ? AND id_kost = ?");
        $stmtPhoto->execute([$idPhoto, $idKost]);
        $photoPath = $stmtPhoto->fetchColumn();

        if ($photoPath) {
            // Delete from database
            $deleteStmt = $pdo->prepare("DELETE FROM photos WHERE id_photo = ?");
            $deleteStmt->execute([$idPhoto]);

            // Delete physical file
            $filePath = __DIR__ . '/../../uploads/kost/' . $photoPath;
            if (file_exists($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }

            setFlash('Foto berhasil dihapus.', 'success');
        } else {
            setFlash('Data foto tidak ditemukan.', 'danger');
        }
    } catch (PDOException $e) {
        setFlash('Gagal menghapus foto dari database: ' . $e->getMessage(), 'danger');
    }
    
    redirect('index.php?id_kost=' . $idKost);
} else {
    setFlash('Metode akses tidak valid.', 'danger');
    redirect(BASE_URL . '/pages/kost/index.php');
}
