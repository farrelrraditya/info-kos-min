<?php
/**
 * InfoKosMin - Public Landing Page & Catalog
 *
 * This is the main entry point for guest users.
 * Displays the hero section, search form, and paginated kost catalog.
 * Queries: view_available_boarding_houses
 */

// Define base URL dynamically for all asset/link references
$base_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', $base_dir === '/' ? '' : $base_dir);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = getDB();

// Fetch total room count for statistics
$totalRows = (int)$pdo->query("SELECT COUNT(*) FROM boarding_houses WHERE availability_status = 'available'")->fetchColumn();

// Fetch distinct districts count for statistics
$distStmt = $pdo->query("SELECT DISTINCT district FROM boarding_houses WHERE availability_status='available' ORDER BY district");
$districts = $distStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch featured boarding houses (stable list of 4 items)
$featStmt = $pdo->prepare("
    SELECT bh.id_kost, bh.kost_name, bh.district, bh.monthly_price, bh.gender_type, bh.is_furnished, bh.availability_status,
           (SELECT p.photo_path FROM photos p WHERE p.id_kost = bh.id_kost ORDER BY p.uploaded_at ASC LIMIT 1) AS cover_photo
    FROM boarding_houses bh
    WHERE bh.availability_status = 'available'
    ORDER BY bh.created_at DESC
    LIMIT 4
");
$featStmt->execute();
$featuredKosts = $featStmt->fetchAll();

$pageTitle = 'Beranda';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<main class="main-content">

<!-- ================================================================
     HERO SECTION
     ================================================================ -->
<section class="hero-section-custom" id="home">
    <div class="container">
        <div class="row align-items-center justify-content-between g-4">
            <!-- Left Stats (Desktop only) -->
            <div class="col-lg-2 d-none d-lg-block">
                <div class="hero-stats-box mb-4">
                    <div class="hero-stats-number">408+</div>
                    <div class="hero-stats-label">Happy Users</div>
                </div>
                <div class="hero-stats-box">
                    <div class="hero-stats-number">4.9</div>
                    <div class="hero-stats-label">Client Ratings</div>
                </div>
            </div>

            <!-- Central Heading -->
            <div class="col-lg-8 text-center px-4">
                <span class="section-tag mb-3">Katalog Kos Pintar</span>
                <h1 class="display-4 fw-bold mb-3 text-dark" style="letter-spacing: -0.02em; line-height: 1.2;">
                    Hunian Kos Nyaman,<br>Mulai Cerita Anda Di Sini.
                </h1>
                <p class="text-muted lead mx-auto mb-4" style="max-width: 620px; font-size: 1.05rem; line-height: 1.6;">
                    Temukan kos terbaik dengan informasi terverifikasi, detail fasilitas lengkap, foto kamar aktual, dan komunikasi langsung ke pemilik kos.
                </p>
                <div class="d-flex justify-content-center gap-3 mb-4 mb-md-5 mb-lg-0">
                    <a href="catalog.php" class="btn btn-primary rounded-pill px-4 py-2" style="font-weight: 600;">
                        Cari Kos Sekarang <i class="bi bi-arrow-down ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Right Stats (Desktop only) -->
            <div class="col-lg-2 d-none d-lg-block text-end">
                <div class="hero-stats-box mb-4">
                    <div class="hero-stats-number"><?= $totalRows ?>+</div>
                    <div class="hero-stats-label">Verified Rooms</div>
                </div>
                <div class="hero-stats-box">
                    <div class="hero-stats-number"><?= count($districts) ?>+</div>
                    <div class="hero-stats-label">Neighborhoods</div>
                </div>
            </div>

            <!-- Mobile Stats Row -->
            <div class="col-12 d-block d-lg-none mt-5 mb-4 text-center">
                <div class="row g-3 justify-content-center">
                    <div class="col-3 border-end">
                        <div class="fw-bold fs-4 text-dark">408+</div>
                        <div class="text-muted small" style="font-size: 0.7rem;">Happy Users</div>
                    </div>
                    <div class="col-3 border-end">
                        <div class="fw-bold fs-4 text-dark">4.9</div>
                        <div class="text-muted small" style="font-size: 0.7rem;">Ratings</div>
                    </div>
                    <div class="col-3 border-end">
                        <div class="fw-bold fs-4 text-dark"><?= $totalRows ?>+</div>
                        <div class="text-muted small" style="font-size: 0.7rem;">Rooms</div>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold fs-4 text-dark"><?= count($districts) ?>+</div>
                        <div class="text-muted small" style="font-size: 0.7rem;">Districts</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================================================================
     POLAROID CASCADE SHOWCASE
     ================================================================ -->
<div class="polaroid-cascade-container">
    <div class="container">
        <div class="polaroid-row">
            <div class="polaroid-card">
                <img src="<?= BASE_URL ?>/uploads/kost/polaroid_1.jfif" alt="Polaroid 1">
            </div>
            <div class="polaroid-card">
                <img src="<?= BASE_URL ?>/uploads/kost/polaroid_2.jfif" alt="Polaroid 2">
            </div>
            <div class="polaroid-card">
                <img src="<?= BASE_URL ?>/uploads/kost/polaroid_3.jfif" alt="Polaroid 3">
            </div>
            <div class="polaroid-card">
                <img src="<?= BASE_URL ?>/uploads/kost/polaroid_4.jfif" alt="Polaroid 4">
            </div>
            <div class="polaroid-card">
                <img src="<?= BASE_URL ?>/uploads/kost/polaroid_5.jfif" alt="Polaroid 5">
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     DWELLING ACHIEVEMENTS SECTION (Dark BG)
     ================================================================ -->
<section class="achievements-section">
    <div class="container text-center py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-3 fs-1 text-white">Apa itu InfoKosMin?</h2>
                <p class="lead fs-6 mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                    Platform direktori kos pintar kami dikembangkan khusus untuk mempermudah civitas akademika dan pekerja. Dengan komitmen keakuratan data visual dan verifikasi survei langsung, InfoKosMin hadir memberikan ketenangan dalam memilih hunian baru Anda.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ================================================================
     FEATURED LISTINGS SECTION
     ================================================================ -->
<section class="py-5 bg-white border-bottom">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-5">
            <div>
                <span class="section-tag">Featured Listing</span>
                <h2 class="fw-bold mb-0 fs-1">Rekomendasi Kos Pilihan</h2>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="catalog.php" class="btn btn-outline-primary rounded-pill px-4">
                    Lebih lanjut <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <?php if (empty($featuredKosts)): ?>
                <div class="col-12 text-center py-4 text-muted">Belum ada kos terdaftar.</div>
            <?php else: ?>
                <?php foreach ($featuredKosts as $kost): ?>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="kost-card-custom">
                            <div class="kost-card-img-wrapper">
                                <div class="kost-card-badge-container">
                                    <span class="kost-badge-status status-<?= h($kost['availability_status']) ?>">
                                        <?= statusLabel($kost['availability_status']) ?>
                                    </span>
                                    <span class="kost-badge-gender gender-<?= h($kost['gender_type']) ?>">
                                        <?= genderLabel($kost['gender_type']) ?>
                                    </span>
                                </div>
                                <?php if ($kost['cover_photo']): ?>
                                    <img
                                        src="<?= BASE_URL ?>/uploads/kost/<?= h($kost['cover_photo']) ?>"
                                        alt="<?= h($kost['kost_name']) ?>"
                                        loading="lazy"
                                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                                    >
                                    <div class="w-100 h-100 d-none align-items-center justify-content-center bg-light text-muted" style="font-size: 3rem;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-muted" style="font-size: 3rem;">
                                        <i class="bi bi-building"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?= h($kost['kost_name']) ?>"><?= h($kost['kost_name']) ?></h5>
                                <p class="text-muted small mb-3"><i class="bi bi-geo-alt me-1 text-danger"></i>Kec. <?= h($kost['district']) ?></p>
                                <div class="kost-card-price mt-auto">
                                    <?= formatRupiah($kost['monthly_price']) ?> <span>/bulan</span>
                                </div>
                                <a href="pages/kost/detail.php?id=<?= $kost['id_kost'] ?>" class="btn btn-primary btn-sm rounded-pill w-100 mt-3 py-2">
                                    Lihat Detail <i class="bi bi-eye ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ================================================================
     OUR VISION & MISSION SECTION
     ================================================================ -->
<section class="py-5 bg-light border-bottom">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="section-tag">Visi & Misi</span>
            <h2 class="fw-bold mb-0 fs-1">Fokus & Nilai InfoKosMin</h2>
        </div>
        <div class="row g-4">
            <!-- Visi -->
            <div class="col-md-6">
                <div class="card vision-mission-card border-0">
                    <img src="<?= BASE_URL ?>/assets/img/vision_illustration.jfif" class="vision-mission-img" alt="Visi Kami">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-2"><i class="bi bi-eye text-primary me-2"></i>Visi Kami</h4>
                        <p class="text-muted small mb-0" style="line-height: 1.7;">
                            Menjadi platform direktori kos utama dan paling terpercaya, membantu pencari hunian mendapatkan kos terbaik dengan visual aktual terverifikasi, serta menghubungkan pemilik secara langsung tanpa perantara.
                        </p>
                    </div>
                </div>
            </div>
            <!-- Misi -->
            <div class="col-md-6">
                <div class="card vision-mission-card border-0">
                    <img src="<?= BASE_URL ?>/assets/img/mision_illustration.jfif" class="vision-mission-img" alt="Misi Kami">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-2"><i class="bi bi-compass text-primary me-2"></i>Misi Kami</h4>
                        <p class="text-muted small mb-0" style="line-height: 1.7;">
                            Menyajikan visualisasi kamar terperinci, melakukan survei berkala kelayakan fasilitas, dan memangkas hambatan birokrasi komunikasi sewa lewat integrasi WhatsApp pemilik properti.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================================================================
     3 CORE VALUES SECTION
     ================================================================ -->
<section class="py-5 bg-white border-bottom">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="section-tag">Brand Values</span>
            <h2 class="fw-bold mb-0 fs-1">Kelebihan Menggunakan InfoKosMin</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="value-card">
                    <div class="value-icon-circle"><i class="bi bi-search"></i></div>
                    <h4 class="fw-bold mb-3">Cari Kos Instan</h4>
                    <p class="text-muted small mb-4" style="line-height: 1.6;">
                        Gunakan filter pencarian pintar kami berdasarkan kecamatan, batasan harga sewa bulanan, dan tipe gender kos (Putra/Putri/Campur) dalam satu klik.
                    </p>
                    <a href="catalog.php" class="value-link">Lebih detail <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card">
                    <div class="value-icon-circle"><i class="bi bi-patch-check"></i></div>
                    <h4 class="fw-bold mb-3">Detail Terverifikasi</h4>
                    <p class="text-muted small mb-4" style="line-height: 1.6;">
                        Tiap data spesifikasi kos, fasilitas terdaftar, serta koordinat lokasi diverifikasi dan disurvei berkala di lapangan oleh tim surveyor kami.
                    </p>
                    <a href="catalog.php" class="value-link">Lebih detail <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card">
                    <div class="value-icon-circle"><i class="bi bi-whatsapp"></i></div>
                    <h4 class="fw-bold mb-3">Hubungi Langsung</h4>
                    <p class="text-muted small mb-4" style="line-height: 1.6;">
                        Tanpa biaya administrasi atau calo perantara. Hubungi langsung kontak WhatsApp pemilik kos dengan template pesan otomatis sekali ketuk.
                    </p>
                    <a href="catalog.php" class="value-link">Lebih detail <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================================================================
     TESTIMONIALS SECTION
     ================================================================ -->
<section class="py-5 bg-light border-bottom">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="section-tag">Testimonials</span>
            <h2 class="fw-bold mb-0 fs-1">Apa Kata Pengguna Kami?</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial-card-custom">
                    <div class="testimonial-quote-icon"><i class="bi bi-quote"></i></div>
                    <p class="testimonial-text">
                        "Sangat mempermudah cari kos dekat Sekolah Vokasi UGM. Fotonya lengkap per kategori dari kamar tidur sampai parkiran. Sangat informatif!"
                    </p>
                    <div class="d-flex align-items-center mt-auto">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px;">
                            <i class="bi bi-person fs-5"></i>
                        </div>
                        <div>
                            <div class="testimonial-author-name">Rian Adi</div>
                            <div class="testimonial-author-role">Mahasiswa UGM</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card-custom">
                    <div class="testimonial-quote-icon"><i class="bi bi-quote"></i></div>
                    <p class="testimonial-text">
                        "Log survei fisik di web ini sangat meyakinkan. Saya langsung tahu kapan kos disurvei terakhir kali oleh tim InfoKosMin. Data fasilitasnya akurat."
                    </p>
                    <div class="d-flex align-items-center mt-auto">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px;">
                            <i class="bi bi-person fs-5"></i>
                        </div>
                        <div>
                            <div class="testimonial-author-name">Siti Alya</div>
                            <div class="testimonial-author-role">Pekerja Kantor (Seturan)</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card-custom">
                    <div class="testimonial-quote-icon"><i class="bi bi-quote"></i></div>
                    <p class="testimonial-text">
                        "Filter harga dan fasilitasnya akurat sekali. Saya langsung dapat kos putra dengan listrik token dan kasur furnished sesuai budget sewa bulanan."
                    </p>
                    <div class="d-flex align-items-center mt-auto">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px;">
                            <i class="bi bi-person fs-5"></i>
                        </div>
                        <div>
                            <div class="testimonial-author-name">Dwi Prasetya</div>
                            <div class="testimonial-author-role">Mahasiswa Kampus Sleman</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================================================================
     CTA BANNER SECTION
     ================================================================ -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="card cta-banner-card text-center text-white border-0">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="display-5 fw-bold text-white mb-3">Pintu Gerbang Anda Menuju Hunian Kos Terbaik</h2>
                    <p class="text-white-50 lead fs-6 mb-4 mx-auto" style="max-width: 600px;">
                        Hubungi kami untuk mendapatkan layanan survei eksklusif atau untuk berkonsultasi mengenai hunian kos impian Anda.
                    </p>
                    <a href="catalog.php" class="btn btn-light btn-lg rounded-pill px-5 py-3" style="font-weight: 700; font-size: 1rem; color: var(--color-primary);">
                        Mulai Cari Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
