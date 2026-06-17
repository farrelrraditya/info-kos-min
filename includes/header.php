<?php
/**
 * InfoKosMin - Shared Header
 * 
 * Outputs the HTML <head> section and the responsive Bootstrap navbar.
 * Requires: functions.php must be included before this file.
 * 
 * Variables expected from including page:
 *   $pageTitle (string) - shown in <title> tag
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? 'InfoKosMin';
$loggedIn  = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="InfoKosMin — Platform Katalog Kos Pintar">
    <title><?= h($pageTitle) ?> — InfoKosMin</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >
    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS with cache busting -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>">
</head>
<body>

<!-- =====================================================
     NAVBAR — Bootstrap responsive navbar with collapse
     Covers: B2 (Navbar responsif), B3 (Navbar component)
     ===================================================== -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-3 shadow-sm">
    <div class="container">

        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>/index.php">
            <img src="<?= BASE_URL ?>/assets/img/Main Logo.png" alt="InfoKosMin Logo" class="brand-logo" style="height: 36px; object-fit: contain;">
        </a>

        <!-- Mobile toggle button -->
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarMain"
            aria-controls="navbarMain"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Collapsible nav links -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/index.php">
                        <i class="bi bi-house me-1"></i>Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/catalog.php">
                        <i class="bi bi-search me-1"></i>Cari Kos
                    </a>
                </li>
            </ul>

            <!-- Right side: Admin or Login -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <?php if ($loggedIn): ?>
                    <!-- Admin dropdown -->
                    <li class="nav-item dropdown">
                        <a
                            class="nav-link dropdown-toggle"
                            href="#"
                            id="adminDropdown"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <i class="bi bi-person-circle me-1"></i>
                            <?= h($_SESSION['username'] ?? 'Admin') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/pages/dashboard.php">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/pages/kost/index.php">
                                    <i class="bi bi-building me-2"></i>Kelola Kos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/pages/owner/index.php">
                                    <i class="bi bi-people me-2"></i>Kelola Pemilik
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/pages/facility/index.php">
                                    <i class="bi bi-grid me-2"></i>Kelola Fasilitas
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/pages/logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-primary rounded-pill px-4 py-2 text-decoration-none d-inline-flex align-items-center" href="<?= BASE_URL ?>/pages/login.php" style="font-size: 0.9rem; font-weight: 600;">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login Admin
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div><!-- /.collapse -->

    </div><!-- /.container -->
</nav>
<!-- /.navbar -->
