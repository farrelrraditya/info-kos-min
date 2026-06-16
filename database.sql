-- ============================================================
-- InfoKosMin - Smart Boarding House Catalog Management Platform
-- Complete Database Export
-- Academic Project: UAS Praktikum Web + Final Project Basis Data
-- Academic Year: 2025/2026
-- Institution: Sekolah Vokasi Universitas Gadjah Mada
-- ============================================================
-- IMPORT INSTRUCTIONS (phpMyAdmin):
--   1. Open phpMyAdmin
--   2. Click "Import" tab
--   3. Choose this file
--   4. Click "Go"
-- ============================================================

-- ============================================================
-- BLOCK 1: DATABASE SETUP
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

DROP DATABASE IF EXISTS infokosmin;
CREATE DATABASE infokosmin
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE infokosmin;

-- ============================================================
-- BLOCK 2: TABLE DEFINITIONS (8 Tables)
-- Order: independent tables first, then dependent tables
-- ============================================================

-- Table 1: users
-- Purpose: Authentication. Stores admin credentials.
CREATE TABLE users (
    id_user       INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    username      VARCHAR(50)      NOT NULL,
    password_hash VARCHAR(255)     NOT NULL,
    role          ENUM('admin')    NOT NULL DEFAULT 'admin',
    created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_user),
    UNIQUE KEY uq_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 2: owners
-- Purpose: Stores landlord/owner contact information.
CREATE TABLE owners (
    id_owner     INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    owner_name   VARCHAR(100)  NOT NULL,
    phone_number VARCHAR(20)   NOT NULL,
    email        VARCHAR(100)  NULL DEFAULT NULL,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_owner)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 3: boarding_houses
-- Purpose: Main business entity. Central table of the catalog.
CREATE TABLE boarding_houses (
    id_kost             INT UNSIGNED                              NOT NULL AUTO_INCREMENT,
    id_owner            INT UNSIGNED                              NOT NULL,
    kost_name           VARCHAR(150)                              NOT NULL,
    address             TEXT                                      NOT NULL,
    district            VARCHAR(100)                              NOT NULL,
    monthly_price       DECIMAL(12,2)                             NOT NULL,
    room_size           VARCHAR(30)                               NULL DEFAULT NULL,
    gender_type         ENUM('male','female','mixed')             NOT NULL,
    curfew              VARCHAR(50)                               NULL DEFAULT NULL,
    is_furnished        TINYINT(1)                                NOT NULL DEFAULT 0,
    electricity_type    ENUM('token','fixed')                     NOT NULL,
    description         TEXT                                      NULL DEFAULT NULL,
    availability_status ENUM('available','full','unavailable')    NOT NULL DEFAULT 'available',
    created_at          TIMESTAMP                                 NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP                                 NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_kost),
    KEY idx_district (district),
    KEY idx_availability (availability_status),
    KEY idx_gender (gender_type),
    CONSTRAINT fk_bh_owner FOREIGN KEY (id_owner)
        REFERENCES owners (id_owner)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4: facilities
-- Purpose: Master data for facility types. Normalized lookup table.
CREATE TABLE facilities (
    id_facility   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    facility_name VARCHAR(100)  NOT NULL,
    PRIMARY KEY (id_facility),
    UNIQUE KEY uq_facility_name (facility_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 5: kost_facilities
-- Purpose: Many-to-many junction between boarding_houses and facilities.
CREATE TABLE kost_facilities (
    id_kost     INT UNSIGNED NOT NULL,
    id_facility INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_kost, id_facility),
    CONSTRAINT fk_kf_kost FOREIGN KEY (id_kost)
        REFERENCES boarding_houses (id_kost)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_kf_facility FOREIGN KEY (id_facility)
        REFERENCES facilities (id_facility)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 6: photos
-- Purpose: Stores file path metadata for categorized property photos.
CREATE TABLE photos (
    id_photo       INT UNSIGNED                                             NOT NULL AUTO_INCREMENT,
    id_kost        INT UNSIGNED                                             NOT NULL,
    photo_category ENUM('bedroom','bathroom','parking','kitchen','exterior') NOT NULL,
    photo_path     VARCHAR(255)                                             NOT NULL,
    uploaded_at    TIMESTAMP                                                NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_photo),
    KEY idx_photo_kost (id_kost),
    CONSTRAINT fk_photo_kost FOREIGN KEY (id_kost)
        REFERENCES boarding_houses (id_kost)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 7: survey_logs
-- Purpose: Tracks survey history per property. Auto-populated by Trigger 1.
CREATE TABLE survey_logs (
    id_log        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_kost       INT UNSIGNED NOT NULL,
    survey_date   DATE         NOT NULL,
    surveyor_note TEXT         NULL DEFAULT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_log),
    KEY idx_log_kost (id_kost),
    CONSTRAINT fk_log_kost FOREIGN KEY (id_kost)
        REFERENCES boarding_houses (id_kost)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 8: status_history
-- Purpose: Audit trail for availability status changes. Auto-populated by Trigger 2.
CREATE TABLE status_history (
    id_history INT UNSIGNED                                  NOT NULL AUTO_INCREMENT,
    id_kost    INT UNSIGNED                                  NOT NULL,
    old_status ENUM('available','full','unavailable')        NOT NULL,
    new_status ENUM('available','full','unavailable')        NOT NULL,
    changed_at TIMESTAMP                                     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_history),
    KEY idx_history_kost (id_kost),
    CONSTRAINT fk_history_kost FOREIGN KEY (id_kost)
        REFERENCES boarding_houses (id_kost)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BLOCK 3: VIEWS (2 Views)
-- ============================================================

-- View 1: view_available_boarding_houses
-- Purpose: Public catalog — only available properties with owner and cover photo.
-- Used by: index.php (public landing page)
CREATE VIEW view_available_boarding_houses AS
SELECT
    bh.id_kost,
    bh.kost_name,
    bh.address,
    bh.district,
    bh.monthly_price,
    bh.room_size,
    bh.gender_type,
    bh.is_furnished,
    bh.electricity_type,
    bh.availability_status,
    bh.created_at,
    o.id_owner,
    o.owner_name,
    o.phone_number,
    (
        SELECT p.photo_path
        FROM photos p
        WHERE p.id_kost = bh.id_kost
        ORDER BY p.uploaded_at ASC
        LIMIT 1
    ) AS cover_photo
FROM boarding_houses bh
INNER JOIN owners o ON bh.id_owner = o.id_owner
WHERE bh.availability_status = 'available';

-- View 2: view_kost_summary
-- Purpose: Admin dashboard analytics — aggregated stats per property.
-- Used by: pages/dashboard.php
CREATE VIEW view_kost_summary AS
SELECT
    bh.id_kost,
    bh.kost_name,
    bh.district,
    bh.monthly_price,
    bh.gender_type,
    bh.availability_status,
    bh.created_at,
    o.owner_name,
    o.phone_number,
    COUNT(DISTINCT kf.id_facility) AS facility_count,
    COUNT(DISTINCT ph.id_photo)    AS photo_count,
    MAX(sl.survey_date)            AS last_surveyed
FROM boarding_houses bh
INNER JOIN owners o ON bh.id_owner = o.id_owner
LEFT JOIN kost_facilities kf ON bh.id_kost = kf.id_kost
LEFT JOIN photos ph           ON bh.id_kost = ph.id_kost
LEFT JOIN survey_logs sl      ON bh.id_kost = sl.id_kost
GROUP BY
    bh.id_kost,
    bh.kost_name,
    bh.district,
    bh.monthly_price,
    bh.gender_type,
    bh.availability_status,
    bh.created_at,
    o.owner_name,
    o.phone_number;

-- ============================================================
-- BLOCK 4: ENABLE STORED FUNCTIONS (Required for XAMPP/MySQL)
-- ============================================================

SET GLOBAL log_bin_trust_function_creators = 1;

-- ============================================================
-- BLOCK 5: FUNCTIONS (2 Functions)
-- ============================================================

DELIMITER $$

-- Function 1: fn_total_facilities
-- Purpose: Returns the number of facilities assigned to a boarding house.
-- Used by: pages/kost/detail.php — displayed as Bootstrap Badge
CREATE FUNCTION fn_total_facilities(p_id_kost INT UNSIGNED)
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE total INT DEFAULT 0;
    SELECT COUNT(*)
    INTO   total
    FROM   kost_facilities
    WHERE  id_kost = p_id_kost;
    RETURN total;
END$$

-- Function 2: fn_estimated_yearly_cost
-- Purpose: Returns estimated annual rent cost (monthly_price * 12).
-- Used by: pages/kost/detail.php — displayed as cost estimate for tenants
CREATE FUNCTION fn_estimated_yearly_cost(p_id_kost INT UNSIGNED)
RETURNS DECIMAL(14,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE monthly DECIMAL(12,2) DEFAULT 0.00;
    SELECT monthly_price
    INTO   monthly
    FROM   boarding_houses
    WHERE  id_kost = p_id_kost;
    RETURN monthly * 12;
END$$

DELIMITER ;

-- ============================================================
-- BLOCK 6: TRIGGERS (2 Triggers)
-- ============================================================

DELIMITER $$

-- Trigger 1: trg_after_kost_insert
-- Fires: AFTER INSERT on boarding_houses
-- Action: Automatically creates initial survey log entry
-- Visible at: pages/survey/index.php
CREATE TRIGGER trg_after_kost_insert
AFTER INSERT ON boarding_houses
FOR EACH ROW
BEGIN
    INSERT INTO survey_logs (id_kost, survey_date, surveyor_note)
    VALUES (
        NEW.id_kost,
        CURDATE(),
        'Log survei awal — dibuat otomatis saat properti didaftarkan ke sistem.'
    );
END$$

-- Trigger 2: trg_after_kost_status_update
-- Fires: AFTER UPDATE on boarding_houses
-- Condition: Only when availability_status actually changes
-- Action: Records old and new status into status_history audit table
-- Visible at: pages/kost/history.php
CREATE TRIGGER trg_after_kost_status_update
AFTER UPDATE ON boarding_houses
FOR EACH ROW
BEGIN
    IF OLD.availability_status <> NEW.availability_status THEN
        INSERT INTO status_history (id_kost, old_status, new_status)
        VALUES (NEW.id_kost, OLD.availability_status, NEW.availability_status);
    END IF;
END$$

DELIMITER ;

-- ============================================================
-- BLOCK 7: SEED DATA
-- Minimum 5 records per major table as per submission requirement
-- ============================================================

-- Users (1 admin account)
-- Password: admin123 (hashed with PASSWORD_BCRYPT)
INSERT INTO users (username, password_hash, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Owners (3 records)
INSERT INTO owners (owner_name, phone_number, email) VALUES
('Budi Santoso',   '08123456789',  'budi.santoso@email.com'),
('Siti Rahayu',    '08234567890',  'siti.rahayu@email.com'),
('Ahmad Fauzi',    '08345678901',  'ahmad.fauzi@email.com');

-- Boarding Houses (5 records — triggers will auto-create survey_logs)
INSERT INTO boarding_houses
    (id_owner, kost_name, address, district, monthly_price, room_size,
     gender_type, curfew, is_furnished, electricity_type, description, availability_status)
VALUES
(1, 'Kost Melati Indah',
    'Jl. Kaliurang No. 12, Sleman',
    'Depok', 800000.00, '3x4',
    'female', '22:00 WIB', 1, 'token',
    'Kost khusus putri di area kampus UGM. Lingkungan aman dan nyaman dengan akses mudah ke fasilitas kampus.',
    'available'),

(1, 'Kost Putra Mandiri',
    'Jl. Colombo No. 5, Sleman',
    'Depok', 650000.00, '3x3',
    'male', '23:00 WIB', 0, 'fixed',
    'Kost putra sederhana dengan harga terjangkau. Cocok untuk mahasiswa yang mengutamakan lokasi strategis.',
    'available'),

(2, 'Griya Sejahtera',
    'Jl. Monjali No. 88, Sleman',
    'Mlati', 1200000.00, '4x5',
    'mixed', NULL, 1, 'token',
    'Kost mewah dengan fasilitas lengkap. Tersedia kamar luas untuk pasangan atau keluarga kecil.',
    'available'),

(2, 'Kost Barokah',
    'Jl. Godean Km 3, Sleman',
    'Godean', 500000.00, '3x3',
    'mixed', '22:00 WIB', 0, 'fixed',
    'Kost ekonomis dengan suasana tenang. Cocok untuk pekerja atau mahasiswa dengan budget terbatas.',
    'full'),

(3, 'Wisma Mahasiswa Seturan',
    'Jl. Seturan Raya No. 20, Sleman',
    'Depok', 950000.00, '3x4',
    'male', '23:30 WIB', 1, 'token',
    'Kost putra dekat kampus ATMA, UPN, dan UII. Akses internet cepat dan parkir luas.',
    'available');

-- Facilities (8 master records)
INSERT INTO facilities (facility_name) VALUES
('WiFi'),
('AC'),
('Laundry'),
('Dapur Bersama'),
('Parkir Motor'),
('Parkir Mobil'),
('CCTV'),
('Water Heater');

-- Kost Facilities (junction — varied per kost)
INSERT INTO kost_facilities (id_kost, id_facility) VALUES
-- Kost Melati Indah (id_kost=1): WiFi, AC, Laundry, Dapur, Parkir Motor, CCTV
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 7),
-- Kost Putra Mandiri (id_kost=2): WiFi, Parkir Motor
(2, 1), (2, 5),
-- Griya Sejahtera (id_kost=3): WiFi, AC, Laundry, Dapur, Parkir Motor, Parkir Mobil, CCTV, Water Heater
(3, 1), (3, 2), (3, 3), (3, 4), (3, 5), (3, 6), (3, 7), (3, 8),
-- Kost Barokah (id_kost=4): Parkir Motor
(4, 5),
-- Wisma Mahasiswa Seturan (id_kost=5): WiFi, AC, Parkir Motor, Parkir Mobil, CCTV
(5, 1), (5, 2), (5, 5), (5, 6), (5, 7);

-- Photos (10 records — metadata only, paths reference placeholder images)
INSERT INTO photos (id_kost, photo_category, photo_path) VALUES
(1, 'bedroom',  'kost_1_bedroom_001.jpg'),
(1, 'exterior', 'kost_1_exterior_001.jpg'),
(2, 'bedroom',  'kost_2_bedroom_001.jpg'),
(2, 'bathroom', 'kost_2_bathroom_001.jpg'),
(3, 'bedroom',  'kost_3_bedroom_001.jpg'),
(3, 'kitchen',  'kost_3_kitchen_001.jpg'),
(3, 'exterior', 'kost_3_exterior_001.jpg'),
(4, 'bedroom',  'kost_4_bedroom_001.jpg'),
(5, 'bedroom',  'kost_5_bedroom_001.jpg'),
(5, 'parking',  'kost_5_parking_001.jpg');

-- Survey Logs (manually seeded for safety — trigger also fires during INSERT above)
-- Note: trigger already created logs during boarding_houses INSERT.
-- These additional entries represent follow-up surveys.
INSERT INTO survey_logs (id_kost, survey_date, surveyor_note) VALUES
(1, '2025-03-15', 'Survei lanjutan. Kondisi bangunan baik. Pemilik kooperatif.'),
(2, '2025-03-16', 'Survei lanjutan. Perlu perbaikan saluran air kamar mandi.'),
(3, '2025-04-01', 'Survei lanjutan. Fasilitas lengkap sesuai klaim. Sangat direkomendasikan.'),
(5, '2025-04-10', 'Survei lanjutan. WiFi cepat terkonfirmasi. Parkir luas.');

-- Status History (sample audit trail — also produced by trigger on UPDATE)
INSERT INTO status_history (id_kost, old_status, new_status, changed_at) VALUES
(4, 'available', 'full',      '2025-05-01 09:00:00'),
(2, 'available', 'unavailable','2025-04-20 14:30:00'),
(2, 'unavailable','available', '2025-04-25 10:00:00');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END OF DATABASE EXPORT
-- InfoKosMin v1.0
-- ============================================================
-- Contoh Query Kompleks yang ada di PHP
SELECT
    bh.id_kost,
    bh.kost_name,
    o.owner_name,
    o.phone_number,
    COUNT(p.id_photo) AS total_photos
FROM boarding_houses bh
INNER JOIN owners o
    ON bh.id_owner = o.id_owner
LEFT JOIN photos p
    ON bh.id_kost = p.id_kost
GROUP BY
    bh.id_kost,
    bh.kost_name,
    o.owner_name,
    o.phone_number;

SELECT
    bh.kost_name,
    COUNT(kf.id_facility) AS total_facilities
FROM boarding_houses bh
LEFT JOIN kost_facilities kf
    ON bh.id_kost = kf.id_kost
GROUP BY bh.id_kost, bh.kost_name
ORDER BY total_facilities DESC;

SELECT
    district,
    COUNT(*) AS total_available
FROM boarding_houses
WHERE availability_status = 'available'
GROUP BY district
HAVING COUNT(*) > 0;

-- ============================================================
-- Query Untuk Percobaan Trigger


INSERT INTO boarding_houses
(
id_owner,
kost_name,
address,
district,
monthly_price,
gender_type,
electricity_type,
availability_status
)
VALUES
(
1,
'Demo Trigger 2',
'Jl Demo Trigger 2',
'Depok',
800000,
'female',
'token',
'available'
);

SELECT COUNT(*) AS total_history
FROM status_history;

UPDATE boarding_houses
SET availability_status='available'
WHERE id_kost=1;


