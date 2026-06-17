# InfoKosMin — Smart Boarding House Catalog

**InfoKosMin** adalah platform katalog kos pintar berbasis web yang dirancang untuk mempermudah pencarian hunian kos terbaik. Aplikasi ini dikembangkan sebagai proyek akademik untuk memenuhi kriteria pemrograman web praktis dan basis data, yang mengutamakan visualisasi data yang andal, struktur basis data ternormalisasi, dan alur administrasi data kos secara dinamis.

---

## Fitur Utama

1. **Katalog Publik (Landing Page)**: 
   - Pencarian dinamis berdasarkan nama kos, alamat, atau kecamatan.
   - Filter kos berdasarkan tipe penghuni (Putra, Putri, Campur) dan kecamatan.
   - Slider harga maksimal interaktif dengan pembaruan nilai langsung melalui JavaScript.
2. **Dashboard Analytics Admin**:
   - Panel visual dengan rangkuman data penting (Total Kos, Kos Tersedia, Pemilik, Fasilitas).
   - Menampilkan peringkat kos berdasarkan fasilitas (Complex Query 2).
   - Menampilkan peta persebaran kos tersedia per kecamatan (Complex Query 3).
   - Menampilkan ringkasan menyeluruh data kos melalui database View (view_kost_summary).
3. **Manajemen Kos (CRUD)**:
   - Form tambah dan edit kos dengan validasi JavaScript interaktif (validation.js).
   - Integrasi checkboxes fasilitas yang tersinkronisasi langsung ke junction table.
   - Audit riwayat perubahan status ketersediaan (Trigger 2).
4. **Manajemen Kontak Pemilik**:
   - CRUD data pemilik kos dengan nomor telepon dan email.
   - Proteksi basis data (`RESTRICT` constraint) untuk mencegah penghapusan pemilik jika masih memiliki kos terdaftar.
5. **Manajemen Fasilitas Master**:
   - CRUD fasilitas master yang dapat disematkan ke berbagai kos.
   - Proteksi pemrograman untuk mencegah penghapusan fasilitas jika sedang digunakan oleh data kos.
6. **Modul Unggah Foto (Photo Management)**:
   - Unggah foto dengan klasifikasi kategori (Kamar Tidur, Kamar Mandi, Parkir, Dapur, Eksterior).
   - Validasi file (format JPG/JPEG/PNG, batas ukuran file 2MB).
   - Galeri foto terkelompok per kategori untuk mempermudah pengelolaan.
7. **Modul Log Survei (Survey Management)**:
   - Pencatatan log kunjungan/survei properti secara berkala.
   - Penciptaan log survei awal otomatis melalui database trigger saat kos baru didaftarkan (Trigger 1).

---

## Teknologi yang Digunakan

- **Core Framework**: Native PHP (XAMPP PHP 8.x compatible)
- **Database**: MySQL / MariaDB (Prepared Statements PDO)
- **UI Framework**: Bootstrap 5.3 & Bootstrap Icons
- **Front-End Logic**: Vanilla JavaScript (DOM Manipulation, Live Filters, Dynamic Modals, Client-side Validations)

---

## Cara Menjalankan (Installation Guide)

### Prasyarat
- XAMPP v3.3+ (PHP 8.0 atau lebih baru)
- MySQL / MariaDB

### Langkah Instalasi
1. Clone repositori ini ke dalam direktori `htdocs` XAMPP Anda:
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/farrelrraditya/info-kos-min.git infokosmin
   ```
2. Pastikan file konfigurasi database di `includes/config.php` telah sesuai dengan setelan MySQL lokal Anda. Secara bawaan (default XAMPP):
   - **DB_HOST**: `localhost`
   - **DB_NAME**: `infokosmin`
   - **DB_USER**: `root`
   - **DB_PASS**: `""` (kosong)

### Setup Database
1. Aktifkan modul **Apache** dan **MySQL** di panel kontrol XAMPP.
2. Buka peramban (browser) dan akses `http://localhost/phpmyadmin`.
3. Buat database baru bernama `infokosmin` (atau biarkan script `database.sql` membuatnya secara otomatis).
4. Klik tab **Import**, pilih file `database.sql` yang berada di direktori akar (root) proyek ini, lalu klik **Go**.
5. Database akan otomatis terisi dengan skema tabel, data benih (seed data), view, stored functions, dan triggers.

---

## Struktur Folder

```
infokosmin/
│
├── index.php                          ← Landing page publik & pencarian katalog
├── database.sql                       ← File tunggal ekspor database lengkap
├── README.md                          ← Dokumentasi lengkap proyek
├── .gitignore                         ← Exclude berkas konfigurasi & berkas unggahan
│
├── assets/
│   ├── css/
│   │   └── style.css                  ← Kustomisasi CSS di atas Bootstrap 5
│   └── js/
│       ├── validation.js              ← Validasi form interaktif (client-side)
│       ├── search.js                  ← Pencarian live & filter katalog
│       └── confirm-delete.js          ← Handler modal konfirmasi hapus
│
├── uploads/
│   └── kost/                          ← Direktori penyimpanan berkas foto (gitignored)
│
├── includes/
│   ├── config.php                     ← Koneksi PDO database (gitignored)
│   ├── auth.php                       ← Proteksi sesi admin
│   ├── header.php                     ← Header bersama & Navigasi responsif
│   ├── footer.php                     ← Footer bersama & Modal hapus Bootstrap
│   └── functions.php                  ← Kumpulan fungsi pembantu (helper functions)
│
└── pages/
    ├── login.php                      ← Halaman masuk admin & password_verify()
    ├── logout.php                     ← Halaman keluar admin & destroy session
    ├── dashboard.php                  ← Halaman utama admin & ringkasan statistik
    │
    ├── kost/
    │   ├── index.php                  ← Kelola Kos: pencarian, filter, tabel admin
    │   ├── create.php                 ← Tambah Kos: form + fasilitas (Fires Trigger 1)
    │   ├── edit.php                   ← Edit Kos: form (Fires Trigger 2 jika status berubah)
    │   ├── delete.php                 ← Hapus Kos: hapus fisik foto + hapus database cascade
    │   ├── detail.php                 ← Detail Kos: detail lengkap publik (Query 1)
    │   └── history.php                ← Riwayat Kos: log audit status ketersediaan (Trigger 2)
    │
    ├── owner/
    │   ├── index.php                  ← Kelola Pemilik: daftar tabel paginasi
    │   ├── create.php                 ← Tambah Pemilik: form data pemilik
    │   ├── edit.php                   ← Edit Pemilik: form pembaruan data
    │   └── delete.php                 ← Hapus Pemilik: proteksi constraint RESTRICT
    │
    ├── facility/
    │   ├── index.php                  ← Kelola Fasilitas: daftar master fasilitas
    │   ├── create.php                 ← Tambah Fasilitas: form master fasilitas
    │   ├── edit.php                   ← Edit Fasilitas: form pembaruan master
    │   └── delete.php                 ← Hapus Fasilitas: proteksi pemrograman jika aktif
    │
    ├── photo/
    │   ├── index.php                  ← Kelola Foto: galeri foto terkelompok per kategori
    │   ├── upload.php                 ← Unggah Foto: validasi file & penyimpanan fisik + DB
    │   └── delete.php                 ← Hapus Foto: hapus file fisik & record DB
    │
    └── survey/
        ├── index.php                  ← Log Survei: daftar catatan survei (Trigger 1)
        └── edit.php                   ← Edit Log: memperbarui tanggal & catatan surveyor
```

---

## Objek Database (Database Objects)

### Tabel Skema (8 Tabel)
- `users`: Data autentikasi admin.
- `owners`: Data pemilik kos.
- `boarding_houses`: Data utama kos (relasi ke `owners`).
- `facilities`: Master data fasilitas.
- `kost_facilities`: Junction table relasi many-to-many kos dan fasilitas.
- `photos`: Berkas foto kos terklasifikasi (relasi ke `boarding_houses`).
- `survey_logs`: Catatan kunjungan berkala (relasi ke `boarding_houses`).
- `status_history`: Audit trail perubahan status ketersediaan (relasi ke `boarding_houses`).

### Views (2 Views)
1. `view_available_boarding_houses`: Menyediakan data kos yang berstatus 'available' lengkap dengan nama pemilik dan cover foto untuk katalog publik.
2. `view_kost_summary`: Mengagregasikan data jumlah fasilitas, jumlah foto, dan tanggal survei terakhir per kos untuk konsumsi dashboard admin.

### Stored Functions (2 Functions)
1. `fn_total_facilities(id_kost)`: Menghitung total fasilitas yang dimiliki sebuah kos. Ditampilkan sebagai badge di halaman detail kos.
2. `fn_estimated_yearly_cost(id_kost)`: Menghitung estimasi biaya tahunan kos (harga bulanan dikali 12). Ditampilkan di halaman detail kos.

### Triggers (2 Triggers)
1. `trg_after_kost_insert`: Otomatis memasukkan log awal ke `survey_logs` saat kos baru ditambahkan. Dapat diamati langsung di halaman **Log Survei**.
2. `trg_after_kost_status_update`: Otomatis merekam perubahan status ketersediaan (available/full/unavailable) ke `status_history` jika terjadi pembaruan status ketersediaan. Dapat diamati di halaman **Riwayat Status**.

### Complex Queries (3 Queries)
1. **Query 1** (JOIN 4 tabel + 2 functions): Berada di `pages/kost/detail.php` untuk merender informasi kos secara terperinci.
2. **Query 2** (GROUP BY + COUNT + ORDER BY): Berada di `pages/dashboard.php` untuk menampilkan peringkat kos berdasarkan jumlah fasilitas terbanyak.
3. **Query 3** (GROUP BY + HAVING): Berada di `pages/dashboard.php` untuk menganalisis ketersediaan kos per kecamatan.

---

## Akun Uji Coba (Demo Account)

Akses panel admin melalui menu **Login Admin** di navbar kanan atas atau langsung melalui alamat `/pages/login.php` dengan kredensial berikut:
- **Username**: `admin`
- **Password**: `password`

---

## Screenshots

| Halaman | Deskripsi / Preview |
|---------|---------------------|
| **Beranda** | Halaman katalog utama dengan filter harga, gender, pencarian, dan card kos. |
| **Daftar Kos** | Panel administrasi tabel kos yang dilengkapi fitur pencarian, filter, dan tombol aksi. |
| **Form Tambah** | Halaman formulir input kos dengan validasi interaktif dan pilihan checkbox fasilitas. |
| **Form Edit** | Halaman formulir perubahan data kos yang memicu audit Trigger status. |
| **Tampilan Mobile** | Visualisasi tata letak antarmuka yang responsif pada resolusi 375px–576px. |

---

## Lisensi
Proyek Akademik - Digunakan untuk keperluan ujian akhir semester (UAS) Pemrograman Web dan Basis Data Sekolah Vokasi Universitas Gadjah Mada.
