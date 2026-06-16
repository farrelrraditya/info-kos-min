<?php
/**
 * InfoKosMin - Authentication Guard
 * 
 * Include this file at the TOP of every admin-only page.
 * It starts the session and redirects unauthenticated users to login.
 * 
 * Usage:
 *   require_once '../../includes/auth.php';  // adjust path as needed
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    // Store intended destination so we can redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
    header('Location: ' . BASE_URL . '/pages/login.php?error=auth');
    exit;
}
