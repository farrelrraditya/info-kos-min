<?php
/**
 * InfoKosMin - Global Helper Functions
 * 
 * Provides reusable utility functions used across the entire application.
 * All output of user-generated data must go through h() before echoing.
 */

/**
 * Sanitize output: wraps htmlspecialchars for safe HTML output.
 * Must be used on ALL user-generated content before echoing to browser.
 *
 * @param  mixed  $value
 * @return string
 */
function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Redirect to a URL and stop execution.
 *
 * @param string $url
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * Store a flash message in session to be displayed on the next page load.
 *
 * @param string $message
 * @param string $type    Bootstrap alert type: success|danger|warning|info
 */
function setFlash(string $message, string $type = 'success'): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

/**
 * Retrieve and clear the stored flash message.
 * Returns null if no flash message exists.
 *
 * @return array|null ['message' => string, 'type' => string]
 */
function getFlash(): ?array {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Render a Bootstrap alert div for a flash message if one exists.
 * Call this once at the top of every page content area.
 */
function showFlash(): void {
    $flash = getFlash();
    if ($flash) {
        echo '<div class="alert alert-' . h($flash['type'])
           . ' alert-dismissible fade show" role="alert">'
           . h($flash['message'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
           . '</div>';
    }
}

/**
 * Format a number as Indonesian Rupiah currency string.
 *
 * @param  float  $amount
 * @return string  e.g. "Rp 1.200.000"
 */
function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Calculate pagination metadata.
 *
 * @param  int $totalRows    Total number of records
 * @param  int $currentPage  Current page number (1-based)
 * @param  int $perPage      Records per page
 * @return array {totalRows, totalPages, currentPage, perPage, offset}
 */
function paginate(int $totalRows, int $currentPage, int $perPage = 10): array {
    $totalPages  = (int) ceil($totalRows / $perPage);
    $currentPage = max(1, min($currentPage, max(1, $totalPages)));
    $offset      = ($currentPage - 1) * $perPage;

    return [
        'totalRows'   => $totalRows,
        'totalPages'  => $totalPages,
        'currentPage' => $currentPage,
        'perPage'     => $perPage,
        'offset'      => $offset,
    ];
}

/**
 * Render Bootstrap pagination links.
 *
 * @param array  $pager   Output of paginate()
 * @param string $baseUrl Base URL without page param, e.g. "index.php?search=foo"
 */
function renderPagination(array $pager, string $baseUrl): void {
    if ($pager['totalPages'] <= 1) return;

    $sep = (strpos($baseUrl, '?') !== false) ? '&' : '?';
    echo '<nav aria-label="Navigasi halaman"><ul class="pagination justify-content-center flex-wrap">';

    // Previous
    if ($pager['currentPage'] > 1) {
        $prev = $pager['currentPage'] - 1;
        echo '<li class="page-item">'
           . '<a class="page-link" href="' . h($baseUrl . $sep . 'page=' . $prev) . '">&laquo; Sebelumnya</a>'
           . '</li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">&laquo; Sebelumnya</span></li>';
    }

    // Page numbers
    $start = max(1, $pager['currentPage'] - 2);
    $end   = min($pager['totalPages'], $pager['currentPage'] + 2);

    if ($start > 1) {
        echo '<li class="page-item"><a class="page-link" href="' . h($baseUrl . $sep . 'page=1') . '">1</a></li>';
        if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
    }

    for ($i = $start; $i <= $end; $i++) {
        $active = ($i === $pager['currentPage']) ? ' active' : '';
        echo '<li class="page-item' . $active . '">'
           . '<a class="page-link" href="' . h($baseUrl . $sep . 'page=' . $i) . '">' . $i . '</a>'
           . '</li>';
    }

    if ($end < $pager['totalPages']) {
        if ($end < $pager['totalPages'] - 1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
        echo '<li class="page-item"><a class="page-link" href="' . h($baseUrl . $sep . 'page=' . $pager['totalPages']) . '">' . $pager['totalPages'] . '</a></li>';
    }

    // Next
    if ($pager['currentPage'] < $pager['totalPages']) {
        $next = $pager['currentPage'] + 1;
        echo '<li class="page-item">'
           . '<a class="page-link" href="' . h($baseUrl . $sep . 'page=' . $next) . '">Selanjutnya &raquo;</a>'
           . '</li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">Selanjutnya &raquo;</span></li>';
    }

    echo '</ul></nav>';
}

/**
 * Return Bootstrap badge class for availability status.
 *
 * @param  string $status
 * @return string  Bootstrap bg-* class
 */
function statusBadgeClass(string $status): string {
    return match($status) {
        'available'   => 'bg-success',
        'full'        => 'bg-danger',
        'unavailable' => 'bg-secondary',
        default       => 'bg-dark',
    };
}

/**
 * Return human-readable label for availability status (Bahasa Indonesia).
 *
 * @param  string $status
 * @return string
 */
function statusLabel(string $status): string {
    return match($status) {
        'available'   => 'Tersedia',
        'full'        => 'Penuh',
        'unavailable' => 'Tidak Tersedia',
        default       => ucfirst($status),
    };
}

/**
 * Return human-readable label for gender type (Bahasa Indonesia).
 *
 * @param  string $gender
 * @return string
 */
function genderLabel(string $gender): string {
    return match($gender) {
        'male'   => 'Putra',
        'female' => 'Putri',
        'mixed'  => 'Campur',
        default  => ucfirst($gender),
    };
}

/**
 * Return Bootstrap badge class for gender type.
 *
 * @param  string $gender
 * @return string
 */
function genderBadgeClass(string $gender): string {
    return match($gender) {
        'male'   => 'bg-primary',
        'female' => 'bg-danger',
        'mixed'  => 'bg-warning text-dark',
        default  => 'bg-secondary',
    };
}

/**
 * Check whether the current admin session is valid.
 * Returns true if logged in, false otherwise.
 *
 * @return bool
 */
function isLoggedIn(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Sanitize a string input for use in application logic.
 * Trims whitespace. Output still requires h() before echoing.
 *
 * @param  string $input
 * @return string
 */
function sanitizeInput(string $input): string {
    return trim($input);
}
