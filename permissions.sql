-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 01 Sep 2024 pada 21.55
-- Versi server: 10.5.22-MariaDB-cll-lve
-- Versi PHP: 8.3.9

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
-- Struktur dari tabel `permissions`
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
-- Dumping data untuk tabel `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `group`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'verifikasi1 berkas', 'Berkas', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(2, 'view riwayatPerubahan', 'Riwayat Perubahan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(3, 'verifikasi1 riwayatPerubahan', 'Riwayat Perubahan', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(4, 'view riwayatPerizinan', 'Riwayat Izin', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(5, 'export riwayatPerizinan', 'Riwayat Izin', 'web', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
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

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
