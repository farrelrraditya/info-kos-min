<?php
/**
 * InfoKosMin - Admin Login Page
 *
 * Handles admin authentication.
 * Uses password_verify() and PHP sessions.
 * Redirects to dashboard on success.
 */

define('BASE_URL', '..');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Already logged in → redirect to dashboard
if (isLoggedIn()) {
    redirect(BASE_URL . '/pages/dashboard.php');
}

$errors = [];

// ─── Handle POST (login submission) ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Server-side validation
    if ($username === '') {
        $errors['username'] = 'Username tidak boleh kosong.';
    }
    if ($password === '') {
        $errors['password'] = 'Password tidak boleh kosong.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter.';
    }

    if (empty($errors)) {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT id_user, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Authentication successful — create session
            session_regenerate_id(true); // prevent session fixation
            $_SESSION['user_id']  = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            // Redirect to intended page or dashboard
            $redirectTo = $_SESSION['redirect_after_login'] ?? BASE_URL . '/pages/dashboard.php';
            unset($_SESSION['redirect_after_login']);
            redirect($redirectTo);
        } else {
            $errors['general'] = 'Username atau password salah.';
        }
    }
}

$pageTitle = 'Login Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> — InfoKosMin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS with cache busting -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-12 col-sm-9 col-md-7 col-lg-5 col-xl-4">

            <!-- Brand -->
            <div class="text-center mb-4">
                <a href="<?= BASE_URL ?>/index.php" class="text-decoration-none d-inline-flex flex-column align-items-center">
                    <img src="<?= BASE_URL ?>/assets/img/Main Logo.png" alt="InfoKosMin Logo" style="height: 54px;" class="mb-2">
                    <h4 class="fw-bold text-dark mb-0">InfoKosMin</h4>
                </a>
                <p class="text-muted small">Admin Panel</p>
            </div>

            <!-- Login Card -->
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4 text-center fw-bold text-dark">Masuk ke Akun Admin</h5>

                    <!-- General error (wrong credentials) -->
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?= h($errors['general']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form id="loginForm" method="POST" action="login.php" novalidate>

                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold small text-muted">
                                Username <span class="text-danger">*</span>
                            </label>
                            <div class="input-group border rounded-3 overflow-hidden bg-white">
                                <span class="input-group-text border-0 bg-transparent text-muted"><i class="bi bi-person"></i></span>
                                <input
                                    type="text"
                                    class="form-control border-0 shadow-none <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                                    id="username"
                                    name="username"
                                    value="<?= h($_POST['username'] ?? '') ?>"
                                    placeholder="Masukkan username"
                                    autocomplete="username"
                                    required
                                >
                            </div>
                            <!-- Inline error container (DOM manipulation target) -->
                            <div id="error-username" class="text-danger small mt-1 <?= isset($errors['username']) ? '' : 'd-none' ?>">
                                <?= h($errors['username'] ?? '') ?>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold small text-muted">
                                Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group border rounded-3 overflow-hidden bg-white">
                                <span class="input-group-text border-0 bg-transparent text-muted"><i class="bi bi-lock"></i></span>
                                <input
                                    type="password"
                                    class="form-control border-0 shadow-none <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                    id="password"
                                    name="password"
                                    placeholder="Masukkan password"
                                    autocomplete="current-password"
                                    required
                                >
                                <button class="btn btn-outline-secondary border-0 bg-transparent text-muted" type="button" id="togglePassword" title="Tampilkan password">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                            <div id="error-password" class="text-danger small mt-1 <?= isset($errors['password']) ? '' : 'd-none' ?>">
                                <?= h($errors['password'] ?? '') ?>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary rounded-pill py-2.5 fw-semibold">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                            </button>
                        </div>

                    </form>

                    <div class="text-center mt-3">
                        <a href="<?= BASE_URL ?>/index.php" class="text-muted small text-decoration-none d-inline-flex align-items-center">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda
                        </a>
                    </div>

                    <!-- Demo credentials hint -->
                    <div class="alert alert-light border mt-4 mb-0 small rounded-3">
                        <i class="bi bi-info-circle me-1 text-primary"></i>
                        <strong>Demo:</strong> username: <code>admin</code> | password: <code>admin123</code>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/validation.js"></script>

<!-- Password visibility toggle -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn  = document.getElementById('togglePassword');
    const passInput  = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            const isPass = passInput.type === 'password';
            passInput.type = isPass ? 'text' : 'password';
            toggleIcon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }
});
</script>
</body>
</html>
