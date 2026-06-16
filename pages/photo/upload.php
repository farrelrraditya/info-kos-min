<?php
/**
 * InfoKosMin - Upload Photo Action
 * 
 * Handles file validation, directory storage, and database entry insertion.
 */

define('BASE_URL', '../..');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idKost        = (int)($_POST['id_kost'] ?? 0);
    $photoCategory = sanitizeInput($_POST['photo_category'] ?? '');

    // 1. Basic Parameter Validations
    if ($idKost <= 0) {
        setFlash('ID Kos tidak valid.', 'danger');
        redirect(BASE_URL . '/pages/kost/index.php');
    }

    $validCategories = ['bedroom', 'bathroom', 'parking', 'kitchen', 'exterior'];
    if (!in_array($photoCategory, $validCategories)) {
        setFlash('Kategori foto tidak valid.', 'danger');
        redirect('index.php?id_kost=' . $idKost);
    }

    // 2. File Upload Validations
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $errorCode = $_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE;
        $errorMessage = match($errorCode) {
            UPLOAD_ERR_INI_SIZE   => 'Ukuran file melebihi batas upload server (upload_max_filesize).',
            UPLOAD_ERR_FORM_SIZE  => 'Ukuran file melebihi batas formulir.',
            UPLOAD_ERR_PARTIAL    => 'File hanya terunggah sebagian.',
            UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang dipilih.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temp server hilang.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk server.',
            default               => 'Terjadi kesalahan upload yang tidak diketahui.'
        };
        setFlash('Gagal mengunggah foto: ' . $errorMessage, 'danger');
        redirect('index.php?id_kost=' . $idKost);
    }

    $file = $_FILES['photo'];
    
    // Check Size (Limit to 2MB)
    $maxSize = 2 * 1024 * 1024; // 2 MB in bytes
    if ($file['size'] > $maxSize) {
        setFlash('Gagal mengunggah: Ukuran foto terlalu besar (maksimal 2MB).', 'danger');
        redirect('index.php?id_kost=' . $idKost);
    }

    // Check MIME Type / Extension
    $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
    $fileMime = mime_content_type($file['tmp_name']);
    
    if (!in_array($fileMime, $allowedMimes)) {
        setFlash('Gagal mengunggah: Format file tidak didukung. Gunakan format JPG, JPEG, atau PNG.', 'danger');
        redirect('index.php?id_kost=' . $idKost);
    }

    // Get extension safely
    $pathInfo = pathinfo($file['name']);
    $extension = strtolower($pathInfo['extension'] ?? 'jpg');
    if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
        $extension = 'jpg';
    }

    // 3. Move File and Insert to DB
    $uniqueName = 'kost_' . $idKost . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $uploadDir  = __DIR__ . '/../../uploads/kost/';
    
    // Make sure directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $targetPath = $uploadDir . $uniqueName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO photos (id_kost, photo_category, photo_path) VALUES (?, ?, ?)");
            $stmt->execute([$idKost, $photoCategory, $uniqueName]);
            
            setFlash('Foto berhasil diunggah dan disimpan ke kategori.', 'success');
        } catch (PDOException $e) {
            // Cleanup physical file on DB failure
            @unlink($targetPath);
            setFlash('Gagal menyimpan metadata foto ke database: ' . $e->getMessage(), 'danger');
        }
    } else {
        setFlash('Gagal menulis file foto ke folder uploads. Periksa hak akses folder.', 'danger');
    }

    redirect('index.php?id_kost=' . $idKost);
} else {
    setFlash('Metode akses tidak valid.', 'danger');
    redirect(BASE_URL . '/pages/kost/index.php');
}
