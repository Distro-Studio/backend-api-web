-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 09, 2024 at 02:04 PM
-- Server version: 10.5.22-MariaDB-cll-lve
-- PHP Version: 8.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `disz7965_webhr`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `activity` text NOT NULL,
  `kategori_activity_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `berkas`
--

CREATE TABLE `berkas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `file_id` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `kategori_berkas_id` bigint(20) UNSIGNED NOT NULL,
  `status_berkas_id` bigint(20) UNSIGNED NOT NULL,
  `path` varchar(255) NOT NULL,
  `tgl_upload` datetime NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `ext` varchar(255) DEFAULT NULL,
  `size` varchar(255) DEFAULT NULL,
  `verifikator_1` bigint(20) UNSIGNED DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cutis`
--

CREATE TABLE `cutis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tipe_cuti_id` bigint(20) UNSIGNED NOT NULL,
  `tgl_from` varchar(255) NOT NULL,
  `tgl_to` varchar(255) NOT NULL,
  `catatan` text DEFAULT NULL,
  `durasi` int(11) NOT NULL,
  `status_cuti_id` bigint(20) UNSIGNED NOT NULL,
  `verifikator_1` bigint(20) UNSIGNED DEFAULT NULL,
  `verifikator_2` bigint(20) UNSIGNED DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `data_karyawans`
--

CREATE TABLE `data_karyawans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `no_rm` varchar(255) DEFAULT NULL,
  `no_manulife` varchar(255) DEFAULT NULL,
  `tgl_masuk` varchar(255) DEFAULT NULL,
  `tgl_keluar` varchar(255) DEFAULT NULL,
  `unit_kerja_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jabatan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `kompetensi_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tunjangan_fungsional` int(11) DEFAULT NULL,
  `tunjangan_khusus` int(11) DEFAULT NULL,
  `tunjangan_lainnya` int(11) DEFAULT NULL,
  `uang_makan` int(11) DEFAULT NULL,
  `uang_lembur` int(11) DEFAULT NULL,
  `nik` varchar(255) DEFAULT NULL,
  `nik_ktp` varchar(16) DEFAULT NULL,
  `gelar_depan` varchar(255) DEFAULT NULL,
  `gelar_belakang` varchar(255) DEFAULT NULL,
  `tempat_lahir` varchar(255) DEFAULT NULL,
  `tgl_lahir` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(50) DEFAULT NULL,
  `no_bpjsksh` varchar(50) DEFAULT NULL,
  `no_bpjsktk` varchar(50) DEFAULT NULL,
  `tgl_diangkat` varchar(255) DEFAULT NULL,
  `masa_kerja` int(11) DEFAULT NULL,
  `npwp` varchar(50) DEFAULT NULL,
  `no_rekening` varchar(50) DEFAULT NULL,
  `jenis_kelamin` tinyint(1) DEFAULT NULL,
  `kategori_agama_id` bigint(20) UNSIGNED DEFAULT NULL,
  `kategori_darah_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tinggi_badan` int(11) DEFAULT NULL,
  `berat_badan` int(11) DEFAULT NULL,
  `pendidikan_terakhir` varchar(255) DEFAULT NULL,
  `no_ijazah` varchar(255) DEFAULT NULL,
  `tahun_lulus` int(11) DEFAULT NULL,
  `no_kk` varchar(20) DEFAULT NULL,
  `status_karyawan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `kelompok_gaji_id` bigint(20) UNSIGNED DEFAULT NULL,
  `no_str` varchar(16) DEFAULT NULL,
  `masa_berlaku_str` varchar(255) DEFAULT NULL,
  `no_sip` varchar(50) DEFAULT NULL,
  `masa_berlaku_sip` varchar(255) DEFAULT NULL,
  `ptkp_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tgl_berakhir_pks` varchar(255) DEFAULT NULL,
  `masa_diklat` int(11) DEFAULT NULL,
  `verifikator_1` bigint(20) UNSIGNED DEFAULT NULL,
  `status_reward_presensi` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `data_karyawans`
--

INSERT INTO `data_karyawans` (`id`, `user_id`, `email`, `no_rm`, `no_manulife`, `tgl_masuk`, `tgl_keluar`, `unit_kerja_id`, `jabatan_id`, `kompetensi_id`, `tunjangan_fungsional`, `tunjangan_khusus`, `tunjangan_lainnya`, `uang_makan`, `uang_lembur`, `nik`, `nik_ktp`, `gelar_depan`, `gelar_belakang`, `tempat_lahir`, `tgl_lahir`, `alamat`, `no_hp`, `no_bpjsksh`, `no_bpjsktk`, `tgl_diangkat`, `masa_kerja`, `npwp`, `no_rekening`, `jenis_kelamin`, `kategori_agama_id`, `kategori_darah_id`, `tinggi_badan`, `berat_badan`, `pendidikan_terakhir`, `no_ijazah`, `tahun_lulus`, `no_kk`, `status_karyawan_id`, `kelompok_gaji_id`, `no_str`, `masa_berlaku_str`, `no_sip`, `masa_berlaku_sip`, `ptkp_id`, `tgl_berakhir_pks`, `masa_diklat`, `verifikator_1`, `status_reward_presensi`, `created_at`, `updated_at`) VALUES
(1, 1, 'super_admin@admin.rski', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2024-09-09 06:26:11', '2024-09-09 06:26:11');

-- --------------------------------------------------------

--
-- Table structure for table `data_keluargas`
--

CREATE TABLE `data_keluargas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `data_karyawan_id` bigint(20) UNSIGNED NOT NULL,
  `nama_keluarga` varchar(255) NOT NULL,
  `hubungan` enum('Ayah','Ibu','Anak','Suami','Istri','Nenek','Kakek','Ayah Suami','Ibu Suami','Ayah Istri','Ibu Istri') NOT NULL,
  `pendidikan_terakhir` varchar(255) NOT NULL,
  `status_hidup` tinyint(1) NOT NULL,
  `pekerjaan` varchar(255) DEFAULT NULL,
  `no_hp` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_gajis`
--

CREATE TABLE `detail_gajis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `penggajian_id` bigint(20) UNSIGNED NOT NULL,
  `kategori_gaji_id` bigint(20) UNSIGNED NOT NULL,
  `nama_detail` varchar(255) NOT NULL,
  `besaran` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diklats`
--

CREATE TABLE `diklats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `gambar` bigint(20) UNSIGNED DEFAULT NULL,
  `dokumen_eksternal` bigint(20) UNSIGNED DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `kategori_diklat_id` bigint(20) UNSIGNED NOT NULL,
  `status_diklat_id` bigint(20) UNSIGNED NOT NULL,
  `deskripsi` varchar(255) NOT NULL,
  `kuota` int(11) DEFAULT NULL,
  `tgl_mulai` varchar(255) NOT NULL,
  `tgl_selesai` varchar(255) NOT NULL,
  `jam_mulai` varchar(255) NOT NULL,
  `jam_selesai` varchar(255) NOT NULL,
  `durasi` int(11) NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `verifikator_1` bigint(20) UNSIGNED DEFAULT NULL,
  `verifikator_2` bigint(20) UNSIGNED DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hari_liburs`
--

CREATE TABLE `hari_liburs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jabatans`
--

CREATE TABLE `jabatans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_jabatan` varchar(255) NOT NULL,
  `is_struktural` tinyint(1) NOT NULL,
  `tunjangan_jabatan` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwals`
--

CREATE TABLE `jadwals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tgl_mulai` varchar(255) NOT NULL,
  `tgl_selesai` varchar(255) DEFAULT NULL,
  `shift_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_penggajians`
--

CREATE TABLE `jadwal_penggajians` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tgl_mulai` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jadwal_penggajians`
--

INSERT INTO `jadwal_penggajians` (`id`, `tgl_mulai`, `created_at`, `updated_at`) VALUES
(1, 27, '2024-08-29 08:10:35', '2024-08-31 21:46:47');

-- --------------------------------------------------------

--
-- Table structure for table `jawabans`
--

CREATE TABLE `jawabans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_penilai` bigint(20) UNSIGNED NOT NULL,
  `pertanyaan_id` bigint(20) UNSIGNED NOT NULL,
  `jawaban` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jenis_penilaians`
--

CREATE TABLE `jenis_penilaians` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `status_karyawan_id` bigint(20) UNSIGNED NOT NULL,
  `jabatan_penilai` bigint(20) UNSIGNED NOT NULL,
  `jabatan_dinilai` bigint(20) UNSIGNED NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jenis_penilaians`
--

INSERT INTO `jenis_penilaians` (`id`, `nama`, `status_karyawan_id`, `jabatan_penilai`, `jabatan_dinilai`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Penilaian Karyawan Tetap', 1, 2, 3, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(2, 'Penilaian Karyawan Kontrak', 2, 4, 5, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(3, 'Penilaian Karyawan Magang', 3, 6, 7, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_activity_logs`
--

CREATE TABLE `kategori_activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_activity_logs`
--

INSERT INTO `kategori_activity_logs` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Test 1', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Test 2', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Test 3', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Test 4', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_agamas`
--

CREATE TABLE `kategori_agamas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_agamas`
--

INSERT INTO `kategori_agamas` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Islam', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Kristen', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Katolik', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Budha', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(5, 'Hindu', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(6, 'Konghucu', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_berkas`
--

CREATE TABLE `kategori_berkas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_berkas`
--

INSERT INTO `kategori_berkas` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Pribadi', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Umum', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'System', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Lainnya', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_darahs`
--

CREATE TABLE `kategori_darahs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_darahs`
--

INSERT INTO `kategori_darahs` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'A', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'B', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'AB', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'O', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(5, 'A+', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(6, 'A-', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(7, 'B+', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(8, 'B-', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(9, 'AB+', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(10, 'AB-', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(11, 'O+', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(12, 'O-', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_diklats`
--

CREATE TABLE `kategori_diklats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_diklats`
--

INSERT INTO `kategori_diklats` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Internal', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Eksternal', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_gajis`
--

CREATE TABLE `kategori_gajis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_gajis`
--

INSERT INTO `kategori_gajis` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Penghasilan Dasar', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Penambah', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Pengurang', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_kompensasis`
--

CREATE TABLE `kategori_kompensasis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_kompensasis`
--

INSERT INTO `kategori_kompensasis` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Test 1', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Test 2', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Test 3', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Test 4', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_notifikasis`
--

CREATE TABLE `kategori_notifikasis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_notifikasis`
--

INSERT INTO `kategori_notifikasis` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Cuti', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Tukar Jadwal', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Lembur', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Event & Diklat', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(5, 'Slip Gajiku', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(6, 'Dokumen', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(7, 'Feedback', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(8, 'Laporan', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(9, 'Koperasi', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(10, 'Perizinan', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_pendidikans`
--

CREATE TABLE `kategori_pendidikans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_pendidikans`
--

INSERT INTO `kategori_pendidikans` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'SD', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'SMP', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'SMA', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'SMK', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(5, 'Diploma 1 (D1)', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(6, 'Diploma 2 (D2)', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(7, 'Diploma 3 (D3)', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(8, 'Diploma 4 (D4) / Sarjana Terapan', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(9, 'Sarjana (S1)', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(10, 'Magister (S2)', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(11, 'Doktor (S3)', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(12, 'Pendidikan Non-Formal', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_potongans`
--

CREATE TABLE `kategori_potongans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_potongans`
--

INSERT INTO `kategori_potongans` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Gaji Bruto', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Gaji Pokok', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_presensis`
--

CREATE TABLE `kategori_presensis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_presensis`
--

INSERT INTO `kategori_presensis` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Tepat Waktu', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Terlambat', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Cuti', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Absen', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_ters`
--

CREATE TABLE `kategori_ters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_kategori_ter` varchar(255) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_ters`
--

INSERT INTO `kategori_ters` (`id`, `nama_kategori_ter`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'TER Kategori A', NULL, '2024-07-02 08:10:20', '2024-08-29 08:10:20'),
(2, 'TER Kategori B', NULL, '2024-07-02 08:10:20', '2024-08-29 08:10:20'),
(3, 'TER Kategori C', NULL, '2024-07-02 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_track_records`
--

CREATE TABLE `kategori_track_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_track_records`
--

INSERT INTO `kategori_track_records` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Perubahan Data', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Mutasi Pegawai', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Promosi Karyawan', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_transfer_karyawans`
--

CREATE TABLE `kategori_transfer_karyawans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_transfer_karyawans`
--

INSERT INTO `kategori_transfer_karyawans` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Promosi Karyawan', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Mutasi Pegawai', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_tukar_jadwals`
--

CREATE TABLE `kategori_tukar_jadwals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_tukar_jadwals`
--

INSERT INTO `kategori_tukar_jadwals` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Tukar Shift', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Tukar Libur', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `kelompok_gajis`
--

CREATE TABLE `kelompok_gajis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_kelompok` varchar(255) NOT NULL,
  `besaran_gaji` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kompetensis`
--

CREATE TABLE `kompetensis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_kompetensi` varchar(255) NOT NULL,
  `jenis_kompetensi` tinyint(1) NOT NULL,
  `tunjangan_kompetensi` int(11) NOT NULL,
  `nilai_bor` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lemburs`
--

CREATE TABLE `lemburs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `jadwal_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tgl_pengajuan` varchar(255) NOT NULL,
  `durasi` varchar(255) NOT NULL,
  `catatan` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lokasi_kantors`
--

CREATE TABLE `lokasi_kantors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `alamat` text NOT NULL,
  `lat` varchar(255) NOT NULL,
  `long` varchar(255) NOT NULL,
  `radius` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lokasi_kantors`
--

INSERT INTO `lokasi_kantors` (`id`, `alamat`, `lat`, `long`, `radius`, `created_at`, `updated_at`) VALUES
(1, 'Jl. Slamet Riyadi No.404, Purwosari, Kec. Laweyan, Kota Surakarta, Jawa Tengah 57142', '-7.563257447528563', '110.80177722497034', 100, '2024-08-29 08:10:20', '2024-09-06 08:49:13');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000001_create_status_aktifs_table', 1),
(2, '2014_10_12_000002_create_users_table', 1),
(3, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(4, '2019_08_19_000000_create_failed_jobs_table', 1),
(5, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(6, '2024_04_19_042605_create_permission_tables', 1),
(7, '2024_04_19_052853_create_unit_kerjas_table', 1),
(8, '2024_04_19_053038_create_jabatans_table', 1),
(9, '2024_04_19_054926_create_kelompok_gajis_table', 1),
(10, '2024_04_19_055629_create_kategori_ters_table', 1),
(11, '2024_04_19_061317_create_kategori_potongans_table', 1),
(12, '2024_04_19_061318_create_premis_table', 1),
(13, '2024_04_19_061759_create_jadwal_penggajians_table', 1),
(14, '2024_04_19_061913_create_status_diklats_table', 1),
(15, '2024_04_19_061914_create_kategori_diklats_table', 1),
(16, '2024_04_19_062156_create_hari_liburs_table', 1),
(17, '2024_04_19_062505_create_tipe_cutis_table', 1),
(18, '2024_04_19_062851_create_thrs_table', 1),
(19, '2024_04_19_081319_create_shifts_table', 1),
(20, '2024_04_19_091158_create_jadwals_table', 1),
(21, '2024_04_19_091160_create_kategori_track_records_table', 1),
(22, '2024_04_26_130901_create_track_records_table', 1),
(23, '2024_04_26_130902_create_kategori_transfer_karyawans_table', 1),
(24, '2024_04_26_131134_create_transfer_karyawans_table', 1),
(25, '2024_04_26_132455_create_kompetensis_table', 1),
(26, '2024_04_26_132457_create_ptkps_table', 1),
(27, '2024_04_26_132458_create_ters_table', 1),
(28, '2024_05_01_122548_add_description_to_roles_tables', 1),
(29, '2024_05_20_123429_add_group_to_permissions_tables', 1),
(30, '2024_06_18_005931_create_status_karyawans_table', 1),
(31, '2024_07_02_011518_create_lokasi_kantors_table', 1),
(32, '2024_07_19_182525_create_pengumumans_table', 1),
(33, '2024_07_19_191130_create_kategori_notifikasis_table', 1),
(34, '2024_07_19_191217_create_notifikasis_table', 1),
(35, '2024_07_20_131007_create_kategori_agamas_table', 1),
(36, '2024_07_20_131033_create_kategori_gajis_table', 1),
(37, '2024_07_20_131054_create_kategori_presensis_table', 1),
(38, '2024_07_20_131129_create_kategori_activity_logs_table', 1),
(39, '2024_07_20_131130_create_status_presensis_table', 1),
(40, '2024_07_20_131155_create_kategori_berkas_table', 1),
(41, '2024_07_20_140007_create_status_lemburs_table', 1),
(42, '2024_07_20_140053_create_status_tukar_jadwals_table', 1),
(43, '2024_07_20_140119_create_status_cutis_table', 1),
(44, '2024_07_20_140202_create_kategori_darahs_table', 1),
(45, '2024_07_20_140205_create_status_berkas_table', 1),
(46, '2024_07_20_140206_create_status_gajis_table', 1),
(47, '2024_07_20_140207_create_kategori_pendidikans_table', 1),
(48, '2024_07_20_140210_create_kategori_kompensasis_table', 1),
(49, '2024_07_20_140211_create_activity_logs_table', 1),
(50, '2024_07_20_140212_create_berkas_table', 1),
(51, '2024_07_20_140213_create_lemburs_table', 1),
(52, '2024_07_20_140214_create_diklats_table', 1),
(53, '2024_07_20_140215_create_data_karyawans_table', 1),
(54, '2024_07_20_225510_create_kategori_tukar_jadwals_table', 1),
(55, '2024_07_20_225511_create_tukar_jadwals_table', 1),
(56, '2024_07_20_225512_create_cutis_table', 1),
(57, '2024_07_20_225513_create_riwayat_penggajians_table', 1),
(58, '2024_07_20_225514_create_penggajians_table', 1),
(59, '2024_07_20_225515_create_run_thrs_table', 1),
(60, '2024_07_20_225516_create_data_keluargas_table', 1),
(61, '2024_07_20_225517_create_pengurang_gajis_table', 1),
(62, '2024_07_20_225518_create_pelaporans_table', 1),
(63, '2024_07_20_225519_create_presensis_table', 1),
(64, '2024_07_20_225520_create_detail_gajis_table', 1),
(65, '2024_07_20_225521_create_penyesuaian_gajis_table', 1),
(66, '2024_07_25_212118_add_foreign_key_to_users_table', 1),
(67, '2024_08_09_000252_create_peserta_diklats_table', 1),
(68, '2024_08_10_225850_create_status_perubahans_table', 1),
(69, '2024_08_10_225852_create_riwayat_perubahans_table', 1),
(70, '2024_08_13_083228_create_jenis_penilaians_table', 1),
(71, '2024_08_13_083230_create_penilaians_table', 1),
(72, '2024_08_13_083231_create_pertanyaans_table', 1),
(73, '2024_08_13_083232_create_jawabans_table', 1),
(74, '2024_08_14_222644_create_perubahan_keluargas_table', 1),
(75, '2024_08_14_222655_create_perubahan_personals_table', 1),
(76, '2024_08_14_222708_create_perubahan_berkas_table', 1),
(77, '2024_08_15_125835_create_non_shifts_table', 1),
(78, '2024_08_21_205555_create_reward_bulan_lalus_table', 1),
(79, '2024_08_22_075544_create_status_riwayat_izins_table', 1),
(80, '2024_08_22_080207_create_riwayat_izins_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(1, 'App\\Models\\User', 56),
(2, 'App\\Models\\User', 10),
(2, 'App\\Models\\User', 14),
(2, 'App\\Models\\User', 21),
(2, 'App\\Models\\User', 22),
(2, 'App\\Models\\User', 27),
(2, 'App\\Models\\User', 28),
(2, 'App\\Models\\User', 29),
(2, 'App\\Models\\User', 33),
(2, 'App\\Models\\User', 35),
(2, 'App\\Models\\User', 36),
(2, 'App\\Models\\User', 37),
(2, 'App\\Models\\User', 40),
(2, 'App\\Models\\User', 45),
(2, 'App\\Models\\User', 46),
(2, 'App\\Models\\User', 47),
(2, 'App\\Models\\User', 48),
(2, 'App\\Models\\User', 49),
(2, 'App\\Models\\User', 55),
(3, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 4),
(3, 'App\\Models\\User', 7),
(3, 'App\\Models\\User', 8),
(3, 'App\\Models\\User', 9),
(3, 'App\\Models\\User', 12),
(3, 'App\\Models\\User', 13),
(3, 'App\\Models\\User', 15),
(3, 'App\\Models\\User', 16),
(3, 'App\\Models\\User', 17),
(3, 'App\\Models\\User', 30),
(3, 'App\\Models\\User', 31),
(3, 'App\\Models\\User', 34),
(3, 'App\\Models\\User', 38),
(3, 'App\\Models\\User', 50),
(4, 'App\\Models\\User', 5),
(4, 'App\\Models\\User', 6),
(4, 'App\\Models\\User', 11),
(4, 'App\\Models\\User', 18),
(4, 'App\\Models\\User', 19),
(4, 'App\\Models\\User', 20),
(4, 'App\\Models\\User', 23),
(4, 'App\\Models\\User', 24),
(4, 'App\\Models\\User', 25),
(4, 'App\\Models\\User', 26),
(4, 'App\\Models\\User', 32),
(4, 'App\\Models\\User', 39),
(4, 'App\\Models\\User', 41),
(4, 'App\\Models\\User', 42),
(4, 'App\\Models\\User', 43),
(4, 'App\\Models\\User', 44),
(4, 'App\\Models\\User', 51),
(4, 'App\\Models\\User', 52),
(4, 'App\\Models\\User', 53),
(4, 'App\\Models\\User', 54),
(4, 'App\\Models\\User', 57),
(4, 'App\\Models\\User', 58);

-- --------------------------------------------------------

--
-- Table structure for table `non_shifts`
--

CREATE TABLE `non_shifts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `jam_from` varchar(255) DEFAULT NULL,
  `jam_to` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `non_shifts`
--

INSERT INTO `non_shifts` (`id`, `nama`, `jam_from`, `jam_to`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Jadwal Non Shift', '06:00:00', '17:30:00', NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `notifikasis`
--

CREATE TABLE `notifikasis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kategori_notifikasi_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pelaporans`
--

CREATE TABLE `pelaporans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pelapor` bigint(20) UNSIGNED NOT NULL,
  `pelaku` bigint(20) UNSIGNED NOT NULL,
  `tgl_kejadian` datetime NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `kronologi` text NOT NULL,
  `upload_foto` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penggajians`
--

CREATE TABLE `penggajians` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `riwayat_penggajian_id` bigint(20) UNSIGNED NOT NULL,
  `data_karyawan_id` bigint(20) UNSIGNED NOT NULL,
  `tgl_penggajian` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `gaji_pokok` int(11) NOT NULL,
  `total_tunjangan` int(11) NOT NULL,
  `reward` int(11) NOT NULL,
  `gaji_bruto` int(11) NOT NULL,
  `total_premi` int(11) NOT NULL,
  `pph_21` int(11) NOT NULL,
  `take_home_pay` int(11) NOT NULL,
  `status_gaji_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengumumans`
--

CREATE TABLE `pengumumans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(255) NOT NULL,
  `konten` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `tgl_berakhir` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengumumans`
--

INSERT INTO `pengumumans` (`id`, `judul`, `konten`, `is_read`, `tgl_berakhir`, `created_at`, `updated_at`) VALUES
(2, 'Pengumuman 2', 'Konten pengumuman 2', 0, '2024-08-31', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(3, 'Pengumuman 3', 'Konten pengumuman 3', 0, '2024-09-01', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(4, 'Pengumuman 4', 'Konten pengumuman 4', 0, '2024-09-02', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(5, 'Pengumuman 5', 'Konten pengumuman 5', 0, '2024-09-03', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(6, 'Pengumuman 6', 'Konten pengumuman 6', 0, '2024-09-04', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(7, 'Pengumuman 7', 'Konten pengumuman 7', 0, '2024-09-05', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(8, 'Pengumuman 8', 'Konten pengumuman 8', 0, '2024-09-06', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(9, 'Pengumuman 9', 'Konten pengumuman 9', 0, '2024-09-07', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(10, 'Pengumuman 10', 'Konten pengumuman 10', 0, '2024-09-08', '2024-08-29 08:10:35', '2024-08-29 08:10:35');

-- --------------------------------------------------------

--
-- Table structure for table `pengurang_gajis`
--

CREATE TABLE `pengurang_gajis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `data_karyawan_id` bigint(20) UNSIGNED NOT NULL,
  `premi_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengurang_gajis`
--

INSERT INTO `pengurang_gajis` (`id`, `data_karyawan_id`, `premi_id`, `created_at`, `updated_at`) VALUES
(1, 50, 1, '2024-08-30 23:07:46', '2024-08-30 23:07:46'),
(2, 50, 2, '2024-08-30 23:07:46', '2024-08-30 23:07:46'),
(3, 50, 3, '2024-08-30 23:07:46', '2024-08-30 23:07:46'),
(4, 50, 4, '2024-08-30 23:07:46', '2024-08-30 23:07:46'),
(5, 52, 1, '2024-08-31 21:55:30', '2024-08-31 21:55:30'),
(6, 52, 2, '2024-08-31 21:55:30', '2024-08-31 21:55:30'),
(7, 52, 3, '2024-08-31 21:55:30', '2024-08-31 21:55:30'),
(8, 53, 1, '2024-08-31 21:57:23', '2024-08-31 21:57:23'),
(9, 53, 2, '2024-08-31 21:57:23', '2024-08-31 21:57:23'),
(10, 53, 3, '2024-08-31 21:57:23', '2024-08-31 21:57:23'),
(11, 54, 1, '2024-08-31 21:59:28', '2024-08-31 21:59:28'),
(12, 54, 2, '2024-08-31 21:59:28', '2024-08-31 21:59:28'),
(13, 54, 3, '2024-08-31 21:59:28', '2024-08-31 21:59:28'),
(23, 55, 1, '2024-09-03 05:29:02', '2024-09-03 05:29:02'),
(24, 55, 2, '2024-09-03 05:29:02', '2024-09-03 05:29:02'),
(25, 55, 3, '2024-09-03 05:29:02', '2024-09-03 05:29:02'),
(26, 57, 1, '2024-09-05 20:16:51', '2024-09-05 20:16:51'),
(27, 57, 2, '2024-09-05 20:16:51', '2024-09-05 20:16:51'),
(28, 57, 3, '2024-09-05 20:16:51', '2024-09-05 20:16:51'),
(29, 57, 4, '2024-09-05 20:16:51', '2024-09-05 20:16:51'),
(30, 51, 1, '2024-09-06 08:25:53', '2024-09-06 08:25:53'),
(31, 51, 2, '2024-09-06 08:25:53', '2024-09-06 08:25:53'),
(32, 51, 3, '2024-09-06 08:25:53', '2024-09-06 08:25:53'),
(33, 51, 4, '2024-09-06 08:25:53', '2024-09-06 08:25:53'),
(34, 58, 1, '2024-09-08 20:15:23', '2024-09-08 20:15:23'),
(35, 58, 2, '2024-09-08 20:15:23', '2024-09-08 20:15:23'),
(36, 58, 3, '2024-09-08 20:15:23', '2024-09-08 20:15:23'),
(37, 58, 4, '2024-09-08 20:15:23', '2024-09-08 20:15:23');

-- --------------------------------------------------------

--
-- Table structure for table `penilaians`
--

CREATE TABLE `penilaians` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_dinilai` bigint(20) UNSIGNED NOT NULL,
  `user_penilai` bigint(20) UNSIGNED NOT NULL,
  `jenis_penilaian_id` bigint(20) UNSIGNED NOT NULL,
  `pertanyaan_jawaban` text NOT NULL,
  `total_pertanyaan` int(11) NOT NULL,
  `rata_rata` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penyesuaian_gajis`
--

CREATE TABLE `penyesuaian_gajis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `penggajian_id` bigint(20) UNSIGNED NOT NULL,
  `kategori_gaji_id` bigint(20) UNSIGNED NOT NULL,
  `nama_detail` varchar(255) NOT NULL,
  `besaran` int(11) NOT NULL,
  `bulan_mulai` varchar(255) DEFAULT NULL,
  `bulan_selesai` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `penyesuaian_gajis`
--

INSERT INTO `penyesuaian_gajis` (`id`, `penggajian_id`, `kategori_gaji_id`, `nama_detail`, `besaran`, `bulan_mulai`, `bulan_selesai`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'Ganti rugi gelas pecah', 80000, NULL, NULL, NULL, '2024-09-02 07:07:06', '2024-09-02 07:07:06');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `group` varchar(255) DEFAULT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `group`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'verifikasi1 berkas', 'Berkas', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(2, 'view riwayatPerubahan', 'Riwayat Perubahan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(3, 'verifikasi1 riwayatPerubahan', 'Riwayat Perubahan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(4, 'view riwayatPerizinan', 'Riwayat Izin', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(6, 'verifikasi1 riwayatPerizinan', 'Riwayat Izin', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(7, 'create diklat', 'Diklat', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(8, 'view diklat', 'Diklat', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(9, 'export diklat', 'Diklat', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(10, 'verifikasi1 diklat', 'Diklat', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(11, 'verifikasi2 diklat', 'Diklat', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(12, 'create thrKaryawan', 'Penggajian THR Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(13, 'view thrKaryawan', 'Penggajian THR Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(14, 'export thrKaryawan', 'Penggajian THR Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(15, 'create penggajianKaryawan', 'Penggajian Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(16, 'edit penggajianKaryawan', 'Penggajian Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(17, 'view penggajianKaryawan', 'Penggajian Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(18, 'export penggajianKaryawan', 'Penggajian Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(19, 'create jadwalKaryawan', 'Jadwal Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(20, 'edit jadwalKaryawan', 'Jadwal Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(22, 'view jadwalKaryawan', 'Jadwal Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(23, 'import jadwalKaryawan', 'Jadwal Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(24, 'export jadwalKaryawan', 'Jadwal Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(25, 'bypass jadwalKaryawan', 'Jadwal Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(26, 'create tukarJadwal', 'Jadwal Tukar Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(27, 'edit tukarJadwal', 'Jadwal Tukar Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(29, 'view tukarJadwal', 'Jadwal Tukar Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(30, 'export tukarJadwal', 'Jadwal Tukar Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(31, 'verifikasi1 tukarJadwal', 'Jadwal Tukar Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(32, 'verifikasi2 tukarJadwal', 'Jadwal Tukar Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(33, 'create lemburKaryawan', 'Jadwal Lembur Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(34, 'edit lemburKaryawan', 'Jadwal Lembur Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(36, 'view lemburKaryawan', 'Jadwal Lembur Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(37, 'export lemburKaryawan', 'Jadwal Lembur Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(38, 'create cutiKaryawan', 'Jadwal Cuti Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(39, 'edit cutiKaryawan', 'Jadwal Cuti Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(41, 'view cutiKaryawan', 'Jadwal Cuti Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(42, 'export cutiKaryawan', 'Jadwal Cuti Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(43, 'verifikasi1 cutiKaryawan', 'Jadwal Cuti Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(44, 'verifikasi2 cutiKaryawan', 'Jadwal Cuti Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(45, 'view presensiKaryawan', 'Presensi Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(46, 'import presensiKaryawan', 'Presensi Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(47, 'export presensiKaryawan', 'Presensi Karyawan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(48, 'create dataKaryawan', 'Karyawan Data', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(49, 'edit dataKaryawan', 'Karyawan Data', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(50, 'view dataKaryawan', 'Karyawan Data', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(51, 'import dataKaryawan', 'Karyawan Data', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(52, 'export dataKaryawan', 'Karyawan Data', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(53, 'create pengumuman', 'Pengumuman', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(54, 'edit pengumuman', 'Pengumuman', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(55, 'delete pengumuman', 'Pengumuman', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(56, 'view pengumuman', 'Pengumuman', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(57, 'verifikasi verifikator1', 'Verifikasi Data', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(58, 'verifikasi verifikator2', 'Verifikasi Data', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(59, 'create role', 'Pengaturan Role', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(60, 'edit role', 'Pengaturan Role', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(61, 'view role', 'Pengaturan Role', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(62, 'edit permission', 'Pengaturan Permission', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(64, 'view permission', 'Pengaturan Permission', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(65, 'create unitKerja', 'Pengaturan Unit Kerja', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(66, 'edit unitKerja', 'Pengaturan Unit Kerja', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(67, 'delete unitKerja', 'Pengaturan Unit Kerja', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(68, 'view unitKerja', 'Pengaturan Unit Kerja', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(69, 'create jabatan', 'Pengaturan Jabatan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(70, 'edit jabatan', 'Pengaturan Jabatan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(71, 'delete jabatan', 'Pengaturan Jabatan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(72, 'view jabatan', 'Pengaturan Jabatan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(73, 'create kompetensi', 'Pengaturan Kompetensi', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(74, 'edit kompetensi', 'Pengaturan Kompetensi', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(75, 'delete kompetensi', 'Pengaturan Kompetensi', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(76, 'view kompetensi', 'Pengaturan Kompetensi', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(77, 'create kelompokGaji', 'Pengaturan Kelompok Gaji', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(78, 'edit kelompokGaji', 'Pengaturan Kelompok Gaji', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(79, 'delete kelompokGaji', 'Pengaturan Kelompok Gaji', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(80, 'view kelompokGaji', 'Pengaturan Kelompok Gaji', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(81, 'create kuesioner', 'Pengaturan Kuesioner', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(82, 'edit kuesioner', 'Pengaturan Kuesioner', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(83, 'delete kuesioner', 'Pengaturan Kuesioner', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(84, 'view kuesioner', 'Pengaturan Kuesioner', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(85, 'create premi', 'Pengaturan Premi', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(86, 'edit premi', 'Pengaturan Premi', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(87, 'delete premi', 'Pengaturan Premi', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(88, 'view premi', 'Pengaturan Premi', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(89, 'create ter21', 'Pengaturan TER21', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(90, 'edit ter21', 'Pengaturan TER21', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(91, 'delete ter21', 'Pengaturan TER21', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(92, 'view ter21', 'Pengaturan TER21', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(93, 'edit jadwalGaji', 'Pengaturan Jadwal Penggajian', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(94, 'view jadwalGaji', 'Pengaturan Jadwal Penggajian', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(95, 'create thr', 'Pengaturan THR', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(96, 'edit thr', 'Pengaturan THR', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(97, 'delete thr', 'Pengaturan THR', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(98, 'view thr', 'Pengaturan THR', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(99, 'create shift', 'Pengaturan Shift', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(100, 'edit shift', 'Pengaturan Shift', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(101, 'delete shift', 'Pengaturan Shift', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(102, 'view shift', 'Pengaturan Shift', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(103, 'create hariLibur', 'Pengaturan Hari Libur', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(104, 'edit hariLibur', 'Pengaturan Hari Libur', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(105, 'delete hariLibur', 'Pengaturan Hari Libur', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(106, 'view hariLibur', 'Pengaturan Hari Libur', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(107, 'create cuti', 'Pengaturan Cuti', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(108, 'edit cuti', 'Pengaturan Cuti', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(109, 'delete cuti', 'Pengaturan Cuti', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(110, 'view cuti', 'Pengaturan Cuti', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(111, 'edit lokasiKantor', 'Pengaturan Lokasi Kantor', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(112, 'view lokasiKantor', 'Pengaturan Lokasi Kantor', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(113, 'create penilaianKaryawan', 'Penilaian Karyawan', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(114, 'view penilaianKaryawan', 'Penilaian Karyawan', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(117, 'export penilaianKaryawan', 'Penilaian Karyawan', 'web', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(118, 'edit penilaianKaryawan', 'Penilaian Karyawan', 'web', '2024-08-30 03:17:26', '2024-08-30 03:17:26'),
(119, 'delete penilaianKaryawan', 'Penilaian Karyawan', 'web', '2024-08-30 03:19:07', '2024-08-30 03:19:07');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(2, 'App\\Models\\User', 1, 'create_token_9f30c5ac-0f21-428e-ad09-1b01c043f861', '9481c400a5269d62fc471b4aeadd6a3175ffb857dfe3a48164609c2dcdabb24e', '[\"*\"]', '2024-08-29 08:27:22', NULL, '2024-08-29 08:15:25', '2024-08-29 08:27:22'),
(6, 'App\\Models\\User', 1, 'create_token_5cd2ceb8-e6c4-4956-bfa3-056ca73ba3b1', '956b5fdabb364efdabef3a17b2797d1026ec4bbc63703ec57e64e5f1af2f0eab', '[\"*\"]', '2024-08-29 17:38:31', NULL, '2024-08-29 08:19:19', '2024-08-29 17:38:31'),
(8, 'App\\Models\\User', 50, 'create_token_04c6202d-7296-42da-9de3-09366f32710c', 'f90381468bbc56f3ff440a920f37351a211eaf23e2970ced92cb673407fa4e2e', '[\"*\"]', '2024-08-29 09:44:14', NULL, '2024-08-29 08:24:53', '2024-08-29 09:44:14'),
(12, 'App\\Models\\User', 1, 'create_token_d1c63bae-60e9-4f18-b21a-b32eaddfdd1f', '4c2fb90f0c649ce22d0e4f593c776c38bf2e46c52ba24851e64d226ee4f49ba9', '[\"*\"]', '2024-08-29 23:30:23', NULL, '2024-08-29 19:11:27', '2024-08-29 23:30:23'),
(14, 'App\\Models\\User', 1, 'create_token_83643661-e2fe-442e-896d-d30e80d02392', '4d3d8f4829fdd61043b04127f774094ec2035b4ac47806701e267c865fb71f9f', '[\"*\"]', '2024-08-29 19:58:01', NULL, '2024-08-29 19:57:52', '2024-08-29 19:58:01'),
(22, 'App\\Models\\User', 1, 'create_token_b3cfaae5-c791-4894-92d3-dd339a9daa59', '5cf139d2ea33b2b21002f1bd2f64b52da6f1999f95402241c0d4690b69d739de', '[\"*\"]', '2024-08-30 03:01:39', NULL, '2024-08-30 00:57:30', '2024-08-30 03:01:39'),
(24, 'App\\Models\\User', 1, 'create_token_5b940f92-ceab-41f3-a314-8cbb0df0b48d', 'a98bebf6127aa6b4c61a732ee1bfd5cd7ebbd370acba7f76ca0b640043934c2a', '[\"*\"]', '2024-08-30 02:20:35', NULL, '2024-08-30 01:48:22', '2024-08-30 02:20:35'),
(27, 'App\\Models\\User', 1, 'create_token_3c3c0939-2329-472e-876f-e14fa5468170', '70f2c80d62f500539aa7096a74debefb488a10cc2cd72c74a677cdb7f517952d', '[\"*\"]', '2024-08-30 16:48:37', NULL, '2024-08-30 16:40:20', '2024-08-30 16:48:37'),
(37, 'App\\Models\\User', 1, 'create_token_87d8509e-21c9-4535-947c-26213c734e3c', 'b1c7a02444a3d2b15294a2aae55f6fcab297a62cd7ac0e2db9433b115d3830a1', '[\"*\"]', '2024-08-30 22:26:25', NULL, '2024-08-30 22:23:05', '2024-08-30 22:26:25'),
(38, 'App\\Models\\User', 2, 'TLogin', '63bd1c51ac5a62685defc1d10e1843c9990a61ee9b99e9efcd361a76a15c9f72', '[\"*\"]', '2024-08-31 05:26:59', NULL, '2024-08-31 05:26:58', '2024-08-31 05:26:59'),
(49, 'App\\Models\\User', 50, 'create_token_b943fc8b-1299-4c80-876b-d3e256c83bde', '828617cb3b3b3f404fd726fc3e316743d9a741e4055c36f531963f3239a37347', '[\"*\"]', '2024-08-31 00:30:21', NULL, '2024-08-31 00:30:21', '2024-08-31 00:30:21'),
(52, 'App\\Models\\User', 50, 'TLogin', '005a3ab1062320511da24e3ecfbe880ad9631e4eecf79e08ce53cb33cc4cc5c8', '[\"*\"]', '2024-08-31 08:14:45', NULL, '2024-08-31 08:14:15', '2024-08-31 08:14:45'),
(53, 'App\\Models\\User', 50, 'TLogin', '057c4af0f382a8c3068365c25e9faf1af8367e68e3e5138260791f9ef3118f02', '[\"*\"]', '2024-08-31 08:28:05', NULL, '2024-08-31 08:19:17', '2024-08-31 08:28:05'),
(55, 'App\\Models\\User', 1, 'create_token_2c522d8f-ec0b-4c25-86d5-3f67c9150abf', '47d607b240f7eaf5fa1e6db5fc20d5943166aadfd989721c0e995f50cd71e519', '[\"*\"]', '2024-08-31 06:05:31', NULL, '2024-08-31 02:47:10', '2024-08-31 06:05:31'),
(57, 'App\\Models\\User', 1, 'create_token_d0a5fce9-2fcf-4217-8164-8e2194950538', 'd9aca7e53c8ebb8bccddd622164339b4a32cc8cff8353f8a68e4d169e24e8f0d', '[\"*\"]', '2024-08-31 07:55:25', NULL, '2024-08-31 07:23:24', '2024-08-31 07:55:25'),
(59, 'App\\Models\\User', 1, 'create_token_07149cdc-e269-4c4c-8611-b476ae8357d6', '44e63416c70e5e80461123a1ceedca4736a83729ce548fb943d7f302a1379b2a', '[\"*\"]', '2024-09-06 08:49:32', NULL, '2024-08-31 07:46:20', '2024-09-06 08:49:32'),
(64, 'App\\Models\\User', 50, 'create_token_dee849a8-7775-409c-99f4-6ea4425b0129', 'fe0c39308892f78205eab7e61e36a57ef9cbcc6741be69e2b0a3b075d5201f58', '[\"*\"]', '2024-08-31 21:49:57', NULL, '2024-08-31 20:13:31', '2024-08-31 21:49:57'),
(66, 'App\\Models\\User', 1, 'create_token_6983b771-cb1b-4c74-8466-3b5c24331702', 'a890cc44a32360f301e3b8cc5b01321633a67928e3b1aa63156c0617cd99022d', '[\"*\"]', '2024-09-01 01:34:27', NULL, '2024-08-31 21:42:37', '2024-09-01 01:34:27'),
(67, 'App\\Models\\User', 55, 'TLogin', '3156f7a7ec88b6f4500abf001c6088535da7d7fe11a5d91ffa4ed85f7b301c93', '[\"*\"]', '2024-09-01 05:36:55', NULL, '2024-09-01 05:05:11', '2024-09-01 05:36:55'),
(68, 'App\\Models\\User', 55, 'TLogin', '8762720c7b937ac002de5f2cfaebb52136a77eba77e101481131285b23c5eedf', '[\"*\"]', '2024-09-01 05:20:50', NULL, '2024-09-01 05:16:04', '2024-09-01 05:20:50'),
(69, 'App\\Models\\User', 55, 'TLogin', '1c174b984ceb5ea9d99594f3111fc5d0421be098f03c0c86b0328b1aa265d7ef', '[\"*\"]', '2024-09-01 08:03:14', NULL, '2024-09-01 05:31:11', '2024-09-01 08:03:14'),
(70, 'App\\Models\\User', 55, 'TLogin', '4487b096f354dae4a0edfbc605a262968f0dc2717bc8b2507d3400cb9475b484', '[\"*\"]', '2024-09-01 07:18:07', NULL, '2024-09-01 05:37:51', '2024-09-01 07:18:07'),
(71, 'App\\Models\\User', 55, 'TLogin', '7c6326c522f2a93c654c6c99ae7d1da6a0e56ef1bf1f58312c866f8b5b77eecb', '[\"*\"]', '2024-09-01 07:18:30', NULL, '2024-09-01 07:18:29', '2024-09-01 07:18:30'),
(73, 'App\\Models\\User', 42, 'TLogin', '869e67b548c33c423135b54be977e98fc281c760cee4910c2baeb83078326383', '[\"*\"]', '2024-09-01 08:04:01', NULL, '2024-09-01 08:03:42', '2024-09-01 08:04:01'),
(74, 'App\\Models\\User', 55, 'TLogin', 'e09c73e877974fbc2ea8ce84cfacdd315d0e5b4da0bd8cb5017c297f6ccf586d', '[\"*\"]', '2024-09-01 08:06:05', NULL, '2024-09-01 08:05:49', '2024-09-01 08:06:05'),
(77, 'App\\Models\\User', 1, 'create_token_d20966bf-fca2-4099-bcc1-553b9ff122ed', '72284fe8f43efc60384a60113e5a76d043a0d33d5889daef6d5690c25bdf94b9', '[\"*\"]', '2024-09-01 19:27:53', NULL, '2024-09-01 18:37:54', '2024-09-01 19:27:53'),
(79, 'App\\Models\\User', 1, 'create_token_92215d94-d817-4586-b303-b45809c8d919', '4591e995badc7564e02e208dea1c2a4ea0c7118efbd2009156cc984e33cc5978', '[\"*\"]', '2024-09-01 18:46:24', NULL, '2024-09-01 18:46:23', '2024-09-01 18:46:24'),
(80, 'App\\Models\\User', 1, 'create_token_f81bc9f5-2d36-4a2d-8d59-b7238cd2984f', '75a2420d01a79ed899ccae18a3b10abad4e56e45015d38b50a0570529cf9cd02', '[\"*\"]', '2024-09-01 19:46:45', NULL, '2024-09-01 19:37:52', '2024-09-01 19:46:45'),
(83, 'App\\Models\\User', 42, 'TLogin', '3c4b37b466600a70826d13d5ac5d89a010208495bf565b8b88af074e5d7dcedc', '[\"*\"]', '2024-09-02 02:42:55', NULL, '2024-09-02 02:42:30', '2024-09-02 02:42:55'),
(94, 'App\\Models\\User', 1, 'create_token_2c26a5cc-b364-4432-9a10-385d1c84c8a5', 'fe31fba17a7720bf5f66aa5673d66e787b7d02d91cc4c61623d85f88edcc402b', '[\"*\"]', '2024-09-02 07:00:00', NULL, '2024-09-01 20:15:44', '2024-09-02 07:00:00'),
(95, 'App\\Models\\User', 42, 'TLogin', 'd63589cb67b7ef693ffef76791b617840d79a4775e649a1a1a92961996f2aad0', '[\"*\"]', '2024-09-02 03:27:55', NULL, '2024-09-02 03:27:53', '2024-09-02 03:27:55'),
(98, 'App\\Models\\User', 1, 'create_token_932f20a3-1283-4a84-bbb4-80583a46c75f', 'bc7c50ff765203ff6611f5a158e6f950dd6311b8fbfee9660782be0c63ad9010', '[\"*\"]', '2024-09-01 20:51:02', NULL, '2024-09-01 20:50:12', '2024-09-01 20:51:02'),
(101, 'App\\Models\\User', 1, 'create_token_17032247-6b07-4b1c-bddd-d048e914d4c8', '6a882bdea0c7ec5a31c76baccc49685fcb601cdac09720b1906c8f8ac530c1b6', '[\"*\"]', '2024-09-01 21:18:54', NULL, '2024-09-01 21:16:33', '2024-09-01 21:18:54'),
(102, 'App\\Models\\User', 1, 'create_token_185b33d8-3f93-4dfe-8ccb-bb37aef65ddd', '68d439a318a45b1f1a0fb1bb15c2c96661970940ad65e40c6b621de8c7684800', '[\"*\"]', '2024-09-01 21:24:08', NULL, '2024-09-01 21:17:05', '2024-09-01 21:24:08'),
(103, 'App\\Models\\User', 1, 'create_token_de4cb057-ffd0-4d0a-aa8b-f3492623c610', '23f58ca75c9669c16bc5ac26a764048b282d7947b1a132fc68d301a29a42bfc8', '[\"*\"]', '2024-09-01 22:41:37', NULL, '2024-09-01 21:17:36', '2024-09-01 22:41:37'),
(104, 'App\\Models\\User', 1, 'create_token_1901b0d8-cd97-4c77-b707-b10f01a59281', '930ad65f1de74c0582236500146df95e8670f61818f039004469547531ddc527', '[\"*\"]', '2024-09-01 21:39:20', NULL, '2024-09-01 21:32:00', '2024-09-01 21:39:20'),
(105, 'App\\Models\\User', 50, 'TLogin', 'b26302a2bf08f111f1970ee8c41b1458687fa203eca9ebb52c7ae96a1c3cc936', '[\"*\"]', '2024-09-02 09:59:58', NULL, '2024-09-02 09:55:08', '2024-09-02 09:59:58'),
(107, 'App\\Models\\User', 1, 'create_token_b7b81e63-f9c8-4cfb-8e15-0840cae461b8', 'b92e1298abbfa7561f1ca18c8bb664dce37b9a437a912a4db929ba5c887837a6', '[\"*\"]', '2024-09-02 09:06:27', NULL, '2024-09-02 07:05:31', '2024-09-02 09:06:27'),
(108, 'App\\Models\\User', 24, 'TLogin', '12c723fe6c8c600a300dd08bb268b2421f9c5afb240488dec4c50bc36358cc96', '[\"*\"]', '2024-09-03 02:46:33', NULL, '2024-09-03 02:33:03', '2024-09-03 02:46:33'),
(109, 'App\\Models\\User', 1, 'create_token_13a65cf1-63f8-440a-958f-565da3f356d9', 'ad266ff943d7afbf78d7ff5e02c32cae547b4f66345c607297efc2a4077e6169', '[\"*\"]', '2024-09-02 23:23:30', NULL, '2024-09-02 20:45:27', '2024-09-02 23:23:30'),
(110, 'App\\Models\\User', 11, 'TLogin', '06440cd3910b3f1d4cf95632b725ffa8eac57be9fc24e14d12dfcde18da0d559', '[\"*\"]', '2024-09-03 04:07:21', NULL, '2024-09-03 04:07:10', '2024-09-03 04:07:21'),
(112, 'App\\Models\\User', 1, 'create_token_a6febfe3-63d2-436c-9c9a-2c9d1be9913e', 'd7bdb020929343a49e2107ac8afb0cfd7fb3a88d4660718df0a422e6fd42f666', '[\"*\"]', '2024-09-03 03:11:28', NULL, '2024-09-03 03:10:30', '2024-09-03 03:11:28'),
(113, 'App\\Models\\User', 1, 'create_token_c6c51c6b-ebf9-4433-88f1-ff45902bc02d', '6d4dd2307806c6031f0799e15a3fb3836f56a0a3f03ef0bb6662760a430cc7be', '[\"*\"]', '2024-09-03 05:29:35', NULL, '2024-09-03 04:56:13', '2024-09-03 05:29:35'),
(114, 'App\\Models\\User', 1, 'create_token_04f2613b-5447-476b-a1da-dbd4167ab602', '1c1033a8a405d0f5e1d299b136c3b449d0e16a1cc7f4b258e5cf1c6036654ac5', '[\"*\"]', '2024-09-03 18:10:44', NULL, '2024-09-03 18:10:40', '2024-09-03 18:10:44'),
(115, 'App\\Models\\User', 12, 'TLogin', 'e9de697926bdd47904b65faf611b49fa8ef79fd1ad7605885cb04f88d303aaed', '[\"*\"]', '2024-09-04 01:11:49', NULL, '2024-09-04 01:11:44', '2024-09-04 01:11:49'),
(116, 'App\\Models\\User', 1, 'create_token_c5e53be6-71b6-449b-85b1-5ea72700bde8', '0978885e4a42bb9b7343730fda31925999c708a97994e9de8dbeba7f675dec5a', '[\"*\"]', '2024-09-03 19:23:39', NULL, '2024-09-03 18:32:44', '2024-09-03 19:23:39'),
(117, 'App\\Models\\User', 1, 'create_token_5d71a9e3-64fe-4061-b9a0-221f66681348', '902da534f04ba2cde1837f830eff3df9c72694b3f8e7cb1318b356475bee9b18', '[\"*\"]', '2024-09-04 06:49:24', NULL, '2024-09-04 02:33:11', '2024-09-04 06:49:24'),
(118, 'App\\Models\\User', 1, 'create_token_9b321136-c713-469f-91b5-edf03debc4b7', '312e5e7e54709f1675bd8e025de50dd85f98dee9ac49ded338041093859cc753', '[\"*\"]', '2024-09-04 03:50:32', NULL, '2024-09-04 02:50:28', '2024-09-04 03:50:32'),
(119, 'App\\Models\\User', 3, 'TLogin', 'f0c8720a1e21e1f0648373df97f19bbcf803861f37decc51cf4996fdf2424c14', '[\"*\"]', '2024-09-04 13:05:44', NULL, '2024-09-04 12:54:18', '2024-09-04 13:05:44'),
(122, 'App\\Models\\User', 1, 'create_token_51bf63db-5af6-4fd2-9e0b-0c600f4d02ed', '21b9ba6220782f2051a5c08073fe4cc3e46bd7e6a5412df36ba4cb61dc8ec12c', '[\"*\"]', '2024-09-08 18:51:10', NULL, '2024-09-04 06:16:05', '2024-09-08 18:51:10'),
(124, 'App\\Models\\User', 1, 'create_token_ef5d9935-2374-47f0-8e21-527655d237d0', 'd714621e67484705efb9c91fe7d932c8c7e012d4d15eabe37cfb0f283986e750', '[\"*\"]', '2024-09-04 20:26:53', NULL, '2024-09-04 18:08:16', '2024-09-04 20:26:53'),
(127, 'App\\Models\\User', 1, 'create_token_89295f7c-f14a-434d-adfb-0f5c5ee10189', '6c0c8243206a6739fe968a24e12bf2d9df97f427a25bd12a484ffa061d14894e', '[\"*\"]', '2024-09-04 22:25:24', NULL, '2024-09-04 22:24:13', '2024-09-04 22:25:24'),
(128, 'App\\Models\\User', 1, 'create_token_4f9c7df0-8413-4f3c-bd24-fec778772286', '6784fc982b0ff49ab455b36eb122000def3e69333e562ca91bf74b1321832d55', '[\"*\"]', '2024-09-04 23:11:57', NULL, '2024-09-04 23:11:41', '2024-09-04 23:11:57'),
(129, 'App\\Models\\User', 1, 'create_token_94f9c188-c932-436c-96b7-ee3aed7d58bb', '3df4eeffebccff32703c14957f4a7068bb34e0c6dbcf5e8fe34c61e455544fd5', '[\"*\"]', '2024-09-05 22:43:42', NULL, '2024-09-05 00:14:24', '2024-09-05 22:43:42'),
(130, 'App\\Models\\User', 1, 'create_token_06ed6016-a766-4847-8d32-daca6daa3118', '69459cdd6bb8fdf7e35108bdf24a1b8d2a71ade63e65b405e93df2fe27b031fe', '[\"*\"]', '2024-09-05 10:06:59', NULL, '2024-09-05 00:15:40', '2024-09-05 10:06:59'),
(131, 'App\\Models\\User', 1, 'create_token_78c13367-47b6-486a-925d-277e0eb1abe1', 'b541978fa77505c3ff279bc4abe496efb053e39aebcfc94e9244409fcf86b2de', '[\"*\"]', '2024-09-05 05:39:51', NULL, '2024-09-05 05:35:18', '2024-09-05 05:39:51'),
(132, 'App\\Models\\User', 1, 'create_token_7d9dda18-2476-4080-954c-41615824ba56', '43b3695aa09042a9fa0cf7a416a8742831cb96371948e7f0bf17a6b812e19aae', '[\"*\"]', '2024-09-05 20:10:55', NULL, '2024-09-05 20:10:29', '2024-09-05 20:10:55'),
(133, 'App\\Models\\User', 1, 'create_token_45596e73-c063-4ce0-85e7-c5de8bdd33a1', '76fae3474a81e838a1cd1a84cddd0245b1bca578cbf07ca873529828ad13c5ca', '[\"*\"]', '2024-09-05 20:21:17', NULL, '2024-09-05 20:11:38', '2024-09-05 20:21:17'),
(134, 'App\\Models\\User', 48, 'TLogin', '3817239129c845311aff17bc643794919118465e8a0d12ec944c691bcfde3727', '[\"*\"]', '2024-09-06 03:22:51', NULL, '2024-09-06 03:19:07', '2024-09-06 03:22:51'),
(135, 'App\\Models\\User', 1, 'create_token_ad34d27f-5a4a-4bfd-b287-301fea501d34', 'f0cd7f4c01d320387993ae391430025f89218bd38325acdbcc01c5551824f479', '[\"*\"]', '2024-09-05 21:55:34', NULL, '2024-09-05 20:36:49', '2024-09-05 21:55:34'),
(136, 'App\\Models\\User', 1, 'create_token_19a7c759-7ad2-4e06-8852-57bebda1998e', 'd8f30a00cedf888f3189a84e1d9e6900d7b20be6239c932868ecd55ce2f62d07', '[\"*\"]', '2024-09-05 22:27:11', NULL, '2024-09-05 22:13:41', '2024-09-05 22:27:11'),
(137, 'App\\Models\\User', 1, 'create_token_f21dd3ce-b761-484e-b497-34acd2e2ad1d', '4e7ef792d400d8d5769c4be3ffe29548719fab99b4a41ce1fe633d332b6e1a7e', '[\"*\"]', '2024-09-05 23:58:15', NULL, '2024-09-05 23:31:53', '2024-09-05 23:58:15'),
(140, 'App\\Models\\User', 50, 'TLogin', 'f16181a0bae8402d1d8119ae2af485344d28cc271072ce16fc0c286f459b00fd', '[\"*\"]', '2024-09-06 09:37:03', NULL, '2024-09-06 09:32:45', '2024-09-06 09:37:03'),
(141, 'App\\Models\\User', 22, 'TLogin', '5afedf31412c1cc45066315ef4e58a7d89c796235cd8dbfb8c05e667dd7d4c99', '[\"*\"]', '2024-09-06 11:17:51', NULL, '2024-09-06 11:17:18', '2024-09-06 11:17:51'),
(142, 'App\\Models\\User', 11, 'TLogin', 'c0162c1f23ff08def83a3ed5eb4c636f72e92a9a867908a6bdc12ab2fffc0874', '[\"*\"]', '2024-09-06 11:23:11', NULL, '2024-09-06 11:22:42', '2024-09-06 11:23:11'),
(143, 'App\\Models\\User', 1, 'create_token_eabbc31a-8754-4ae1-b32f-8fb78e5d7af1', 'a65c3ad5527c69e0c963665bd021043f3a665632ded918e8feb9f060d375f938', '[\"*\"]', '2024-09-06 05:56:58', NULL, '2024-09-06 04:41:37', '2024-09-06 05:56:58'),
(144, 'App\\Models\\User', 24, 'TLogin', '260da51da166363f8558c8e5de800960e776e98bde4205c5ba77831cc1ab17e4', '[\"*\"]', '2024-09-06 13:07:11', NULL, '2024-09-06 12:53:53', '2024-09-06 13:07:11'),
(145, 'App\\Models\\User', 24, 'TLogin', '9f27b37ad1d4d6a695e8ada3ec757f5c1c9634541bc757a216c97cf334129bd2', '[\"*\"]', '2024-09-07 05:51:05', NULL, '2024-09-07 04:30:00', '2024-09-07 05:51:05'),
(146, 'App\\Models\\User', 1, 'create_token_6eeff3f1-4b5b-4883-bef8-5f72aae1d34d', '9ddb625cfa74d68ef8f7923e6a2adba5468f9e2f8df57b99e4f07f34fac1c7e1', '[\"*\"]', '2024-09-07 03:23:49', NULL, '2024-09-07 03:23:48', '2024-09-07 03:23:49'),
(156, 'App\\Models\\User', 12, 'TLogin', 'a467b75c8f0c1700f8c9dfd7207e164f0aa6c256b258741d5e25388210ebfe70', '[\"*\"]', '2024-09-08 10:39:49', NULL, '2024-09-08 10:38:45', '2024-09-08 10:39:49'),
(158, 'App\\Models\\User', 1, 'create_token_948137e6-814b-4a37-a3ba-1fd1b1e2101b', 'a024370c6d750b2022fd15d471f73cb5a21e4e38ff5f6f7fa16a5cbf4296bcd4', '[\"*\"]', '2024-09-08 20:21:28', NULL, '2024-09-08 18:16:28', '2024-09-08 20:21:28'),
(159, 'App\\Models\\User', 24, 'TLogin', '4ef3e81ace38b5cdd7f795db204b5e98cf3de90cb2a64cb257c2c08b2df114b8', '[\"*\"]', '2024-09-09 01:27:30', NULL, '2024-09-09 01:24:07', '2024-09-09 01:27:30'),
(160, 'App\\Models\\User', 1, 'create_token_5cbed055-5123-4793-85a7-85593c65afa4', '00ad7ac42d5202b8f26932a2302213b1beb0a2bd90ca549728bcca8bc85c14e2', '[\"*\"]', '2024-09-08 19:14:33', NULL, '2024-09-08 19:03:39', '2024-09-08 19:14:33'),
(161, 'App\\Models\\User', 58, 'TLogin', '36cf00db457f7f34b81e6d14eb31fa8c4d598280ae5e66856049d200cf5f3d4a', '[\"*\"]', NULL, NULL, '2024-09-09 03:15:48', '2024-09-09 03:15:48'),
(162, 'App\\Models\\User', 1, 'create_token_f57df6b1-59bc-471c-8fa6-1776359cca80', 'd548b434076148d434caa9cfd9920c31d9b7e3d08f38d82723e428c8c6c16400', '[\"*\"]', '2024-09-08 21:47:34', NULL, '2024-09-08 21:46:38', '2024-09-08 21:47:34'),
(164, 'App\\Models\\User', 1, 'create_token_58946b2e-00bc-4fca-85f5-ef59b331eeb9', 'bceb40bcb0ce2c1655cb15021ea7f72a8edf901f9791a529ba44e474ed1cca41', '[\"*\"]', '2024-09-09 00:04:38', NULL, '2024-09-08 23:26:59', '2024-09-09 00:04:38');

-- --------------------------------------------------------

--
-- Table structure for table `pertanyaans`
--

CREATE TABLE `pertanyaans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pertanyaan` varchar(255) NOT NULL,
  `jenis_penilaian_id` bigint(20) UNSIGNED NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `perubahan_berkas`
--

CREATE TABLE `perubahan_berkas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `riwayat_perubahan_id` bigint(20) UNSIGNED NOT NULL,
  `berkas_id` bigint(20) UNSIGNED NOT NULL,
  `file_id` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `tgl_upload` datetime NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `ext` varchar(255) DEFAULT NULL,
  `size` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `perubahan_keluargas`
--

CREATE TABLE `perubahan_keluargas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `riwayat_perubahan_id` bigint(20) UNSIGNED NOT NULL,
  `data_keluarga_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nama_keluarga` varchar(255) NOT NULL,
  `hubungan` enum('Ayah','Ibu','Anak','Suami','Istri','Nenek','Kakek','Ayah Suami','Ibu Suami','Ayah Istri','Ibu Istri') NOT NULL,
  `pendidikan_terakhir` bigint(20) UNSIGNED NOT NULL,
  `status_hidup` tinyint(1) NOT NULL,
  `pekerjaan` varchar(255) DEFAULT NULL,
  `no_hp` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `perubahan_personals`
--

CREATE TABLE `perubahan_personals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `riwayat_perubahan_id` bigint(20) UNSIGNED NOT NULL,
  `tempat_lahir` varchar(255) NOT NULL,
  `tgl_lahir` varchar(255) NOT NULL,
  `no_hp` varchar(255) NOT NULL,
  `jenis_kelamin` tinyint(1) NOT NULL,
  `nik_ktp` varchar(255) NOT NULL,
  `no_kk` varchar(255) NOT NULL,
  `kategori_agama_id` bigint(20) UNSIGNED NOT NULL,
  `kategori_darah_id` bigint(20) UNSIGNED NOT NULL,
  `tinggi_badan` int(11) NOT NULL,
  `berat_badan` int(11) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `no_ijasah` varchar(255) NOT NULL,
  `tahun_lulus` int(11) NOT NULL,
  `pendidikan_terakhir` bigint(20) UNSIGNED NOT NULL,
  `gelar_depan` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `peserta_diklats`
--

CREATE TABLE `peserta_diklats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `diklat_id` bigint(20) UNSIGNED NOT NULL,
  `peserta` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `premis`
--

CREATE TABLE `premis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_premi` varchar(255) NOT NULL,
  `kategori_potongan_id` bigint(20) UNSIGNED NOT NULL,
  `jenis_premi` tinyint(1) NOT NULL,
  `besaran_premi` int(11) NOT NULL,
  `minimal_rate` int(11) DEFAULT NULL,
  `maksimal_rate` int(11) DEFAULT NULL,
  `has_custom_formula` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `premis`
--

INSERT INTO `premis` (`id`, `nama_premi`, `kategori_potongan_id`, `jenis_premi`, `besaran_premi`, `minimal_rate`, `maksimal_rate`, `has_custom_formula`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'BPJS Kesehatan', 1, 0, 1, NULL, 12000000, 1, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'BPJS Ketenagakerjaan', 2, 1, 12000, 520000, 700000, 1, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Iuran Pensiun', 2, 1, 150000, NULL, NULL, 0, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Jaminan Hari Tua', 2, 0, 1, NULL, NULL, 0, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `presensis`
--

CREATE TABLE `presensis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `data_karyawan_id` bigint(20) UNSIGNED NOT NULL,
  `jadwal_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jam_masuk` varchar(255) NOT NULL,
  `jam_keluar` varchar(255) DEFAULT NULL,
  `durasi` int(11) DEFAULT NULL,
  `lat` varchar(255) NOT NULL,
  `long` varchar(255) NOT NULL,
  `latkeluar` varchar(255) DEFAULT NULL,
  `longkeluar` varchar(255) DEFAULT NULL,
  `foto_masuk` bigint(20) UNSIGNED DEFAULT NULL,
  `foto_keluar` bigint(20) UNSIGNED DEFAULT NULL,
  `kategori_presensi_id` bigint(20) UNSIGNED NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ptkps`
--

CREATE TABLE `ptkps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_ptkp` varchar(255) NOT NULL,
  `kategori_ter_id` bigint(20) UNSIGNED NOT NULL,
  `nilai` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ptkps`
--

INSERT INTO `ptkps` (`id`, `kode_ptkp`, `kategori_ter_id`, `nilai`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'TK/0', 1, 54000000, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'TK/1', 1, 58500000, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'K/0', 1, 58500000, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'TK/2', 2, 63000000, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(5, 'TK/3', 2, 67500000, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(6, 'K/1', 2, 63000000, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(7, 'K/2', 2, 67500000, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(8, 'K/3', 3, 72000000, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `reward_bulan_lalus`
--

CREATE TABLE `reward_bulan_lalus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `data_karyawan_id` bigint(20) UNSIGNED NOT NULL,
  `status_reward` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reward_bulan_lalus`
--

INSERT INTO `reward_bulan_lalus` (`id`, `data_karyawan_id`, `status_reward`, `created_at`, `updated_at`) VALUES
(1, 2, 0, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(2, 3, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(3, 4, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(4, 5, 0, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(5, 6, 0, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(6, 7, 0, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(7, 8, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(8, 9, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(9, 10, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(10, 11, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(11, 12, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(12, 13, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(13, 14, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(14, 15, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(15, 16, 0, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(16, 17, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(17, 18, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(18, 19, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(19, 20, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(20, 21, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(21, 22, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(22, 23, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(23, 24, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(24, 25, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(25, 26, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(26, 27, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(27, 28, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(28, 29, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(29, 30, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(30, 31, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(31, 32, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(32, 33, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(33, 34, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(34, 35, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(35, 36, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(36, 37, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(37, 38, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(38, 39, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(39, 40, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(40, 41, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(41, 42, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(42, 43, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(43, 44, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(44, 45, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(45, 46, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(46, 47, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(47, 48, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(48, 49, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(49, 50, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04'),
(50, 51, 1, '2024-08-30 10:00:04', '2024-08-30 10:00:04');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_izins`
--

CREATE TABLE `riwayat_izins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tgl_izin` varchar(255) NOT NULL,
  `waktu_izin` varchar(255) NOT NULL,
  `durasi` int(11) NOT NULL,
  `keterangan` text NOT NULL,
  `status_izin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `verifikator_1` bigint(20) UNSIGNED DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_penggajians`
--

CREATE TABLE `riwayat_penggajians` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `periode` date NOT NULL,
  `karyawan_verifikasi` int(11) NOT NULL,
  `jenis_riwayat` tinyint(1) NOT NULL,
  `status_gaji_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_perubahans`
--

CREATE TABLE `riwayat_perubahans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `data_karyawan_id` bigint(20) UNSIGNED NOT NULL,
  `jenis_perubahan` enum('Keluarga','Personal') NOT NULL,
  `kolom` varchar(255) NOT NULL,
  `original_data` text NOT NULL,
  `updated_data` text NOT NULL,
  `status_perubahan_id` bigint(20) UNSIGNED NOT NULL,
  `verifikator_1` bigint(20) UNSIGNED DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `deskripsi`, `deleted_at`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'entitas awal', NULL, 'web', '2024-05-12 08:10:20', '2024-08-30 01:35:46'),
(2, 'Personalia', 'untuk jabatan Personalia', NULL, 'web', '2024-05-12 08:10:20', '2024-08-30 00:55:42'),
(3, 'Kepala Ruang', 'untuk jabatan Kepala Ruang', NULL, 'web', '2024-05-12 08:10:20', '2024-08-29 08:53:40'),
(4, 'Karyawan', 'untuk Karyawan', NULL, 'web', '2024-05-12 08:10:20', '2024-08-30 01:35:31');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 1),
(2, 2),
(2, 3),
(3, 1),
(3, 2),
(4, 1),
(4, 2),
(4, 3),
(6, 1),
(6, 2),
(7, 1),
(7, 2),
(8, 1),
(8, 2),
(8, 3),
(9, 1),
(9, 2),
(10, 1),
(10, 2),
(11, 1),
(11, 2),
(12, 1),
(12, 2),
(13, 1),
(13, 2),
(13, 3),
(14, 1),
(14, 2),
(15, 1),
(15, 2),
(16, 1),
(16, 2),
(17, 1),
(17, 2),
(17, 3),
(18, 1),
(18, 2),
(19, 1),
(19, 2),
(19, 3),
(20, 1),
(20, 2),
(20, 3),
(22, 1),
(22, 2),
(22, 3),
(23, 1),
(23, 2),
(23, 3),
(24, 1),
(24, 2),
(24, 3),
(25, 1),
(25, 2),
(26, 1),
(26, 2),
(26, 3),
(27, 1),
(27, 2),
(27, 3),
(29, 1),
(29, 2),
(29, 3),
(30, 1),
(30, 2),
(30, 3),
(31, 1),
(31, 2),
(31, 3),
(32, 1),
(32, 2),
(32, 3),
(33, 1),
(33, 2),
(33, 3),
(34, 1),
(34, 2),
(34, 3),
(36, 1),
(36, 2),
(36, 3),
(37, 1),
(37, 2),
(37, 3),
(38, 1),
(38, 2),
(38, 3),
(39, 1),
(39, 2),
(39, 3),
(41, 1),
(41, 2),
(41, 3),
(42, 1),
(42, 2),
(42, 3),
(43, 1),
(43, 2),
(43, 3),
(44, 1),
(44, 2),
(44, 3),
(45, 1),
(45, 2),
(45, 3),
(46, 1),
(46, 2),
(47, 1),
(47, 2),
(48, 1),
(48, 2),
(49, 1),
(49, 2),
(50, 1),
(50, 2),
(50, 3),
(51, 1),
(51, 2),
(52, 1),
(52, 2),
(53, 1),
(53, 2),
(54, 1),
(54, 2),
(55, 1),
(55, 2),
(56, 1),
(56, 2),
(56, 3),
(57, 1),
(58, 1),
(59, 1),
(59, 2),
(60, 1),
(60, 2),
(61, 1),
(61, 2),
(61, 3),
(62, 1),
(62, 2),
(64, 1),
(64, 2),
(64, 3),
(65, 1),
(65, 2),
(66, 1),
(66, 2),
(67, 1),
(67, 2),
(68, 1),
(68, 2),
(68, 3),
(69, 1),
(69, 2),
(70, 1),
(70, 2),
(71, 1),
(71, 2),
(72, 1),
(72, 2),
(72, 3),
(73, 1),
(73, 2),
(74, 1),
(74, 2),
(75, 1),
(75, 2),
(76, 1),
(76, 2),
(76, 3),
(77, 1),
(77, 2),
(78, 1),
(78, 2),
(79, 1),
(79, 2),
(80, 1),
(80, 2),
(80, 3),
(81, 1),
(81, 2),
(82, 1),
(82, 2),
(83, 1),
(83, 2),
(84, 1),
(84, 2),
(84, 3),
(85, 1),
(85, 2),
(86, 1),
(86, 2),
(87, 1),
(87, 2),
(88, 1),
(88, 2),
(88, 3),
(89, 1),
(89, 2),
(90, 1),
(90, 2),
(91, 1),
(91, 2),
(92, 1),
(92, 2),
(92, 3),
(93, 1),
(93, 2),
(94, 1),
(94, 2),
(94, 3),
(95, 1),
(95, 2),
(96, 1),
(96, 2),
(97, 1),
(97, 2),
(98, 1),
(98, 2),
(98, 3),
(99, 1),
(99, 2),
(100, 1),
(100, 2),
(101, 1),
(101, 2),
(102, 1),
(102, 2),
(102, 3),
(103, 1),
(103, 2),
(104, 1),
(104, 2),
(105, 1),
(105, 2),
(106, 1),
(106, 2),
(106, 3),
(107, 1),
(107, 2),
(108, 1),
(108, 2),
(109, 1),
(109, 2),
(110, 1),
(110, 2),
(110, 3),
(111, 1),
(111, 2),
(112, 1),
(112, 2),
(112, 3),
(113, 1),
(113, 2),
(114, 1),
(114, 2),
(114, 3),
(117, 1),
(117, 2),
(118, 1),
(118, 2),
(119, 1),
(119, 2);

-- --------------------------------------------------------

--
-- Table structure for table `run_thrs`
--

CREATE TABLE `run_thrs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `data_karyawan_id` bigint(20) UNSIGNED NOT NULL,
  `tgl_run_thr` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `run_thrs`
--

INSERT INTO `run_thrs` (`id`, `data_karyawan_id`, `tgl_run_thr`, `created_at`, `updated_at`) VALUES
(1, 55, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(2, 51, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(3, 50, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(4, 56, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(5, 2, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(6, 3, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(7, 12, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(8, 13, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(9, 14, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(10, 15, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(11, 16, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(12, 17, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(13, 18, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(14, 19, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(15, 20, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(16, 21, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(17, 4, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(18, 22, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(19, 23, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(20, 24, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(21, 25, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(22, 26, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(23, 27, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(24, 28, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(25, 29, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(26, 30, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(27, 31, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(28, 5, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(29, 32, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(30, 33, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(31, 34, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(32, 35, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(33, 36, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(34, 37, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(35, 38, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(36, 39, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(37, 40, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(38, 41, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(39, 6, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(40, 42, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(41, 43, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(42, 44, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(43, 45, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(44, 46, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(45, 47, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(46, 48, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(47, 49, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(48, 7, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(49, 8, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(50, 9, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(51, 10, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57'),
(52, 11, '2024-10-01', '2024-09-02 07:23:57', '2024-09-02 07:23:57');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `jam_from` varchar(255) DEFAULT NULL,
  `jam_to` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `nama`, `jam_from`, `jam_to`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Pagi', '06:00:00', '16:00:00', NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Sore', '16:00:00', '23:00:00', NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Malam', '23:00:00', '06:00:00', NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `status_aktifs`
--

CREATE TABLE `status_aktifs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_aktifs`
--

INSERT INTO `status_aktifs` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Belum Aktif', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(2, 'Aktif', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(3, 'Tidak Aktif', '2024-08-29 08:10:19', '2024-08-29 08:10:19');

-- --------------------------------------------------------

--
-- Table structure for table `status_berkas`
--

CREATE TABLE `status_berkas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_berkas`
--

INSERT INTO `status_berkas` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Menunggu', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Diverifikasi', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `status_cutis`
--

CREATE TABLE `status_cutis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_cutis`
--

INSERT INTO `status_cutis` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Menunggu Verifikasi', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Verifikasi 1 Disetujui', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Verifikasi 1 Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Verifikasi 2 Disetujui', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(5, 'Verifikasi 2 Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `status_diklats`
--

CREATE TABLE `status_diklats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_diklats`
--

INSERT INTO `status_diklats` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Menunggu Verifikasi', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Verifikasi 1 Disetujui', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Verifikasi 1 Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Verifikasi 2 Disetujui', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(5, 'Verifikasi 2 Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `status_gajis`
--

CREATE TABLE `status_gajis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_gajis`
--

INSERT INTO `status_gajis` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Belum Dipublikasi', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Sudah Dipublikasi', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `status_karyawans`
--

CREATE TABLE `status_karyawans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_karyawans`
--

INSERT INTO `status_karyawans` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Tetap', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Kontrak', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Training', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `status_lemburs`
--

CREATE TABLE `status_lemburs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_lemburs`
--

INSERT INTO `status_lemburs` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Menunggu', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Berlangsung', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Selesai', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `status_perubahans`
--

CREATE TABLE `status_perubahans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_perubahans`
--

INSERT INTO `status_perubahans` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Menunggu', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Diverifikasi', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `status_presensis`
--

CREATE TABLE `status_presensis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `status_riwayat_izins`
--

CREATE TABLE `status_riwayat_izins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_riwayat_izins`
--

INSERT INTO `status_riwayat_izins` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Menunggu', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Disetujui', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `status_tukar_jadwals`
--

CREATE TABLE `status_tukar_jadwals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_tukar_jadwals`
--

INSERT INTO `status_tukar_jadwals` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Menunggu Verifikasi', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Verifikasi 1 Disetujui', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Verifikasi 1 Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Verifikasi 2 Disetujui', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(5, 'Verifikasi 2 Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `ters`
--

CREATE TABLE `ters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kategori_ter_id` bigint(20) UNSIGNED NOT NULL,
  `from_ter` int(11) NOT NULL,
  `to_ter` int(11) DEFAULT NULL,
  `percentage` decimal(4,2) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ters`
--

INSERT INTO `ters` (`id`, `kategori_ter_id`, `from_ter`, `to_ter`, `percentage`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 0, 5400000, 0.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 1, 5400001, 5650000, 0.25, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 1, 5650001, 5950000, 0.50, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 1, 5950001, 6300000, 0.75, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(5, 1, 6300001, 6750000, 1.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(6, 1, 6750001, 7500000, 1.25, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(7, 1, 7500001, 8550000, 1.50, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(8, 1, 8550001, 9650000, 1.75, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(9, 1, 9650001, 10050000, 2.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(10, 1, 10050001, 10350000, 2.25, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(11, 1, 10350001, 10700000, 2.50, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(12, 1, 10700001, 11050000, 3.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(13, 1, 11050001, 11600000, 3.50, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(14, 1, 11600001, 12500000, 4.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(15, 1, 12500001, 13750000, 5.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(16, 1, 13750001, 15100000, 6.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(17, 1, 15100001, 16950000, 7.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(18, 1, 16950001, 19750000, 8.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(19, 1, 19750001, 24150000, 9.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(20, 1, 24150001, 26450000, 10.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(21, 1, 26450001, 28000000, 11.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(22, 1, 28000001, 30050000, 12.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(23, 1, 30050001, 32400000, 13.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(24, 1, 32400001, 35400000, 14.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(25, 1, 35400001, 39100000, 15.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(26, 1, 39100001, 43850000, 16.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(27, 1, 43850001, 47800000, 17.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(28, 1, 47800001, 51400000, 18.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(29, 1, 51400001, 56300000, 19.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(30, 1, 56300001, 62200000, 20.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(31, 1, 62200001, 68600000, 21.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(32, 1, 68600001, 77500000, 22.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(33, 1, 77500001, 89000000, 23.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(34, 1, 89000001, 103000000, 24.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(35, 1, 103000001, 125000000, 25.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(36, 1, 125000001, 157000000, 26.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(37, 1, 157000001, 206000000, 27.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(38, 1, 206000001, 337000000, 28.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(39, 1, 337000001, 454000000, 29.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(40, 1, 454000001, 550000000, 30.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(41, 1, 550000001, 695000000, 31.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(42, 1, 695000001, 910000000, 32.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(43, 1, 910000001, 1400000000, 33.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(44, 1, 1400000001, NULL, 34.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(45, 2, 0, 6200000, 0.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(46, 2, 6200001, 6500000, 0.25, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(47, 2, 6500001, 6850000, 0.50, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(48, 2, 6850001, 7300000, 0.75, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(49, 2, 7300001, 9200000, 1.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(50, 2, 9200001, 10750000, 1.50, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(51, 2, 10750001, 11250000, 2.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(52, 2, 11250001, 11600000, 2.50, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(53, 2, 11600001, 12600000, 3.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(54, 2, 12600001, 13600000, 4.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(55, 2, 13600001, 14950000, 5.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(56, 2, 14950001, 16400000, 6.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(57, 2, 16400001, 18450000, 7.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(58, 2, 18450001, 21850000, 8.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(59, 2, 21850001, 26000000, 9.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(60, 2, 26000001, 27700000, 10.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(61, 2, 27700001, 29350000, 11.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(62, 2, 29350001, 31450000, 12.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(63, 2, 31450001, 33950000, 13.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(64, 2, 33950001, 37100000, 14.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(65, 2, 37100001, 41100000, 15.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(66, 2, 41100001, 45800000, 16.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(67, 2, 45800001, 49500000, 17.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(68, 2, 49500001, 53800000, 18.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(69, 2, 53800001, 58500000, 19.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(70, 2, 58500001, 64000000, 20.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(71, 2, 64000001, 71000000, 21.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(72, 2, 71000001, 80000000, 22.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(73, 2, 80000001, 93000000, 23.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(74, 2, 93000001, 109000000, 24.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(75, 2, 109000001, 129000000, 25.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(76, 2, 129000001, 163000000, 26.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(77, 2, 163000001, 211000000, 27.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(78, 2, 211000001, 374000000, 28.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(79, 2, 374000001, 459000000, 29.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(80, 2, 459000001, 555000000, 30.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(81, 2, 555000001, 704000000, 31.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(82, 2, 704000001, 957000000, 32.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(83, 2, 957000001, 1405000000, 33.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(84, 2, 1405000001, NULL, 34.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(85, 3, 0, 6600000, 0.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(86, 3, 6600001, 6950000, 0.25, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(87, 3, 6950001, 7350000, 0.50, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(88, 3, 7350001, 7800000, 0.75, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(89, 3, 7800001, 8850000, 1.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(90, 3, 8850001, 9800000, 1.25, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(91, 3, 9800001, 10950000, 1.50, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(92, 3, 10950001, 11200000, 1.75, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(93, 3, 11200001, 12050000, 2.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(94, 3, 12050001, 12950000, 3.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(95, 3, 12950001, 14150000, 4.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(96, 3, 14150001, 15550000, 5.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(97, 3, 15550001, 17050000, 6.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(98, 3, 17050001, 19500000, 7.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(99, 3, 19500001, 22700000, 8.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(100, 3, 22700001, 26600000, 9.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(101, 3, 26600001, 28100000, 10.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(102, 3, 28100001, 30100000, 11.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(103, 3, 30100001, 32600000, 12.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(104, 3, 32600001, 35400000, 13.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(105, 3, 35400001, 38900000, 14.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(106, 3, 38900001, 43000000, 15.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(107, 3, 43000001, 47400000, 16.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(108, 3, 47400001, 51200000, 17.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(109, 3, 51200001, 55800000, 18.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(110, 3, 55800001, 60400000, 19.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(111, 3, 60400001, 66700000, 20.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(112, 3, 66700001, 74500000, 21.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(113, 3, 74500001, 83200000, 22.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(114, 3, 83200001, 95000000, 23.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(115, 3, 95000001, 110000000, 24.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(116, 3, 110000001, 134000000, 25.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(117, 3, 134000001, 169000000, 26.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(118, 3, 169000001, 221000000, 27.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(119, 3, 221000001, 390000000, 28.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(120, 3, 390000001, 463000000, 29.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(121, 3, 463000001, 561000000, 30.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(122, 3, 561000001, 709000000, 31.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(123, 3, 709000001, 965000000, 32.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(124, 3, 965000001, 1419000000, 33.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(125, 3, 1419000001, NULL, 34.00, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `thrs`
--

CREATE TABLE `thrs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `perhitungan` varchar(255) NOT NULL,
  `nominal_satu` int(11) DEFAULT NULL,
  `nominal_dua` int(11) DEFAULT NULL,
  `potongan` varchar(255) DEFAULT NULL,
  `kriteria_karyawan_kontrak` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipe_cutis`
--

CREATE TABLE `tipe_cutis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `kuota` int(11) NOT NULL,
  `is_need_requirement` tinyint(1) NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `cuti_administratif` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tipe_cutis`
--

INSERT INTO `tipe_cutis` (`id`, `nama`, `kuota`, `is_need_requirement`, `keterangan`, `cuti_administratif`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Cuti Tahunan', 12, 0, 'Maksimal cuti 12 hari', 1, NULL, NULL, NULL),
(2, 'Cuti Melahirkan', 90, 0, 'Maksimal cuti 3 bulan setelah melahirkan', 0, NULL, NULL, NULL),
(3, 'Cuti Sakit', 30, 1, 'Cuti membutuhkan surat dokter', 0, NULL, NULL, NULL),
(4, 'Cuti Luar Tanggungan', 30, 0, 'Maksimal cuti 30 hari dalam 1 tahun', 0, NULL, NULL, NULL),
(5, 'Cuti Besar', 12, 1, 'Maksimal cuti 12 hari, jika masa kerja lebih dari 8 tahun', 1, NULL, NULL, NULL),
(6, 'Cuti Nikah', 3, 0, 'Maksimal cuti 3 hari (untuk pernikahan pertama)', 0, NULL, NULL, NULL),
(7, 'Cuti Kematian', 2, 0, 'Maksimal cuti 2 hari (sejak tanggal kematian keluarga/saudara/kerabat)', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `track_records`
--

CREATE TABLE `track_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `kategori_record_id` bigint(20) UNSIGNED NOT NULL,
  `tgl_masuk` varchar(255) NOT NULL,
  `tgl_keluar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transfer_karyawans`
--

CREATE TABLE `transfer_karyawans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tgl_mulai` varchar(255) NOT NULL,
  `unit_kerja_asal` bigint(20) UNSIGNED NOT NULL,
  `unit_kerja_tujuan` bigint(20) UNSIGNED DEFAULT NULL,
  `jabatan_asal` bigint(20) UNSIGNED NOT NULL,
  `jabatan_tujuan` bigint(20) UNSIGNED DEFAULT NULL,
  `kelompok_gaji_asal` bigint(20) UNSIGNED NOT NULL,
  `kelompok_gaji_tujuan` bigint(20) UNSIGNED DEFAULT NULL,
  `role_asal` bigint(20) UNSIGNED NOT NULL,
  `role_tujuan` bigint(20) UNSIGNED DEFAULT NULL,
  `kategori_transfer_id` bigint(20) UNSIGNED NOT NULL,
  `alasan` text NOT NULL,
  `dokumen` varchar(255) NOT NULL,
  `is_processed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tukar_jadwals`
--

CREATE TABLE `tukar_jadwals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_pengajuan` bigint(20) UNSIGNED NOT NULL,
  `jadwal_pengajuan` bigint(20) UNSIGNED NOT NULL,
  `user_ditukar` bigint(20) UNSIGNED NOT NULL,
  `jadwal_ditukar` bigint(20) UNSIGNED NOT NULL,
  `status_penukaran_id` bigint(20) UNSIGNED NOT NULL,
  `kategori_penukaran_id` bigint(20) UNSIGNED NOT NULL,
  `verifikator_1` bigint(20) UNSIGNED DEFAULT NULL,
  `verifikator_2` bigint(20) UNSIGNED DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `unit_kerjas`
--

CREATE TABLE `unit_kerjas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_unit` varchar(255) NOT NULL,
  `jenis_karyawan` tinyint(1) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `data_karyawan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `data_completion_step` int(11) NOT NULL DEFAULT 1,
  `status_aktif` bigint(20) UNSIGNED NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `remember_token_expired_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email_verified_at`, `password`, `role_id`, `data_karyawan_id`, `foto_profil`, `data_completion_step`, `status_aktif`, `remember_token`, `remember_token_expired_at`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', NULL, '$2y$12$oi0EMhrCJcKaBTUTTt6FRu9UUSoTd.thhHEBA1pTWjhI/03rWRUKu', NULL, NULL, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:20', '2024-08-29 17:26:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_kategori_activity_id_foreign` (`kategori_activity_id`),
  ADD KEY `activity_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `berkas`
--
ALTER TABLE `berkas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `berkas_user_id_foreign` (`user_id`),
  ADD KEY `berkas_kategori_berkas_id_foreign` (`kategori_berkas_id`),
  ADD KEY `berkas_status_berkas_id_foreign` (`status_berkas_id`),
  ADD KEY `berkas_verifikator_1_foreign` (`verifikator_1`);

--
-- Indexes for table `cutis`
--
ALTER TABLE `cutis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cutis_user_id_foreign` (`user_id`),
  ADD KEY `cutis_tipe_cuti_id_foreign` (`tipe_cuti_id`),
  ADD KEY `cutis_status_cuti_id_foreign` (`status_cuti_id`),
  ADD KEY `cutis_verifikator_1_foreign` (`verifikator_1`),
  ADD KEY `cutis_verifikator_2_foreign` (`verifikator_2`);

--
-- Indexes for table `data_karyawans`
--
ALTER TABLE `data_karyawans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `data_karyawans_email_unique` (`email`),
  ADD KEY `data_karyawans_user_id_foreign` (`user_id`),
  ADD KEY `data_karyawans_unit_kerja_id_foreign` (`unit_kerja_id`),
  ADD KEY `data_karyawans_jabatan_id_foreign` (`jabatan_id`),
  ADD KEY `data_karyawans_kompetensi_id_foreign` (`kompetensi_id`),
  ADD KEY `data_karyawans_kategori_agama_id_foreign` (`kategori_agama_id`),
  ADD KEY `data_karyawans_kategori_darah_id_foreign` (`kategori_darah_id`),
  ADD KEY `data_karyawans_status_karyawan_id_foreign` (`status_karyawan_id`),
  ADD KEY `data_karyawans_kelompok_gaji_id_foreign` (`kelompok_gaji_id`),
  ADD KEY `data_karyawans_ptkp_id_foreign` (`ptkp_id`),
  ADD KEY `data_karyawans_verifikator_1_foreign` (`verifikator_1`);

--
-- Indexes for table `data_keluargas`
--
ALTER TABLE `data_keluargas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `data_keluargas_data_karyawan_id_foreign` (`data_karyawan_id`);

--
-- Indexes for table `detail_gajis`
--
ALTER TABLE `detail_gajis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detail_gajis_penggajian_id_foreign` (`penggajian_id`),
  ADD KEY `detail_gajis_kategori_gaji_id_foreign` (`kategori_gaji_id`);

--
-- Indexes for table `diklats`
--
ALTER TABLE `diklats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `diklats_gambar_foreign` (`gambar`),
  ADD KEY `diklats_dokumen_eksternal_foreign` (`dokumen_eksternal`),
  ADD KEY `diklats_kategori_diklat_id_foreign` (`kategori_diklat_id`),
  ADD KEY `diklats_status_diklat_id_foreign` (`status_diklat_id`),
  ADD KEY `diklats_verifikator_1_foreign` (`verifikator_1`),
  ADD KEY `diklats_verifikator_2_foreign` (`verifikator_2`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `hari_liburs`
--
ALTER TABLE `hari_liburs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jabatans`
--
ALTER TABLE `jabatans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jadwals`
--
ALTER TABLE `jadwals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jadwals_user_id_foreign` (`user_id`);

--
-- Indexes for table `jadwal_penggajians`
--
ALTER TABLE `jadwal_penggajians`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jawabans`
--
ALTER TABLE `jawabans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jawabans_user_penilai_foreign` (`user_penilai`),
  ADD KEY `jawabans_pertanyaan_id_foreign` (`pertanyaan_id`);

--
-- Indexes for table `jenis_penilaians`
--
ALTER TABLE `jenis_penilaians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jenis_penilaians_status_karyawan_id_foreign` (`status_karyawan_id`),
  ADD KEY `jenis_penilaians_jabatan_penilai_foreign` (`jabatan_penilai`),
  ADD KEY `jenis_penilaians_jabatan_dinilai_foreign` (`jabatan_dinilai`);

--
-- Indexes for table `kategori_activity_logs`
--
ALTER TABLE `kategori_activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_agamas`
--
ALTER TABLE `kategori_agamas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_berkas`
--
ALTER TABLE `kategori_berkas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_darahs`
--
ALTER TABLE `kategori_darahs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_diklats`
--
ALTER TABLE `kategori_diklats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_gajis`
--
ALTER TABLE `kategori_gajis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_kompensasis`
--
ALTER TABLE `kategori_kompensasis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_notifikasis`
--
ALTER TABLE `kategori_notifikasis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_pendidikans`
--
ALTER TABLE `kategori_pendidikans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_potongans`
--
ALTER TABLE `kategori_potongans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_presensis`
--
ALTER TABLE `kategori_presensis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_ters`
--
ALTER TABLE `kategori_ters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_track_records`
--
ALTER TABLE `kategori_track_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_transfer_karyawans`
--
ALTER TABLE `kategori_transfer_karyawans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_tukar_jadwals`
--
ALTER TABLE `kategori_tukar_jadwals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kelompok_gajis`
--
ALTER TABLE `kelompok_gajis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kompetensis`
--
ALTER TABLE `kompetensis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lemburs`
--
ALTER TABLE `lemburs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lemburs_user_id_foreign` (`user_id`),
  ADD KEY `lemburs_jadwal_id_foreign` (`jadwal_id`);

--
-- Indexes for table `lokasi_kantors`
--
ALTER TABLE `lokasi_kantors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `non_shifts`
--
ALTER TABLE `non_shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifikasis`
--
ALTER TABLE `notifikasis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifikasis_kategori_notifikasi_id_foreign` (`kategori_notifikasi_id`),
  ADD KEY `notifikasis_user_id_foreign` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pelaporans`
--
ALTER TABLE `pelaporans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pelaporans_pelapor_foreign` (`pelapor`),
  ADD KEY `pelaporans_pelaku_foreign` (`pelaku`),
  ADD KEY `pelaporans_upload_foto_foreign` (`upload_foto`);

--
-- Indexes for table `penggajians`
--
ALTER TABLE `penggajians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penggajians_riwayat_penggajian_id_foreign` (`riwayat_penggajian_id`),
  ADD KEY `penggajians_data_karyawan_id_foreign` (`data_karyawan_id`),
  ADD KEY `penggajians_status_gaji_id_foreign` (`status_gaji_id`);

--
-- Indexes for table `pengumumans`
--
ALTER TABLE `pengumumans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengurang_gajis`
--
ALTER TABLE `pengurang_gajis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengurang_gajis_data_karyawan_id_foreign` (`data_karyawan_id`),
  ADD KEY `pengurang_gajis_premi_id_foreign` (`premi_id`);

--
-- Indexes for table `penilaians`
--
ALTER TABLE `penilaians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penilaians_user_dinilai_foreign` (`user_dinilai`),
  ADD KEY `penilaians_user_penilai_foreign` (`user_penilai`),
  ADD KEY `penilaians_jenis_penilaian_id_foreign` (`jenis_penilaian_id`);

--
-- Indexes for table `penyesuaian_gajis`
--
ALTER TABLE `penyesuaian_gajis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penyesuaian_gajis_penggajian_id_foreign` (`penggajian_id`),
  ADD KEY `penyesuaian_gajis_kategori_gaji_id_foreign` (`kategori_gaji_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `pertanyaans`
--
ALTER TABLE `pertanyaans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pertanyaans_jenis_penilaian_id_foreign` (`jenis_penilaian_id`);

--
-- Indexes for table `perubahan_berkas`
--
ALTER TABLE `perubahan_berkas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `perubahan_berkas_riwayat_perubahan_id_foreign` (`riwayat_perubahan_id`),
  ADD KEY `perubahan_berkas_berkas_id_foreign` (`berkas_id`);

--
-- Indexes for table `perubahan_keluargas`
--
ALTER TABLE `perubahan_keluargas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `perubahan_keluargas_riwayat_perubahan_id_foreign` (`riwayat_perubahan_id`),
  ADD KEY `perubahan_keluargas_data_keluarga_id_foreign` (`data_keluarga_id`),
  ADD KEY `fk_pendidikan_terakhir` (`pendidikan_terakhir`);

--
-- Indexes for table `perubahan_personals`
--
ALTER TABLE `perubahan_personals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `perubahan_personals_riwayat_perubahan_id_foreign` (`riwayat_perubahan_id`),
  ADD KEY `fk_kategori_agama_id` (`kategori_agama_id`),
  ADD KEY `fk_kategori_darah_id` (`kategori_darah_id`),
  ADD KEY `fk_perubahan_personals_pendidikan` (`pendidikan_terakhir`);

--
-- Indexes for table `peserta_diklats`
--
ALTER TABLE `peserta_diklats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peserta_diklats_diklat_id_foreign` (`diklat_id`),
  ADD KEY `peserta_diklats_peserta_foreign` (`peserta`);

--
-- Indexes for table `premis`
--
ALTER TABLE `premis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `premis_kategori_potongan_id_foreign` (`kategori_potongan_id`);

--
-- Indexes for table `presensis`
--
ALTER TABLE `presensis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `presensis_user_id_foreign` (`user_id`),
  ADD KEY `presensis_data_karyawan_id_foreign` (`data_karyawan_id`),
  ADD KEY `presensis_jadwal_id_foreign` (`jadwal_id`),
  ADD KEY `presensis_foto_masuk_foreign` (`foto_masuk`),
  ADD KEY `presensis_foto_keluar_foreign` (`foto_keluar`),
  ADD KEY `presensis_kategori_presensi_id_foreign` (`kategori_presensi_id`);

--
-- Indexes for table `ptkps`
--
ALTER TABLE `ptkps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ptkps_kategori_ter_id_foreign` (`kategori_ter_id`);

--
-- Indexes for table `reward_bulan_lalus`
--
ALTER TABLE `reward_bulan_lalus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reward_bulan_lalus_data_karyawan_id_foreign` (`data_karyawan_id`);

--
-- Indexes for table `riwayat_izins`
--
ALTER TABLE `riwayat_izins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_izins_user_id_foreign` (`user_id`),
  ADD KEY `riwayat_izins_status_izin_id_foreign` (`status_izin_id`),
  ADD KEY `riwayat_izins_verifikator_1_foreign` (`verifikator_1`);

--
-- Indexes for table `riwayat_penggajians`
--
ALTER TABLE `riwayat_penggajians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_penggajians_status_gaji_id_foreign` (`status_gaji_id`),
  ADD KEY `fk_riwayat_penggajians_created_by` (`created_by`),
  ADD KEY `fk_riwayat_penggajians_submitted_by` (`submitted_by`);

--
-- Indexes for table `riwayat_perubahans`
--
ALTER TABLE `riwayat_perubahans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_perubahans_data_karyawan_id_foreign` (`data_karyawan_id`),
  ADD KEY `riwayat_perubahans_status_perubahan_id_foreign` (`status_perubahan_id`),
  ADD KEY `riwayat_perubahans_verifikator_1_foreign` (`verifikator_1`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `run_thrs`
--
ALTER TABLE `run_thrs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `run_thrs_data_karyawan_id_foreign` (`data_karyawan_id`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_aktifs`
--
ALTER TABLE `status_aktifs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_berkas`
--
ALTER TABLE `status_berkas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_cutis`
--
ALTER TABLE `status_cutis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_diklats`
--
ALTER TABLE `status_diklats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_gajis`
--
ALTER TABLE `status_gajis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_karyawans`
--
ALTER TABLE `status_karyawans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_lemburs`
--
ALTER TABLE `status_lemburs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_perubahans`
--
ALTER TABLE `status_perubahans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_presensis`
--
ALTER TABLE `status_presensis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_riwayat_izins`
--
ALTER TABLE `status_riwayat_izins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_tukar_jadwals`
--
ALTER TABLE `status_tukar_jadwals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ters`
--
ALTER TABLE `ters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ters_kategori_ter_id_foreign` (`kategori_ter_id`);

--
-- Indexes for table `thrs`
--
ALTER TABLE `thrs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tipe_cutis`
--
ALTER TABLE `tipe_cutis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `track_records`
--
ALTER TABLE `track_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `track_records_user_id_foreign` (`user_id`),
  ADD KEY `track_records_kategori_record_id_foreign` (`kategori_record_id`);

--
-- Indexes for table `transfer_karyawans`
--
ALTER TABLE `transfer_karyawans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transfer_karyawans_user_id_foreign` (`user_id`),
  ADD KEY `transfer_karyawans_unit_kerja_asal_foreign` (`unit_kerja_asal`),
  ADD KEY `transfer_karyawans_unit_kerja_tujuan_foreign` (`unit_kerja_tujuan`),
  ADD KEY `transfer_karyawans_jabatan_asal_foreign` (`jabatan_asal`),
  ADD KEY `transfer_karyawans_jabatan_tujuan_foreign` (`jabatan_tujuan`),
  ADD KEY `transfer_karyawans_kelompok_gaji_asal_foreign` (`kelompok_gaji_asal`),
  ADD KEY `transfer_karyawans_kelompok_gaji_tujuan_foreign` (`kelompok_gaji_tujuan`),
  ADD KEY `transfer_karyawans_kategori_transfer_id_foreign` (`kategori_transfer_id`);

--
-- Indexes for table `tukar_jadwals`
--
ALTER TABLE `tukar_jadwals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tukar_jadwals_user_pengajuan_foreign` (`user_pengajuan`),
  ADD KEY `tukar_jadwals_user_ditukar_foreign` (`user_ditukar`),
  ADD KEY `tukar_jadwals_status_penukaran_id_foreign` (`status_penukaran_id`),
  ADD KEY `tukar_jadwals_kategori_penukaran_id_foreign` (`kategori_penukaran_id`),
  ADD KEY `tukar_jadwals_verifikator_1_foreign` (`verifikator_1`),
  ADD KEY `tukar_jadwals_verifikator_2_foreign` (`verifikator_2`),
  ADD KEY `tukar_jadwals_jadwal_pengajuan_foreign` (`jadwal_pengajuan`),
  ADD KEY `tukar_jadwals_jadwal_ditukar_foreign` (`jadwal_ditukar`);

--
-- Indexes for table `unit_kerjas`
--
ALTER TABLE `unit_kerjas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_status_aktif_foreign` (`status_aktif`),
  ADD KEY `users_data_karyawan_id_foreign` (`data_karyawan_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `berkas`
--
ALTER TABLE `berkas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cutis`
--
ALTER TABLE `cutis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `data_karyawans`
--
ALTER TABLE `data_karyawans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `data_keluargas`
--
ALTER TABLE `data_keluargas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detail_gajis`
--
ALTER TABLE `detail_gajis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diklats`
--
ALTER TABLE `diklats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hari_liburs`
--
ALTER TABLE `hari_liburs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jabatans`
--
ALTER TABLE `jabatans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwals`
--
ALTER TABLE `jadwals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal_penggajians`
--
ALTER TABLE `jadwal_penggajians`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jawabans`
--
ALTER TABLE `jawabans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jenis_penilaians`
--
ALTER TABLE `jenis_penilaians`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori_activity_logs`
--
ALTER TABLE `kategori_activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kategori_agamas`
--
ALTER TABLE `kategori_agamas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `kategori_berkas`
--
ALTER TABLE `kategori_berkas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kategori_darahs`
--
ALTER TABLE `kategori_darahs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `kategori_diklats`
--
ALTER TABLE `kategori_diklats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kategori_gajis`
--
ALTER TABLE `kategori_gajis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori_kompensasis`
--
ALTER TABLE `kategori_kompensasis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kategori_notifikasis`
--
ALTER TABLE `kategori_notifikasis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `kategori_pendidikans`
--
ALTER TABLE `kategori_pendidikans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `kategori_potongans`
--
ALTER TABLE `kategori_potongans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kategori_presensis`
--
ALTER TABLE `kategori_presensis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kategori_ters`
--
ALTER TABLE `kategori_ters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori_track_records`
--
ALTER TABLE `kategori_track_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori_transfer_karyawans`
--
ALTER TABLE `kategori_transfer_karyawans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kategori_tukar_jadwals`
--
ALTER TABLE `kategori_tukar_jadwals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kelompok_gajis`
--
ALTER TABLE `kelompok_gajis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kompetensis`
--
ALTER TABLE `kompetensis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lemburs`
--
ALTER TABLE `lemburs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lokasi_kantors`
--
ALTER TABLE `lokasi_kantors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `non_shifts`
--
ALTER TABLE `non_shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifikasis`
--
ALTER TABLE `notifikasis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pelaporans`
--
ALTER TABLE `pelaporans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penggajians`
--
ALTER TABLE `penggajians`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengumumans`
--
ALTER TABLE `pengumumans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pengurang_gajis`
--
ALTER TABLE `pengurang_gajis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `penilaians`
--
ALTER TABLE `penilaians`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penyesuaian_gajis`
--
ALTER TABLE `penyesuaian_gajis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=165;

--
-- AUTO_INCREMENT for table `pertanyaans`
--
ALTER TABLE `pertanyaans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `perubahan_berkas`
--
ALTER TABLE `perubahan_berkas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `perubahan_keluargas`
--
ALTER TABLE `perubahan_keluargas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `perubahan_personals`
--
ALTER TABLE `perubahan_personals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `peserta_diklats`
--
ALTER TABLE `peserta_diklats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `premis`
--
ALTER TABLE `premis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `presensis`
--
ALTER TABLE `presensis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ptkps`
--
ALTER TABLE `ptkps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reward_bulan_lalus`
--
ALTER TABLE `reward_bulan_lalus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `riwayat_izins`
--
ALTER TABLE `riwayat_izins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `riwayat_penggajians`
--
ALTER TABLE `riwayat_penggajians`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `riwayat_perubahans`
--
ALTER TABLE `riwayat_perubahans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `run_thrs`
--
ALTER TABLE `run_thrs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `status_aktifs`
--
ALTER TABLE `status_aktifs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `status_berkas`
--
ALTER TABLE `status_berkas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `status_cutis`
--
ALTER TABLE `status_cutis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `status_diklats`
--
ALTER TABLE `status_diklats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `status_gajis`
--
ALTER TABLE `status_gajis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `status_karyawans`
--
ALTER TABLE `status_karyawans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `status_lemburs`
--
ALTER TABLE `status_lemburs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `status_perubahans`
--
ALTER TABLE `status_perubahans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `status_presensis`
--
ALTER TABLE `status_presensis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `status_riwayat_izins`
--
ALTER TABLE `status_riwayat_izins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `status_tukar_jadwals`
--
ALTER TABLE `status_tukar_jadwals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ters`
--
ALTER TABLE `ters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `thrs`
--
ALTER TABLE `thrs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tipe_cutis`
--
ALTER TABLE `tipe_cutis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `track_records`
--
ALTER TABLE `track_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfer_karyawans`
--
ALTER TABLE `transfer_karyawans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tukar_jadwals`
--
ALTER TABLE `tukar_jadwals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unit_kerjas`
--
ALTER TABLE `unit_kerjas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_kategori_activity_id_foreign` FOREIGN KEY (`kategori_activity_id`) REFERENCES `kategori_activity_logs` (`id`),
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `berkas`
--
ALTER TABLE `berkas`
  ADD CONSTRAINT `berkas_kategori_berkas_id_foreign` FOREIGN KEY (`kategori_berkas_id`) REFERENCES `kategori_berkas` (`id`),
  ADD CONSTRAINT `berkas_status_berkas_id_foreign` FOREIGN KEY (`status_berkas_id`) REFERENCES `status_berkas` (`id`),
  ADD CONSTRAINT `berkas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `berkas_verifikator_1_foreign` FOREIGN KEY (`verifikator_1`) REFERENCES `users` (`id`);

--
-- Constraints for table `cutis`
--
ALTER TABLE `cutis`
  ADD CONSTRAINT `cutis_status_cuti_id_foreign` FOREIGN KEY (`status_cuti_id`) REFERENCES `status_cutis` (`id`),
  ADD CONSTRAINT `cutis_tipe_cuti_id_foreign` FOREIGN KEY (`tipe_cuti_id`) REFERENCES `tipe_cutis` (`id`),
  ADD CONSTRAINT `cutis_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cutis_verifikator_1_foreign` FOREIGN KEY (`verifikator_1`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cutis_verifikator_2_foreign` FOREIGN KEY (`verifikator_2`) REFERENCES `users` (`id`);

--
-- Constraints for table `data_karyawans`
--
ALTER TABLE `data_karyawans`
  ADD CONSTRAINT `data_karyawans_jabatan_id_foreign` FOREIGN KEY (`jabatan_id`) REFERENCES `jabatans` (`id`),
  ADD CONSTRAINT `data_karyawans_kategori_agama_id_foreign` FOREIGN KEY (`kategori_agama_id`) REFERENCES `kategori_agamas` (`id`),
  ADD CONSTRAINT `data_karyawans_kategori_darah_id_foreign` FOREIGN KEY (`kategori_darah_id`) REFERENCES `kategori_darahs` (`id`),
  ADD CONSTRAINT `data_karyawans_kelompok_gaji_id_foreign` FOREIGN KEY (`kelompok_gaji_id`) REFERENCES `kelompok_gajis` (`id`),
  ADD CONSTRAINT `data_karyawans_kompetensi_id_foreign` FOREIGN KEY (`kompetensi_id`) REFERENCES `kompetensis` (`id`),
  ADD CONSTRAINT `data_karyawans_ptkp_id_foreign` FOREIGN KEY (`ptkp_id`) REFERENCES `ptkps` (`id`),
  ADD CONSTRAINT `data_karyawans_status_karyawan_id_foreign` FOREIGN KEY (`status_karyawan_id`) REFERENCES `status_karyawans` (`id`),
  ADD CONSTRAINT `data_karyawans_unit_kerja_id_foreign` FOREIGN KEY (`unit_kerja_id`) REFERENCES `unit_kerjas` (`id`),
  ADD CONSTRAINT `data_karyawans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `data_karyawans_verifikator_1_foreign` FOREIGN KEY (`verifikator_1`) REFERENCES `users` (`id`);

--
-- Constraints for table `data_keluargas`
--
ALTER TABLE `data_keluargas`
  ADD CONSTRAINT `data_keluargas_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`);

--
-- Constraints for table `detail_gajis`
--
ALTER TABLE `detail_gajis`
  ADD CONSTRAINT `detail_gajis_kategori_gaji_id_foreign` FOREIGN KEY (`kategori_gaji_id`) REFERENCES `kategori_gajis` (`id`),
  ADD CONSTRAINT `detail_gajis_penggajian_id_foreign` FOREIGN KEY (`penggajian_id`) REFERENCES `penggajians` (`id`);

--
-- Constraints for table `diklats`
--
ALTER TABLE `diklats`
  ADD CONSTRAINT `diklats_dokumen_eksternal_foreign` FOREIGN KEY (`dokumen_eksternal`) REFERENCES `berkas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `diklats_gambar_foreign` FOREIGN KEY (`gambar`) REFERENCES `berkas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `diklats_kategori_diklat_id_foreign` FOREIGN KEY (`kategori_diklat_id`) REFERENCES `kategori_diklats` (`id`),
  ADD CONSTRAINT `diklats_status_diklat_id_foreign` FOREIGN KEY (`status_diklat_id`) REFERENCES `status_diklats` (`id`),
  ADD CONSTRAINT `diklats_verifikator_1_foreign` FOREIGN KEY (`verifikator_1`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `diklats_verifikator_2_foreign` FOREIGN KEY (`verifikator_2`) REFERENCES `users` (`id`);

--
-- Constraints for table `jadwals`
--
ALTER TABLE `jadwals`
  ADD CONSTRAINT `jadwals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `jawabans`
--
ALTER TABLE `jawabans`
  ADD CONSTRAINT `jawabans_pertanyaan_id_foreign` FOREIGN KEY (`pertanyaan_id`) REFERENCES `pertanyaans` (`id`),
  ADD CONSTRAINT `jawabans_user_penilai_foreign` FOREIGN KEY (`user_penilai`) REFERENCES `users` (`id`);

--
-- Constraints for table `jenis_penilaians`
--
ALTER TABLE `jenis_penilaians`
  ADD CONSTRAINT `jenis_penilaians_jabatan_dinilai_foreign` FOREIGN KEY (`jabatan_dinilai`) REFERENCES `jabatans` (`id`),
  ADD CONSTRAINT `jenis_penilaians_jabatan_penilai_foreign` FOREIGN KEY (`jabatan_penilai`) REFERENCES `jabatans` (`id`),
  ADD CONSTRAINT `jenis_penilaians_status_karyawan_id_foreign` FOREIGN KEY (`status_karyawan_id`) REFERENCES `status_karyawans` (`id`);

--
-- Constraints for table `lemburs`
--
ALTER TABLE `lemburs`
  ADD CONSTRAINT `lemburs_jadwal_id_foreign` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwals` (`id`),
  ADD CONSTRAINT `lemburs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifikasis`
--
ALTER TABLE `notifikasis`
  ADD CONSTRAINT `notifikasis_kategori_notifikasi_id_foreign` FOREIGN KEY (`kategori_notifikasi_id`) REFERENCES `kategori_notifikasis` (`id`),
  ADD CONSTRAINT `notifikasis_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pelaporans`
--
ALTER TABLE `pelaporans`
  ADD CONSTRAINT `pelaporans_pelaku_foreign` FOREIGN KEY (`pelaku`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pelaporans_pelapor_foreign` FOREIGN KEY (`pelapor`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pelaporans_upload_foto_foreign` FOREIGN KEY (`upload_foto`) REFERENCES `berkas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `penggajians`
--
ALTER TABLE `penggajians`
  ADD CONSTRAINT `penggajians_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`),
  ADD CONSTRAINT `penggajians_riwayat_penggajian_id_foreign` FOREIGN KEY (`riwayat_penggajian_id`) REFERENCES `riwayat_penggajians` (`id`),
  ADD CONSTRAINT `penggajians_status_gaji_id_foreign` FOREIGN KEY (`status_gaji_id`) REFERENCES `status_gajis` (`id`);

--
-- Constraints for table `pengurang_gajis`
--
ALTER TABLE `pengurang_gajis`
  ADD CONSTRAINT `pengurang_gajis_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`),
  ADD CONSTRAINT `pengurang_gajis_premi_id_foreign` FOREIGN KEY (`premi_id`) REFERENCES `premis` (`id`);

--
-- Constraints for table `penilaians`
--
ALTER TABLE `penilaians`
  ADD CONSTRAINT `penilaians_jenis_penilaian_id_foreign` FOREIGN KEY (`jenis_penilaian_id`) REFERENCES `jenis_penilaians` (`id`),
  ADD CONSTRAINT `penilaians_user_dinilai_foreign` FOREIGN KEY (`user_dinilai`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `penilaians_user_penilai_foreign` FOREIGN KEY (`user_penilai`) REFERENCES `users` (`id`);

--
-- Constraints for table `penyesuaian_gajis`
--
ALTER TABLE `penyesuaian_gajis`
  ADD CONSTRAINT `penyesuaian_gajis_kategori_gaji_id_foreign` FOREIGN KEY (`kategori_gaji_id`) REFERENCES `kategori_gajis` (`id`),
  ADD CONSTRAINT `penyesuaian_gajis_penggajian_id_foreign` FOREIGN KEY (`penggajian_id`) REFERENCES `penggajians` (`id`);

--
-- Constraints for table `pertanyaans`
--
ALTER TABLE `pertanyaans`
  ADD CONSTRAINT `pertanyaans_jenis_penilaian_id_foreign` FOREIGN KEY (`jenis_penilaian_id`) REFERENCES `jenis_penilaians` (`id`);

--
-- Constraints for table `perubahan_berkas`
--
ALTER TABLE `perubahan_berkas`
  ADD CONSTRAINT `perubahan_berkas_berkas_id_foreign` FOREIGN KEY (`berkas_id`) REFERENCES `berkas` (`id`),
  ADD CONSTRAINT `perubahan_berkas_riwayat_perubahan_id_foreign` FOREIGN KEY (`riwayat_perubahan_id`) REFERENCES `riwayat_perubahans` (`id`);

--
-- Constraints for table `perubahan_keluargas`
--
ALTER TABLE `perubahan_keluargas`
  ADD CONSTRAINT `fk_pendidikan_terakhir` FOREIGN KEY (`pendidikan_terakhir`) REFERENCES `kategori_pendidikans` (`id`),
  ADD CONSTRAINT `perubahan_keluargas_data_keluarga_id_foreign` FOREIGN KEY (`data_keluarga_id`) REFERENCES `data_keluargas` (`id`),
  ADD CONSTRAINT `perubahan_keluargas_riwayat_perubahan_id_foreign` FOREIGN KEY (`riwayat_perubahan_id`) REFERENCES `riwayat_perubahans` (`id`);

--
-- Constraints for table `perubahan_personals`
--
ALTER TABLE `perubahan_personals`
  ADD CONSTRAINT `fk_kategori_agama_id` FOREIGN KEY (`kategori_agama_id`) REFERENCES `kategori_agamas` (`id`),
  ADD CONSTRAINT `fk_kategori_darah_id` FOREIGN KEY (`kategori_darah_id`) REFERENCES `kategori_darahs` (`id`),
  ADD CONSTRAINT `fk_perubahan_personals_pendidikan` FOREIGN KEY (`pendidikan_terakhir`) REFERENCES `kategori_pendidikans` (`id`),
  ADD CONSTRAINT `perubahan_personals_riwayat_perubahan_id_foreign` FOREIGN KEY (`riwayat_perubahan_id`) REFERENCES `riwayat_perubahans` (`id`);

--
-- Constraints for table `peserta_diklats`
--
ALTER TABLE `peserta_diklats`
  ADD CONSTRAINT `peserta_diklats_diklat_id_foreign` FOREIGN KEY (`diklat_id`) REFERENCES `diklats` (`id`),
  ADD CONSTRAINT `peserta_diklats_peserta_foreign` FOREIGN KEY (`peserta`) REFERENCES `users` (`id`);

--
-- Constraints for table `premis`
--
ALTER TABLE `premis`
  ADD CONSTRAINT `premis_kategori_potongan_id_foreign` FOREIGN KEY (`kategori_potongan_id`) REFERENCES `kategori_potongans` (`id`);

--
-- Constraints for table `presensis`
--
ALTER TABLE `presensis`
  ADD CONSTRAINT `presensis_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`),
  ADD CONSTRAINT `presensis_foto_keluar_foreign` FOREIGN KEY (`foto_keluar`) REFERENCES `berkas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `presensis_foto_masuk_foreign` FOREIGN KEY (`foto_masuk`) REFERENCES `berkas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `presensis_jadwal_id_foreign` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwals` (`id`),
  ADD CONSTRAINT `presensis_kategori_presensi_id_foreign` FOREIGN KEY (`kategori_presensi_id`) REFERENCES `kategori_presensis` (`id`),
  ADD CONSTRAINT `presensis_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `ptkps`
--
ALTER TABLE `ptkps`
  ADD CONSTRAINT `ptkps_kategori_ter_id_foreign` FOREIGN KEY (`kategori_ter_id`) REFERENCES `kategori_ters` (`id`);

--
-- Constraints for table `reward_bulan_lalus`
--
ALTER TABLE `reward_bulan_lalus`
  ADD CONSTRAINT `reward_bulan_lalus_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`);

--
-- Constraints for table `riwayat_izins`
--
ALTER TABLE `riwayat_izins`
  ADD CONSTRAINT `riwayat_izins_status_izin_id_foreign` FOREIGN KEY (`status_izin_id`) REFERENCES `status_riwayat_izins` (`id`),
  ADD CONSTRAINT `riwayat_izins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `riwayat_izins_verifikator_1_foreign` FOREIGN KEY (`verifikator_1`) REFERENCES `users` (`id`);

--
-- Constraints for table `riwayat_penggajians`
--
ALTER TABLE `riwayat_penggajians`
  ADD CONSTRAINT `fk_riwayat_penggajians_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_riwayat_penggajians_submitted_by` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `riwayat_penggajians_status_gaji_id_foreign` FOREIGN KEY (`status_gaji_id`) REFERENCES `status_gajis` (`id`);

--
-- Constraints for table `riwayat_perubahans`
--
ALTER TABLE `riwayat_perubahans`
  ADD CONSTRAINT `riwayat_perubahans_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`),
  ADD CONSTRAINT `riwayat_perubahans_status_perubahan_id_foreign` FOREIGN KEY (`status_perubahan_id`) REFERENCES `status_perubahans` (`id`),
  ADD CONSTRAINT `riwayat_perubahans_verifikator_1_foreign` FOREIGN KEY (`verifikator_1`) REFERENCES `users` (`id`);

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `run_thrs`
--
ALTER TABLE `run_thrs`
  ADD CONSTRAINT `run_thrs_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`);

--
-- Constraints for table `ters`
--
ALTER TABLE `ters`
  ADD CONSTRAINT `ters_kategori_ter_id_foreign` FOREIGN KEY (`kategori_ter_id`) REFERENCES `kategori_ters` (`id`);

--
-- Constraints for table `track_records`
--
ALTER TABLE `track_records`
  ADD CONSTRAINT `track_records_kategori_record_id_foreign` FOREIGN KEY (`kategori_record_id`) REFERENCES `kategori_track_records` (`id`),
  ADD CONSTRAINT `track_records_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transfer_karyawans`
--
ALTER TABLE `transfer_karyawans`
  ADD CONSTRAINT `transfer_karyawans_jabatan_asal_foreign` FOREIGN KEY (`jabatan_asal`) REFERENCES `jabatans` (`id`),
  ADD CONSTRAINT `transfer_karyawans_jabatan_tujuan_foreign` FOREIGN KEY (`jabatan_tujuan`) REFERENCES `jabatans` (`id`),
  ADD CONSTRAINT `transfer_karyawans_kategori_transfer_id_foreign` FOREIGN KEY (`kategori_transfer_id`) REFERENCES `kategori_transfer_karyawans` (`id`),
  ADD CONSTRAINT `transfer_karyawans_kelompok_gaji_asal_foreign` FOREIGN KEY (`kelompok_gaji_asal`) REFERENCES `kelompok_gajis` (`id`),
  ADD CONSTRAINT `transfer_karyawans_kelompok_gaji_tujuan_foreign` FOREIGN KEY (`kelompok_gaji_tujuan`) REFERENCES `kelompok_gajis` (`id`),
  ADD CONSTRAINT `transfer_karyawans_unit_kerja_asal_foreign` FOREIGN KEY (`unit_kerja_asal`) REFERENCES `unit_kerjas` (`id`),
  ADD CONSTRAINT `transfer_karyawans_unit_kerja_tujuan_foreign` FOREIGN KEY (`unit_kerja_tujuan`) REFERENCES `unit_kerjas` (`id`),
  ADD CONSTRAINT `transfer_karyawans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `tukar_jadwals`
--
ALTER TABLE `tukar_jadwals`
  ADD CONSTRAINT `tukar_jadwals_jadwal_ditukar_foreign` FOREIGN KEY (`jadwal_ditukar`) REFERENCES `jadwals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tukar_jadwals_jadwal_pengajuan_foreign` FOREIGN KEY (`jadwal_pengajuan`) REFERENCES `jadwals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tukar_jadwals_kategori_penukaran_id_foreign` FOREIGN KEY (`kategori_penukaran_id`) REFERENCES `kategori_tukar_jadwals` (`id`),
  ADD CONSTRAINT `tukar_jadwals_status_penukaran_id_foreign` FOREIGN KEY (`status_penukaran_id`) REFERENCES `status_tukar_jadwals` (`id`),
  ADD CONSTRAINT `tukar_jadwals_user_ditukar_foreign` FOREIGN KEY (`user_ditukar`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tukar_jadwals_user_pengajuan_foreign` FOREIGN KEY (`user_pengajuan`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tukar_jadwals_verifikator_1_foreign` FOREIGN KEY (`verifikator_1`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tukar_jadwals_verifikator_2_foreign` FOREIGN KEY (`verifikator_2`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`),
  ADD CONSTRAINT `users_status_aktif_foreign` FOREIGN KEY (`status_aktif`) REFERENCES `status_aktifs` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
