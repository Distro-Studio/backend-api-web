-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 02 Sep 2024 pada 17.05
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
-- Struktur dari tabel `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `activity` text NOT NULL,
  `kategori_activity_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `activity`, `kategori_activity_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'Masuk', 1, 55, '2024-09-01 05:41:31', '2024-09-01 05:41:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `berkas`
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

--
-- Dumping data untuk tabel `berkas`
--

INSERT INTO `berkas` (`id`, `user_id`, `file_id`, `nama`, `kategori_berkas_id`, `status_berkas_id`, `path`, `tgl_upload`, `nama_file`, `ext`, `size`, `verifikator_1`, `alasan`, `created_at`, `updated_at`) VALUES
(1, 2, '415e48d3-20c3-445b-b709-ceb0331c1b47', 'Berkas Transfer - User 0', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_2', '2024-08-29 15:10:35', 'dokumen_2', 'application/pdf', '3006', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(2, 3, '2c7d89c2-3a30-4043-9549-6eaf40252e8e', 'Berkas Transfer - User 1', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_3', '2024-08-29 15:10:35', 'dokumen_3', 'application/pdf', '2534', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(3, 4, '464a7cd7-3890-48cd-9c94-9eca7866bad1', 'Berkas Transfer - User 2', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_4', '2024-08-29 15:10:35', 'dokumen_4', 'application/pdf', '3790', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(4, 5, '3dd96436-8306-49f5-b4ce-68e9a954db0e', 'Berkas Transfer - User 3', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_5', '2024-08-29 15:10:35', 'dokumen_5', 'application/pdf', '3836', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(5, 6, 'd223625f-9a35-4a5b-afcf-59b4c5e359e6', 'Berkas Transfer - User 4', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_6', '2024-08-29 15:10:35', 'dokumen_6', 'application/pdf', '3296', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(6, 7, '0542e839-f4b9-4e93-acc0-cb9e90242fca', 'Berkas Transfer - User 5', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_7', '2024-08-29 15:10:35', 'dokumen_7', 'application/pdf', '2846', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(7, 8, '60492fb8-bb7d-448c-ae7f-f3ba93ff499c', 'Berkas Transfer - User 6', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_8', '2024-08-29 15:10:35', 'dokumen_8', 'application/pdf', '2770', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(8, 9, '1be4df40-b71f-4b5c-95e0-817339d2139e', 'Berkas Transfer - User 7', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_9', '2024-08-29 15:10:35', 'dokumen_9', 'application/pdf', '3226', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(9, 10, '20371975-3df6-4279-b212-e69de7f93d20', 'Berkas Transfer - User 8', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_10', '2024-08-29 15:10:35', 'dokumen_10', 'application/pdf', '4105', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(10, 11, '9bd5c628-74a2-4e0a-9c43-e317d9a7103e', 'Berkas Transfer - User 9', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_11', '2024-08-29 15:10:35', 'dokumen_11', 'application/pdf', '2849', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(11, 12, 'a0a2b983-83b4-4152-852c-61bf9d1aeb02', 'Berkas Transfer - User 10', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_12', '2024-08-29 15:10:35', 'dokumen_12', 'application/pdf', '4608', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(12, 13, '39868080-032f-4da8-8a79-77fba56b915a', 'Berkas Transfer - User 11', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_13', '2024-08-29 15:10:35', 'dokumen_13', 'application/pdf', '3637', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(13, 14, 'ba2040b8-c971-4e54-8c4b-e096ee6d4e31', 'Berkas Transfer - User 12', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_14', '2024-08-29 15:10:35', 'dokumen_14', 'application/pdf', '4635', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(14, 15, 'e407525e-8e2e-416d-a62a-c9cb2150abdb', 'Berkas Transfer - User 13', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_15', '2024-08-29 15:10:35', 'dokumen_15', 'application/pdf', '4681', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(15, 16, '7175428d-e458-45cf-bbcf-de755a5e44f2', 'Berkas Transfer - User 14', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_16', '2024-08-29 15:10:35', 'dokumen_16', 'application/pdf', '3568', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(16, 17, '46ab25aa-9e47-4a07-b3e1-bd2568ebb640', 'Berkas Transfer - User 15', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_17', '2024-08-29 15:10:35', 'dokumen_17', 'application/pdf', '2673', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(17, 18, '732b3557-0932-4bd7-8398-82209d342101', 'Berkas Transfer - User 16', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_18', '2024-08-29 15:10:35', 'dokumen_18', 'application/pdf', '3694', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(18, 19, 'dc0b6570-6b60-431f-a51a-53849f9bd4c6', 'Berkas Transfer - User 17', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_19', '2024-08-29 15:10:35', 'dokumen_19', 'application/pdf', '2433', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(19, 20, 'b8853032-07f2-436e-93f7-a59a96fc04c8', 'Berkas Transfer - User 18', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_20', '2024-08-29 15:10:35', 'dokumen_20', 'application/pdf', '4742', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(20, 21, 'b937d4d4-4306-435b-a4c7-dbfeff42d8df', 'Berkas Transfer - User 19', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_21', '2024-08-29 15:10:35', 'dokumen_21', 'application/pdf', '2736', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(21, 22, '56be1916-f08e-494c-8e0a-571c92911568', 'Berkas Transfer - User 20', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_22', '2024-08-29 15:10:35', 'dokumen_22', 'application/pdf', '3425', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(22, 23, 'a5168b1e-604a-47fc-9c8c-b184bf4a1022', 'Berkas Transfer - User 21', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_23', '2024-08-29 15:10:35', 'dokumen_23', 'application/pdf', '2405', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(23, 24, '44152968-c8d4-4d84-8f43-4a169698d2f9', 'Berkas Transfer - User 22', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_24', '2024-08-29 15:10:35', 'dokumen_24', 'application/pdf', '3730', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(24, 25, '7a2a5c8c-5638-4755-8e75-3c8fe1ff0b9a', 'Berkas Transfer - User 23', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_25', '2024-08-29 15:10:35', 'dokumen_25', 'application/pdf', '2443', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(25, 26, 'e3787990-a63e-4c1f-baf9-295cb21f8244', 'Berkas Transfer - User 24', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_26', '2024-08-29 15:10:35', 'dokumen_26', 'application/pdf', '3634', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(26, 27, '951a9d1a-fe93-48b4-ab4d-3539e0ecd919', 'Berkas Transfer - User 25', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_27', '2024-08-29 15:10:35', 'dokumen_27', 'application/pdf', '4108', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(27, 28, '4c0f969e-69cb-47f5-8cc6-93e47fc68d13', 'Berkas Transfer - User 26', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_28', '2024-08-29 15:10:35', 'dokumen_28', 'application/pdf', '4140', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(28, 29, 'fa010317-6277-4635-aa3f-b19b03d9c98f', 'Berkas Transfer - User 27', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_29', '2024-08-29 15:10:35', 'dokumen_29', 'application/pdf', '3485', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(29, 30, '9191dbb8-f716-45d7-9817-7cfb5c3028a7', 'Berkas Transfer - User 28', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_30', '2024-08-29 15:10:35', 'dokumen_30', 'application/pdf', '3798', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(30, 31, 'ba5c460e-e80b-4560-854b-8921c380682b', 'Berkas Transfer - User 29', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_31', '2024-08-29 15:10:35', 'dokumen_31', 'application/pdf', '3382', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(31, 32, '8f4268e2-8e7a-46cd-9064-c4a475aafdb8', 'Berkas Transfer - User 30', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_32', '2024-08-29 15:10:35', 'dokumen_32', 'application/pdf', '2788', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(32, 33, '71aa8f9b-18be-4687-b0f1-b42e5aa4f805', 'Berkas Transfer - User 31', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_33', '2024-08-29 15:10:35', 'dokumen_33', 'application/pdf', '3987', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(33, 34, 'f03441af-819d-482e-9f60-13f3c3cc1422', 'Berkas Transfer - User 32', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_34', '2024-08-29 15:10:35', 'dokumen_34', 'application/pdf', '3060', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(34, 35, 'c3222647-d8e6-4fdb-a2d7-8584b59ae0f3', 'Berkas Transfer - User 33', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_35', '2024-08-29 15:10:35', 'dokumen_35', 'application/pdf', '3164', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(35, 36, 'c551fae4-85c3-4e8a-bb01-9a5f22a6fe6d', 'Berkas Transfer - User 34', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_36', '2024-08-29 15:10:35', 'dokumen_36', 'application/pdf', '4685', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(36, 37, 'c8a66faa-31eb-40d6-a145-59a90aa1ead9', 'Berkas Transfer - User 35', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_37', '2024-08-29 15:10:35', 'dokumen_37', 'application/pdf', '4662', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(37, 38, 'eb019394-0b4d-4fb6-abb1-ffe8b2b2006f', 'Berkas Transfer - User 36', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_38', '2024-08-29 15:10:35', 'dokumen_38', 'application/pdf', '3868', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(38, 39, '7728f66d-13ef-4ce5-9370-dea77360cd9f', 'Berkas Transfer - User 37', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_39', '2024-08-29 15:10:35', 'dokumen_39', 'application/pdf', '2307', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(39, 40, 'e087db0a-a693-4896-8383-392e09351ed2', 'Berkas Transfer - User 38', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_40', '2024-08-29 15:10:35', 'dokumen_40', 'application/pdf', '4499', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(40, 41, '4de0fe74-b2a5-4abd-ad4c-2e195c02346a', 'Berkas Transfer - User 39', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_41', '2024-08-29 15:10:35', 'dokumen_41', 'application/pdf', '2732', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(41, 42, '0e5c62bd-1064-4c28-bd29-d33d742e8346', 'Berkas Transfer - User 40', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_42', '2024-08-29 15:10:35', 'dokumen_42', 'application/pdf', '2809', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(42, 43, 'ce6b033e-3cdc-4835-851e-864e9dcfec52', 'Berkas Transfer - User 41', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_43', '2024-08-29 15:10:35', 'dokumen_43', 'application/pdf', '4652', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(43, 44, 'fdb211ac-b5e1-4827-aa27-71b8d5a40e9f', 'Berkas Transfer - User 42', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_44', '2024-08-29 15:10:35', 'dokumen_44', 'application/pdf', '3071', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(44, 45, 'ea22d838-49d7-4b36-bc8d-1e68b32f4172', 'Berkas Transfer - User 43', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_45', '2024-08-29 15:10:35', 'dokumen_45', 'application/pdf', '3894', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(45, 46, '7a612707-e24a-4867-bc09-e9a3a6106ffa', 'Berkas Transfer - User 44', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_46', '2024-08-29 15:10:35', 'dokumen_46', 'application/pdf', '2444', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(46, 47, '9712618e-446b-434c-8088-aeacaed7d378', 'Berkas Transfer - User 45', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_47', '2024-08-29 15:10:35', 'dokumen_47', 'application/pdf', '2685', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(47, 48, '3f69ab49-246e-4c98-a0e4-06849f7f5c37', 'Berkas Transfer - User 46', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_48', '2024-08-29 15:10:35', 'dokumen_48', 'application/pdf', '3674', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(48, 49, 'a0723119-50e4-408f-9d3b-549829e3dfad', 'Berkas Transfer - User 47', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_49', '2024-08-29 15:10:35', 'dokumen_49', 'application/pdf', '3914', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(49, 50, '3a343a3f-3fd2-475d-97f1-903a3fa404bf', 'Berkas Transfer - User 48', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_50', '2024-08-29 15:10:35', 'dokumen_50', 'application/pdf', '2372', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(50, 51, 'a613df0d-30f3-4cd7-9240-0b8322c86072', 'Berkas Transfer - User 49', 3, 1, '/berkas/karyawan/karyawan-transfer/dokumen_51', '2024-08-29 15:10:35', 'dokumen_51', 'application/pdf', '3495', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(51, 2, '930b4d03-046e-4fde-9581-19f3b1b82ef5', 'Berkas Diklat Eksternal - User 0', 3, 1, '/path/to/diklat/berkas/Diklat_Eksternal_0', '2024-08-29 15:10:35', 'dokumen_2', 'image/jpeg', '1493', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(52, 3, '4abc66bc-d6b8-4d53-863c-c4bc68a1d42b', 'Berkas Diklat - User 1', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_1', '2024-08-29 15:10:35', 'dokumen_3', 'image/jpeg', '1110', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(53, 4, '0326eab9-948c-4d4e-876d-ab3b7040dffa', 'Berkas Diklat - User 2', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_2', '2024-08-29 15:10:35', 'dokumen_4', 'image/jpeg', '1056', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(54, 5, '2e4c00b1-ff7c-4f22-a243-84fe349ba2e4', 'Berkas Diklat - User 3', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_3', '2024-08-29 15:10:35', 'dokumen_5', 'image/jpeg', '1437', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(55, 6, '9f0db6cb-2e88-4f0d-83a7-572b6565f815', 'Berkas Diklat Eksternal - User 4', 3, 1, '/path/to/diklat/berkas/Diklat_Eksternal_4', '2024-08-29 15:10:35', 'dokumen_6', 'image/jpeg', '1727', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(56, 7, 'b7cfae61-076d-4be1-bc57-ea34a3d6523f', 'Berkas Diklat - User 5', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_5', '2024-08-29 15:10:35', 'dokumen_7', 'image/jpeg', '1387', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(57, 8, 'd4f35dc7-d6ac-465b-96e9-dc2bcd9246f2', 'Berkas Diklat - User 6', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_6', '2024-08-29 15:10:35', 'dokumen_8', 'image/jpeg', '1694', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(58, 9, '7ee94cc0-1f8e-445c-9866-7145abb77a1b', 'Berkas Diklat - User 7', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_7', '2024-08-29 15:10:35', 'dokumen_9', 'image/jpeg', '1998', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(59, 10, 'c72e36ce-8624-4c68-9f39-50f0a4f5a439', 'Berkas Diklat - User 8', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_8', '2024-08-29 15:10:35', 'dokumen_10', 'image/jpeg', '1142', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(60, 11, 'a682a6ab-99da-4b79-9d49-1a3476011032', 'Berkas Diklat Eksternal - User 9', 3, 1, '/path/to/diklat/berkas/Diklat_Eksternal_9', '2024-08-29 15:10:35', 'dokumen_11', 'image/jpeg', '1200', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(61, 12, '59347e19-aa1d-46a6-81a7-a1235f56756c', 'Berkas Diklat Eksternal - User 10', 3, 1, '/path/to/diklat/berkas/Diklat_Eksternal_10', '2024-08-29 15:10:35', 'dokumen_12', 'image/jpeg', '1190', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(62, 13, 'e5b25683-44d5-45b0-8e15-d56571a4d6a9', 'Berkas Diklat Eksternal - User 11', 3, 1, '/path/to/diklat/berkas/Diklat_Eksternal_11', '2024-08-29 15:10:35', 'dokumen_13', 'image/jpeg', '1139', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(63, 14, '062543be-c04b-422e-88ee-aa36e73d0733', 'Berkas Diklat Eksternal - User 12', 3, 1, '/path/to/diklat/berkas/Diklat_Eksternal_12', '2024-08-29 15:10:35', 'dokumen_14', 'image/jpeg', '1337', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(64, 15, '953b0bd8-93b0-4743-ae97-ea9aa2092908', 'Berkas Diklat Eksternal - User 13', 3, 1, '/path/to/diklat/berkas/Diklat_Eksternal_13', '2024-08-29 15:10:35', 'dokumen_15', 'image/jpeg', '1375', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(65, 16, 'ceb64178-a5b6-46c8-b61e-eab1f29d9527', 'Berkas Diklat - User 14', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_14', '2024-08-29 15:10:35', 'dokumen_16', 'image/jpeg', '1312', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(66, 17, '10a22cfe-06f6-4d36-b9dd-e9649fcd14b1', 'Berkas Diklat - User 15', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_15', '2024-08-29 15:10:35', 'dokumen_17', 'image/jpeg', '1754', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(67, 18, '8abdab82-aa2b-40f0-ac16-13b858176810', 'Berkas Diklat Eksternal - User 16', 3, 1, '/path/to/diklat/berkas/Diklat_Eksternal_16', '2024-08-29 15:10:35', 'dokumen_18', 'image/jpeg', '1093', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(68, 19, '44d007ea-0beb-452e-9c1c-7a8acf8a5d8f', 'Berkas Diklat - User 17', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_17', '2024-08-29 15:10:35', 'dokumen_19', 'image/jpeg', '1339', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(69, 20, 'ae1369fe-794c-4506-8757-f6621437894b', 'Berkas Diklat - User 18', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_18', '2024-08-29 15:10:35', 'dokumen_20', 'image/jpeg', '1834', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(70, 21, '1c5ae697-bc6a-4439-a4b3-6eb96c649dbb', 'Berkas Diklat - User 19', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_19', '2024-08-29 15:10:35', 'dokumen_21', 'image/jpeg', '1222', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(71, 22, 'd98e1e57-b99a-4307-bac3-761cb75e6277', 'Berkas Diklat Eksternal - User 20', 3, 1, '/path/to/diklat/berkas/Diklat_Eksternal_20', '2024-08-29 15:10:35', 'dokumen_22', 'image/jpeg', '1500', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(72, 23, '8c59044b-ba09-4d75-a23a-e881b84a58cc', 'Berkas Diklat - User 21', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_21', '2024-08-29 15:10:35', 'dokumen_23', 'image/jpeg', '1521', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(73, 24, 'd8af2199-acc9-4748-b7c0-db3933dcad18', 'Berkas Diklat - User 22', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_22', '2024-08-29 15:10:35', 'dokumen_24', 'image/jpeg', '1036', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(74, 25, 'bbffeb71-07bb-4235-996f-c864c7ff57b7', 'Berkas Diklat - User 23', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_23', '2024-08-29 15:10:35', 'dokumen_25', 'image/jpeg', '1284', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(75, 26, '1965811f-dac7-4bcd-90a4-da6d9e0e5ec4', 'Berkas Diklat - User 24', 3, 1, '/path/to/diklat/berkas/Diklat_Thumbnail_24', '2024-08-29 15:10:35', 'dokumen_26', 'image/jpeg', '1362', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(76, 43, 'b2df1040-94fb-4c85-be34-b3cd6eb40a06', 'Upload Foto Pelaporan 1', 3, 1, '/path/to/uploads/pelaporan_1.jpg', '2024-08-29 15:10:35', 'pelaporan_1.jpg', 'image/jpeg', '2023', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(77, 24, 'db5eb441-ea11-4dd3-b87b-508be3d6cbf9', 'Upload Foto Pelaporan 2', 3, 1, '/path/to/uploads/pelaporan_2.jpg', '2024-08-29 15:10:35', 'pelaporan_2.jpg', 'image/jpeg', '2041', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(78, 6, 'ea8095a4-2b94-401a-9589-9326d8d76eb4', 'Upload Foto Pelaporan 3', 3, 1, '/path/to/uploads/pelaporan_3.jpg', '2024-08-29 15:10:35', 'pelaporan_3.jpg', 'image/jpeg', '2467', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(79, 19, '79be8aeb-2230-42c5-a303-1e23935813a2', 'Upload Foto Pelaporan 4', 3, 1, '/path/to/uploads/pelaporan_4.jpg', '2024-08-29 15:10:35', 'pelaporan_4.jpg', 'image/jpeg', '1663', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(80, 42, 'aab883db-a519-4a81-bf2f-d60fc71439c0', 'Upload Foto Pelaporan 5', 3, 1, '/path/to/uploads/pelaporan_5.jpg', '2024-08-29 15:10:35', 'pelaporan_5.jpg', 'image/jpeg', '2576', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(81, 46, 'ca1d21ae-8c5f-4ea3-977b-af13e798ac5b', 'Upload Foto Pelaporan 6', 3, 1, '/path/to/uploads/pelaporan_6.jpg', '2024-08-29 15:10:35', 'pelaporan_6.jpg', 'image/jpeg', '2963', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(82, 28, 'dde0d8cd-e0d4-4bed-80ce-7958f2c51eef', 'Upload Foto Pelaporan 7', 3, 1, '/path/to/uploads/pelaporan_7.jpg', '2024-08-29 15:10:35', 'pelaporan_7.jpg', 'image/jpeg', '1697', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(83, 6, '71630921-32f7-472a-a8ee-877e975983fb', 'Upload Foto Pelaporan 8', 3, 1, '/path/to/uploads/pelaporan_8.jpg', '2024-08-29 15:10:35', 'pelaporan_8.jpg', 'image/jpeg', '2649', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(84, 21, 'f8d38f1a-f6f0-4856-9e13-38dbb1f5b152', 'Upload Foto Pelaporan 9', 3, 1, '/path/to/uploads/pelaporan_9.jpg', '2024-08-29 15:10:35', 'pelaporan_9.jpg', 'image/jpeg', '2309', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(85, 21, '3bfa75bc-a113-4f49-a04b-3b937dfceb13', 'Upload Foto Pelaporan 10', 3, 1, '/path/to/uploads/pelaporan_10.jpg', '2024-08-29 15:10:35', 'pelaporan_10.jpg', 'image/jpeg', '1535', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(86, 6, '28f60bae-257f-4d54-9e3d-10850620d0ce', 'Upload Foto Pelaporan 11', 3, 1, '/path/to/uploads/pelaporan_11.jpg', '2024-08-29 15:10:35', 'pelaporan_11.jpg', 'image/jpeg', '2963', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(87, 24, 'f8cae05b-06ec-4a64-8fdb-003dad2ed54d', 'Upload Foto Pelaporan 12', 3, 1, '/path/to/uploads/pelaporan_12.jpg', '2024-08-29 15:10:35', 'pelaporan_12.jpg', 'image/jpeg', '2879', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(88, 11, '571a4849-d5a0-4ffd-88ae-7c9f9e0eb982', 'Upload Foto Pelaporan 13', 3, 1, '/path/to/uploads/pelaporan_13.jpg', '2024-08-29 15:10:35', 'pelaporan_13.jpg', 'image/jpeg', '2458', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(89, 44, '9a405fb7-725c-436e-934b-1cc08acb0ea7', 'Upload Foto Pelaporan 14', 3, 1, '/path/to/uploads/pelaporan_14.jpg', '2024-08-29 15:10:35', 'pelaporan_14.jpg', 'image/jpeg', '1737', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(90, 15, 'e15d97b2-2928-4d5f-bdfe-35ff3a2c4181', 'Upload Foto Pelaporan 15', 3, 1, '/path/to/uploads/pelaporan_15.jpg', '2024-08-29 15:10:35', 'pelaporan_15.jpg', 'image/jpeg', '1381', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(91, 11, '835b523f-9c50-4246-8284-0a69b866ba60', 'Upload Foto Pelaporan 16', 3, 1, '/path/to/uploads/pelaporan_16.jpg', '2024-08-29 15:10:35', 'pelaporan_16.jpg', 'image/jpeg', '1747', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(92, 15, 'bea1b1ad-c9a0-4f45-882d-361219cf280c', 'Upload Foto Pelaporan 17', 3, 1, '/path/to/uploads/pelaporan_17.jpg', '2024-08-29 15:10:35', 'pelaporan_17.jpg', 'image/jpeg', '1677', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(93, 9, 'db240128-2d0a-4706-b86c-24bc932fd19d', 'Upload Foto Pelaporan 18', 3, 1, '/path/to/uploads/pelaporan_18.jpg', '2024-08-29 15:10:35', 'pelaporan_18.jpg', 'image/jpeg', '1054', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(94, 42, '0d78b4c6-d032-43ae-bc08-d301b9a2e0e0', 'Upload Foto Pelaporan 19', 3, 1, '/path/to/uploads/pelaporan_19.jpg', '2024-08-29 15:10:35', 'pelaporan_19.jpg', 'image/jpeg', '2499', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(95, 24, '7e36d009-8c25-4a64-ac72-4a831f18cd6f', 'Upload Foto Pelaporan 20', 3, 1, '/path/to/uploads/pelaporan_20.jpg', '2024-08-29 15:10:35', 'pelaporan_20.jpg', 'image/jpeg', '2276', NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(103, 55, '9ce7387b-2769-4503-bbeb-a3202d2b64af', 'KTP', 1, 1, '/storage/file/V1GrnMrC', '2024-09-01 00:00:00', 'V1GrnMrC', 'image/jpeg', '17555 KB', NULL, NULL, '2024-09-01 05:36:29', '2024-09-01 05:36:29'),
(104, 55, '9ce7387c-aa5a-492e-890d-39a9428724d8', 'KK', 1, 1, '/storage/file/xJ4yDBby', '2024-09-01 00:00:00', 'xJ4yDBby', 'image/jpeg', '17555 KB', NULL, NULL, '2024-09-01 05:36:30', '2024-09-01 05:36:30'),
(105, 55, '9ce7387e-0849-4cd7-9280-1abf11568588', 'SIP', 1, 1, '/storage/file/XdwAXJYB', '2024-09-01 00:00:00', 'XdwAXJYB', 'image/jpeg', '17555 KB', NULL, NULL, '2024-09-01 05:36:31', '2024-09-01 05:36:31'),
(106, 55, '9ce7387f-918a-4fdb-b739-63631c5c02b8', 'BPJS Kesehatan', 1, 1, '/storage/file/Bd67DuRp', '2024-09-01 00:00:00', 'Bd67DuRp', 'image/jpeg', '17555 KB', NULL, NULL, '2024-09-01 05:36:32', '2024-09-01 05:36:32'),
(107, 55, '9ce73881-1d0a-4e6a-bab7-96e5c461ea42', 'BPJS Ketenagakerjaan', 1, 1, '/storage/file/7ngti46R', '2024-09-01 00:00:00', '7ngti46R', 'image/jpeg', '17555 KB', NULL, NULL, '2024-09-01 05:36:33', '2024-09-01 05:36:33'),
(108, 55, '9ce73882-9c0d-4888-8c9e-7aa44e9bc17b', 'Ijazah', 1, 1, '/storage/file/r4CWMs7i', '2024-09-01 00:00:00', 'r4CWMs7i', 'image/jpeg', '17555 KB', NULL, NULL, '2024-09-01 05:36:34', '2024-09-01 05:36:34'),
(109, 55, '9ce73884-27aa-4bc2-8fd0-63582918b9a2', 'Sertifikat', 1, 1, '/storage/file/Hz60lwAR', '2024-09-01 00:00:00', 'Hz60lwAR', 'image/jpeg', '17555 KB', NULL, NULL, '2024-09-01 05:36:35', '2024-09-01 05:36:35'),
(110, 55, '9ce73a47-b630-4409-8e27-4d1ec0aac406', 'Haris Adiyatma Farhan', 3, 2, '/storage/file/CQH2v9wlmJQJ', '2024-09-01 00:00:00', 'CQH2v9wlmJQJ', 'image/jpeg', '182985 KB', NULL, NULL, '2024-09-01 05:41:31', '2024-09-01 05:41:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cutis`
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

--
-- Dumping data untuk tabel `cutis`
--

INSERT INTO `cutis` (`id`, `user_id`, `tipe_cuti_id`, `tgl_from`, `tgl_to`, `catatan`, `durasi`, `status_cuti_id`, `verifikator_1`, `verifikator_2`, `alasan`, `created_at`, `updated_at`) VALUES
(1, 38, 3, '02-09-2024', '03-09-2024', NULL, 2, 4, 50, 50, NULL, '2024-08-31 20:31:32', '2024-08-31 20:32:50'),
(2, 55, 5, '2024-09-30T17:00:00.000Z', '2024-10-10T17:00:00.000Z', NULL, 11, 1, NULL, NULL, NULL, '2024-09-01 05:44:02', '2024-09-01 05:44:02'),
(3, 55, 1, '2024-09-30T17:00:00.000Z', '2024-10-09T17:00:00.000Z', NULL, 10, 1, NULL, NULL, NULL, '2024-09-01 05:46:01', '2024-09-01 05:46:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_karyawans`
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
  `pendidikan_terakhir` bigint(20) UNSIGNED DEFAULT NULL,
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
-- Dumping data untuk tabel `data_karyawans`
--

INSERT INTO `data_karyawans` (`id`, `user_id`, `email`, `no_rm`, `no_manulife`, `tgl_masuk`, `tgl_keluar`, `unit_kerja_id`, `jabatan_id`, `kompetensi_id`, `tunjangan_fungsional`, `tunjangan_khusus`, `tunjangan_lainnya`, `uang_makan`, `uang_lembur`, `nik`, `nik_ktp`, `gelar_depan`, `tempat_lahir`, `tgl_lahir`, `alamat`, `no_hp`, `no_bpjsksh`, `no_bpjsktk`, `tgl_diangkat`, `masa_kerja`, `npwp`, `no_rekening`, `jenis_kelamin`, `kategori_agama_id`, `kategori_darah_id`, `tinggi_badan`, `berat_badan`, `pendidikan_terakhir`, `no_ijazah`, `tahun_lulus`, `no_kk`, `status_karyawan_id`, `kelompok_gaji_id`, `no_str`, `masa_berlaku_str`, `no_sip`, `masa_berlaku_sip`, `ptkp_id`, `tgl_berakhir_pks`, `masa_diklat`, `verifikator_1`, `status_reward_presensi`, `created_at`, `updated_at`) VALUES
(1, 1, 'super_admin@admin.rski', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 2, 'user0@example.com', '4530031', '4860191', '2020-02-01', '2023-06-06', 3, 15, 7, 144113, 87509, 320227, 62522, 90716, '2267924', '4481530', 'Ir.', 'Tasikmalaya', '1939-09-08', 'be former rear pool driver porch meal bottle meet cloud same', '335816290', '256578249', '482555156', '2023-06-06', 27, '306503597', '493226751', 1, 2, 6, 137, 99, 1, 'IJ/VII/97185074', 1921, '227744589', 3, 21, 'STR/01/RA/271605', '2024-07-09', 'SIP/01/VI/25578', '2024-07-09', 4, '2023-06-06', 7, NULL, 1, '2024-08-29 08:10:21', '2024-08-29 08:10:21'),
(3, 3, 'user1@example.com', '183773', '4993998', '2012-02-03', '2023-04-28', 15, 13, 12, 132995, 90593, 717088, 52455, 82051, '10565133', '769122', 'drg.', 'Pemalang', '1973-02-02', 'be former rear pool driver porch meal bottle meet cloud same', '387107704', '157497046', '73699136', '2023-04-28', 23, '474473817', '395041160', 1, 2, 7, 160, 126, 12, 'IJ/VII/209821073', 1846, '254619157', 3, 15, 'STR/01/RA/222470', '2026-12-12', 'SIP/01/VI/134130', '2026-12-12', 1, '2023-04-28', 7, NULL, 1, '2024-08-29 08:10:21', '2024-08-29 08:10:21'),
(4, 4, 'user2@example.com', '1028309', '2759661', '2016-09-30', '2024-10-20', 18, 18, 13, 134402, 17543, 913300, 11172, 116202, '33125412', '8394354', 'Ir.', 'Kebumen', '1940-09-29', 'be former rear pool driver porch meal bottle meet cloud same', '352329108', '22246543', '81501865', '2024-10-20', 39, '86886499', '356619161', 1, 5, 1, 165, 34, 11, 'IJ/VII/336236170', 1837, '196599563', 1, 23, 'STR/01/RA/484751', '2026-03-22', 'SIP/01/VI/205519', '2026-03-22', 2, '2024-10-20', 8, NULL, 1, '2024-08-29 08:10:21', '2024-08-29 08:10:21'),
(5, 5, 'user3@example.com', '2362312', '4457547', '2012-06-26', '2024-08-10', 19, 18, 2, 245484, 114138, 668748, 47120, 35357, '12955657', '22104227', 'Ar.', 'Banyuwangi', '1985-05-16', 'be former rear pool driver porch meal bottle meet cloud same', '37063240', '170396988', '257553222', '2024-08-10', 23, '158952080', '413408908', 1, 3, 4, 126, 37, 12, 'IJ/VII/65360234', 1933, '70246404', 2, 13, 'STR/01/RA/12546', '2023-05-31', 'SIP/01/VI/79325', '2023-05-31', 1, '2024-08-10', 9, NULL, 1, '2024-08-29 08:10:22', '2024-08-29 08:10:22'),
(6, 6, 'user4@example.com', '3618923', '4508516', '2022-02-19', '2023-04-19', 18, 5, 1, 243290, 92386, 483200, 61455, 30116, '7362160', '45824678', 'dr.', 'Madiun', '1975-03-04', 'be former rear pool driver porch meal bottle meet cloud same', '134578551', '171788702', '47012112', '2023-04-19', 38, '448576818', '293040069', 0, 2, 9, 21, 112, 11, 'IJ/VII/269653368', 1888, '453394930', 2, 9, 'STR/01/RA/6556', '2026-06-13', 'SIP/01/VI/90545', '2026-06-13', 7, '2023-04-19', 1, NULL, 1, '2024-08-29 08:10:22', '2024-08-29 08:10:22'),
(7, 7, 'user5@example.com', '4537131', '2388785', '2007-02-28', '2023-01-28', 9, 18, 9, 228628, 59530, 382275, 2884, 119854, '18698170', '34070057', 'drg.', 'Kuningan', '1902-01-24', 'be former rear pool driver porch meal bottle meet cloud same', '127480172', '399806609', '419384542', '2023-01-28', 27, '354220715', '334017306', 0, 5, 12, 236, 83, 4, 'IJ/VII/303929469', 1860, '36080177', 2, 18, 'STR/01/RA/497670', '2024-09-15', 'SIP/01/VI/279280', '2024-09-15', 8, '2023-01-28', 2, NULL, 1, '2024-08-29 08:10:22', '2024-08-29 08:10:22'),
(8, 8, 'user6@example.com', '3500908', '528044', '2009-05-29', '2023-01-23', 16, 16, 5, 212964, 70461, 806811, 20874, 12442, '9763680', '9839791', 'drh.', 'Banyumas', '2001-04-01', 'be former rear pool driver porch meal bottle meet cloud same', '45279521', '370620529', '107631819', '2023-01-23', 7, '244100203', '467090473', 0, 6, 3, 40, 161, 12, 'IJ/VII/347327713', 1825, '230701709', 2, 17, 'STR/01/RA/109828', '2023-04-02', 'SIP/01/VI/219646', '2023-04-02', 4, '2023-01-23', 7, NULL, 1, '2024-08-29 08:10:22', '2024-08-29 08:10:22'),
(9, 9, 'user7@example.com', '4950019', '3042867', '2018-05-07', '2024-05-15', 1, 18, 9, 228636, 7515, 263834, 11832, 80741, '26778094', '29572106', 'Adv.', 'Lumajang', '1988-05-08', 'be former rear pool driver porch meal bottle meet cloud same', '317041978', '318535196', '488226836', '2024-05-15', 34, '463995051', '146187438', 1, 3, 8, 125, 49, 7, 'IJ/VII/206461388', 1916, '66224321', 1, 15, 'STR/01/RA/88424', '2028-10-23', 'SIP/01/VI/427005', '2028-10-23', 7, '2024-05-15', 7, NULL, 1, '2024-08-29 08:10:23', '2024-08-29 08:10:23'),
(10, 10, 'user8@example.com', '4715307', '93354', '2022-02-02', '2023-06-13', 21, 14, 2, 161370, 73697, 302791, 1805, 26639, '34033482', '23043962', 'Adv.', 'Jombang', '1963-07-09', 'be former rear pool driver porch meal bottle meet cloud same', '488535908', '113243945', '20874473', '2023-06-13', 33, '451131367', '108737732', 0, 2, 10, 194, 191, 4, 'IJ/VII/196983687', 1878, '119895755', 1, 5, 'STR/01/RA/444494', '2024-06-15', 'SIP/01/VI/156217', '2024-06-15', 7, '2023-06-13', 10, NULL, 1, '2024-08-29 08:10:23', '2024-08-29 08:10:23'),
(11, 11, 'user9@example.com', '2677617', '865986', '2017-03-17', '2023-01-29', 10, 16, 9, 212103, 57713, 450039, 56278, 65708, '14207361', '13567596', 'Adv.', 'Sidoarjo', '1984-10-17', 'be former rear pool driver porch meal bottle meet cloud same', '278662757', '298205192', '239068654', '2023-01-29', 13, '1918800', '433366969', 0, 3, 8, 162, 199, 5, 'IJ/VII/140978698', 1863, '347564015', 3, 18, 'STR/01/RA/442407', '2026-03-13', 'SIP/01/VI/164231', '2026-03-13', 3, '2023-01-29', 8, NULL, 1, '2024-08-29 08:10:23', '2024-08-29 08:10:23'),
(12, 12, 'user10@example.com', '640690', '2608233', '2021-09-11', '2024-04-30', 2, 6, 11, 87050, 101957, 539908, 63184, 96429, '23312732', '43621589', 'apt.', 'Kuningan', '1979-04-16', 'be former rear pool driver porch meal bottle meet cloud same', '203788208', '335037520', '61682503', '2024-04-30', 26, '20489818', '458571118', 0, 5, 10, 299, 114, 4, 'IJ/VII/225132862', 1867, '350653782', 3, 10, 'STR/01/RA/287748', '2028-11-18', 'SIP/01/VI/471968', '2028-11-18', 3, '2024-04-30', 10, NULL, 0, '2024-08-29 08:10:24', '2024-08-29 08:10:24'),
(13, 13, 'user11@example.com', '361167', '4922924', '2017-03-08', '2024-10-12', 11, 7, 12, 170457, 32372, 737330, 68267, 106331, '37304921', '11162967', 'apt.', 'Sumedang', '1933-12-29', 'be former rear pool driver porch meal bottle meet cloud same', '357942551', '416198659', '256976118', '2024-10-12', 36, '397834338', '196883788', 0, 1, 5, 141, 186, 6, 'IJ/VII/70113087', 1892, '44295026', 1, 6, 'STR/01/RA/99107', '2028-03-08', 'SIP/01/VI/320952', '2028-03-08', 2, '2024-10-12', 8, NULL, 1, '2024-08-29 08:10:24', '2024-08-29 08:10:24'),
(14, 14, 'user12@example.com', '2110170', '3625145', '2010-06-05', '2024-05-04', 7, 17, 7, 112671, 71799, 335494, 45563, 72787, '35331125', '29125743', 'Ir.', 'Trenggalek', '1964-02-25', 'be former rear pool driver porch meal bottle meet cloud same', '286482385', '303688049', '20676477', '2024-05-04', 31, '73515643', '499040992', 0, 5, 2, 227, 64, 8, 'IJ/VII/220764933', 1923, '88807813', 3, 8, 'STR/01/RA/113895', '2028-09-02', 'SIP/01/VI/75187', '2028-09-02', 4, '2024-05-04', 9, NULL, 1, '2024-08-29 08:10:24', '2024-08-29 08:10:24'),
(15, 15, 'user13@example.com', '693415', '1174503', '2009-04-13', '2024-03-10', 14, 15, 1, 249484, 18416, 482772, 27869, 37009, '12527657', '30001657', 'apt.', 'Pemalang', '1957-02-08', 'be former rear pool driver porch meal bottle meet cloud same', '102478147', '2733537', '394009686', '2024-03-10', 18, '377866742', '246283128', 1, 5, 9, 157, 57, 10, 'IJ/VII/463879003', 1898, '471965982', 3, 23, 'STR/01/RA/314605', '2028-08-14', 'SIP/01/VI/314295', '2028-08-14', 5, '2024-03-10', 1, NULL, 1, '2024-08-29 08:10:25', '2024-08-29 08:10:25'),
(16, 16, 'user14@example.com', '2511135', '1864040', '2010-10-13', '2023-02-20', 6, 2, 3, 189586, 58112, 865192, 17072, 73646, '5724870', '27634954', 'Ak.', 'Pekalongan', '1985-07-22', 'be former rear pool driver porch meal bottle meet cloud same', '131630185', '277015105', '70048358', '2023-02-20', 27, '267536512', '388190644', 0, 6, 2, 136, 130, 4, 'IJ/VII/244759999', 1958, '427376535', 3, 5, 'STR/01/RA/438041', '2027-04-24', 'SIP/01/VI/159831', '2027-04-24', 7, '2023-02-20', 5, NULL, 0, '2024-08-29 08:10:25', '2024-08-29 08:10:25'),
(17, 17, 'user15@example.com', '1921685', '1473496', '2013-04-03', '2024-05-30', 9, 7, 11, 220690, 61822, 348491, 25865, 64605, '3462300', '12570093', 'drh.', 'Ponorogo', '1967-09-19', 'be former rear pool driver porch meal bottle meet cloud same', '10910915', '95602647', '338934769', '2024-05-30', 10, '443909920', '234512348', 1, 4, 10, 43, 130, 10, 'IJ/VII/466180631', 1953, '21387983', 2, 18, 'STR/01/RA/143992', '2026-03-10', 'SIP/01/VI/295501', '2026-03-10', 8, '2024-05-30', 9, NULL, 1, '2024-08-29 08:10:25', '2024-08-29 08:10:25'),
(18, 18, 'user16@example.com', '1347194', '1832414', '2009-03-24', '2024-08-10', 19, 10, 12, 141523, 114857, 347864, 57251, 91324, '17290198', '42089171', 'dr.', 'Blitar', '1952-04-29', 'be former rear pool driver porch meal bottle meet cloud same', '230077154', '182433617', '35330536', '2024-08-10', 6, '56463918', '357614510', 1, 3, 7, 26, 17, 9, 'IJ/VII/75607847', 1990, '199716440', 2, 18, 'STR/01/RA/410268', '2024-05-05', 'SIP/01/VI/413024', '2024-05-05', 8, '2024-08-10', 2, NULL, 1, '2024-08-29 08:10:25', '2024-08-29 08:10:25'),
(19, 19, 'user17@example.com', '2792718', '4138614', '2019-05-08', '2023-02-05', 9, 18, 3, 222365, 48863, 738240, 50283, 66025, '44237029', '45528248', 'Adv.', 'Kudus', '1951-04-18', 'be former rear pool driver porch meal bottle meet cloud same', '292421235', '459374179', '476016774', '2023-02-05', 16, '64830069', '114606047', 0, 6, 6, 33, 157, 2, 'IJ/VII/420525474', 1908, '365626324', 1, 13, 'STR/01/RA/213671', '2023-03-24', 'SIP/01/VI/469723', '2023-03-24', 5, '2023-02-05', 2, NULL, 1, '2024-08-29 08:10:26', '2024-08-29 08:10:26'),
(20, 20, 'user18@example.com', '1241028', '2354048', '2013-03-08', '2023-04-28', 6, 15, 13, 85279, 69844, 348519, 38802, 59371, '27519001', '6121730', 'dr.', 'Indramayu', '1951-06-30', 'be former rear pool driver porch meal bottle meet cloud same', '357908715', '5814992', '198615881', '2023-04-28', 13, '12086676', '302261977', 1, 1, 3, 149, 163, 7, 'IJ/VII/74514396', 1885, '379694316', 2, 17, 'STR/01/RA/127571', '2026-09-16', 'SIP/01/VI/191766', '2026-09-16', 1, '2023-04-28', 1, NULL, 1, '2024-08-29 08:10:26', '2024-08-29 08:10:26'),
(21, 21, 'user19@example.com', '4736058', '2159138', '2022-11-22', '2024-08-17', 7, 16, 6, 192965, 54612, 795232, 67255, 38865, '42035122', '9552192', 'drg.', 'Demak', '1995-02-15', 'be former rear pool driver porch meal bottle meet cloud same', '338436844', '144465519', '94070886', '2024-08-17', 30, '457631399', '307086632', 1, 2, 10, 290, 33, 5, 'IJ/VII/105585090', 1896, '409983954', 3, 16, 'STR/01/RA/327660', '2023-06-08', 'SIP/01/VI/238686', '2023-06-08', 6, '2024-08-17', 9, NULL, 1, '2024-08-29 08:10:26', '2024-08-29 08:10:26'),
(22, 22, 'user20@example.com', '784101', '1758375', '2022-02-07', '2023-07-20', 20, 15, 1, 133724, 71597, 318917, 35430, 119313, '33221623', '39180429', 'Adv.', 'Demak', '1911-06-16', 'be former rear pool driver porch meal bottle meet cloud same', '335375262', '147689423', '433833140', '2023-07-20', 24, '399217944', '28931209', 1, 5, 10, 131, 140, 2, 'IJ/VII/282525067', 2007, '193070756', 1, 2, 'STR/01/RA/256472', '2028-04-12', 'SIP/01/VI/344889', '2028-04-12', 5, '2023-07-20', 5, NULL, 1, '2024-08-29 08:10:27', '2024-08-29 08:10:27'),
(23, 23, 'user21@example.com', '121473', '1678931', '2012-10-27', '2023-11-18', 10, 9, 12, 127266, 38451, 253339, 60872, 26129, '45344226', '14498630', 'drh.', 'Depok', '1990-05-17', 'be former rear pool driver porch meal bottle meet cloud same', '125412831', '334489561', '264551738', '2023-11-18', 6, '43275778', '457666302', 1, 2, 1, 176, 185, 6, 'IJ/VII/468328417', 1927, '34271069', 3, 24, 'STR/01/RA/177068', '2023-01-11', 'SIP/01/VI/475959', '2023-01-11', 2, '2023-11-18', 7, NULL, 1, '2024-08-29 08:10:27', '2024-08-29 08:10:27'),
(24, 24, 'user22@example.com', '1077940', '530838', '2018-04-13', '2023-07-08', 10, 4, 9, 146056, 34316, 853467, 59194, 76219, '32543256', '26437350', 'Ns.', 'Sumedang', '1913-01-16', 'be former rear pool driver porch meal bottle meet cloud same', '296720914', '325159', '28957417', '2023-07-08', 37, '253362764', '187690075', 1, 3, 8, 238, 157, 4, 'IJ/VII/129717502', 1935, '416785314', 3, 20, 'STR/01/RA/38235', '2024-07-18', 'SIP/01/VI/386044', '2024-07-18', 6, '2023-07-08', 7, NULL, 1, '2024-08-29 08:10:27', '2024-08-29 08:10:27'),
(25, 25, 'user23@example.com', '2646517', '4025105', '2011-04-24', '2024-04-19', 2, 4, 12, 178897, 43899, 550793, 27128, 44699, '44112766', '45096467', 'drg.', 'Banyuwangi', '1947-07-25', 'be former rear pool driver porch meal bottle meet cloud same', '122519520', '35202166', '444278076', '2024-04-19', 38, '451607937', '117449871', 1, 1, 9, 63, 199, 2, 'IJ/VII/158163194', 1979, '382578618', 1, 14, 'STR/01/RA/413303', '2026-03-26', 'SIP/01/VI/77811', '2026-03-26', 2, '2024-04-19', 6, NULL, 1, '2024-08-29 08:10:27', '2024-08-29 08:10:27'),
(26, 26, 'user24@example.com', '2287917', '951497', '2020-02-27', '2023-06-13', 11, 3, 12, 84116, 110699, 907803, 8539, 99237, '24746072', '37208297', 'drg.', 'Trenggalek', '1978-01-06', 'be former rear pool driver porch meal bottle meet cloud same', '337995238', '66093236', '255048217', '2023-06-13', 20, '75964579', '180363331', 0, 2, 11, 84, 59, 12, 'IJ/VII/186197527', 1973, '242076569', 3, 18, 'STR/01/RA/350895', '2027-10-23', 'SIP/01/VI/456599', '2027-10-23', 7, '2023-06-13', 9, NULL, 1, '2024-08-29 08:10:28', '2024-08-29 08:10:28'),
(27, 27, 'user25@example.com', '4018005', '4492598', '2020-05-27', '2024-10-23', 16, 17, 11, 124513, 118005, 437178, 42147, 40099, '9827385', '3899363', 'Adv.', 'Sragen', '1924-08-30', 'be former rear pool driver porch meal bottle meet cloud same', '3455226', '423633732', '271532856', '2024-10-23', 17, '13617619', '389787363', 1, 3, 3, 118, 15, 7, 'IJ/VII/397494958', 1849, '174291902', 3, 23, 'STR/01/RA/419540', '2027-06-15', 'SIP/01/VI/332254', '2027-06-15', 3, '2024-10-23', 9, NULL, 1, '2024-08-29 08:10:28', '2024-08-29 08:10:28'),
(28, 28, 'user26@example.com', '267822', '2485222', '2007-03-26', '2023-09-05', 7, 18, 11, 206494, 874, 473067, 35745, 107740, '15834035', '25734330', 'Ns.', 'Kajen', '1969-05-29', 'be former rear pool driver porch meal bottle meet cloud same', '64821041', '476106995', '200014760', '2023-09-05', 16, '255592822', '467706785', 1, 5, 10, 274, 177, 12, 'IJ/VII/395148050', 1855, '365248452', 3, 18, 'STR/01/RA/57659', '2025-03-23', 'SIP/01/VI/380382', '2025-03-23', 7, '2023-09-05', 1, NULL, 1, '2024-08-29 08:10:28', '2024-08-29 08:10:28'),
(29, 29, 'user27@example.com', '2731347', '415518', '2022-06-05', '2023-05-14', 10, 12, 7, 120977, 76841, 989939, 35462, 100108, '30452154', '28383718', 'dr.', 'Jombang', '1994-06-04', 'be former rear pool driver porch meal bottle meet cloud same', '21439', '367960832', '161107931', '2023-05-14', 1, '325252719', '279803143', 0, 5, 4, 174, 99, 11, 'IJ/VII/166977347', 1867, '151500296', 1, 7, 'STR/01/RA/401523', '2024-07-09', 'SIP/01/VI/177931', '2024-07-09', 5, '2023-05-14', 3, NULL, 1, '2024-08-29 08:10:29', '2024-08-29 08:10:29'),
(30, 30, 'user28@example.com', '2040508', '2035395', '2017-01-20', '2024-05-24', 9, 7, 6, 177828, 2721, 415136, 12881, 16732, '44461808', '45429518', 'Adv.', 'Lamongan', '1953-04-30', 'be former rear pool driver porch meal bottle meet cloud same', '318053044', '83446181', '321653028', '2024-05-24', 7, '323225097', '218531210', 0, 3, 1, 172, 126, 1, 'IJ/VII/234341811', 1890, '171358804', 2, 7, 'STR/01/RA/295048', '2024-07-31', 'SIP/01/VI/480848', '2024-07-31', 1, '2024-05-24', 3, NULL, 1, '2024-08-29 08:10:29', '2024-08-29 08:10:29'),
(31, 31, 'user29@example.com', '2587219', '3050369', '2013-12-22', '2023-07-24', 19, 15, 10, 126557, 19774, 846626, 59093, 50600, '12623532', '12550553', 'Ir.', 'Brebes', '1926-03-27', 'be former rear pool driver porch meal bottle meet cloud same', '401677018', '334068382', '165379939', '2023-07-24', 3, '353492619', '361450855', 0, 3, 1, 245, 97, 6, 'IJ/VII/322984937', 1994, '74275178', 1, 24, 'STR/01/RA/424467', '2023-01-19', 'SIP/01/VI/429772', '2023-01-19', 3, '2023-07-24', 4, NULL, 1, '2024-08-29 08:10:29', '2024-08-29 08:10:29'),
(32, 32, 'user30@example.com', '3401077', '2526812', '2008-05-09', '2024-06-06', 11, 18, 5, 91328, 53324, 893897, 65298, 41652, '19523629', '35745618', 'Ak.', 'Subang', '1930-09-28', 'be former rear pool driver porch meal bottle meet cloud same', '443122181', '1656977', '399746678', '2024-06-06', 13, '136561677', '39407477', 1, 3, 10, 229, 183, 11, 'IJ/VII/114679558', 1904, '163525987', 2, 25, 'STR/01/RA/160878', '2028-01-09', 'SIP/01/VI/485197', '2028-01-09', 4, '2024-06-06', 10, NULL, 1, '2024-08-29 08:10:29', '2024-08-29 08:10:29'),
(33, 33, 'user31@example.com', '3045704', '4414506', '2009-05-07', '2024-11-11', 6, 4, 11, 101957, 116821, 352725, 29392, 117933, '18528231', '4515595', 'Ir.', 'Purwakarta', '1988-06-12', 'be former rear pool driver porch meal bottle meet cloud same', '186878927', '215857384', '22923159', '2024-11-11', 40, '250384970', '208713464', 1, 6, 3, 190, 18, 11, 'IJ/VII/189106449', 1992, '103624661', 2, 12, 'STR/01/RA/447783', '2024-07-10', 'SIP/01/VI/209721', '2024-07-10', 1, '2024-11-11', 1, NULL, 1, '2024-08-29 08:10:30', '2024-08-29 08:10:30'),
(34, 34, 'user32@example.com', '1355085', '4362377', '2008-04-03', '2024-04-22', 21, 17, 4, 215115, 92396, 999400, 4569, 90640, '8102317', '6290948', 'drg.', 'Pati', '1933-06-09', 'be former rear pool driver porch meal bottle meet cloud same', '238718909', '35352651', '443658018', '2024-04-22', 14, '449757882', '236035871', 1, 5, 2, 272, 24, 6, 'IJ/VII/473254730', 1841, '381501930', 3, 13, 'STR/01/RA/246125', '2027-12-08', 'SIP/01/VI/84889', '2027-12-08', 2, '2024-04-22', 6, NULL, 1, '2024-08-29 08:10:30', '2024-08-29 08:10:30'),
(35, 35, 'user33@example.com', '783444', '87865', '2015-07-12', '2023-04-27', 8, 16, 5, 207549, 31197, 812084, 36799, 113268, '27369668', '27395629', 'Adv.', 'Surabaya', '1918-01-07', 'be former rear pool driver porch meal bottle meet cloud same', '283335396', '372922411', '368463257', '2023-04-27', 32, '399732177', '5160798', 0, 5, 6, 63, 192, 12, 'IJ/VII/195370326', 1838, '314023553', 1, 11, 'STR/01/RA/354082', '2028-03-10', 'SIP/01/VI/181597', '2028-03-10', 6, '2023-04-27', 3, NULL, 1, '2024-08-29 08:10:30', '2024-08-29 08:10:30'),
(36, 36, 'user34@example.com', '4667337', '4316152', '2021-01-16', '2024-05-29', 13, 5, 3, 111152, 64837, 661154, 50284, 20882, '18499222', '2282860', 'apt.', 'Banyuwangi', '1930-08-10', 'be former rear pool driver porch meal bottle meet cloud same', '208584124', '197607750', '245076445', '2024-05-29', 5, '191239023', '327095077', 1, 2, 2, 124, 86, 10, 'IJ/VII/356418251', 1994, '293239153', 3, 20, 'STR/01/RA/37439', '2024-11-10', 'SIP/01/VI/214988', '2024-11-10', 1, '2024-05-29', 10, NULL, 1, '2024-08-29 08:10:31', '2024-08-29 08:10:31'),
(37, 37, 'user35@example.com', '2519673', '4590360', '2008-04-08', '2023-10-23', 2, 4, 9, 218140, 31472, 500738, 27151, 46559, '24803917', '41410145', 'Ns.', 'Tuban', '2003-04-17', 'be former rear pool driver porch meal bottle meet cloud same', '32195081', '354000781', '465843323', '2023-10-23', 3, '78425655', '10000427', 1, 1, 10, 65, 162, 4, 'IJ/VII/218645627', 1941, '451690856', 3, 6, 'STR/01/RA/497909', '2025-05-12', 'SIP/01/VI/480304', '2025-05-12', 7, '2023-10-23', 8, NULL, 1, '2024-08-29 08:10:31', '2024-08-29 08:10:31'),
(38, 38, 'user36@example.com', '3129095', '2431913', '2011-12-09', '2024-01-11', 6, 14, 8, 187873, 34931, 906778, 10724, 52804, '23434910', '45456518', 'drh.', 'Trenggalek', '1986-09-05', 'be former rear pool driver porch meal bottle meet cloud same', '46514821', '382237908', '458893125', '2024-01-11', 22, '187973549', '288947709', 0, 3, 3, 75, 143, 12, 'IJ/VII/416896803', 1965, '264889471', 2, 8, 'STR/01/RA/92649', '2028-07-27', 'SIP/01/VI/41025', '2028-07-27', 5, '2024-01-11', 8, NULL, 0, '2024-08-29 08:10:31', '2024-08-29 08:10:31'),
(39, 39, 'user37@example.com', '1865172', '1328901', '2022-12-20', '2024-08-25', 7, 10, 13, 100997, 88622, 736511, 11751, 64064, '1034239', '21278433', 'Ar.', 'Cirebon', '1947-01-01', 'be former rear pool driver porch meal bottle meet cloud same', '55485727', '154943900', '131230239', '2024-08-25', 29, '14345429', '205111084', 1, 2, 7, 95, 119, 8, 'IJ/VII/170216888', 2007, '278654512', 1, 12, 'STR/01/RA/483886', '2028-05-03', 'SIP/01/VI/168372', '2028-05-03', 2, '2024-08-25', 4, NULL, 1, '2024-08-29 08:10:31', '2024-08-29 08:10:31'),
(40, 40, 'user38@example.com', '1932322', '797404', '2007-04-09', '2024-05-03', 5, 13, 4, 246048, 18174, 864821, 47558, 32855, '41166003', '47594044', 'Ak.', 'Purworejo', '1959-01-24', 'be former rear pool driver porch meal bottle meet cloud same', '11502117', '260268822', '493222566', '2024-05-03', 3, '390997591', '350184332', 0, 5, 5, 230, 78, 10, 'IJ/VII/32605647', 1947, '112982492', 3, 17, 'STR/01/RA/267348', '2023-02-17', 'SIP/01/VI/188655', '2023-02-17', 6, '2024-05-03', 2, NULL, 1, '2024-08-29 08:10:32', '2024-08-29 08:10:32'),
(41, 41, 'user39@example.com', '796652', '1923236', '2011-01-13', '2024-07-19', 7, 11, 7, 125417, 89759, 780485, 48979, 10661, '31907881', '32421733', 'apt.', 'Bandung', '1972-06-05', 'be former rear pool driver porch meal bottle meet cloud same', '115129733', '387934202', '257640609', '2024-07-19', 1, '177537020', '372030408', 0, 6, 5, 58, 104, 5, 'IJ/VII/384698886', 1964, '263858852', 1, 21, 'STR/01/RA/282732', '2025-02-10', 'SIP/01/VI/424568', '2025-02-10', 8, '2024-07-19', 10, NULL, 1, '2024-08-29 08:10:32', '2024-08-29 08:10:32'),
(42, 42, 'user40@example.com', '954496', '312172', '2012-07-16', '2024-03-04', 9, 17, 11, 195571, 97963, 710162, 25192, 112594, '4384686', '13988051', 'Ak.', 'Banyuwangi', '1964-01-19', 'be former rear pool driver porch meal bottle meet cloud same', '248663001', '111623215', '113690523', '2024-03-04', 6, '412799627', '103549815', 1, 4, 3, 140, 163, 12, 'IJ/VII/341667592', 1915, '178772385', 3, 17, 'STR/01/RA/240058', '2025-08-27', 'SIP/01/VI/33860', '2025-08-27', 1, '2024-03-04', 5, NULL, 1, '2024-08-29 08:10:32', '2024-08-29 08:10:32'),
(43, 43, 'user41@example.com', '3527251', '2344345', '2017-12-25', '2024-04-24', 5, 19, 3, 113701, 72761, 552001, 558, 37714, '4444076', '28559845', 'Ir.', 'Nganjuk', '1939-06-14', 'be former rear pool driver porch meal bottle meet cloud same', '209849342', '108674779', '104878594', '2024-04-24', 25, '72123575', '8805487', 0, 5, 3, 69, 145, 2, 'IJ/VII/427956478', 1857, '76160732', 3, 25, 'STR/01/RA/116958', '2028-06-02', 'SIP/01/VI/498685', '2028-06-02', 4, '2024-04-24', 4, NULL, 1, '2024-08-29 08:10:33', '2024-08-29 08:10:33'),
(44, 44, 'user42@example.com', '4788736', '1150062', '2007-02-14', '2023-09-11', 13, 8, 4, 218363, 94149, 812820, 26191, 83737, '41659982', '21449568', 'Adv.', 'Pemalang', '1962-09-29', 'be former rear pool driver porch meal bottle meet cloud same', '401873983', '126756002', '327063742', '2023-09-11', 2, '485526478', '478640808', 1, 6, 1, 131, 59, 4, 'IJ/VII/441495429', 1826, '205322426', 2, 18, 'STR/01/RA/383501', '2025-08-08', 'SIP/01/VI/353632', '2025-08-08', 5, '2023-09-11', 5, NULL, 1, '2024-08-29 08:10:33', '2024-08-29 08:10:33'),
(45, 45, 'user43@example.com', '1291705', '924227', '2014-04-22', '2023-03-02', 2, 4, 13, 241667, 114766, 948008, 9404, 28771, '15589199', '11708257', 'Ir.', 'Sidoarjo', '1909-03-11', 'be former rear pool driver porch meal bottle meet cloud same', '457723433', '356733029', '15836224', '2023-03-02', 24, '78953653', '345940924', 1, 2, 10, 109, 21, 10, 'IJ/VII/443922348', 2006, '472012874', 1, 26, 'STR/01/RA/433845', '2023-10-25', 'SIP/01/VI/260311', '2023-10-25', 4, '2023-03-02', 8, NULL, 1, '2024-08-29 08:10:33', '2024-08-29 08:10:33'),
(46, 46, 'user44@example.com', '3847071', '4853029', '2012-09-17', '2024-04-21', 20, 1, 4, 194476, 48073, 443828, 28385, 66979, '11313510', '18408665', 'Adv.', 'Pasuruan', '1940-01-14', 'be former rear pool driver porch meal bottle meet cloud same', '249009557', '1815014', '352988966', '2024-04-21', 1, '446680409', '38390922', 1, 2, 5, 13, 68, 5, 'IJ/VII/493435134', 1801, '422340628', 1, 22, 'STR/01/RA/230103', '2028-05-19', 'SIP/01/VI/376252', '2028-05-19', 6, '2024-04-21', 7, NULL, 1, '2024-08-29 08:10:33', '2024-08-29 08:10:33'),
(47, 47, 'user45@example.com', '1905041', '4299541', '2018-05-08', '2024-08-03', 20, 10, 8, 199872, 13493, 921410, 9856, 110227, '21390360', '3572204', 'Ir.', 'Pati', '1971-02-14', 'be former rear pool driver porch meal bottle meet cloud same', '96385756', '127422697', '110264544', '2024-08-03', 10, '476800673', '108704721', 0, 3, 1, 36, 14, 5, 'IJ/VII/169623925', 1949, '499017517', 1, 25, 'STR/01/RA/267856', '2026-04-05', 'SIP/01/VI/149831', '2026-04-05', 1, '2024-08-03', 10, NULL, 1, '2024-08-29 08:10:34', '2024-08-29 08:10:34'),
(48, 48, 'user46@example.com', '4241828', '3229182', '2014-05-22', '2023-11-07', 8, 6, 8, 76433, 95394, 937079, 62152, 38696, '46254097', '29224274', 'Adv.', 'Magetan', '1956-09-18', 'be former rear pool driver porch meal bottle meet cloud same', '73438538', '481515518', '268708105', '2023-11-07', 29, '211426986', '318608031', 0, 3, 11, 142, 40, 12, 'IJ/VII/136799562', 1942, '57878737', 1, 1, 'STR/01/RA/289426', '2028-04-06', 'SIP/01/VI/471345', '2028-04-06', 6, '2023-11-07', 8, NULL, 1, '2024-08-29 08:10:34', '2024-08-29 08:10:34'),
(49, 49, 'user47@example.com', '3216531', '3297626', '2013-10-02', '2023-02-17', 9, 10, 6, 175024, 88555, 463654, 38684, 83578, '9435341', '22180808', 'Adv.', 'Bogor', '1910-01-14', 'be former rear pool driver porch meal bottle meet cloud same', '293728240', '5064815', '144700112', '2023-02-17', 22, '267393991', '29802647', 0, 5, 11, 79, 36, 8, 'IJ/VII/216004364', 1822, '495331637', 3, 7, 'STR/01/RA/101769', '2024-01-17', 'SIP/01/VI/265931', '2024-01-17', 8, '2023-02-17', 7, NULL, 1, '2024-08-29 08:10:34', '2024-08-29 08:10:34'),
(50, 50, 'fatwalinovera@gmail.com', '33319', '796788', '07-07-2018', '2024-11-06', 6, 9, 10, 70422, 4315, 676504, 47764, 18741, '7404864', '1234123412341234', 'Ak.', 'Boyolali', '1905-12-25', 'be former rear pool driver porch meal bottle meet cloud same', '49579815', '252963535', '426315229', '2024-11-06', 10, '287455758', '395329587', 1, 1, 9, 297, 131, 7, 'IJ/VII/259813122', 2005, '1234123412341234', 3, 23, 'STR/01/RA/377398', '2024-08-29T00:00:00.000Z', 'SIP/01/VI/69969', '2024-08-29T00:00:00.000Z', 3, '06-11-2024', 3, NULL, 1, '2024-08-29 08:10:35', '2024-08-30 22:54:48'),
(51, 51, 'candhy.fadhila.arsyad@gmail.com', '526321', '3308922', '16-02-2018', '2024-05-10', 3, 4, 4, 219304, 47973, 354123, 45823, 75950, '23305258', '1234567891012131', 'drg.', 'Blitar', '1949-06-13', 'be former rear pool driver porch meal bottle meet cloud same', '454028861', '154203924', '131317742', '2024-05-10', 7, '93887299', '410600201', 0, 1, 12, 72, 117, 9, 'IJ/VII/311840660', 1944, '1234567891012131', 3, 13, 'STR/01/RA/108186', '2024-12-21T00:00:00.000Z', 'SIP/01/VI/228289', '2024-12-21T00:00:00.000Z', 3, '10-05-2024', 10, NULL, 0, '2024-08-29 08:10:35', '2024-08-31 01:17:51'),
(55, 55, 'adiyatmaharis21@gmail.com', '56886585', '5847474', '01-09-2024', NULL, 1, 3, 3, 2000000, 120000, 120000, 78000, 560000, '337405161001000202', '3374070504010003', 'djasdka', 'Semarang', '2024-09-28', 'jalan test', '82226582306', '231312', '321321312', NULL, NULL, '321321312', '76967955', 1, 1, 2, 170, 85, 9, '8931803810', 2023, '3374070504010003', 2, 1, '7381798', 'Seumur Hidup', '123123', 'Seumur Hidup', 3, '25-10-2024', NULL, 1, 1, '2024-08-31 22:04:43', '2024-08-31 22:37:47'),
(56, 56, 'superadmin@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_keluargas`
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

--
-- Dumping data untuk tabel `data_keluargas`
--

INSERT INTO `data_keluargas` (`id`, `data_karyawan_id`, `nama_keluarga`, `hubungan`, `pendidikan_terakhir`, `status_hidup`, `pekerjaan`, `no_hp`, `email`, `created_at`, `updated_at`) VALUES
(1, 1, 'Nama Ayah 1', 'Ayah', 'S3', 1, 'Pekerjaan Ayah 1', '4212966', 'ayah1@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(2, 1, 'Nama Ibu 1', 'Ibu', 'S1', 0, 'Pekerjaan Ibu 1', '2432550', 'ibu1@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(3, 1, 'Nama Keluarga 1 0', 'Ibu Suami', 'D2', 1, 'Pekerjaan 1 0', '4337778', 'keluarga10@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(4, 2, 'Nama Ayah 2', 'Ayah', 'D1', 0, 'Pekerjaan Ayah 2', '133818', 'ayah2@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(5, 2, 'Nama Ibu 2', 'Ibu', 'D2', 0, 'Pekerjaan Ibu 2', '151469', 'ibu2@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(6, 2, 'Nama Keluarga 2 0', 'Suami', 'S2', 1, 'Pekerjaan 2 0', '3252295', 'keluarga20@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(7, 2, 'Nama Keluarga 2 1', 'Kakek', 'D1', 1, 'Pekerjaan 2 1', '82974', 'keluarga21@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(8, 3, 'Nama Ayah 3', 'Ayah', 'S2', 0, 'Pekerjaan Ayah 3', '1922799', 'ayah3@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(9, 3, 'Nama Ibu 3', 'Ibu', 'S3', 1, 'Pekerjaan Ibu 3', '274298', 'ibu3@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(10, 3, 'Nama Keluarga 3 0', 'Suami', 'D1', 1, 'Pekerjaan 3 0', '4555339', 'keluarga30@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(11, 3, 'Nama Keluarga 3 1', 'Ayah Istri', 'S1', 0, 'Pekerjaan 3 1', '1242591', 'keluarga31@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(12, 4, 'Nama Ayah 4', 'Ayah', 'D4', 0, 'Pekerjaan Ayah 4', '1125150', 'ayah4@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(13, 4, 'Nama Ibu 4', 'Ibu', 'S1', 0, 'Pekerjaan Ibu 4', '1210356', 'ibu4@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(14, 4, 'Nama Keluarga 4 0', 'Ayah Istri', 'D1', 0, 'Pekerjaan 4 0', '1341931', 'keluarga40@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(15, 4, 'Nama Keluarga 4 1', 'Nenek', 'S3', 1, 'Pekerjaan 4 1', '3098918', 'keluarga41@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(16, 5, 'Nama Ayah 5', 'Ayah', 'D1', 0, 'Pekerjaan Ayah 5', '474296', 'ayah5@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(17, 5, 'Nama Ibu 5', 'Ibu', 'S1', 1, 'Pekerjaan Ibu 5', '2376802', 'ibu5@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(18, 5, 'Nama Keluarga 5 0', 'Nenek', 'SMK', 1, 'Pekerjaan 5 0', '2447899', 'keluarga50@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(19, 6, 'Nama Ayah 6', 'Ayah', 'D4', 1, 'Pekerjaan Ayah 6', '995547', 'ayah6@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(20, 6, 'Nama Ibu 6', 'Ibu', 'SMA', 0, 'Pekerjaan Ibu 6', '3417873', 'ibu6@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(21, 7, 'Nama Ayah 7', 'Ayah', 'SMA', 0, 'Pekerjaan Ayah 7', '2965277', 'ayah7@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(22, 7, 'Nama Ibu 7', 'Ibu', 'SMK', 0, 'Pekerjaan Ibu 7', '1149685', 'ibu7@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(23, 7, 'Nama Keluarga 7 0', 'Suami', 'S1', 0, 'Pekerjaan 7 0', '715799', 'keluarga70@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(24, 8, 'Nama Ayah 8', 'Ayah', 'S1', 0, 'Pekerjaan Ayah 8', '2498242', 'ayah8@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(25, 8, 'Nama Ibu 8', 'Ibu', 'S2', 0, 'Pekerjaan Ibu 8', '2928524', 'ibu8@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(26, 9, 'Nama Ayah 9', 'Ayah', 'D1', 1, 'Pekerjaan Ayah 9', '2432116', 'ayah9@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(27, 9, 'Nama Ibu 9', 'Ibu', 'D2', 1, 'Pekerjaan Ibu 9', '1387735', 'ibu9@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(28, 10, 'Nama Ayah 10', 'Ayah', 'D2', 1, 'Pekerjaan Ayah 10', '2203859', 'ayah10@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(29, 10, 'Nama Ibu 10', 'Ibu', 'D1', 1, 'Pekerjaan Ibu 10', '145672', 'ibu10@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(30, 11, 'Nama Ayah 11', 'Ayah', 'S1', 0, 'Pekerjaan Ayah 11', '1076236', 'ayah11@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(31, 11, 'Nama Ibu 11', 'Ibu', 'S2', 1, 'Pekerjaan Ibu 11', '4522252', 'ibu11@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(32, 11, 'Nama Keluarga 11 0', 'Ibu Istri', 'D4', 0, 'Pekerjaan 11 0', '1102469', 'keluarga110@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(33, 11, 'Nama Keluarga 11 1', 'Ibu Istri', 'S3', 0, 'Pekerjaan 11 1', '542154', 'keluarga111@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(34, 12, 'Nama Ayah 12', 'Ayah', 'SMK', 0, 'Pekerjaan Ayah 12', '3437890', 'ayah12@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(35, 12, 'Nama Ibu 12', 'Ibu', 'D2', 0, 'Pekerjaan Ibu 12', '3748968', 'ibu12@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(36, 12, 'Nama Keluarga 12 0', 'Nenek', 'S2', 1, 'Pekerjaan 12 0', '4637309', 'keluarga120@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(37, 12, 'Nama Keluarga 12 1', 'Anak', 'D3', 0, 'Pekerjaan 12 1', '3644534', 'keluarga121@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(38, 13, 'Nama Ayah 13', 'Ayah', 'D2', 0, 'Pekerjaan Ayah 13', '4012163', 'ayah13@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(39, 13, 'Nama Ibu 13', 'Ibu', 'S3', 1, 'Pekerjaan Ibu 13', '4095253', 'ibu13@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(40, 14, 'Nama Ayah 14', 'Ayah', 'S2', 0, 'Pekerjaan Ayah 14', '3831313', 'ayah14@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(41, 14, 'Nama Ibu 14', 'Ibu', 'D2', 1, 'Pekerjaan Ibu 14', '173821', 'ibu14@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(42, 15, 'Nama Ayah 15', 'Ayah', 'D1', 1, 'Pekerjaan Ayah 15', '818738', 'ayah15@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(43, 15, 'Nama Ibu 15', 'Ibu', 'D3', 0, 'Pekerjaan Ibu 15', '3298999', 'ibu15@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(44, 15, 'Nama Keluarga 15 0', 'Kakek', 'SMA', 1, 'Pekerjaan 15 0', '634643', 'keluarga150@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(45, 15, 'Nama Keluarga 15 1', 'Nenek', 'SMK', 0, 'Pekerjaan 15 1', '412840', 'keluarga151@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(46, 16, 'Nama Ayah 16', 'Ayah', 'D2', 1, 'Pekerjaan Ayah 16', '4671464', 'ayah16@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(47, 16, 'Nama Ibu 16', 'Ibu', 'D2', 0, 'Pekerjaan Ibu 16', '1548387', 'ibu16@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(48, 16, 'Nama Keluarga 16 0', 'Istri', 'SMK', 1, 'Pekerjaan 16 0', '1790955', 'keluarga160@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(49, 17, 'Nama Ayah 17', 'Ayah', 'D3', 1, 'Pekerjaan Ayah 17', '3888929', 'ayah17@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(50, 17, 'Nama Ibu 17', 'Ibu', 'D1', 0, 'Pekerjaan Ibu 17', '3142646', 'ibu17@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(51, 17, 'Nama Keluarga 17 0', 'Anak', 'S1', 1, 'Pekerjaan 17 0', '3273301', 'keluarga170@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(52, 18, 'Nama Ayah 18', 'Ayah', 'D1', 1, 'Pekerjaan Ayah 18', '1795460', 'ayah18@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(53, 18, 'Nama Ibu 18', 'Ibu', 'SMK', 0, 'Pekerjaan Ibu 18', '3575103', 'ibu18@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(54, 18, 'Nama Keluarga 18 0', 'Istri', 'S3', 0, 'Pekerjaan 18 0', '3676798', 'keluarga180@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(55, 18, 'Nama Keluarga 18 1', 'Kakek', 'S3', 0, 'Pekerjaan 18 1', '1761726', 'keluarga181@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(56, 19, 'Nama Ayah 19', 'Ayah', 'S3', 1, 'Pekerjaan Ayah 19', '2630618', 'ayah19@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(57, 19, 'Nama Ibu 19', 'Ibu', 'S3', 0, 'Pekerjaan Ibu 19', '3407961', 'ibu19@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(58, 19, 'Nama Keluarga 19 0', 'Anak', 'D1', 0, 'Pekerjaan 19 0', '3125017', 'keluarga190@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(59, 19, 'Nama Keluarga 19 1', 'Ayah Istri', 'SMA', 1, 'Pekerjaan 19 1', '2070392', 'keluarga191@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(60, 20, 'Nama Ayah 20', 'Ayah', 'D1', 1, 'Pekerjaan Ayah 20', '4376155', 'ayah20@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(61, 20, 'Nama Ibu 20', 'Ibu', 'SMA', 0, 'Pekerjaan Ibu 20', '3408081', 'ibu20@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(62, 20, 'Nama Keluarga 20 0', 'Anak', 'D4', 0, 'Pekerjaan 20 0', '2509276', 'keluarga200@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(63, 20, 'Nama Keluarga 20 1', 'Nenek', 'S3', 1, 'Pekerjaan 20 1', '825910', 'keluarga201@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(64, 21, 'Nama Ayah 21', 'Ayah', 'D4', 0, 'Pekerjaan Ayah 21', '4636384', 'ayah21@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(65, 21, 'Nama Ibu 21', 'Ibu', 'SMK', 1, 'Pekerjaan Ibu 21', '4554598', 'ibu21@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(66, 21, 'Nama Keluarga 21 0', 'Kakek', 'S1', 1, 'Pekerjaan 21 0', '2842698', 'keluarga210@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(67, 22, 'Nama Ayah 22', 'Ayah', 'SMK', 1, 'Pekerjaan Ayah 22', '1061504', 'ayah22@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(68, 22, 'Nama Ibu 22', 'Ibu', 'S3', 0, 'Pekerjaan Ibu 22', '479425', 'ibu22@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(69, 22, 'Nama Keluarga 22 0', 'Ibu Suami', 'S2', 0, 'Pekerjaan 22 0', '3964217', 'keluarga220@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(70, 22, 'Nama Keluarga 22 1', 'Istri', 'SMK', 0, 'Pekerjaan 22 1', '3680731', 'keluarga221@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(71, 23, 'Nama Ayah 23', 'Ayah', 'SMK', 1, 'Pekerjaan Ayah 23', '3840670', 'ayah23@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(72, 23, 'Nama Ibu 23', 'Ibu', 'D4', 1, 'Pekerjaan Ibu 23', '4610367', 'ibu23@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(73, 23, 'Nama Keluarga 23 0', 'Ayah Istri', 'SMK', 0, 'Pekerjaan 23 0', '4939619', 'keluarga230@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(74, 23, 'Nama Keluarga 23 1', 'Ayah Suami', 'SMK', 1, 'Pekerjaan 23 1', '1365434', 'keluarga231@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(75, 24, 'Nama Ayah 24', 'Ayah', 'D4', 1, 'Pekerjaan Ayah 24', '1756060', 'ayah24@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(76, 24, 'Nama Ibu 24', 'Ibu', 'SMA', 0, 'Pekerjaan Ibu 24', '4006665', 'ibu24@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(77, 24, 'Nama Keluarga 24 0', 'Ibu Suami', 'S1', 1, 'Pekerjaan 24 0', '4577354', 'keluarga240@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(78, 25, 'Nama Ayah 25', 'Ayah', 'SMK', 1, 'Pekerjaan Ayah 25', '1631050', 'ayah25@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(79, 25, 'Nama Ibu 25', 'Ibu', 'S2', 1, 'Pekerjaan Ibu 25', '2790343', 'ibu25@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(80, 25, 'Nama Keluarga 25 0', 'Ayah Suami', 'D4', 1, 'Pekerjaan 25 0', '3997275', 'keluarga250@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(81, 25, 'Nama Keluarga 25 1', 'Nenek', 'S2', 1, 'Pekerjaan 25 1', '315940', 'keluarga251@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(82, 26, 'Nama Ayah 26', 'Ayah', 'D3', 0, 'Pekerjaan Ayah 26', '1563670', 'ayah26@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(83, 26, 'Nama Ibu 26', 'Ibu', 'D4', 1, 'Pekerjaan Ibu 26', '4108712', 'ibu26@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(84, 26, 'Nama Keluarga 26 0', 'Nenek', 'D2', 1, 'Pekerjaan 26 0', '1609444', 'keluarga260@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(85, 27, 'Nama Ayah 27', 'Ayah', 'D1', 0, 'Pekerjaan Ayah 27', '1622241', 'ayah27@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(86, 27, 'Nama Ibu 27', 'Ibu', 'SMA', 0, 'Pekerjaan Ibu 27', '4794183', 'ibu27@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(87, 27, 'Nama Keluarga 27 0', 'Anak', 'D4', 0, 'Pekerjaan 27 0', '2125325', 'keluarga270@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(88, 27, 'Nama Keluarga 27 1', 'Nenek', 'S1', 0, 'Pekerjaan 27 1', '2059992', 'keluarga271@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(89, 27, 'Nama Keluarga 27 2', 'Istri', 'S2', 1, 'Pekerjaan 27 2', '2120319', 'keluarga272@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(90, 28, 'Nama Ayah 28', 'Ayah', 'D4', 1, 'Pekerjaan Ayah 28', '333050', 'ayah28@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(91, 28, 'Nama Ibu 28', 'Ibu', 'S3', 1, 'Pekerjaan Ibu 28', '1499006', 'ibu28@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(92, 28, 'Nama Keluarga 28 0', 'Kakek', 'D1', 0, 'Pekerjaan 28 0', '817229', 'keluarga280@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(93, 28, 'Nama Keluarga 28 1', 'Kakek', 'SMA', 0, 'Pekerjaan 28 1', '1522024', 'keluarga281@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(94, 29, 'Nama Ayah 29', 'Ayah', 'D3', 1, 'Pekerjaan Ayah 29', '2694474', 'ayah29@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(95, 29, 'Nama Ibu 29', 'Ibu', 'D1', 1, 'Pekerjaan Ibu 29', '3051678', 'ibu29@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(96, 29, 'Nama Keluarga 29 0', 'Ibu Suami', 'S2', 1, 'Pekerjaan 29 0', '1014505', 'keluarga290@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(97, 30, 'Nama Ayah 30', 'Ayah', 'SMK', 1, 'Pekerjaan Ayah 30', '858795', 'ayah30@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(98, 30, 'Nama Ibu 30', 'Ibu', 'D4', 0, 'Pekerjaan Ibu 30', '2436042', 'ibu30@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(99, 30, 'Nama Keluarga 30 0', 'Ibu Istri', 'S2', 0, 'Pekerjaan 30 0', '1919419', 'keluarga300@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(100, 31, 'Nama Ayah 31', 'Ayah', 'S2', 1, 'Pekerjaan Ayah 31', '4455235', 'ayah31@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(101, 31, 'Nama Ibu 31', 'Ibu', 'S3', 0, 'Pekerjaan Ibu 31', '1215853', 'ibu31@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(102, 31, 'Nama Keluarga 31 0', 'Kakek', 'S3', 0, 'Pekerjaan 31 0', '1546579', 'keluarga310@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(103, 32, 'Nama Ayah 32', 'Ayah', 'SMK', 0, 'Pekerjaan Ayah 32', '2533037', 'ayah32@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(104, 32, 'Nama Ibu 32', 'Ibu', 'D3', 0, 'Pekerjaan Ibu 32', '780689', 'ibu32@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(105, 32, 'Nama Keluarga 32 0', 'Ayah Suami', 'D1', 1, 'Pekerjaan 32 0', '3746775', 'keluarga320@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(106, 33, 'Nama Ayah 33', 'Ayah', 'SMA', 1, 'Pekerjaan Ayah 33', '4853192', 'ayah33@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(107, 33, 'Nama Ibu 33', 'Ibu', 'SMK', 1, 'Pekerjaan Ibu 33', '1321327', 'ibu33@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(108, 33, 'Nama Keluarga 33 0', 'Ayah Istri', 'SMK', 1, 'Pekerjaan 33 0', '3011431', 'keluarga330@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(109, 33, 'Nama Keluarga 33 1', 'Kakek', 'D1', 1, 'Pekerjaan 33 1', '3736470', 'keluarga331@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(110, 33, 'Nama Keluarga 33 2', 'Nenek', 'S2', 0, 'Pekerjaan 33 2', '71991', 'keluarga332@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(111, 34, 'Nama Ayah 34', 'Ayah', 'D2', 0, 'Pekerjaan Ayah 34', '3894203', 'ayah34@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(112, 34, 'Nama Ibu 34', 'Ibu', 'D1', 0, 'Pekerjaan Ibu 34', '2029677', 'ibu34@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(113, 34, 'Nama Keluarga 34 0', 'Nenek', 'D2', 1, 'Pekerjaan 34 0', '4522620', 'keluarga340@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(114, 35, 'Nama Ayah 35', 'Ayah', 'S2', 0, 'Pekerjaan Ayah 35', '3705788', 'ayah35@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(115, 35, 'Nama Ibu 35', 'Ibu', 'D1', 1, 'Pekerjaan Ibu 35', '525814', 'ibu35@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(116, 35, 'Nama Keluarga 35 0', 'Anak', 'D2', 0, 'Pekerjaan 35 0', '295155', 'keluarga350@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(117, 35, 'Nama Keluarga 35 1', 'Ayah Suami', 'SMA', 0, 'Pekerjaan 35 1', '3640279', 'keluarga351@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(118, 36, 'Nama Ayah 36', 'Ayah', 'D2', 1, 'Pekerjaan Ayah 36', '2446080', 'ayah36@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(119, 36, 'Nama Ibu 36', 'Ibu', 'D2', 0, 'Pekerjaan Ibu 36', '4159379', 'ibu36@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(120, 37, 'Nama Ayah 37', 'Ayah', 'D3', 1, 'Pekerjaan Ayah 37', '3709249', 'ayah37@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(121, 37, 'Nama Ibu 37', 'Ibu', 'D1', 0, 'Pekerjaan Ibu 37', '4208610', 'ibu37@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(122, 37, 'Nama Keluarga 37 0', 'Anak', 'S1', 1, 'Pekerjaan 37 0', '1365896', 'keluarga370@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(123, 38, 'Nama Ayah 38', 'Ayah', 'D3', 0, 'Pekerjaan Ayah 38', '4510053', 'ayah38@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(124, 38, 'Nama Ibu 38', 'Ibu', 'S1', 0, 'Pekerjaan Ibu 38', '3098444', 'ibu38@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(125, 39, 'Nama Ayah 39', 'Ayah', 'D4', 0, 'Pekerjaan Ayah 39', '1409168', 'ayah39@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(126, 39, 'Nama Ibu 39', 'Ibu', 'SMK', 0, 'Pekerjaan Ibu 39', '4108341', 'ibu39@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(127, 39, 'Nama Keluarga 39 0', 'Nenek', 'D1', 1, 'Pekerjaan 39 0', '9220', 'keluarga390@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(128, 39, 'Nama Keluarga 39 1', 'Ayah Istri', 'D1', 0, 'Pekerjaan 39 1', '2523914', 'keluarga391@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(129, 39, 'Nama Keluarga 39 2', 'Nenek', 'S1', 0, 'Pekerjaan 39 2', '1528677', 'keluarga392@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(130, 40, 'Nama Ayah 40', 'Ayah', 'SMK', 1, 'Pekerjaan Ayah 40', '1518154', 'ayah40@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(131, 40, 'Nama Ibu 40', 'Ibu', 'S2', 1, 'Pekerjaan Ibu 40', '1376580', 'ibu40@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(132, 40, 'Nama Keluarga 40 0', 'Kakek', 'S1', 0, 'Pekerjaan 40 0', '655844', 'keluarga400@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(133, 40, 'Nama Keluarga 40 1', 'Ayah Istri', 'S3', 1, 'Pekerjaan 40 1', '1822148', 'keluarga401@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(134, 41, 'Nama Ayah 41', 'Ayah', 'SMA', 0, 'Pekerjaan Ayah 41', '3067961', 'ayah41@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(135, 41, 'Nama Ibu 41', 'Ibu', 'D4', 1, 'Pekerjaan Ibu 41', '3829522', 'ibu41@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(136, 42, 'Nama Ayah 42', 'Ayah', 'S2', 0, 'Pekerjaan Ayah 42', '3639186', 'ayah42@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(137, 42, 'Nama Ibu 42', 'Ibu', 'D3', 0, 'Pekerjaan Ibu 42', '2345185', 'ibu42@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(138, 42, 'Nama Keluarga 42 0', 'Istri', 'D1', 1, 'Pekerjaan 42 0', '1447016', 'keluarga420@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(139, 43, 'Nama Ayah 43', 'Ayah', 'SMA', 1, 'Pekerjaan Ayah 43', '3687893', 'ayah43@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(140, 43, 'Nama Ibu 43', 'Ibu', 'S3', 0, 'Pekerjaan Ibu 43', '1420554', 'ibu43@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(141, 44, 'Nama Ayah 44', 'Ayah', 'D2', 0, 'Pekerjaan Ayah 44', '4712412', 'ayah44@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(142, 44, 'Nama Ibu 44', 'Ibu', 'D3', 0, 'Pekerjaan Ibu 44', '3152800', 'ibu44@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(143, 44, 'Nama Keluarga 44 0', 'Ayah Suami', 'SMA', 1, 'Pekerjaan 44 0', '1050298', 'keluarga440@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(144, 45, 'Nama Ayah 45', 'Ayah', 'SMK', 1, 'Pekerjaan Ayah 45', '755970', 'ayah45@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(145, 45, 'Nama Ibu 45', 'Ibu', 'S1', 1, 'Pekerjaan Ibu 45', '159375', 'ibu45@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(146, 45, 'Nama Keluarga 45 0', 'Ibu Suami', 'D3', 1, 'Pekerjaan 45 0', '4858668', 'keluarga450@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(147, 46, 'Nama Ayah 46', 'Ayah', 'D3', 1, 'Pekerjaan Ayah 46', '714862', 'ayah46@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(148, 46, 'Nama Ibu 46', 'Ibu', 'SMK', 0, 'Pekerjaan Ibu 46', '1351765', 'ibu46@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(149, 46, 'Nama Keluarga 46 0', 'Suami', 'D2', 0, 'Pekerjaan 46 0', '260562', 'keluarga460@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(150, 46, 'Nama Keluarga 46 1', 'Ayah Suami', 'D3', 0, 'Pekerjaan 46 1', '3536041', 'keluarga461@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(151, 47, 'Nama Ayah 47', 'Ayah', 'S3', 1, 'Pekerjaan Ayah 47', '3176189', 'ayah47@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(152, 47, 'Nama Ibu 47', 'Ibu', 'S3', 1, 'Pekerjaan Ibu 47', '1844298', 'ibu47@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(153, 48, 'Nama Ayah 48', 'Ayah', 'S2', 1, 'Pekerjaan Ayah 48', '4308353', 'ayah48@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(154, 48, 'Nama Ibu 48', 'Ibu', 'D4', 0, 'Pekerjaan Ibu 48', '3547307', 'ibu48@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(155, 48, 'Nama Keluarga 48 0', 'Ibu Suami', 'D4', 0, 'Pekerjaan 48 0', '3230207', 'keluarga480@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(156, 49, 'Nama Ayah 49', 'Ayah', 'SMK', 1, 'Pekerjaan Ayah 49', '2061826', 'ayah49@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(157, 49, 'Nama Ibu 49', 'Ibu', 'S3', 0, 'Pekerjaan Ibu 49', '300143', 'ibu49@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(158, 50, 'Nama Ayah 50', 'Ayah', 'D2', 1, 'Pekerjaan Ayah 50', '3630790', 'ayah50@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(159, 50, 'Nama Ibu 50', 'Ibu', 'SMA', 1, 'Pekerjaan Ibu 50', '1231990', 'ibu50@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(160, 50, 'Nama Keluarga 50 0', 'Anak', 'D3', 1, 'Pekerjaan 50 0', '504279', 'keluarga500@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(161, 50, 'Nama Keluarga 50 1', 'Ayah Istri', 'S2', 1, 'Pekerjaan 50 1', '4796383', 'keluarga501@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(162, 51, 'Nama Ayah 51', 'Ayah', 'S1', 1, 'Pekerjaan Ayah 51', '3209975', 'ayah51@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(163, 51, 'Nama Ibu 51', 'Ibu', 'D3', 0, 'Pekerjaan Ibu 51', '3140959', 'ibu51@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(164, 51, 'Nama Keluarga 51 0', 'Suami', 'S2', 1, 'Pekerjaan 51 0', '3941669', 'keluarga510@example.com', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(165, 55, 'joko', 'Suami', 'ini pendidikan', 1, 'DOKTER', '82226582306', 'adiyatmaharis21@gmail.com', '2024-09-01 05:07:45', '2024-09-01 05:07:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_gajis`
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

--
-- Dumping data untuk tabel `detail_gajis`
--

INSERT INTO `detail_gajis` (`id`, `penggajian_id`, `kategori_gaji_id`, `nama_detail`, `besaran`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Gaji Pokok', 5824907, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(2, 1, 2, 'Tunjangan Jabatan', 875721, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(3, 1, 2, 'Tunjangan Fungsional', 134402, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(4, 1, 2, 'Tunjangan Khusus', 17543, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(5, 1, 2, 'Tunjangan Kompetensi', 2042795, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(6, 1, 2, 'Tunjangan Lainnya', 913300, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(7, 1, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(8, 1, 2, 'Uang Makan', 11172, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(9, 1, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(10, 1, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(11, 1, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(12, 1, 3, 'PPH21', 230397, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(13, 2, 1, 'Gaji Pokok', 5597658, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(14, 2, 2, 'Tunjangan Jabatan', 875721, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(15, 2, 2, 'Tunjangan Fungsional', 228636, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(16, 2, 2, 'Tunjangan Khusus', 7515, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(17, 2, 2, 'Tunjangan Kompetensi', 2111467, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(18, 2, 2, 'Tunjangan Lainnya', 263834, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(19, 2, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(20, 2, 2, 'Uang Makan', 11832, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(21, 2, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(22, 2, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(23, 2, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(24, 2, 3, 'PPH21', 142750, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(25, 3, 1, 'Gaji Pokok', 8633546, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(26, 3, 2, 'Tunjangan Jabatan', 2682958, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(27, 3, 2, 'Tunjangan Fungsional', 161370, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(28, 3, 2, 'Tunjangan Khusus', 73697, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(29, 3, 2, 'Tunjangan Kompetensi', 2533495, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(30, 3, 2, 'Tunjangan Lainnya', 302791, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(31, 3, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(32, 3, 2, 'Uang Makan', 1805, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(33, 3, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(34, 3, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(35, 3, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(36, 3, 3, 'PPH21', 740484, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(37, 4, 1, 'Gaji Pokok', 8618140, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(38, 4, 2, 'Tunjangan Jabatan', 1887160, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(39, 4, 2, 'Tunjangan Fungsional', 170457, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(40, 4, 2, 'Tunjangan Khusus', 32372, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(41, 4, 2, 'Tunjangan Kompetensi', 1280395, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(42, 4, 2, 'Tunjangan Lainnya', 737330, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(43, 4, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(44, 4, 2, 'Uang Makan', 68267, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(45, 4, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(46, 4, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(47, 4, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(48, 4, 3, 'PPH21', 660707, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(49, 5, 1, 'Gaji Pokok', 8004405, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(50, 5, 2, 'Tunjangan Jabatan', 875721, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(51, 5, 2, 'Tunjangan Fungsional', 222365, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(52, 5, 2, 'Tunjangan Khusus', 48863, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(53, 5, 2, 'Tunjangan Kompetensi', 1676674, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(54, 5, 2, 'Tunjangan Lainnya', 738240, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(55, 5, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(56, 5, 2, 'Uang Makan', 50283, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(57, 5, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(58, 5, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(59, 5, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(60, 5, 3, 'PPH21', 361097, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(61, 6, 1, 'Gaji Pokok', 6598400, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(62, 6, 2, 'Tunjangan Jabatan', 4564034, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(63, 6, 2, 'Tunjangan Fungsional', 133724, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(64, 6, 2, 'Tunjangan Khusus', 71597, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(65, 6, 2, 'Tunjangan Kompetensi', 1654494, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(66, 6, 2, 'Tunjangan Lainnya', 318917, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(67, 6, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(68, 6, 2, 'Uang Makan', 35430, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(69, 6, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(70, 6, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(71, 6, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(72, 6, 3, 'PPH21', 689830, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(73, 7, 1, 'Gaji Pokok', 9941121, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(74, 7, 2, 'Tunjangan Jabatan', 2521526, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(75, 7, 2, 'Tunjangan Fungsional', 178897, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(76, 7, 2, 'Tunjangan Khusus', 43899, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(77, 7, 2, 'Tunjangan Kompetensi', 1280395, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(78, 7, 2, 'Tunjangan Lainnya', 550793, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(79, 7, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(80, 7, 2, 'Uang Makan', 27128, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(81, 7, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(82, 7, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(83, 7, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(84, 7, 3, 'PPH21', 897826, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(85, 8, 1, 'Gaji Pokok', 5726342, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(86, 8, 2, 'Tunjangan Jabatan', 861929, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(87, 8, 2, 'Tunjangan Fungsional', 120977, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(88, 8, 2, 'Tunjangan Khusus', 76841, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(89, 8, 2, 'Tunjangan Kompetensi', 2551117, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(90, 8, 2, 'Tunjangan Lainnya', 989939, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(91, 8, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(92, 8, 2, 'Uang Makan', 35462, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(93, 8, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(94, 8, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(95, 8, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(96, 8, 3, 'PPH21', 215653, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(97, 9, 1, 'Gaji Pokok', 9731618, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(98, 9, 2, 'Tunjangan Jabatan', 4564034, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(99, 9, 2, 'Tunjangan Fungsional', 126557, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(100, 9, 2, 'Tunjangan Khusus', 19774, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(101, 9, 2, 'Tunjangan Kompetensi', 2256991, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(102, 9, 2, 'Tunjangan Lainnya', 846626, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(103, 9, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(104, 9, 2, 'Uang Makan', 59093, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(105, 9, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(106, 9, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(107, 9, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(108, 9, 3, 'PPH21', 1441976, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(109, 10, 1, 'Gaji Pokok', 9983220, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(110, 10, 2, 'Tunjangan Jabatan', 2924399, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(111, 10, 2, 'Tunjangan Fungsional', 207549, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(112, 10, 2, 'Tunjangan Khusus', 31197, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(113, 10, 2, 'Tunjangan Kompetensi', 815886, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(114, 10, 2, 'Tunjangan Lainnya', 812084, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(115, 10, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(116, 10, 2, 'Uang Makan', 36799, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(117, 10, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(118, 10, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(119, 10, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(120, 10, 3, 'PPH21', 913869, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(121, 11, 1, 'Gaji Pokok', 5180527, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(122, 11, 2, 'Tunjangan Jabatan', 3650022, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(123, 11, 2, 'Tunjangan Fungsional', 100997, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(124, 11, 2, 'Tunjangan Khusus', 88622, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(125, 11, 2, 'Tunjangan Kompetensi', 2042795, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(126, 11, 2, 'Tunjangan Lainnya', 736511, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(127, 11, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(128, 11, 2, 'Uang Makan', 11751, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(129, 11, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(130, 11, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(131, 11, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(132, 11, 3, 'PPH21', 489249, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(133, 12, 1, 'Gaji Pokok', 9587648, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(134, 12, 2, 'Tunjangan Jabatan', 4797307, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(135, 12, 2, 'Tunjangan Fungsional', 125417, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(136, 12, 2, 'Tunjangan Khusus', 89759, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(137, 12, 2, 'Tunjangan Kompetensi', 2551117, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(138, 12, 2, 'Tunjangan Lainnya', 780485, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(139, 12, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(140, 12, 2, 'Uang Makan', 48979, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(141, 12, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(142, 12, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(143, 12, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(144, 12, 3, 'PPH21', 1288050, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(145, 13, 1, 'Gaji Pokok', 5821227, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(146, 13, 2, 'Tunjangan Jabatan', 2521526, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(147, 13, 2, 'Tunjangan Fungsional', 241667, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(148, 13, 2, 'Tunjangan Khusus', 114766, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(149, 13, 2, 'Tunjangan Kompetensi', 2042795, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(150, 13, 2, 'Tunjangan Lainnya', 948008, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(151, 13, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(152, 13, 2, 'Uang Makan', 9404, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(153, 13, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(154, 13, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(155, 13, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(156, 13, 3, 'PPH21', 363582, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(157, 14, 1, 'Gaji Pokok', 9427437, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(158, 14, 2, 'Tunjangan Jabatan', 1943104, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(159, 14, 2, 'Tunjangan Fungsional', 194476, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(160, 14, 2, 'Tunjangan Khusus', 48073, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(161, 14, 2, 'Tunjangan Kompetensi', 543084, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(162, 14, 2, 'Tunjangan Lainnya', 443828, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(163, 14, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(164, 14, 2, 'Uang Makan', 28385, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(165, 14, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(166, 14, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(167, 14, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(168, 14, 3, 'PPH21', 521936, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(169, 15, 1, 'Gaji Pokok', 8699894, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(170, 15, 2, 'Tunjangan Jabatan', 3650022, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(171, 15, 2, 'Tunjangan Fungsional', 199872, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(172, 15, 2, 'Tunjangan Khusus', 13493, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(173, 15, 2, 'Tunjangan Kompetensi', 1570488, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(174, 15, 2, 'Tunjangan Lainnya', 921410, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(175, 15, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(176, 15, 2, 'Uang Makan', 9856, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(177, 15, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(178, 15, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(179, 15, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(180, 15, 3, 'PPH21', 1083953, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(181, 16, 1, 'Gaji Pokok', 6580013, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(182, 16, 2, 'Tunjangan Jabatan', 1818356, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(183, 16, 2, 'Tunjangan Fungsional', 76433, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(184, 16, 2, 'Tunjangan Khusus', 95394, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(185, 16, 2, 'Tunjangan Kompetensi', 1570488, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(186, 16, 2, 'Tunjangan Lainnya', 937079, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(187, 16, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(188, 16, 2, 'Uang Makan', 62152, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(189, 16, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(190, 16, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(191, 16, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(192, 16, 3, 'PPH21', 288998, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(193, 17, 1, 'Gaji Pokok', 8004405, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(194, 17, 2, 'Tunjangan Jabatan', 875721, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(195, 17, 2, 'Tunjangan Fungsional', 245484, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(196, 17, 2, 'Tunjangan Khusus', 114138, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(197, 17, 2, 'Tunjangan Kompetensi', 2533495, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(198, 17, 2, 'Tunjangan Lainnya', 668748, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(199, 17, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(200, 17, 2, 'Uang Makan', 47120, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(201, 17, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(202, 17, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(203, 17, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(204, 17, 3, 'PPH21', 645456, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(205, 18, 1, 'Gaji Pokok', 6404119, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(206, 18, 2, 'Tunjangan Jabatan', 3382896, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(207, 18, 2, 'Tunjangan Fungsional', 243290, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(208, 18, 2, 'Tunjangan Khusus', 92386, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(209, 18, 2, 'Tunjangan Kompetensi', 1654494, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(210, 18, 2, 'Tunjangan Lainnya', 483200, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(211, 18, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(212, 18, 2, 'Uang Makan', 61455, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(213, 18, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(214, 18, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(215, 18, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(216, 18, 3, 'PPH21', 509674, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(217, 19, 1, 'Gaji Pokok', 5594718, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(218, 19, 2, 'Tunjangan Jabatan', 875721, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(219, 19, 2, 'Tunjangan Fungsional', 228628, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(220, 19, 2, 'Tunjangan Khusus', 59530, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(221, 19, 2, 'Tunjangan Kompetensi', 2111467, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(222, 19, 2, 'Tunjangan Lainnya', 382275, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(223, 19, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(224, 19, 2, 'Uang Makan', 2884, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(225, 19, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(226, 19, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(227, 19, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(228, 19, 3, 'PPH21', 120941, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(229, 20, 1, 'Gaji Pokok', 9281637, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(230, 20, 2, 'Tunjangan Jabatan', 2924399, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(231, 20, 2, 'Tunjangan Fungsional', 212964, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(232, 20, 2, 'Tunjangan Khusus', 70461, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(233, 20, 2, 'Tunjangan Kompetensi', 815886, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(234, 20, 2, 'Tunjangan Lainnya', 806811, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(235, 20, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(236, 20, 2, 'Uang Makan', 20874, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(237, 20, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(238, 20, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(239, 20, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(240, 20, 3, 'PPH21', 727652, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(241, 21, 1, 'Gaji Pokok', 5594718, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(242, 21, 2, 'Tunjangan Jabatan', 1887160, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(243, 21, 2, 'Tunjangan Fungsional', 220690, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(244, 21, 2, 'Tunjangan Khusus', 61822, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(245, 21, 2, 'Tunjangan Kompetensi', 2673527, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(246, 21, 2, 'Tunjangan Lainnya', 348491, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(247, 21, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(248, 21, 2, 'Uang Makan', 25865, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(249, 21, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(250, 21, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(251, 21, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(252, 21, 3, 'PPH21', 224646, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(253, 22, 1, 'Gaji Pokok', 5594718, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(254, 22, 2, 'Tunjangan Jabatan', 3650022, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(255, 22, 2, 'Tunjangan Fungsional', 141523, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(256, 22, 2, 'Tunjangan Khusus', 114857, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(257, 22, 2, 'Tunjangan Kompetensi', 1280395, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(258, 22, 2, 'Tunjangan Lainnya', 347864, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(259, 22, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(260, 22, 2, 'Uang Makan', 57251, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(261, 22, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(262, 22, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(263, 22, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(264, 22, 3, 'PPH21', 232133, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(265, 23, 1, 'Gaji Pokok', 9281637, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(266, 23, 2, 'Tunjangan Jabatan', 4564034, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(267, 23, 2, 'Tunjangan Fungsional', 85279, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(268, 23, 2, 'Tunjangan Khusus', 69844, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(269, 23, 2, 'Tunjangan Kompetensi', 2042795, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(270, 23, 2, 'Tunjangan Lainnya', 348519, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(271, 23, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(272, 23, 2, 'Uang Makan', 38802, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(273, 23, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(274, 23, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(275, 23, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(276, 23, 3, 'PPH21', 1179564, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(277, 24, 1, 'Gaji Pokok', 5726342, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(278, 24, 2, 'Tunjangan Jabatan', 1887160, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(279, 24, 2, 'Tunjangan Fungsional', 177828, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(280, 24, 2, 'Tunjangan Khusus', 2721, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(281, 24, 2, 'Tunjangan Kompetensi', 2055918, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(282, 24, 2, 'Tunjangan Lainnya', 415136, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(283, 24, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(284, 24, 2, 'Uang Makan', 12881, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(285, 24, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(286, 24, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(287, 24, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(288, 24, 3, 'PPH21', 267450, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(289, 25, 1, 'Gaji Pokok', 8699894, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(290, 25, 2, 'Tunjangan Jabatan', 875721, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(291, 25, 2, 'Tunjangan Fungsional', 91328, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(292, 25, 2, 'Tunjangan Khusus', 53324, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(293, 25, 2, 'Tunjangan Kompetensi', 815886, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(294, 25, 2, 'Tunjangan Lainnya', 893897, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(295, 25, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(296, 25, 2, 'Uang Makan', 65298, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(297, 25, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(298, 25, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(299, 25, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(300, 25, 3, 'PPH21', 357461, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(301, 26, 1, 'Gaji Pokok', 5180527, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(302, 26, 2, 'Tunjangan Jabatan', 2521526, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(303, 26, 2, 'Tunjangan Fungsional', 101957, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(304, 26, 2, 'Tunjangan Khusus', 116821, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(305, 26, 2, 'Tunjangan Kompetensi', 2673527, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(306, 26, 2, 'Tunjangan Lainnya', 352725, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(307, 26, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(308, 26, 2, 'Uang Makan', 29392, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(309, 26, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(310, 26, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(311, 26, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(312, 26, 3, 'PPH21', 398877, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(313, 27, 1, 'Gaji Pokok', 8379663, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(314, 27, 2, 'Tunjangan Jabatan', 2682958, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(315, 27, 2, 'Tunjangan Fungsional', 187873, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(316, 27, 2, 'Tunjangan Khusus', 34931, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(317, 27, 2, 'Tunjangan Kompetensi', 1570488, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(318, 27, 2, 'Tunjangan Lainnya', 906778, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(319, 27, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(320, 27, 2, 'Uang Makan', 10724, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(321, 27, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(322, 27, 2, 'Bonus Presensi', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(323, 27, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(324, 27, 3, 'PPH21', 694671, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(325, 28, 1, 'Gaji Pokok', 5594718, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(326, 28, 2, 'Tunjangan Jabatan', 2233528, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(327, 28, 2, 'Tunjangan Fungsional', 218363, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(328, 28, 2, 'Tunjangan Khusus', 94149, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(329, 28, 2, 'Tunjangan Kompetensi', 543084, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(330, 28, 2, 'Tunjangan Lainnya', 812820, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(331, 28, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(332, 28, 2, 'Uang Makan', 26191, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(333, 28, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(334, 28, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(335, 28, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(336, 28, 3, 'PPH21', 149143, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(337, 29, 1, 'Gaji Pokok', 6580013, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(338, 29, 2, 'Tunjangan Jabatan', 4081677, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(339, 29, 2, 'Tunjangan Fungsional', 2000000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(340, 29, 2, 'Tunjangan Khusus', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(341, 29, 2, 'Tunjangan Kompetensi', 1676674, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(342, 29, 2, 'Tunjangan Lainnya', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(343, 29, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(344, 29, 2, 'Uang Makan', 78000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(345, 29, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(346, 29, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(347, 29, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(348, 29, 3, 'PPH21', 904582, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(349, 29, 3, 'BPJS Kesehatan', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(350, 29, 3, 'BPJS Ketenagakerjaan', 12000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(351, 29, 3, 'Iuran Pensiun', 150000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(352, 30, 1, 'Gaji Pokok', 9587648, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(353, 30, 2, 'Tunjangan Jabatan', 4564034, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(354, 30, 2, 'Tunjangan Fungsional', 144113, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(355, 30, 2, 'Tunjangan Khusus', 87509, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(356, 30, 2, 'Tunjangan Kompetensi', 2551117, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(357, 30, 2, 'Tunjangan Lainnya', 320227, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(358, 30, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(359, 30, 2, 'Uang Makan', 62522, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(360, 30, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(361, 30, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(362, 30, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(363, 30, 3, 'PPH21', 1241602, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(364, 31, 1, 'Gaji Pokok', 5597658, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(365, 31, 2, 'Tunjangan Jabatan', 3132479, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(366, 31, 2, 'Tunjangan Fungsional', 132995, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(367, 31, 2, 'Tunjangan Khusus', 90593, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(368, 31, 2, 'Tunjangan Kompetensi', 1280395, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(369, 31, 2, 'Tunjangan Lainnya', 717088, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(370, 31, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(371, 31, 2, 'Uang Makan', 52455, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(372, 31, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(373, 31, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(374, 31, 2, 'THR', NULL, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(375, 31, 3, 'PPH21', 399829, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(376, 32, 1, 'Gaji Pokok', 5594718, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(377, 32, 2, 'Tunjangan Jabatan', 2924399, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(378, 32, 2, 'Tunjangan Fungsional', 212103, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(379, 32, 2, 'Tunjangan Khusus', 57713, NULL, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(380, 32, 2, 'Tunjangan Kompetensi', 2111467, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(381, 32, 2, 'Tunjangan Lainnya', 450039, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(382, 32, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(383, 32, 2, 'Uang Makan', 56278, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(384, 32, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(385, 32, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(386, 32, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(387, 32, 3, 'PPH21', 473069, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(388, 33, 1, 'Gaji Pokok', 5195340, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(389, 33, 2, 'Tunjangan Jabatan', 1818356, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(390, 33, 2, 'Tunjangan Fungsional', 87050, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(391, 33, 2, 'Tunjangan Khusus', 101957, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(392, 33, 2, 'Tunjangan Kompetensi', 2673527, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(393, 33, 2, 'Tunjangan Lainnya', 539908, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(394, 33, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(395, 33, 2, 'Uang Makan', 63184, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(396, 33, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(397, 33, 2, 'Bonus Presensi', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(398, 33, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(399, 33, 3, 'PPH21', 264984, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(400, 34, 1, 'Gaji Pokok', 8379663, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(401, 34, 2, 'Tunjangan Jabatan', 3893788, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(402, 34, 2, 'Tunjangan Fungsional', 112671, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(403, 34, 2, 'Tunjangan Khusus', 71799, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(404, 34, 2, 'Tunjangan Kompetensi', 2551117, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(405, 34, 2, 'Tunjangan Lainnya', 335494, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(406, 34, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(407, 34, 2, 'Uang Makan', 45563, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(408, 34, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(409, 34, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(410, 34, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(411, 34, 3, 'PPH21', 948606, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(412, 35, 1, 'Gaji Pokok', 5824907, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(413, 35, 2, 'Tunjangan Jabatan', 4564034, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(414, 35, 2, 'Tunjangan Fungsional', 249484, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(415, 35, 2, 'Tunjangan Khusus', 18416, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(416, 35, 2, 'Tunjangan Kompetensi', 1654494, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(417, 35, 2, 'Tunjangan Lainnya', 482772, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(418, 35, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(419, 35, 2, 'Uang Makan', 27869, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(420, 35, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(421, 35, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(422, 35, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(423, 35, 3, 'PPH21', 529680, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(424, 36, 1, 'Gaji Pokok', 8633546, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(425, 36, 2, 'Tunjangan Jabatan', 1201902, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(426, 36, 2, 'Tunjangan Fungsional', 189586, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(427, 36, 2, 'Tunjangan Khusus', 58112, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(428, 36, 2, 'Tunjangan Kompetensi', 1676674, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(429, 36, 2, 'Tunjangan Lainnya', 865192, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(430, 36, 2, 'Uang Lembur', 220938, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(431, 36, 2, 'Uang Makan', 17072, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(432, 36, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(433, 36, 2, 'Bonus Presensi', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(434, 36, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(435, 36, 3, 'PPH21', 519321, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(436, 37, 1, 'Gaji Pokok', 7002145, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(437, 37, 2, 'Tunjangan Jabatan', 2924399, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(438, 37, 2, 'Tunjangan Fungsional', 192965, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(439, 37, 2, 'Tunjangan Khusus', 54612, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(440, 37, 2, 'Tunjangan Kompetensi', 2055918, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(441, 37, 2, 'Tunjangan Lainnya', 795232, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(442, 37, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(443, 37, 2, 'Uang Makan', 67255, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(444, 37, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(445, 37, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(446, 37, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(447, 37, 3, 'PPH21', 540502, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(448, 38, 1, 'Gaji Pokok', 9731618, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(449, 38, 2, 'Tunjangan Jabatan', 4099127, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(450, 38, 2, 'Tunjangan Fungsional', 127266, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(451, 38, 2, 'Tunjangan Khusus', 38451, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(452, 38, 2, 'Tunjangan Kompetensi', 1280395, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(453, 38, 2, 'Tunjangan Lainnya', 253339, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(454, 38, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(455, 38, 2, 'Uang Makan', 60872, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(456, 38, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(457, 38, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(458, 38, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(459, 38, 3, 'PPH21', 1120775, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(460, 39, 1, 'Gaji Pokok', 9446166, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(461, 39, 2, 'Tunjangan Jabatan', 2521526, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(462, 39, 2, 'Tunjangan Fungsional', 146056, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(463, 39, 2, 'Tunjangan Khusus', 34316, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(464, 39, 2, 'Tunjangan Kompetensi', 2111467, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(465, 39, 2, 'Tunjangan Lainnya', 853467, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(466, 39, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(467, 39, 2, 'Uang Makan', 59194, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(468, 39, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(469, 39, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(470, 39, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(471, 39, 3, 'PPH21', 935532, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(472, 40, 1, 'Gaji Pokok', 5594718, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(473, 40, 2, 'Tunjangan Jabatan', 4081677, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(474, 40, 2, 'Tunjangan Fungsional', 84116, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(475, 40, 2, 'Tunjangan Khusus', 110699, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(476, 40, 2, 'Tunjangan Kompetensi', 1280395, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(477, 40, 2, 'Tunjangan Lainnya', 907803, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(478, 40, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(479, 40, 2, 'Uang Makan', 8539, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(480, 40, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(481, 40, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(482, 40, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(483, 40, 3, 'PPH21', 374639, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(484, 41, 1, 'Gaji Pokok', 5824907, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(485, 41, 2, 'Tunjangan Jabatan', 3893788, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(486, 41, 2, 'Tunjangan Fungsional', 124513, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(487, 41, 2, 'Tunjangan Khusus', 118005, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(488, 41, 2, 'Tunjangan Kompetensi', 2673527, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(489, 41, 2, 'Tunjangan Lainnya', 437178, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(490, 41, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(491, 41, 2, 'Uang Makan', 42147, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(492, 41, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(493, 41, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(494, 41, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(495, 41, 3, 'PPH21', 676704, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(496, 42, 1, 'Gaji Pokok', 5594718, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(497, 42, 2, 'Tunjangan Jabatan', 875721, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(498, 42, 2, 'Tunjangan Fungsional', 206494, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(499, 42, 2, 'Tunjangan Khusus', 874, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(500, 42, 2, 'Tunjangan Kompetensi', 2673527, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(501, 42, 2, 'Tunjangan Lainnya', 473067, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(502, 42, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(503, 42, 2, 'Uang Makan', 35745, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(504, 42, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(505, 42, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(506, 42, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(507, 42, 3, 'PPH21', 154203, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(508, 43, 1, 'Gaji Pokok', 8004405, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(509, 43, 2, 'Tunjangan Jabatan', 3893788, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(510, 43, 2, 'Tunjangan Fungsional', 215115, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(511, 43, 2, 'Tunjangan Khusus', 92396, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(512, 43, 2, 'Tunjangan Kompetensi', 543084, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(513, 43, 2, 'Tunjangan Lainnya', 999400, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(514, 43, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(515, 43, 2, 'Uang Makan', 4569, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(516, 43, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(517, 43, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(518, 43, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(519, 43, 3, 'PPH21', 850366, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(520, 44, 1, 'Gaji Pokok', 9446166, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(521, 44, 2, 'Tunjangan Jabatan', 3382896, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(522, 44, 2, 'Tunjangan Fungsional', 111152, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(523, 44, 2, 'Tunjangan Khusus', 64837, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(524, 44, 2, 'Tunjangan Kompetensi', 1676674, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(525, 44, 2, 'Tunjangan Lainnya', 661154, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(526, 44, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(527, 44, 2, 'Uang Makan', 50284, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(528, 44, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(529, 44, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(530, 44, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(531, 44, 3, 'PPH21', 1106922, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(532, 45, 1, 'Gaji Pokok', 8618140, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(533, 45, 2, 'Tunjangan Jabatan', 2521526, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(534, 45, 2, 'Tunjangan Fungsional', 218140, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(535, 45, 2, 'Tunjangan Khusus', 31472, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(536, 45, 2, 'Tunjangan Kompetensi', 2111467, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(537, 45, 2, 'Tunjangan Lainnya', 500738, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(538, 45, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(539, 45, 2, 'Uang Makan', 27151, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(540, 45, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(541, 45, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(542, 45, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(543, 45, 3, 'PPH21', 722432, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(544, 46, 1, 'Gaji Pokok', 9281637, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(545, 46, 2, 'Tunjangan Jabatan', 3132479, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(546, 46, 2, 'Tunjangan Fungsional', 246048, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(547, 46, 2, 'Tunjangan Khusus', 18174, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(548, 46, 2, 'Tunjangan Kompetensi', 543084, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(549, 46, 2, 'Tunjangan Lainnya', 864821, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(550, 46, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(551, 46, 2, 'Uang Makan', 47558, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(552, 46, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(553, 46, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(554, 46, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(555, 46, 3, 'PPH21', 727691, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(556, 47, 1, 'Gaji Pokok', 9281637, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(557, 47, 2, 'Tunjangan Jabatan', 3893788, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(558, 47, 2, 'Tunjangan Fungsional', 195571, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(559, 47, 2, 'Tunjangan Khusus', 97963, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(560, 47, 2, 'Tunjangan Kompetensi', 2673527, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(561, 47, 2, 'Tunjangan Lainnya', 710162, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(562, 47, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(563, 47, 2, 'Uang Makan', 25192, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(564, 47, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(565, 47, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(566, 47, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34');
INSERT INTO `detail_gajis` (`id`, `penggajian_id`, `kategori_gaji_id`, `nama_detail`, `besaran`, `deleted_at`, `created_at`, `updated_at`) VALUES
(567, 47, 3, 'PPH21', 1383828, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(568, 48, 1, 'Gaji Pokok', 8699894, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(569, 48, 2, 'Tunjangan Jabatan', 4173362, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(570, 48, 2, 'Tunjangan Fungsional', 113701, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(571, 48, 2, 'Tunjangan Khusus', 72761, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(572, 48, 2, 'Tunjangan Kompetensi', 1676674, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(573, 48, 2, 'Tunjangan Lainnya', 552001, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(574, 48, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(575, 48, 2, 'Uang Makan', 558, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(576, 48, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(577, 48, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(578, 48, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(579, 48, 3, 'PPH21', 942538, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(580, 49, 1, 'Gaji Pokok', 5726342, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(581, 49, 2, 'Tunjangan Jabatan', 3650022, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(582, 49, 2, 'Tunjangan Fungsional', 175024, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(583, 49, 2, 'Tunjangan Khusus', 88555, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(584, 49, 2, 'Tunjangan Kompetensi', 2055918, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(585, 49, 2, 'Tunjangan Lainnya', 463654, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(586, 49, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(587, 49, 2, 'Uang Makan', 38684, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(588, 49, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(589, 49, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(590, 49, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(591, 49, 3, 'PPH21', 378546, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(592, 50, 1, 'Gaji Pokok', 5824907, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(593, 50, 2, 'Tunjangan Jabatan', 4099127, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(594, 50, 2, 'Tunjangan Fungsional', 70422, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(595, 50, 2, 'Tunjangan Khusus', 4315, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(596, 50, 2, 'Tunjangan Kompetensi', 2256991, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(597, 50, 2, 'Tunjangan Lainnya', 676504, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(598, 50, 2, 'Uang Lembur', 312, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(599, 50, 2, 'Uang Makan', 47764, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(600, 50, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(601, 50, 2, 'Bonus Presensi', 300000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(602, 50, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(603, 50, 3, 'PPH21', 670018, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(604, 50, 3, 'BPJS Kesehatan', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(605, 50, 3, 'BPJS Ketenagakerjaan', 12000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(606, 50, 3, 'Iuran Pensiun', 150000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(607, 50, 3, 'Jaminan Hari Tua', 58249, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(608, 51, 1, 'Gaji Pokok', 8004405, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(609, 51, 2, 'Tunjangan Jabatan', 2521526, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(610, 51, 2, 'Tunjangan Fungsional', 219304, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(611, 51, 2, 'Tunjangan Khusus', 47973, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(612, 51, 2, 'Tunjangan Kompetensi', 543084, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(613, 51, 2, 'Tunjangan Lainnya', 354123, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(614, 51, 2, 'Uang Lembur', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(615, 51, 2, 'Uang Makan', 45823, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(616, 51, 2, 'Bonus BOR', 120000, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(617, 51, 2, 'Bonus Presensi', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(618, 51, 2, 'THR', NULL, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(619, 51, 3, 'PPH21', 474250, NULL, '2024-09-01 01:32:34', '2024-09-01 01:32:34');

-- --------------------------------------------------------

--
-- Struktur dari tabel `diklats`
--

CREATE TABLE `diklats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `gambar` bigint(20) UNSIGNED DEFAULT NULL,
  `dokumen_eksternal` bigint(20) UNSIGNED DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `kategori_diklat_id` bigint(20) UNSIGNED NOT NULL,
  `status_diklat_id` bigint(20) UNSIGNED NOT NULL,
  `deskripsi` varchar(255) NOT NULL,
  `kuota` int(11) NOT NULL,
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

--
-- Dumping data untuk tabel `diklats`
--

INSERT INTO `diklats` (`id`, `gambar`, `dokumen_eksternal`, `nama`, `kategori_diklat_id`, `status_diklat_id`, `deskripsi`, `kuota`, `tgl_mulai`, `tgl_selesai`, `jam_mulai`, `jam_selesai`, `durasi`, `lokasi`, `verifikator_1`, `verifikator_2`, `alasan`, `created_at`, `updated_at`) VALUES
(1, NULL, 51, 'Diklat 1', 2, 2, 'Deskripsi Diklat 1', 1, '2024-05-23', '2024-05-24', '08:00:00', '11:00:00', 10800, 'Lokasi 1', 1, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 18:06:33'),
(2, 52, NULL, 'Diklat 2', 1, 1, 'Deskripsi Diklat 2', 16, '2023-10-01', '2023-10-04', '08:00:00', '12:00:00', 14400, 'Lokasi 2', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(3, 53, NULL, 'Diklat 3', 1, 2, 'Deskripsi Diklat 3', 47, '2024-06-07', '2024-06-09', '08:00:00', '12:00:00', 14400, 'Lokasi 3', 1, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 18:06:36'),
(4, 54, NULL, 'Diklat 4', 1, 1, 'Deskripsi Diklat 4', 38, '2024-08-15', '2024-08-18', '08:00:00', '10:00:00', 7200, 'Lokasi 4', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(5, NULL, 55, 'Diklat 5', 2, 1, 'Deskripsi Diklat 5', 1, '2024-06-29', '2024-06-30', '08:00:00', '11:00:00', 10800, 'Lokasi 5', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(6, 56, NULL, 'Diklat 6', 1, 1, 'Deskripsi Diklat 6', 33, '2024-03-07', '2024-03-12', '08:00:00', '10:00:00', 7200, 'Lokasi 6', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(7, 57, NULL, 'Diklat 7', 1, 1, 'Deskripsi Diklat 7', 43, '2024-05-05', '2024-05-06', '08:00:00', '09:00:00', 3600, 'Lokasi 7', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(8, 58, NULL, 'Diklat 8', 1, 1, 'Deskripsi Diklat 8', 17, '2024-05-25', '2024-05-27', '08:00:00', '09:00:00', 3600, 'Lokasi 8', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(9, 59, NULL, 'Diklat 9', 1, 1, 'Deskripsi Diklat 9', 37, '2023-12-21', '2023-12-24', '08:00:00', '11:00:00', 10800, 'Lokasi 9', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(10, NULL, 60, 'Diklat 10', 2, 1, 'Deskripsi Diklat 10', 1, '2023-12-24', '2023-12-25', '08:00:00', '09:00:00', 3600, 'Lokasi 10', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(11, NULL, 61, 'Diklat 11', 2, 1, 'Deskripsi Diklat 11', 1, '2024-05-29', '2024-06-01', '08:00:00', '11:00:00', 10800, 'Lokasi 11', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(12, NULL, 62, 'Diklat 12', 2, 1, 'Deskripsi Diklat 12', 1, '2023-09-15', '2023-09-16', '08:00:00', '11:00:00', 10800, 'Lokasi 12', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(13, NULL, 63, 'Diklat 13', 2, 1, 'Deskripsi Diklat 13', 1, '2024-04-26', '2024-04-27', '08:00:00', '10:00:00', 7200, 'Lokasi 13', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(14, NULL, 64, 'Diklat 14', 2, 1, 'Deskripsi Diklat 14', 1, '2024-02-08', '2024-02-10', '08:00:00', '11:00:00', 10800, 'Lokasi 14', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(15, 65, NULL, 'Diklat 15', 1, 1, 'Deskripsi Diklat 15', 50, '2024-08-03', '2024-08-06', '08:00:00', '11:00:00', 10800, 'Lokasi 15', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(16, 66, NULL, 'Diklat 16', 1, 1, 'Deskripsi Diklat 16', 49, '2024-01-06', '2024-01-07', '08:00:00', '10:00:00', 7200, 'Lokasi 16', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(17, NULL, 67, 'Diklat 17', 2, 1, 'Deskripsi Diklat 17', 1, '2024-05-13', '2024-05-17', '08:00:00', '09:00:00', 3600, 'Lokasi 17', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(18, 68, NULL, 'Diklat 18', 1, 1, 'Deskripsi Diklat 18', 25, '2024-08-24', '2024-08-28', '08:00:00', '12:00:00', 14400, 'Lokasi 18', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(19, 69, NULL, 'Diklat 19', 1, 1, 'Deskripsi Diklat 19', 12, '2024-08-26', '2024-08-27', '08:00:00', '09:00:00', 3600, 'Lokasi 19', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(20, 70, NULL, 'Diklat 20', 1, 1, 'Deskripsi Diklat 20', 21, '2023-12-29', '2023-12-31', '08:00:00', '11:00:00', 10800, 'Lokasi 20', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(21, NULL, 71, 'Diklat 21', 2, 1, 'Deskripsi Diklat 21', 1, '2023-10-07', '2023-10-12', '08:00:00', '10:00:00', 7200, 'Lokasi 21', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(22, 72, NULL, 'Diklat 22', 1, 1, 'Deskripsi Diklat 22', 30, '2024-03-08', '2024-03-09', '08:00:00', '12:00:00', 14400, 'Lokasi 22', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(23, 73, NULL, 'Diklat 23', 1, 1, 'Deskripsi Diklat 23', 42, '2023-12-24', '2023-12-27', '08:00:00', '09:00:00', 3600, 'Lokasi 23', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(24, 74, NULL, 'Diklat 24', 1, 1, 'Deskripsi Diklat 24', 43, '2024-01-27', '2024-02-01', '08:00:00', '09:00:00', 3600, 'Lokasi 24', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(25, 75, NULL, 'Diklat 25', 1, 1, 'Deskripsi Diklat 25', 28, '2023-10-13', '2023-10-18', '08:00:00', '11:00:00', 10800, 'Lokasi 25', NULL, NULL, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
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
-- Struktur dari tabel `hari_liburs`
--

CREATE TABLE `hari_liburs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `hari_liburs`
--

INSERT INTO `hari_liburs` (`id`, `nama`, `tanggal`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Minggu', '2024-06-01', '2024-08-29 18:11:06', NULL, '2024-08-29 18:11:06'),
(2, 'HUT RI 2024', '2024-08-17', '2024-08-29 18:11:21', '2024-08-29 18:11:21', '2024-08-29 18:11:21'),
(3, 'Cuti Bersama', '2024-09-01', NULL, '2024-08-29 18:11:34', '2024-08-29 18:11:34'),
(4, 'Hari Buruh', '2024-08-31', '2024-08-31 21:48:38', '2024-08-29 18:14:00', '2024-08-31 21:48:38'),
(5, 'Hari ini', '2024-09-01', NULL, '2024-08-31 21:49:00', '2024-08-31 21:49:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jabatans`
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

--
-- Dumping data untuk tabel `jabatans`
--

INSERT INTO `jabatans` (`id`, `nama_jabatan`, `is_struktural`, `tunjangan_jabatan`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Keuangan', 1, 1943104, NULL, '2023-12-16 08:10:20', '2024-08-29 08:10:20'),
(2, 'Dokter Umum', 0, 1201902, NULL, '2024-02-05 08:10:20', '2024-08-29 08:10:20'),
(3, 'Bidan', 0, 4081677, NULL, '2024-07-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'HRD', 1, 2521526, NULL, '2024-07-07 08:10:20', '2024-08-29 08:10:20'),
(5, 'Staf Tata Usaha', 0, 3382896, NULL, '2024-05-31 08:10:20', '2024-08-29 08:10:20'),
(6, 'Apoteker', 0, 1818356, NULL, '2024-08-18 08:10:20', '2024-08-29 08:10:20'),
(7, 'Kepala Rumah Sakit', 1, 1887160, NULL, '2024-05-14 08:10:20', '2024-08-29 08:10:20'),
(8, 'Tenaga Radiologi', 0, 2233528, NULL, '2023-10-06 08:10:20', '2024-08-29 08:10:20'),
(9, 'Satpam', 1, 4099127, NULL, '2024-04-11 08:10:20', '2024-08-29 08:10:20'),
(10, 'Tenaga Medis Darurat', 1, 3650022, NULL, '2024-04-11 08:10:20', '2024-08-29 08:10:20'),
(11, 'Kabid Keperawatan', 0, 4797307, NULL, '2024-07-12 08:10:20', '2024-08-29 08:10:20'),
(12, 'Pekerja Sosial', 0, 861929, NULL, '2024-02-09 08:10:20', '2024-08-29 08:10:20'),
(13, 'Kabid Pelayanan Penunjang', 0, 3132479, NULL, '2024-06-06 08:10:20', '2024-08-29 08:10:20'),
(14, 'Fisioterapis', 1, 2682958, NULL, '2024-06-21 08:10:20', '2024-08-29 08:10:20'),
(15, 'Kabid Pendidikan dan Penelitian', 1, 4564034, NULL, '2024-07-02 08:10:20', '2024-08-29 08:10:20'),
(16, 'Bendahara', 0, 2924399, NULL, '2024-07-30 08:10:20', '2024-08-29 08:10:20'),
(17, 'Wakil Direktur', 0, 3893788, NULL, '2023-09-17 08:10:20', '2024-08-29 08:10:20'),
(18, 'Dokter Spesialis', 0, 875721, NULL, '2024-01-01 08:10:20', '2024-08-29 08:10:20'),
(19, 'Tenaga Kebersihan', 0, 4173362, NULL, '2024-01-04 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwals`
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

--
-- Dumping data untuk tabel `jadwals`
--

INSERT INTO `jadwals` (`id`, `user_id`, `tgl_mulai`, `tgl_selesai`, `shift_id`, `created_at`, `updated_at`) VALUES
(1, 2, '2024-09-03', '2024-09-03', 1, '2024-08-31 15:18:46', '2024-08-31 15:18:46'),
(2, 51, '2024-09-03', '2024-09-04', 3, '2024-08-31 15:18:51', '2024-08-31 15:18:51'),
(3, 20, '2024-09-02', '2024-09-02', 1, '2024-08-31 19:49:48', '2024-08-31 20:04:05'),
(4, 50, '2024-09-02', '2024-09-02', 2, '2024-08-31 20:02:36', '2024-08-31 20:38:21'),
(5, 33, '2024-09-02', '2024-09-02', 1, '2024-08-31 20:26:28', '2024-08-31 20:32:50'),
(7, 16, '2024-09-02', '2024-09-02', 1, '2024-08-31 20:33:58', '2024-08-31 20:38:21'),
(8, 20, '2024-09-04', '2024-09-04', 1, '2024-08-31 21:23:17', '2024-08-31 21:24:11'),
(9, 16, '2024-09-04', '2024-09-05', 3, '2024-08-31 21:23:24', '2024-08-31 21:24:11'),
(10, 55, '2024-09-01', '2024-09-01', 1, '2024-08-31 22:38:57', '2024-08-31 22:38:57'),
(11, 9, '2024-09-01', '2024-09-01', 2, '2024-08-31 22:52:04', '2024-08-31 22:52:04'),
(12, 2, '2024-09-04', '2024-09-04', 1, '2024-09-01 22:40:57', '2024-09-01 22:41:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_penggajians`
--

CREATE TABLE `jadwal_penggajians` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tgl_mulai` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `jadwal_penggajians`
--

INSERT INTO `jadwal_penggajians` (`id`, `tgl_mulai`, `created_at`, `updated_at`) VALUES
(1, 27, '2024-08-29 08:10:35', '2024-08-31 21:46:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jawabans`
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
-- Struktur dari tabel `jenis_penilaians`
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
-- Dumping data untuk tabel `jenis_penilaians`
--

INSERT INTO `jenis_penilaians` (`id`, `nama`, `status_karyawan_id`, `jabatan_penilai`, `jabatan_dinilai`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Penilaian Karyawan Tetap', 1, 2, 3, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(2, 'Penilaian Karyawan Kontrak', 2, 4, 5, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(3, 'Penilaian Karyawan Magang', 3, 6, 7, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_activity_logs`
--

CREATE TABLE `kategori_activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_activity_logs`
--

INSERT INTO `kategori_activity_logs` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Test 1', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Test 2', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Test 3', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Test 4', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_agamas`
--

CREATE TABLE `kategori_agamas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_agamas`
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
-- Struktur dari tabel `kategori_berkas`
--

CREATE TABLE `kategori_berkas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_berkas`
--

INSERT INTO `kategori_berkas` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Pribadi', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Umum', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'System', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Lainnya', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_darahs`
--

CREATE TABLE `kategori_darahs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_darahs`
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
-- Struktur dari tabel `kategori_diklats`
--

CREATE TABLE `kategori_diklats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_diklats`
--

INSERT INTO `kategori_diklats` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Internal', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Eksternal', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_gajis`
--

CREATE TABLE `kategori_gajis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_gajis`
--

INSERT INTO `kategori_gajis` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Penghasilan Dasar', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Penambah', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Pengurang', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_kompensasis`
--

CREATE TABLE `kategori_kompensasis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_kompensasis`
--

INSERT INTO `kategori_kompensasis` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Test 1', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Test 2', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Test 3', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Test 4', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_notifikasis`
--

CREATE TABLE `kategori_notifikasis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_notifikasis`
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
-- Struktur dari tabel `kategori_pendidikans`
--

CREATE TABLE `kategori_pendidikans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_pendidikans`
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
-- Struktur dari tabel `kategori_potongans`
--

CREATE TABLE `kategori_potongans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_potongans`
--

INSERT INTO `kategori_potongans` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Gaji Bruto', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Gaji Pokok', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_presensis`
--

CREATE TABLE `kategori_presensis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_presensis`
--

INSERT INTO `kategori_presensis` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Tepat Waktu', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Terlambat', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Cuti', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Absen', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_ters`
--

CREATE TABLE `kategori_ters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_kategori_ter` varchar(255) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_ters`
--

INSERT INTO `kategori_ters` (`id`, `nama_kategori_ter`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'TER Kategori A', NULL, '2024-07-02 08:10:20', '2024-08-29 08:10:20'),
(2, 'TER Kategori B', NULL, '2024-07-02 08:10:20', '2024-08-29 08:10:20'),
(3, 'TER Kategori C', NULL, '2024-07-02 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_track_records`
--

CREATE TABLE `kategori_track_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_track_records`
--

INSERT INTO `kategori_track_records` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Perubahan Data', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Mutasi Pegawai', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Promosi Karyawan', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_transfer_karyawans`
--

CREATE TABLE `kategori_transfer_karyawans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_transfer_karyawans`
--

INSERT INTO `kategori_transfer_karyawans` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Promosi Karyawan', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Mutasi Pegawai', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_tukar_jadwals`
--

CREATE TABLE `kategori_tukar_jadwals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori_tukar_jadwals`
--

INSERT INTO `kategori_tukar_jadwals` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Tukar Shift', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Tukar Libur', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelompok_gajis`
--

CREATE TABLE `kelompok_gajis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_kelompok` varchar(255) NOT NULL,
  `besaran_gaji` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kelompok_gajis`
--

INSERT INTO `kelompok_gajis` (`id`, `nama_kelompok`, `besaran_gaji`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Kelompok Gaji A', 6580013, NULL, '2024-03-11 08:10:20', '2024-08-29 08:10:20'),
(2, 'Kelompok Gaji B', 6598400, NULL, '2023-10-11 08:10:20', '2024-08-29 08:10:20'),
(3, 'Kelompok Gaji C', 9782931, NULL, '2024-08-18 08:10:20', '2024-08-29 08:10:20'),
(4, 'Kelompok Gaji D', 5455079, NULL, '2023-09-09 08:10:20', '2024-08-29 08:10:20'),
(5, 'Kelompok Gaji E', 8633546, NULL, '2023-11-15 08:10:20', '2024-08-29 08:10:20'),
(6, 'Kelompok Gaji F', 8618140, NULL, '2024-03-20 08:10:20', '2024-08-29 08:10:20'),
(7, 'Kelompok Gaji G', 5726342, NULL, '2024-01-19 08:10:20', '2024-08-29 08:10:20'),
(8, 'Kelompok Gaji H', 8379663, NULL, '2024-08-01 08:10:20', '2024-08-29 08:10:20'),
(9, 'Kelompok Gaji I', 6404119, NULL, '2023-11-08 08:10:20', '2024-08-29 08:10:20'),
(10, 'Kelompok Gaji J', 5195340, NULL, '2024-08-08 08:10:20', '2024-08-29 08:10:20'),
(11, 'Kelompok Gaji K', 9983220, NULL, '2023-10-26 08:10:20', '2024-08-29 08:10:20'),
(12, 'Kelompok Gaji L', 5180527, NULL, '2024-02-11 08:10:20', '2024-08-29 08:10:20'),
(13, 'Kelompok Gaji M', 8004405, NULL, '2024-06-08 08:10:20', '2024-08-29 08:10:20'),
(14, 'Kelompok Gaji N', 9941121, NULL, '2023-12-03 08:10:20', '2024-08-29 08:10:20'),
(15, 'Kelompok Gaji O', 5597658, NULL, '2024-03-01 08:10:20', '2024-08-29 08:10:20'),
(16, 'Kelompok Gaji P', 7002145, NULL, '2024-02-29 08:10:20', '2024-08-29 08:10:20'),
(17, 'Kelompok Gaji Q', 9281637, '2024-08-31 21:43:58', '2024-08-28 08:10:20', '2024-08-31 21:43:58'),
(18, 'Kelompok Gaji R', 5594718, NULL, '2024-07-19 08:10:20', '2024-08-29 08:10:20'),
(19, 'Kelompok Gaji S', 6282663, NULL, '2023-09-09 08:10:20', '2024-08-29 08:10:20'),
(20, 'Kelompok Gaji T', 9446166, NULL, '2024-04-23 08:10:20', '2024-08-29 08:10:20'),
(21, 'Kelompok Gaji U', 9587648, NULL, '2024-01-16 08:10:20', '2024-08-29 08:10:20'),
(22, 'Kelompok Gaji V', 9427437, NULL, '2023-11-22 08:10:20', '2024-08-29 08:10:20'),
(23, 'Kelompok Gaji W', 5824907, NULL, '2024-02-09 08:10:20', '2024-08-29 08:10:20'),
(24, 'Kelompok Gaji X', 9731618, NULL, '2023-09-09 08:10:20', '2024-08-29 08:10:20'),
(25, 'Kelompok Gaji Y', 8699894, NULL, '2024-02-13 08:10:20', '2024-08-29 08:10:20'),
(26, 'Kelompok Gaji Z', 5821227, NULL, '2024-01-18 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kompetensis`
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

--
-- Dumping data untuk tabel `kompetensis`
--

INSERT INTO `kompetensis` (`id`, `nama_kompetensi`, `jenis_kompetensi`, `tunjangan_kompetensi`, `nilai_bor`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Dokter Spesialis Jantung', 0, 1654494, 120000, NULL, '2024-03-16 08:10:20', '2024-08-29 08:10:20'),
(2, 'Dokter Spesialis Penyakit Dalam', 0, 2533495, 120000, NULL, '2024-05-25 08:10:20', '2024-08-29 08:10:20'),
(3, 'Dokter Spesialis Anak', 0, 1676674, 120000, NULL, '2024-03-17 08:10:20', '2024-08-29 08:10:20'),
(4, 'Dokter Spesialis Bedah', 0, 543084, 120000, NULL, '2023-11-22 08:10:20', '2024-08-29 08:10:20'),
(5, 'Perawat', 1, 815886, 120000, NULL, '2024-06-22 08:10:20', '2024-08-29 08:10:20'),
(6, 'Bidan', 0, 2055918, 120000, NULL, '2023-12-20 08:10:20', '2024-08-29 08:10:20'),
(7, 'Ahli Gizi', 1, 2551117, 120000, NULL, '2023-11-14 08:10:20', '2024-08-29 08:10:20'),
(8, 'Tenaga Farmasi', 1, 1570488, 120000, NULL, '2024-02-27 08:10:20', '2024-08-29 08:10:20'),
(9, 'Petugas Laboratorium', 0, 2111467, 120000, NULL, '2024-07-19 08:10:20', '2024-08-29 08:10:20'),
(10, 'Ahli Radiologi', 0, 2256991, 120000, NULL, '2023-11-23 08:10:20', '2024-08-29 08:10:20'),
(11, 'Fisioterapis', 0, 2673527, 120000, NULL, '2024-05-26 08:10:20', '2024-08-29 08:10:20'),
(12, 'Petugas Administrasi', 0, 1280395, 120000, NULL, '2024-01-16 08:10:20', '2024-08-29 08:10:20'),
(13, 'Petugas Kebersihan', 0, 2042795, 120000, NULL, '2024-05-14 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `lemburs`
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
-- Struktur dari tabel `lokasi_kantors`
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
-- Dumping data untuk tabel `lokasi_kantors`
--

INSERT INTO `lokasi_kantors` (`id`, `alamat`, `lat`, `long`, `radius`, `created_at`, `updated_at`) VALUES
(1, 'Jl. Slamet Riyadi No.404, Purwosari, Kec. Laweyan, Kota Surakarta, Jawa Tengah 57142', '-6.9859222980560185', '110.418359041214', 100, '2024-08-29 08:10:20', '2024-08-31 22:41:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
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
-- Struktur dari tabel `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `model_has_roles`
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
(4, 'App\\Models\\User', 49),
(4, 'App\\Models\\User', 51),
(4, 'App\\Models\\User', 52),
(4, 'App\\Models\\User', 53),
(4, 'App\\Models\\User', 54),
(4, 'App\\Models\\User', 55);

-- --------------------------------------------------------

--
-- Struktur dari tabel `non_shifts`
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
-- Dumping data untuk tabel `non_shifts`
--

INSERT INTO `non_shifts` (`id`, `nama`, `jam_from`, `jam_to`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Jadwal Non Shift', '06:00:00', '17:30:00', NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasis`
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

--
-- Dumping data untuk tabel `notifikasis`
--

INSERT INTO `notifikasis` (`id`, `kategori_notifikasi_id`, `user_id`, `message`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 2, 2, 'Jadwal Anda berhasil ditukar dengan karyawan Mas Ndo pada tanggal 2 September 2024.', 0, '2024-08-31 03:22:44', '2024-08-31 03:22:44'),
(2, 2, 51, 'Jadwal Anda berhasil ditukar dengan karyawan User 0 pada tanggal 2 September 2024.', 0, '2024-08-31 03:22:44', '2024-08-31 03:22:44'),
(3, 3, 51, 'Mas Ndo, Anda mendapatkan pengajuan lembur dengan durasi 3 Jam 0 Menit.', 0, '2024-08-31 03:23:09', '2024-08-31 03:23:09'),
(4, 1, 51, 'Pengajuan cuti \'Cuti Sakit\' Anda pada tanggal \'31 Agustus 2024\' telah \'Disetujui\' tahap 1 oleh \'Super Admin\'.', 0, '2024-08-31 03:24:32', '2024-08-31 03:24:32'),
(5, 2, 2, 'Jadwal Anda berhasil ditukar dengan karyawan Mas Ndo pada tanggal 2 September 2024.', 0, '2024-08-31 03:32:03', '2024-08-31 03:32:03'),
(6, 2, 51, 'Jadwal Anda berhasil ditukar dengan karyawan User 0 pada tanggal 2 September 2024.', 0, '2024-08-31 03:32:03', '2024-08-31 03:32:03'),
(7, 3, 51, 'Mas Ndo, Anda mendapatkan pengajuan lembur dengan durasi 2 Jam 0 Menit.', 0, '2024-08-31 03:32:35', '2024-08-31 03:32:35'),
(8, 1, 51, 'Pengajuan cuti \'Cuti Sakit\' Anda pada tanggal \'31 Agustus 2024\' telah \'Disetujui\' tahap 1 oleh \'Super Admin\'.', 0, '2024-08-31 03:32:43', '2024-08-31 03:32:43'),
(9, 2, 2, 'Jadwal Anda berhasil ditukar dengan karyawan Mas Ndo pada tanggal 2 September 2024.', 0, '2024-08-31 04:07:13', '2024-08-31 04:07:13'),
(10, 2, 51, 'Jadwal Anda berhasil ditukar dengan karyawan User 0 pada tanggal 2 September 2024.', 0, '2024-08-31 04:07:13', '2024-08-31 04:07:13'),
(11, 3, 51, 'Mas Ndo, Anda mendapatkan pengajuan lembur dengan durasi 2 Jam 0 Menit.', 0, '2024-08-31 04:07:33', '2024-08-31 04:07:33'),
(12, 1, 51, 'Mas Ndo, anda mendapatkan cuti Cuti Sakit dengan durasi 4 hari yang dimulai pada 1 September 2024 s/d 4 September 2024.', 0, '2024-08-31 04:07:51', '2024-08-31 04:07:51'),
(13, 1, 51, 'Pengajuan cuti \'Cuti Sakit\' Anda pada tanggal \'31 Agustus 2024\' telah \'Disetujui\' tahap 1 oleh \'Super Admin\'.', 0, '2024-08-31 04:08:04', '2024-08-31 04:08:04'),
(14, 2, 2, 'Jadwal Anda berhasil ditukar dengan karyawan Mas Ndo pada tanggal 2 September 2024.', 0, '2024-08-31 04:17:42', '2024-08-31 04:17:42'),
(15, 2, 51, 'Jadwal Anda berhasil ditukar dengan karyawan User 0 pada tanggal 2 September 2024.', 0, '2024-08-31 04:17:42', '2024-08-31 04:17:42'),
(16, 3, 51, 'Mas Ndo, Anda mendapatkan pengajuan lembur dengan durasi 3 Jam 0 Menit.', 0, '2024-08-31 04:18:36', '2024-08-31 04:18:36'),
(17, 1, 51, 'Pengajuan cuti \'Cuti Sakit\' Anda pada tanggal \'31 Agustus 2024\' telah \'Disetujui\' tahap 1 oleh \'Super Admin\'.', 0, '2024-08-31 04:19:10', '2024-08-31 04:19:10'),
(18, 1, 51, 'Pengajuan cuti \'Cuti Sakit\' Anda pada tanggal \'31 Agustus 2024\' telah \'Disetujui\' tahap 2 oleh \'Super Admin\'.', 0, '2024-08-31 04:22:07', '2024-08-31 04:22:07'),
(19, 2, 16, 'Jadwal Anda berhasil ditukar dengan karyawan User 18 pada tanggal 2 September 2024.', 0, '2024-08-31 04:28:10', '2024-08-31 04:28:10'),
(20, 2, 20, 'Jadwal Anda berhasil ditukar dengan karyawan User 14 pada tanggal 2 September 2024.', 0, '2024-08-31 04:28:10', '2024-08-31 04:28:10'),
(21, 3, 16, 'User 14, Anda mendapatkan pengajuan lembur dengan durasi 4 Jam 0 Menit.', 0, '2024-08-31 04:28:30', '2024-08-31 04:28:30'),
(22, 1, 16, 'User 14, anda mendapatkan cuti Cuti Sakit dengan durasi 4 hari yang dimulai pada 1 September 2024 s/d 4 September 2024.', 0, '2024-08-31 04:29:00', '2024-08-31 04:29:00'),
(23, 1, 16, 'Pengajuan cuti \'Cuti Sakit\' Anda pada tanggal \'31 Agustus 2024\' telah \'Disetujui\' tahap 1 oleh \'Super Admin\'.', 0, '2024-08-31 04:29:11', '2024-08-31 04:29:11'),
(24, 2, 16, 'Jadwal Anda berhasil ditukar dengan karyawan User 18 pada tanggal 2 September 2024.', 0, '2024-08-31 04:44:46', '2024-08-31 04:44:46'),
(25, 2, 20, 'Jadwal Anda berhasil ditukar dengan karyawan User 14 pada tanggal 2 September 2024.', 0, '2024-08-31 04:44:46', '2024-08-31 04:44:46'),
(26, 3, 16, 'User 14, Anda mendapatkan pengajuan lembur dengan durasi 3 Jam 0 Menit.', 0, '2024-08-31 04:45:08', '2024-08-31 04:45:08'),
(27, 1, 16, 'Pengajuan cuti \'Cuti Sakit\' Anda pada tanggal \'31 Agustus 2024\' telah \'Disetujui\' tahap 1 oleh \'Super Admin\'.', 0, '2024-08-31 04:45:14', '2024-08-31 04:45:14'),
(28, 2, 2, 'Jadwal Anda berhasil ditukar dengan karyawan Mas Ndo pada tanggal 2 September 2024.', 0, '2024-08-31 04:48:54', '2024-08-31 04:48:54'),
(29, 2, 51, 'Jadwal Anda berhasil ditukar dengan karyawan User 0 pada tanggal 2 September 2024.', 0, '2024-08-31 04:48:54', '2024-08-31 04:48:54'),
(30, 1, 51, 'Mas Ndo, anda mendapatkan cuti Cuti Sakit dengan durasi 5 hari yang dimulai pada 1 September 2024 s/d 5 September 2024.', 0, '2024-08-31 04:49:32', '2024-08-31 04:49:32'),
(31, 1, 51, 'Pengajuan cuti \'Cuti Sakit\' Anda pada tanggal \'31 Agustus 2024\' telah \'Disetujui\' tahap 1 oleh \'Super Admin\'.', 0, '2024-08-31 04:49:38', '2024-08-31 04:49:38'),
(32, 2, 2, 'Jadwal Anda berhasil ditukar dengan karyawan Mas Ndo pada tanggal 2 September 2024.', 0, '2024-08-31 05:56:07', '2024-08-31 05:56:07'),
(33, 2, 51, 'Jadwal Anda berhasil ditukar dengan karyawan User 0 pada tanggal 2 September 2024.', 0, '2024-08-31 05:56:07', '2024-08-31 05:56:07'),
(34, 1, 2, 'User 0, anda mendapatkan cuti Cuti Sakit dengan durasi 5 hari yang dimulai pada 1 September 2024 s/d 5 September 2024.', 0, '2024-08-31 05:56:28', '2024-08-31 05:56:28'),
(35, 2, 2, 'Jadwal Anda berhasil ditukar dengan karyawan Mas Ndo pada tanggal 2 September 2024.', 0, '2024-08-31 07:39:19', '2024-08-31 07:39:19'),
(36, 2, 51, 'Jadwal Anda berhasil ditukar dengan karyawan User 0 pada tanggal 2 September 2024.', 0, '2024-08-31 07:39:19', '2024-08-31 07:39:19'),
(37, 1, 51, 'Mas Ndo, anda mendapatkan cuti Cuti Sakit dengan durasi 4 hari yang dimulai pada 1 September 2024 s/d 4 September 2024.', 0, '2024-08-31 07:39:55', '2024-08-31 07:39:55'),
(38, 1, 4, 'User 2, anda mendapatkan cuti Cuti Sakit dengan durasi 2 hari yang dimulai pada 2 September 2024 s/d 3 September 2024.', 0, '2024-08-31 07:51:49', '2024-08-31 07:51:49'),
(39, 2, 2, 'Jadwal Anda berhasil ditukar dengan karyawan Mas Ndo pada tanggal 2 September 2024.', 0, '2024-08-31 07:54:42', '2024-08-31 07:54:42'),
(40, 2, 51, 'Jadwal Anda berhasil ditukar dengan karyawan User 0 pada tanggal 2 September 2024.', 0, '2024-08-31 07:54:42', '2024-08-31 07:54:42'),
(41, 1, 51, 'Mas Ndo, anda mendapatkan cuti Cuti Sakit dengan durasi 5 hari yang dimulai pada 1 September 2024 s/d 5 September 2024.', 0, '2024-08-31 07:55:15', '2024-08-31 07:55:15'),
(42, 2, 2, 'Jadwal Anda berhasil ditukar dengan karyawan Mas Ndo pada tanggal 2 September 2024.', 0, '2024-08-31 14:59:11', '2024-08-31 14:59:11'),
(43, 2, 51, 'Jadwal Anda berhasil ditukar dengan karyawan User 0 pada tanggal 2 September 2024.', 0, '2024-08-31 14:59:11', '2024-08-31 14:59:11'),
(44, 1, 51, 'Mas Ndo, anda mendapatkan cuti Cuti Sakit dengan durasi 4 hari yang dimulai pada 2 September 2024 s/d 5 September 2024.', 0, '2024-08-31 15:00:03', '2024-08-31 15:00:03'),
(45, 2, 2, 'Jadwal Anda berhasil ditukar dengan karyawan Mas Ndo pada tanggal 2 September 2024.', 0, '2024-08-31 15:04:46', '2024-08-31 15:04:46'),
(46, 2, 51, 'Jadwal Anda berhasil ditukar dengan karyawan User 0 pada tanggal 2 September 2024.', 0, '2024-08-31 15:04:46', '2024-08-31 15:04:46'),
(47, 1, 51, 'Mas Ndo, anda mendapatkan cuti Cuti Sakit dengan durasi 4 hari yang dimulai pada 2 September 2024 s/d 5 September 2024.', 0, '2024-08-31 15:06:45', '2024-08-31 15:06:45'),
(48, 2, 2, 'Jadwal Anda berhasil ditukar dengan karyawan Mas Ndo pada tanggal 3 September 2024.', 0, '2024-08-31 15:08:23', '2024-08-31 15:08:23'),
(49, 2, 51, 'Jadwal Anda berhasil ditukar dengan karyawan User 0 pada tanggal 3 September 2024.', 0, '2024-08-31 15:08:23', '2024-08-31 15:08:23'),
(50, 1, 51, 'Mas Ndo, anda mendapatkan cuti Cuti Sakit dengan durasi 4 hari yang dimulai pada 2 September 2024 s/d 5 September 2024.', 0, '2024-08-31 15:08:58', '2024-08-31 15:08:58'),
(51, 2, 2, 'Jadwal Anda berhasil ditukar dengan karyawan Mas Ndo pada tanggal 3 September 2024.', 0, '2024-08-31 15:10:45', '2024-08-31 15:10:45'),
(52, 2, 51, 'Jadwal Anda berhasil ditukar dengan karyawan User 0 pada tanggal 3 September 2024.', 0, '2024-08-31 15:10:45', '2024-08-31 15:10:45'),
(53, 1, 51, 'Mas Ndo, anda mendapatkan cuti Cuti Sakit dengan durasi 4 hari yang dimulai pada 2 September 2024 s/d 5 September 2024.', 0, '2024-08-31 15:12:02', '2024-08-31 15:12:02'),
(54, 2, 2, 'Jadwal Anda berhasil ditukar dengan karyawan Mas Ndo pada tanggal 3 September 2024.', 0, '2024-08-31 15:13:40', '2024-08-31 15:13:40'),
(55, 2, 51, 'Jadwal Anda berhasil ditukar dengan karyawan User 0 pada tanggal 3 September 2024.', 0, '2024-08-31 15:13:40', '2024-08-31 15:13:40'),
(56, 1, 51, 'Mas Ndo, anda mendapatkan cuti Cuti Sakit dengan durasi 4 hari yang dimulai pada 2 September 2024 s/d 5 September 2024.', 0, '2024-08-31 15:13:59', '2024-08-31 15:13:59'),
(57, 2, 2, 'Jadwal Anda berhasil ditukar dengan karyawan Mas Ndo pada tanggal 3 September 2024.', 0, '2024-08-31 15:15:12', '2024-08-31 15:15:12'),
(58, 2, 51, 'Jadwal Anda berhasil ditukar dengan karyawan User 0 pada tanggal 3 September 2024.', 0, '2024-08-31 15:15:12', '2024-08-31 15:15:12'),
(59, 1, 51, 'Mas Ndo, anda mendapatkan cuti Cuti Sakit dengan durasi 4 hari yang dimulai pada 2 September 2024 s/d 5 September 2024.', 0, '2024-08-31 15:15:48', '2024-08-31 15:15:48'),
(60, 3, 16, 'User 14, Anda mendapatkan pengajuan lembur dengan durasi 1 Jam 0 Menit.', 0, '2024-08-31 19:50:22', '2024-08-31 19:50:22'),
(61, 2, 16, 'Jadwal Anda berhasil ditukar dengan karyawan User 18 pada tanggal 2 September 2024.', 0, '2024-08-31 20:04:05', '2024-08-31 20:04:05'),
(62, 2, 20, 'Jadwal Anda berhasil ditukar dengan karyawan User 14 pada tanggal 2 September 2024.', 0, '2024-08-31 20:04:05', '2024-08-31 20:04:05'),
(63, 3, 16, 'Verifikasi tahap 2 untuk pengajuan tukar jadwal Anda telah disetujui.', 0, '2024-08-31 20:22:26', '2024-08-31 20:22:26'),
(64, 3, 20, 'Verifikasi tahap 2 untuk pengajuan tukar jadwal dari User 14 telah disetujui.', 0, '2024-08-31 20:22:26', '2024-08-31 20:22:26'),
(65, 2, 33, 'Jadwal Anda berhasil ditukar dengan karyawan User 36 pada tanggal 2 September 2024.', 0, '2024-08-31 20:27:23', '2024-08-31 20:27:23'),
(66, 2, 38, 'Jadwal Anda berhasil ditukar dengan karyawan User 31 pada tanggal 2 September 2024.', 0, '2024-08-31 20:27:23', '2024-08-31 20:27:23'),
(67, 3, 38, 'User 36, Anda mendapatkan pengajuan lembur dengan durasi 0 Jam 1 Menit.', 0, '2024-08-31 20:28:37', '2024-08-31 20:28:37'),
(68, 1, 38, 'User 36, anda mendapatkan cuti Cuti Sakit dengan durasi 2 hari yang dimulai pada 2 September 2024 s/d 3 September 2024.', 0, '2024-08-31 20:31:32', '2024-08-31 20:31:32'),
(69, 1, 38, 'Pengajuan cuti \'Cuti Sakit\' Anda pada tanggal \'1 September 2024\' telah \'Disetujui\' tahap 1 oleh \'Sulenq Wazawsky\'.', 0, '2024-08-31 20:32:01', '2024-08-31 20:32:01'),
(70, 1, 38, 'Pengajuan cuti \'Cuti Sakit\' Anda pada tanggal \'1 September 2024\' telah \'Disetujui\' tahap 2 oleh \'Sulenq Wazawsky\'.', 0, '2024-08-31 20:32:50', '2024-08-31 20:32:50'),
(71, 3, 50, 'Sulenq Wazawsky, Anda mendapatkan pengajuan lembur dengan durasi 0 Jam 1 Menit.', 0, '2024-08-31 20:34:18', '2024-08-31 20:34:18'),
(72, 2, 50, 'Jadwal Anda berhasil ditukar dengan karyawan User 14 pada tanggal 2 September 2024.', 0, '2024-08-31 20:38:21', '2024-08-31 20:38:21'),
(73, 2, 16, 'Jadwal Anda berhasil ditukar dengan karyawan Sulenq Wazawsky pada tanggal 2 September 2024.', 0, '2024-08-31 20:38:21', '2024-08-31 20:38:21'),
(74, 3, 16, 'User 14, Anda mendapatkan pengajuan lembur dengan durasi 2 Jam 0 Menit.', 0, '2024-08-31 21:23:44', '2024-08-31 21:23:44'),
(75, 2, 16, 'Jadwal Anda berhasil ditukar dengan karyawan User 18 pada tanggal 4 September 2024.', 0, '2024-08-31 21:24:11', '2024-08-31 21:24:11'),
(76, 2, 20, 'Jadwal Anda berhasil ditukar dengan karyawan User 14 pada tanggal 4 September 2024.', 0, '2024-08-31 21:24:11', '2024-08-31 21:24:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelaporans`
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

--
-- Dumping data untuk tabel `pelaporans`
--

INSERT INTO `pelaporans` (`id`, `pelapor`, `pelaku`, `tgl_kejadian`, `lokasi`, `kronologi`, `upload_foto`, `created_at`, `updated_at`) VALUES
(1, 43, 25, '2024-08-19 15:10:35', 'Lokasi Kejadian 1', 'Kronologi kejadian pelaporan 1 yang berisi detail kejadian dan saksi-saksi.', 76, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(2, 24, 5, '2024-08-14 15:10:35', 'Lokasi Kejadian 2', 'Kronologi kejadian pelaporan 2 yang berisi detail kejadian dan saksi-saksi.', 77, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(3, 6, 45, '2024-08-05 15:10:35', 'Lokasi Kejadian 3', 'Kronologi kejadian pelaporan 3 yang berisi detail kejadian dan saksi-saksi.', 78, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(4, 19, 1, '2024-08-06 15:10:35', 'Lokasi Kejadian 4', 'Kronologi kejadian pelaporan 4 yang berisi detail kejadian dan saksi-saksi.', 79, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(5, 42, 25, '2024-08-02 15:10:35', 'Lokasi Kejadian 5', 'Kronologi kejadian pelaporan 5 yang berisi detail kejadian dan saksi-saksi.', 80, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(6, 46, 15, '2024-07-30 15:10:35', 'Lokasi Kejadian 6', 'Kronologi kejadian pelaporan 6 yang berisi detail kejadian dan saksi-saksi.', 81, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(7, 28, 51, '2024-08-08 15:10:35', 'Lokasi Kejadian 7', 'Kronologi kejadian pelaporan 7 yang berisi detail kejadian dan saksi-saksi.', 82, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(8, 6, 44, '2024-08-21 15:10:35', 'Lokasi Kejadian 8', 'Kronologi kejadian pelaporan 8 yang berisi detail kejadian dan saksi-saksi.', 83, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(9, 21, 8, '2024-08-09 15:10:35', 'Lokasi Kejadian 9', 'Kronologi kejadian pelaporan 9 yang berisi detail kejadian dan saksi-saksi.', 84, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(10, 21, 35, '2024-08-09 15:10:35', 'Lokasi Kejadian 10', 'Kronologi kejadian pelaporan 10 yang berisi detail kejadian dan saksi-saksi.', 85, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(11, 6, 10, '2024-08-24 15:10:35', 'Lokasi Kejadian 11', 'Kronologi kejadian pelaporan 11 yang berisi detail kejadian dan saksi-saksi.', 86, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(12, 24, 31, '2024-08-14 15:10:35', 'Lokasi Kejadian 12', 'Kronologi kejadian pelaporan 12 yang berisi detail kejadian dan saksi-saksi.', 87, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(13, 11, 10, '2024-08-25 15:10:35', 'Lokasi Kejadian 13', 'Kronologi kejadian pelaporan 13 yang berisi detail kejadian dan saksi-saksi.', 88, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(14, 44, 31, '2024-08-09 15:10:35', 'Lokasi Kejadian 14', 'Kronologi kejadian pelaporan 14 yang berisi detail kejadian dan saksi-saksi.', 89, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(15, 15, 7, '2024-08-05 15:10:35', 'Lokasi Kejadian 15', 'Kronologi kejadian pelaporan 15 yang berisi detail kejadian dan saksi-saksi.', 90, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(16, 11, 19, '2024-08-28 15:10:35', 'Lokasi Kejadian 16', 'Kronologi kejadian pelaporan 16 yang berisi detail kejadian dan saksi-saksi.', 91, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(17, 15, 45, '2024-08-11 15:10:35', 'Lokasi Kejadian 17', 'Kronologi kejadian pelaporan 17 yang berisi detail kejadian dan saksi-saksi.', 92, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(18, 9, 21, '2024-08-15 15:10:35', 'Lokasi Kejadian 18', 'Kronologi kejadian pelaporan 18 yang berisi detail kejadian dan saksi-saksi.', 93, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(19, 42, 34, '2024-08-19 15:10:35', 'Lokasi Kejadian 19', 'Kronologi kejadian pelaporan 19 yang berisi detail kejadian dan saksi-saksi.', 94, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(20, 24, 2, '2024-08-08 15:10:35', 'Lokasi Kejadian 20', 'Kronologi kejadian pelaporan 20 yang berisi detail kejadian dan saksi-saksi.', 95, '2024-08-29 08:10:35', '2024-08-29 08:10:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penggajians`
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

--
-- Dumping data untuk tabel `penggajians`
--

INSERT INTO `penggajians` (`id`, `riwayat_penggajian_id`, `data_karyawan_id`, `tgl_penggajian`, `gaji_pokok`, `total_tunjangan`, `reward`, `gaji_bruto`, `total_premi`, `pph_21`, `take_home_pay`, `status_gaji_id`, `created_at`, `updated_at`) VALUES
(1, 1, 4, '2024-09-01 01:32:33', 5824907, 3983761, 420000, 10239840, 0, 230397, 10009443, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(2, 1, 9, '2024-09-01 01:32:33', 5597658, 3487173, 420000, 9516663, 0, 142750, 9373913, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(3, 1, 10, '2024-09-01 01:32:33', 8633546, 5754311, 420000, 14809662, 0, 740484, 14069178, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(4, 1, 13, '2024-09-01 01:32:33', 8618140, 4107714, 420000, 13214121, 0, 660707, 12553414, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(5, 1, 19, '2024-09-01 01:32:33', 8004405, 3561863, 420000, 12036551, 0, 361097, 11675454, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(6, 1, 22, '2024-09-01 01:32:33', 6598400, 6742766, 420000, 13796596, 0, 689830, 13106766, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(7, 1, 25, '2024-09-01 01:32:33', 9941121, 4575510, 420000, 14963759, 0, 897826, 14065933, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(8, 1, 29, '2024-09-01 01:32:33', 5726342, 4600803, 420000, 10782607, 0, 215653, 10566954, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(9, 1, 31, '2024-09-01 01:32:33', 9731618, 7813982, 420000, 18024693, 0, 1441976, 16582717, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(10, 1, 35, '2024-09-01 01:32:33', 9983220, 4791115, 420000, 15231134, 0, 913869, 14317265, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(11, 1, 39, '2024-09-01 01:32:33', 5180527, 6618947, 420000, 12231225, 0, 489249, 11741976, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(12, 1, 41, '2024-09-01 01:32:33', 9587648, 8344085, 420000, 18400712, 0, 1288050, 17112662, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(13, 1, 45, '2024-09-01 01:32:33', 5821227, 5868762, 420000, 12119393, 0, 363582, 11755811, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(14, 1, 46, '2024-09-01 01:32:33', 9427437, 3172565, 420000, 13048387, 0, 521936, 12526451, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(15, 1, 47, '2024-09-01 01:32:33', 8699894, 6355285, 420000, 15485035, 0, 1083953, 14401082, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(16, 1, 48, '2024-09-01 01:32:33', 6580013, 4497750, 420000, 11559915, 0, 288998, 11270917, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(17, 1, 5, '2024-09-01 01:32:33', 8004405, 4437586, 420000, 12909111, 0, 645456, 12263655, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(18, 1, 6, '2024-09-01 01:32:33', 6404119, 5856266, 420000, 12741840, 0, 509674, 12232166, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(19, 1, 7, '2024-09-01 01:32:33', 5594718, 3657621, 420000, 9675223, 0, 120941, 9554282, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(20, 1, 8, '2024-09-01 01:32:33', 9281637, 4830521, 420000, 14553032, 0, 727652, 13825380, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(21, 1, 17, '2024-09-01 01:32:33', 5594718, 5191690, 420000, 11232273, 0, 224646, 11007627, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(22, 1, 18, '2024-09-01 01:32:33', 5594718, 5534661, 420000, 11606630, 0, 232133, 11374497, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(23, 1, 20, '2024-09-01 01:32:33', 9281637, 7110471, 420000, 16850910, 0, 1179564, 15671346, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(24, 1, 30, '2024-09-01 01:32:33', 5726342, 4538763, 420000, 10697986, 0, 267450, 10430536, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(25, 1, 32, '2024-09-01 01:32:33', 8699894, 2730156, 420000, 11915348, 0, 357461, 11557887, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(26, 1, 33, '2024-09-01 01:32:33', 5180527, 5766556, 420000, 11396475, 0, 398877, 10997598, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(27, 1, 38, '2024-09-01 01:32:33', 8379663, 5383028, 120000, 13893415, 0, 694671, 13198744, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(28, 1, 44, '2024-09-01 01:32:33', 5594718, 3901944, 420000, 9942853, 0, 149143, 9793710, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(29, 1, 55, '2024-09-01 01:32:33', 6580013, 7998351, 420000, 15076364, 282000, 904582, 13889782, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(30, 1, 2, '2024-09-01 01:32:33', 9587648, 7667000, 420000, 17737170, 0, 1241602, 16495568, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(31, 1, 3, '2024-09-01 01:32:33', 5597658, 5353550, 420000, 11423663, 0, 399829, 11023834, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(32, 1, 11, '2024-09-01 01:32:33', 5594718, 5755721, 420000, 11826717, 0, 473069, 11353648, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33'),
(33, 1, 12, '2024-09-01 01:32:34', 5195340, 5220798, 120000, 10599322, 0, 264984, 10334338, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(34, 1, 14, '2024-09-01 01:32:34', 8379663, 6964869, 420000, 15810095, 0, 948606, 14861489, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(35, 1, 15, '2024-09-01 01:32:34', 5824907, 6969200, 420000, 13241976, 0, 529680, 12712296, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(36, 1, 16, '2024-09-01 01:32:34', 8633546, 3991466, 340938, 12983022, 0, 519321, 12463701, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(37, 1, 21, '2024-09-01 01:32:34', 7002145, 6023126, 420000, 13512526, 0, 540502, 12972024, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(38, 1, 23, '2024-09-01 01:32:34', 9731618, 5798578, 420000, 16011068, 0, 1120775, 14890293, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(39, 1, 24, '2024-09-01 01:32:34', 9446166, 5666832, 420000, 15592192, 0, 935532, 14656660, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(40, 1, 26, '2024-09-01 01:32:34', 5594718, 6464690, 420000, 12487947, 0, 374639, 12113308, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(41, 1, 27, '2024-09-01 01:32:34', 5824907, 7247011, 420000, 13534065, 0, 676704, 12857361, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(42, 1, 28, '2024-09-01 01:32:34', 5594718, 4229683, 420000, 10280146, 0, 154203, 10125943, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(43, 1, 34, '2024-09-01 01:32:34', 8004405, 5743783, 420000, 14172757, 0, 850366, 13322391, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(44, 1, 36, '2024-09-01 01:32:34', 9446166, 5896713, 420000, 15813163, 0, 1106922, 14706241, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(45, 1, 37, '2024-09-01 01:32:34', 8618140, 5383343, 420000, 14448634, 0, 722432, 13726202, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(46, 1, 40, '2024-09-01 01:32:34', 9281637, 4804606, 420000, 14553801, 0, 727691, 13826110, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(47, 1, 42, '2024-09-01 01:32:34', 9281637, 7571011, 420000, 17297840, 0, 1383828, 15914012, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(48, 1, 43, '2024-09-01 01:32:34', 8699894, 6588499, 420000, 15708951, 0, 942538, 14766413, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(49, 1, 49, '2024-09-01 01:32:34', 5726342, 6433173, 420000, 12618199, 0, 378546, 12239653, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(50, 1, 50, '2024-09-01 01:32:34', 5824907, 7107359, 420312, 13400342, 340249, 670018, 12390075, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34'),
(51, 1, 51, '2024-09-01 01:32:34', 8004405, 3686010, 120000, 11856238, 0, 474250, 11381988, 1, '2024-09-01 01:32:34', '2024-09-01 01:32:34');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengumumans`
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
-- Dumping data untuk tabel `pengumumans`
--

INSERT INTO `pengumumans` (`id`, `judul`, `konten`, `is_read`, `tgl_berakhir`, `created_at`, `updated_at`) VALUES
(1, 'Pengumuman 1', 'Konten pengumuman 1', 0, '2024-08-30', '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
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
-- Struktur dari tabel `pengurang_gajis`
--

CREATE TABLE `pengurang_gajis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `data_karyawan_id` bigint(20) UNSIGNED NOT NULL,
  `premi_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pengurang_gajis`
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
(14, 55, 1, '2024-08-31 22:04:43', '2024-08-31 22:04:43'),
(15, 55, 2, '2024-08-31 22:04:43', '2024-08-31 22:04:43'),
(16, 55, 3, '2024-08-31 22:04:43', '2024-08-31 22:04:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penilaians`
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

--
-- Dumping data untuk tabel `penilaians`
--

INSERT INTO `penilaians` (`id`, `user_dinilai`, `user_penilai`, `jenis_penilaian_id`, `pertanyaan_jawaban`, `total_pertanyaan`, `rata_rata`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, '[{\"pertanyaan\":\"Bagaimana kinerja harian?\",\"jawaban\":4},{\"pertanyaan\":\"Apakah karyawan berinisiatif?\",\"jawaban\":5}]', 2, 3, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(2, 3, 4, 2, '[{\"pertanyaan\":\"Bagaimana sikap kerja?\",\"jawaban\":3},{\"pertanyaan\":\"Apakah karyawan bekerja sama dengan tim?\",\"jawaban\":4}]', 2, 5, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(3, 5, 6, 3, '[{\"pertanyaan\":\"Bagaimana kehadiran kerja?\",\"jawaban\":5},{\"pertanyaan\":\"Apakah karyawan menyelesaikan tugas tepat waktu?\",\"jawaban\":4}]', 2, 4, '2024-08-29 08:10:35', '2024-08-29 08:10:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penyesuaian_gajis`
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
-- Struktur dari tabel `personal_access_tokens`
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
-- Dumping data untuk tabel `personal_access_tokens`
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
(59, 'App\\Models\\User', 1, 'create_token_07149cdc-e269-4c4c-8611-b476ae8357d6', '44e63416c70e5e80461123a1ceedca4736a83729ce548fb943d7f302a1379b2a', '[\"*\"]', '2024-08-31 08:08:20', NULL, '2024-08-31 07:46:20', '2024-08-31 08:08:20'),
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
(94, 'App\\Models\\User', 1, 'create_token_2c26a5cc-b364-4432-9a10-385d1c84c8a5', 'fe31fba17a7720bf5f66aa5673d66e787b7d02d91cc4c61623d85f88edcc402b', '[\"*\"]', '2024-09-01 20:24:17', NULL, '2024-09-01 20:15:44', '2024-09-01 20:24:17'),
(95, 'App\\Models\\User', 42, 'TLogin', 'd63589cb67b7ef693ffef76791b617840d79a4775e649a1a1a92961996f2aad0', '[\"*\"]', '2024-09-02 03:27:55', NULL, '2024-09-02 03:27:53', '2024-09-02 03:27:55'),
(98, 'App\\Models\\User', 1, 'create_token_932f20a3-1283-4a84-bbb4-80583a46c75f', 'bc7c50ff765203ff6611f5a158e6f950dd6311b8fbfee9660782be0c63ad9010', '[\"*\"]', '2024-09-01 20:51:02', NULL, '2024-09-01 20:50:12', '2024-09-01 20:51:02'),
(101, 'App\\Models\\User', 1, 'create_token_17032247-6b07-4b1c-bddd-d048e914d4c8', '6a882bdea0c7ec5a31c76baccc49685fcb601cdac09720b1906c8f8ac530c1b6', '[\"*\"]', '2024-09-01 21:18:54', NULL, '2024-09-01 21:16:33', '2024-09-01 21:18:54'),
(102, 'App\\Models\\User', 1, 'create_token_185b33d8-3f93-4dfe-8ccb-bb37aef65ddd', '68d439a318a45b1f1a0fb1bb15c2c96661970940ad65e40c6b621de8c7684800', '[\"*\"]', '2024-09-01 21:24:08', NULL, '2024-09-01 21:17:05', '2024-09-01 21:24:08'),
(103, 'App\\Models\\User', 1, 'create_token_de4cb057-ffd0-4d0a-aa8b-f3492623c610', '23f58ca75c9669c16bc5ac26a764048b282d7947b1a132fc68d301a29a42bfc8', '[\"*\"]', '2024-09-01 22:41:37', NULL, '2024-09-01 21:17:36', '2024-09-01 22:41:37'),
(104, 'App\\Models\\User', 1, 'create_token_1901b0d8-cd97-4c77-b707-b10f01a59281', '930ad65f1de74c0582236500146df95e8670f61818f039004469547531ddc527', '[\"*\"]', '2024-09-01 21:39:20', NULL, '2024-09-01 21:32:00', '2024-09-01 21:39:20'),
(105, 'App\\Models\\User', 50, 'TLogin', 'b26302a2bf08f111f1970ee8c41b1458687fa203eca9ebb52c7ae96a1c3cc936', '[\"*\"]', '2024-09-02 09:59:58', NULL, '2024-09-02 09:55:08', '2024-09-02 09:59:58');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pertanyaans`
--

CREATE TABLE `pertanyaans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pertanyaan` varchar(255) NOT NULL,
  `jenis_penilaian_id` bigint(20) UNSIGNED NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pertanyaans`
--

INSERT INTO `pertanyaans` (`id`, `pertanyaan`, `jenis_penilaian_id`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Bagaimana kualitas kerja karyawan ini?', 1, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(2, 'Seberapa baik karyawan ini dalam bekerja sama dengan tim?', 1, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(3, 'Apakah karyawan ini menunjukkan inisiatif dalam pekerjaannya?', 1, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(4, 'Seberapa baik karyawan ini dalam menyelesaikan tugas tepat waktu?', 1, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(5, 'Bagaimana karyawan ini menangani tekanan kerja?', 1, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(6, 'Apakah karyawan ini menunjukkan kemampuan komunikasi yang baik?', 1, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(7, 'Seberapa baik karyawan ini dalam mengikuti instruksi dan prosedur?', 1, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(8, 'Bagaimana karyawan ini beradaptasi dengan perubahan di tempat kerja?', 1, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(9, 'Apakah karyawan ini menunjukkan sikap yang positif di tempat kerja?', 1, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(10, 'Seberapa baik karyawan ini dalam belajar hal-hal baru?', 1, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(11, 'Bagaimana kualitas kerja karyawan ini?', 2, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(12, 'Seberapa baik karyawan ini dalam bekerja sama dengan tim?', 2, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(13, 'Apakah karyawan ini menunjukkan inisiatif dalam pekerjaannya?', 2, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(14, 'Seberapa baik karyawan ini dalam menyelesaikan tugas tepat waktu?', 2, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(15, 'Bagaimana karyawan ini menangani tekanan kerja?', 2, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(16, 'Apakah karyawan ini menunjukkan kemampuan komunikasi yang baik?', 2, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(17, 'Seberapa baik karyawan ini dalam mengikuti instruksi dan prosedur?', 2, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(18, 'Bagaimana karyawan ini beradaptasi dengan perubahan di tempat kerja?', 2, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(19, 'Apakah karyawan ini menunjukkan sikap yang positif di tempat kerja?', 2, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(20, 'Seberapa baik karyawan ini dalam belajar hal-hal baru?', 2, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(21, 'Bagaimana kualitas kerja karyawan ini?', 3, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(22, 'Seberapa baik karyawan ini dalam bekerja sama dengan tim?', 3, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(23, 'Apakah karyawan ini menunjukkan inisiatif dalam pekerjaannya?', 3, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(24, 'Seberapa baik karyawan ini dalam menyelesaikan tugas tepat waktu?', 3, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(25, 'Bagaimana karyawan ini menangani tekanan kerja?', 3, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(26, 'Apakah karyawan ini menunjukkan kemampuan komunikasi yang baik?', 3, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(27, 'Seberapa baik karyawan ini dalam mengikuti instruksi dan prosedur?', 3, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(28, 'Bagaimana karyawan ini beradaptasi dengan perubahan di tempat kerja?', 3, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(29, 'Apakah karyawan ini menunjukkan sikap yang positif di tempat kerja?', 3, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(30, 'Seberapa baik karyawan ini dalam belajar hal-hal baru?', 3, NULL, '2024-08-29 08:10:35', '2024-08-29 08:10:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `perubahan_berkas`
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
-- Struktur dari tabel `perubahan_keluargas`
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
-- Struktur dari tabel `perubahan_personals`
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
-- Struktur dari tabel `peserta_diklats`
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
-- Struktur dari tabel `premis`
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
-- Dumping data untuk tabel `premis`
--

INSERT INTO `premis` (`id`, `nama_premi`, `kategori_potongan_id`, `jenis_premi`, `besaran_premi`, `minimal_rate`, `maksimal_rate`, `has_custom_formula`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'BPJS Kesehatan', 1, 0, 1, NULL, 12000000, 1, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'BPJS Ketenagakerjaan', 2, 1, 12000, 520000, 700000, 1, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Iuran Pensiun', 2, 1, 150000, NULL, NULL, 0, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Jaminan Hari Tua', 2, 0, 1, NULL, NULL, 0, NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `presensis`
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

--
-- Dumping data untuk tabel `presensis`
--

INSERT INTO `presensis` (`id`, `user_id`, `data_karyawan_id`, `jadwal_id`, `jam_masuk`, `jam_keluar`, `durasi`, `lat`, `long`, `latkeluar`, `longkeluar`, `foto_masuk`, `foto_keluar`, `kategori_presensi_id`, `note`, `created_at`, `updated_at`) VALUES
(1, 55, 55, 10, '2024-09-01 12:41:31', NULL, NULL, '-6.9858976', '110.4180278', NULL, NULL, 110, NULL, 2, NULL, '2024-09-01 05:41:31', '2024-09-01 05:41:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ptkps`
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
-- Dumping data untuk tabel `ptkps`
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
-- Struktur dari tabel `reward_bulan_lalus`
--

CREATE TABLE `reward_bulan_lalus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `data_karyawan_id` bigint(20) UNSIGNED NOT NULL,
  `status_reward` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `reward_bulan_lalus`
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
-- Struktur dari tabel `riwayat_izins`
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
-- Struktur dari tabel `riwayat_penggajians`
--

CREATE TABLE `riwayat_penggajians` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `periode` date NOT NULL,
  `karyawan_verifikasi` int(11) NOT NULL,
  `jenis_riwayat` tinyint(1) NOT NULL,
  `status_gaji_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `riwayat_penggajians`
--

INSERT INTO `riwayat_penggajians` (`id`, `periode`, `karyawan_verifikasi`, `jenis_riwayat`, `status_gaji_id`, `created_at`, `updated_at`) VALUES
(1, '2024-09-01', 51, 1, 1, '2024-09-01 01:32:33', '2024-09-01 01:32:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_perubahans`
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
-- Struktur dari tabel `roles`
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
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`, `deskripsi`, `deleted_at`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'entitas awal', NULL, 'web', '2024-05-12 08:10:20', '2024-08-30 01:35:46'),
(2, 'Personalia', 'untuk jabatan Personalia', NULL, 'web', '2024-05-12 08:10:20', '2024-08-30 00:55:42'),
(3, 'Kepala Ruang', 'untuk jabatan Kepala Ruang', NULL, 'web', '2024-05-12 08:10:20', '2024-08-29 08:53:40'),
(4, 'Karyawan', 'untuk Karyawan', NULL, 'web', '2024-05-12 08:10:20', '2024-08-30 01:35:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `role_has_permissions`
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
-- Struktur dari tabel `run_thrs`
--

CREATE TABLE `run_thrs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `data_karyawan_id` bigint(20) UNSIGNED NOT NULL,
  `tgl_run_thr` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `shifts`
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
-- Dumping data untuk tabel `shifts`
--

INSERT INTO `shifts` (`id`, `nama`, `jam_from`, `jam_to`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Pagi', '06:00:00', '16:00:00', NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Sore', '16:00:00', '23:00:00', NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Malam', '23:00:00', '06:00:00', NULL, '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `status_aktifs`
--

CREATE TABLE `status_aktifs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `status_aktifs`
--

INSERT INTO `status_aktifs` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Belum Aktif', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(2, 'Aktif', '2024-08-29 08:10:19', '2024-08-29 08:10:19'),
(3, 'Tidak Aktif', '2024-08-29 08:10:19', '2024-08-29 08:10:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `status_berkas`
--

CREATE TABLE `status_berkas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `status_berkas`
--

INSERT INTO `status_berkas` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Menunggu', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Diverifikasi', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `status_cutis`
--

CREATE TABLE `status_cutis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `status_cutis`
--

INSERT INTO `status_cutis` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Menunggu Verifikasi', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Verifikasi 1 Disetujui', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Verifikasi 1 Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Verifikasi 2 Disetujui', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(5, 'Verifikasi 2 Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `status_diklats`
--

CREATE TABLE `status_diklats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `status_diklats`
--

INSERT INTO `status_diklats` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Menunggu Verifikasi', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Verifikasi 1 Disetujui', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Verifikasi 1 Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Verifikasi 2 Disetujui', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(5, 'Verifikasi 2 Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `status_gajis`
--

CREATE TABLE `status_gajis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `status_gajis`
--

INSERT INTO `status_gajis` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Belum Dipublikasi', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Sudah Dipublikasi', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `status_karyawans`
--

CREATE TABLE `status_karyawans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `status_karyawans`
--

INSERT INTO `status_karyawans` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Tetap', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Kontrak', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Training', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `status_lemburs`
--

CREATE TABLE `status_lemburs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `status_lemburs`
--

INSERT INTO `status_lemburs` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Menunggu', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Berlangsung', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Selesai', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `status_perubahans`
--

CREATE TABLE `status_perubahans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `status_perubahans`
--

INSERT INTO `status_perubahans` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Menunggu', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Diverifikasi', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `status_presensis`
--

CREATE TABLE `status_presensis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `status_riwayat_izins`
--

CREATE TABLE `status_riwayat_izins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `status_riwayat_izins`
--

INSERT INTO `status_riwayat_izins` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Menunggu', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Disetujui', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `status_tukar_jadwals`
--

CREATE TABLE `status_tukar_jadwals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `status_tukar_jadwals`
--

INSERT INTO `status_tukar_jadwals` (`id`, `label`, `created_at`, `updated_at`) VALUES
(1, 'Menunggu Verifikasi', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(2, 'Verifikasi 1 Disetujui', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(3, 'Verifikasi 1 Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(4, 'Verifikasi 2 Disetujui', '2024-08-29 08:10:20', '2024-08-29 08:10:20'),
(5, 'Verifikasi 2 Ditolak', '2024-08-29 08:10:20', '2024-08-29 08:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ters`
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
-- Dumping data untuk tabel `ters`
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
-- Struktur dari tabel `thrs`
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
-- Struktur dari tabel `tipe_cutis`
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
-- Dumping data untuk tabel `tipe_cutis`
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
-- Struktur dari tabel `track_records`
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
-- Struktur dari tabel `transfer_karyawans`
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

--
-- Dumping data untuk tabel `transfer_karyawans`
--

INSERT INTO `transfer_karyawans` (`id`, `user_id`, `tgl_mulai`, `unit_kerja_asal`, `unit_kerja_tujuan`, `jabatan_asal`, `jabatan_tujuan`, `kelompok_gaji_asal`, `kelompok_gaji_tujuan`, `role_asal`, `role_tujuan`, `kategori_transfer_id`, `alasan`, `dokumen`, `is_processed`, `created_at`, `updated_at`) VALUES
(1, 2, '2024-02-09', 11, 20, 16, 10, 5, 18, 4, 2, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_2', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(2, 3, '2024-05-19', 1, 18, 6, 7, 21, 12, 1, 2, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_3', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(3, 4, '2024-08-26', 3, 15, 14, 12, 20, 12, 2, 4, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_4', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(4, 5, '2024-11-09', 17, 1, 16, 8, 15, 25, 2, 1, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_5', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(5, 6, '2024-12-25', 19, 18, 4, 7, 8, 20, 2, 3, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_6', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(6, 7, '2024-09-17', 21, 12, 5, 3, 19, 4, 4, 1, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_7', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(7, 8, '2024-09-20', 12, 9, 16, 9, 18, 23, 1, 3, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_8', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(8, 9, '2024-07-17', 21, 2, 11, 13, 9, 23, 3, 1, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_9', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(9, 10, '2024-10-27', 12, 1, 10, 11, 2, 3, 4, 2, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_10', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(10, 11, '2024-06-14', 20, 4, 10, 1, 10, 15, 1, 4, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_11', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(11, 12, '2024-02-11', 9, 12, 5, 17, 5, 15, 4, 1, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_12', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(12, 13, '2024-02-05', 1, 13, 13, 5, 3, 26, 1, 3, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_13', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(13, 14, '2024-12-07', 8, 17, 8, 16, 18, 8, 4, 2, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_14', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(14, 15, '2024-08-13', 16, 14, 12, 19, 4, 20, 3, 2, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_15', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(15, 16, '2024-04-02', 4, 3, 6, 9, 10, 15, 4, 1, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_16', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(16, 17, '2024-05-11', 19, 15, 3, 2, 23, 17, 4, 3, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_17', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(17, 18, '2024-09-28', 1, 5, 8, 4, 5, 17, 3, 2, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_18', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(18, 19, '2024-04-23', 19, 13, 1, 4, 21, 23, 1, 2, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_19', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(19, 20, '2024-01-31', 3, 15, 15, 12, 15, 24, 1, 2, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_20', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(20, 21, '2024-07-13', 1, 17, 11, 5, 15, 21, 4, 3, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_21', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(21, 22, '2024-03-20', 20, 14, 18, 1, 15, 5, 3, 2, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_22', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(22, 23, '2024-08-23', 17, 5, 9, 14, 24, 14, 1, 2, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_23', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(23, 24, '2024-04-11', 15, 2, 17, 10, 18, 22, 3, 1, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_24', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(24, 25, '2024-08-08', 10, 1, 9, 6, 11, 20, 3, 4, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_25', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(25, 26, '2024-04-15', 19, 2, 9, 18, 9, 16, 1, 4, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_26', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(26, 27, '2024-05-02', 19, 20, 6, 11, 18, 17, 1, 2, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_27', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(27, 28, '2024-10-25', 6, 2, 16, 12, 26, 15, 4, 2, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_28', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(28, 29, '2024-08-06', 1, 7, 6, 17, 24, 11, 4, 1, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_29', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(29, 30, '2024-04-20', 11, 20, 5, 1, 15, 26, 1, 4, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_30', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(30, 31, '2024-05-08', 13, 18, 6, 8, 24, 2, 1, 4, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_31', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(31, 32, '2024-06-18', 17, 13, 14, 8, 11, 16, 2, 1, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_32', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(32, 33, '2024-07-14', 8, 6, 11, 2, 7, 20, 2, 3, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_33', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(33, 34, '2024-09-09', 5, 14, 2, 5, 14, 24, 4, 3, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_34', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(34, 35, '2024-05-08', 15, 3, 1, 3, 24, 23, 3, 2, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_35', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(35, 36, '2024-06-13', 7, 2, 2, 13, 13, 2, 2, 1, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_36', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(36, 37, '2024-02-21', 8, 7, 8, 6, 22, 8, 3, 4, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_37', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(37, 38, '2024-12-15', 18, 1, 6, 8, 22, 11, 3, 2, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_38', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(38, 39, '2024-06-12', 21, 8, 8, 1, 11, 16, 1, 3, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_39', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(39, 40, '2024-07-08', 10, 9, 4, 12, 2, 20, 4, 1, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_40', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(40, 41, '2024-06-27', 18, 7, 6, 3, 24, 25, 4, 1, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_41', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(41, 42, '2024-03-13', 8, 18, 7, 16, 23, 1, 2, 1, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_42', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(42, 43, '2024-07-13', 19, 7, 16, 7, 17, 8, 1, 4, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_43', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(43, 44, '2024-09-28', 10, 9, 12, 8, 10, 26, 1, 4, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_44', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(44, 45, '2024-12-07', 7, 3, 17, 9, 26, 1, 4, 3, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_45', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(45, 46, '2024-06-20', 13, 1, 13, 7, 2, 5, 3, 4, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_46', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(46, 47, '2024-10-06', 3, 4, 1, 2, 3, 14, 2, 1, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_47', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(47, 48, '2024-06-17', 3, 7, 15, 5, 23, 15, 4, 2, 2, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_48', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(48, 49, '2024-05-17', 4, 12, 15, 5, 20, 2, 2, 1, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_49', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(49, 50, '2024-09-06', 10, 1, 15, 19, 7, 19, 1, 2, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_50', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35'),
(50, 51, '2024-06-14', 14, 6, 4, 7, 10, 4, 4, 2, 1, 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!', '/berkas/karyawan/karyawan-transfer/dokumen_51', 0, '2024-08-29 08:10:35', '2024-08-29 08:10:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tukar_jadwals`
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

--
-- Dumping data untuk tabel `tukar_jadwals`
--

INSERT INTO `tukar_jadwals` (`id`, `user_pengajuan`, `jadwal_pengajuan`, `user_ditukar`, `jadwal_ditukar`, `status_penukaran_id`, `kategori_penukaran_id`, `verifikator_1`, `verifikator_2`, `alasan`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 16, 3, 20, 4, 4, 1, NULL, 50, NULL, NULL, '2024-08-31 20:04:05', '2024-08-31 20:22:26'),
(3, 50, 7, 16, 4, 4, 1, NULL, NULL, NULL, NULL, '2024-08-31 20:38:21', '2024-08-31 20:38:21'),
(4, 16, 8, 20, 9, 4, 1, NULL, NULL, NULL, NULL, '2024-08-31 21:24:11', '2024-08-31 21:24:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `unit_kerjas`
--

CREATE TABLE `unit_kerjas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_unit` varchar(255) NOT NULL,
  `jenis_karyawan` tinyint(1) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `unit_kerjas`
--

INSERT INTO `unit_kerjas` (`id`, `nama_unit`, `jenis_karyawan`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Radiologi', 1, NULL, '2023-10-27 08:10:20', '2024-08-29 08:10:20'),
(2, 'Psikiatri', 1, NULL, '2024-06-09 08:10:20', '2024-08-29 08:10:20'),
(3, 'Pengembangan Mutu', 1, NULL, '2023-09-12 08:10:20', '2024-08-29 08:10:20'),
(4, 'Sumber Daya Manusia (SDM)', 1, NULL, '2023-11-20 08:10:20', '2024-08-29 08:10:20'),
(5, 'Hukum dan Kerjasama', 0, NULL, '2024-05-21 08:10:20', '2024-08-29 08:10:20'),
(6, 'Bedah', 1, NULL, '2024-03-29 08:10:20', '2024-08-29 08:10:20'),
(7, 'Asuransi Kesehatan', 1, NULL, '2024-02-13 08:10:20', '2024-08-29 08:10:20'),
(8, 'Gizi', 1, NULL, '2024-04-06 08:10:20', '2024-08-29 08:10:20'),
(9, 'Apotek', 1, NULL, '2024-02-25 08:10:20', '2024-08-29 08:10:20'),
(10, 'Rehabilitasi Medik', 1, NULL, '2024-07-25 08:10:20', '2024-08-29 08:10:20'),
(11, 'Teknologi Informasi dan Komunikasi (TIK)', 0, NULL, '2024-02-23 08:10:20', '2024-08-29 08:10:20'),
(12, 'Onkologi', 0, NULL, '2023-12-14 08:10:20', '2024-08-29 08:10:20'),
(13, 'Unit Gawat Darurat (UGD)', 0, NULL, '2023-10-31 08:10:20', '2024-08-29 08:10:20'),
(14, 'Kebidanan dan Kandungan', 1, NULL, '2023-12-08 08:10:20', '2024-08-29 08:10:20'),
(15, 'Telinga Hidung Tenggorokan (THT)', 0, NULL, '2024-06-16 08:10:20', '2024-08-29 08:10:20'),
(16, 'Badan Penyelenggara Jaminan Sosial (BPJS)', 0, NULL, '2023-10-03 08:10:20', '2024-08-29 08:10:20'),
(17, 'Penyakit Dalam', 0, NULL, '2024-08-13 08:10:20', '2024-08-29 08:10:20'),
(18, 'Laundry', 0, NULL, '2024-06-30 08:10:20', '2024-08-29 08:10:20'),
(19, 'Keamanan', 1, NULL, '2024-08-01 08:10:20', '2024-08-29 08:10:20'),
(20, 'Gigi dan Mulut', 1, NULL, '2023-12-28 08:10:20', '2024-08-29 08:10:20'),
(21, 'Kantin', 1, NULL, '2024-04-09 08:10:20', '2024-08-29 08:10:20'),
(22, 'Nakes', 1, NULL, '2024-08-29 20:05:39', '2024-08-29 20:05:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
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
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email_verified_at`, `password`, `role_id`, `data_karyawan_id`, `foto_profil`, `data_completion_step`, `status_aktif`, `remember_token`, `remember_token_expired_at`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', NULL, '$2y$12$oi0EMhrCJcKaBTUTTt6FRu9UUSoTd.thhHEBA1pTWjhI/03rWRUKu', NULL, NULL, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:20', '2024-08-29 17:26:58'),
(2, 'User 0', NULL, '$2y$12$FQePRCbtzdXwEZQjFHRA/eWz0wCX4bhGyEGQo6Gst5DJlXFm.ozWe', NULL, 2, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:21', '2024-08-29 08:10:21'),
(3, 'User 1', NULL, '$2y$12$gst3GC.TSGn5o2ctJSDsCOnAjurQTD0i1dt9h/di9ycvtbWNvrB7W', NULL, 3, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:21', '2024-08-29 08:10:21'),
(4, 'User 2', NULL, '$2y$12$wCszgfpS3lGVMyr6IkzYBOqgyo7xBIZTYWA22IVchccW148udJeoS', NULL, 4, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:21', '2024-08-29 08:10:21'),
(5, 'User 3', NULL, '$2y$12$DaObJOMet7rEhekt.1WjpOb6/OWlritHh/QfruU2cVyBjWpPqNX32', NULL, 5, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:22', '2024-08-29 08:10:22'),
(6, 'User 4', NULL, '$2y$12$c3LbBqeWZ1DFwUERl36MA.GW0IcChDhtJ9SzGpw/BYITze9cpa0YO', NULL, 6, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:22', '2024-08-29 08:10:22'),
(7, 'User 5', NULL, '$2y$12$IKnWxKMOXosl5rCBMzZgeeuXL0pCAK3xGDRzxVmZvY7t9XF2F.vFW', NULL, 7, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:22', '2024-08-29 08:10:22'),
(8, 'User 6', NULL, '$2y$12$A.Tu2pP5AEyQ/j.zMCYeA.L79ac5/U9cJ6O9i0HlRRRrFQw/PIiuy', NULL, 8, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:22', '2024-08-29 08:10:22'),
(9, 'User 7', NULL, '$2y$12$/8unhf3Bgwu6wz.4.Giz4OvOSRXI1mazh82QW2c8oQykninUHIzFC', NULL, 9, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:23', '2024-08-29 08:10:23'),
(10, 'User 8', NULL, '$2y$12$xzNFINggC8QsqhW0cqf70OP3MSqyudfwnyh4YmJ.1coe.cVORCNGO', NULL, 10, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:23', '2024-08-29 08:10:23'),
(11, 'User 9', NULL, '$2y$12$7TRcYl4RbvWwUInWSk0yC.WKoTIHyVAdXkuSrlonrQCg/C59skutS', NULL, 11, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:23', '2024-08-29 08:10:23'),
(12, 'User 10', NULL, '$2y$12$a3230NGJNExuz96CkB5aSOnR.mSVkmBPijzHiF.dWZjzdmTCzsg3u', NULL, 12, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:24', '2024-08-29 08:10:24'),
(13, 'User 11', NULL, '$2y$12$WV1DGCY1tt5NmbmXBhQENuMlYutLqIeMzrHV3XY9hYZ.YF66rLg3C', NULL, 13, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:24', '2024-08-29 08:10:24'),
(14, 'User 12', NULL, '$2y$12$Ype5r.ZwZoDJjyMY8F7kYurHaf2e7Vm6AMlVagIE7jdTWVSCnluEq', NULL, 14, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:24', '2024-08-29 08:10:24'),
(15, 'User 13', NULL, '$2y$12$1VwQOYM/GlDxTMb7xZYHvu6yeL2NyDvZN2z919a2sWEkb30HzsknO', NULL, 15, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:25', '2024-08-29 08:10:25'),
(16, 'User 14', NULL, '$2y$12$BCKq5/YrbhfBX36N9QOyzuMImgrme8R.QS5zfKIBEylXBcBk.V4be', NULL, 16, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:25', '2024-08-29 08:10:25'),
(17, 'User 15', NULL, '$2y$12$rsKMA1lM2Lvi1bE5wu.NHuYR33D8flJsl.jt328gBN2id2oJkso1y', NULL, 17, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:25', '2024-08-29 08:10:25'),
(18, 'User 16', NULL, '$2y$12$1ZcS812/V0tggbnh8lO1e.olkiIZQaHBfB8Jr0iD0LwZpKlTLgohm', NULL, 18, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:25', '2024-08-29 08:10:25'),
(19, 'User 17', NULL, '$2y$12$20cAmRGcbYF8EhxHbFlwvOw3E8kUVkhIM35OvO6JTNrDX4a.iE1JK', NULL, 19, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:26', '2024-08-29 08:10:26'),
(20, 'User 18', NULL, '$2y$12$sc32kwMchtPJNMYDGrA9D.lPEY3spn3g2H20LTlZQLqiLNyuvV2au', NULL, 20, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:26', '2024-08-29 08:10:26'),
(21, 'User 19', NULL, '$2y$12$O142h18BU5zEqoXyxaiH6OERlmJQktzrtyednlopK93TZsx2CQ90q', NULL, 21, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:26', '2024-08-29 08:10:26'),
(22, 'User 20', NULL, '$2y$12$7lKK2iClg00Dnwwny2X4oO/FfLSAFp.xHtLwzLvhFEtwrZrVAdu.a', NULL, 22, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:27', '2024-08-29 08:10:27'),
(23, 'User 21', NULL, '$2y$12$Q6Pv5BKGGK2n.Bt0Y1NUcebzsqKCqu8AIIhKGeGNnxY3Nw7FZkFE.', NULL, 23, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:27', '2024-08-29 08:10:27'),
(24, 'User 22', NULL, '$2y$12$W7xVuw9PPWBt3eaWX45f5uYIc3e7eluJJKcoL.8obnfGlwB/jOiBW', NULL, 24, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:27', '2024-08-29 08:10:27'),
(25, 'User 23', NULL, '$2y$12$sgYszcAQ03JG0Vcjn/LSiu1gpewCJy/0mL91DEouwC9posMrl1iCC', NULL, 25, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:27', '2024-08-29 08:10:27'),
(26, 'User 24', NULL, '$2y$12$f9tdSBWpktyDkjtU.C2tHOwDRVXPeYfg7NOBrGtLW69X/M9NwMbR2', NULL, 26, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:28', '2024-08-29 08:10:28'),
(27, 'User 25', NULL, '$2y$12$XsFUyw04iV1Tu2y.c0GCn.RC8Ghbru1oppTwpDZ9YG3nFuuP9XJoW', NULL, 27, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:28', '2024-08-29 08:10:28'),
(28, 'User 26', NULL, '$2y$12$sxdKuIY9KsykBtz8ZfpbeOM.4P4N10T7r6hpIFURhW45884QLS8KC', NULL, 28, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:28', '2024-08-29 08:10:28'),
(29, 'User 27', NULL, '$2y$12$KjXSfFI6Oy9w4k2uh2/Lzug17mPKz4RtaT5q/LNr0tDMxuVUPfs8i', NULL, 29, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:29', '2024-08-29 08:10:29'),
(30, 'User 28', NULL, '$2y$12$bNIXMaUwLNakkcAKcIhgjO.JYaSve/rzuviNvM.nphODyR9aVebW2', NULL, 30, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:29', '2024-08-29 08:10:29'),
(31, 'User 29', NULL, '$2y$12$dmhb/BdPSeVjEBp6uSo0EOdBHr7sIrAjB7X3O66d6KnQ0v6w9muYW', NULL, 31, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:29', '2024-08-29 08:10:29'),
(32, 'User 30', NULL, '$2y$12$3Qg/s5sjp/p.ZQBqym5LguDKL9L9KujuXl3CdlO4I5ZfakNwxRMP.', NULL, 32, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:29', '2024-08-29 08:10:29'),
(33, 'User 31', NULL, '$2y$12$t47gDHbqeB9dcyZwJPZGP.Y5/CULLgp3BICRox6hB1EJKyVmbCg8S', NULL, 33, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:30', '2024-08-29 08:10:30'),
(34, 'User 32', NULL, '$2y$12$HN8VYxqDutZNdJf5SoC2LO73DObDmC0W6z1lUh6iggK0AbxRwh4vW', NULL, 34, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:30', '2024-08-29 08:10:30'),
(35, 'User 33', NULL, '$2y$12$l5u1lEbQvej3OgKJyWINr.l1LlVOU4o3BVAXDRn2vOv1Mu93q.DGG', NULL, 35, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:30', '2024-08-29 08:10:30'),
(36, 'User 34', NULL, '$2y$12$5R.qPrlqrdox/lzk3iAPHOanQXEcW2bmdrWxZHrtqrfVR876rKjR2', NULL, 36, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:31', '2024-08-29 08:10:31'),
(37, 'User 35', NULL, '$2y$12$cFf/VOsyHn2yrrufx/VIWeNaVwu3eWB.7r6HD4SYtHJG8kSaU4/AG', NULL, 37, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:31', '2024-08-29 08:10:31'),
(38, 'User 36', NULL, '$2y$12$A9UHFhFlyvVoxyhQdKBMH.CYrx3TmtcvnJEsEjP88uEiGcARUfIH.', NULL, 38, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:31', '2024-08-29 08:10:31'),
(39, 'User 37', NULL, '$2y$12$Px2Ow517yJ8y..4IPeP7kOy522LHzQr5BI6DNjflo8xW1ZxYIw9ye', NULL, 39, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:31', '2024-08-29 08:10:31'),
(40, 'User 38', NULL, '$2y$12$GUF2y/91ZOJWHEUeEgAKJu1UjvDbb2P0KpaWYWplI3xFj5ZHY1S2K', NULL, 40, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:32', '2024-08-29 08:10:32'),
(41, 'User 39', NULL, '$2y$12$ob9KFEDoQ00SsbbTWFKSBOdavIGe5CFyQwUuUKqnkNjU46Jgns7ty', NULL, 41, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:32', '2024-08-29 08:10:32'),
(42, 'User 40', NULL, '$2y$12$/4vc/WtNCdNzqvmNlZqYsOSi6STY.YqaKZoKz5K0u7Be2gfObstJK', NULL, 42, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:32', '2024-08-29 08:10:32'),
(43, 'User 41', NULL, '$2y$12$G7R4.O5RaKcmSvJ98ln3iuAqx0afYvoIN2QGMN3T6uliRJdGAYTgu', NULL, 43, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:33', '2024-08-29 08:10:33'),
(44, 'User 42', NULL, '$2y$12$8zca3PjoXWFt1o/F/ceH5uz0rOt/3alYlvFj28d7k0Cw/OuebQWwW', NULL, 44, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:33', '2024-08-29 08:10:33'),
(45, 'User 43', NULL, '$2y$12$rPmrBmzFxdYrf/3HFy0aYOkqLkX402/PRmH1bkEjdD1pW0BLcUl3q', NULL, 45, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:33', '2024-08-29 08:10:33'),
(46, 'User 44', NULL, '$2y$12$ZnKbiaeGIZjjsE2HLNb/8.Fs2ckNeETlFBKMeM06nN8NnbzC268t2', NULL, 46, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:33', '2024-08-29 08:10:33'),
(47, 'User 45', NULL, '$2y$12$A9lxUs.sPAIQJP.n168gF.pAJKO/5vmVscXHEb8bSu1WcSt1Erdl2', NULL, 47, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:34', '2024-08-29 08:10:34'),
(48, 'User 46', NULL, '$2y$12$mIKgTywD5Fhpts4T6nrsgueRfnMaLliUwE1KPeJ4swmo73rMeVu8u', NULL, 48, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:34', '2024-08-29 08:10:34'),
(49, 'User 47', NULL, '$2y$12$vgB1qy6JWUkDibCJpeXtyOydlr5PNIGws3Mb4MKIv.MzU8ytwuxla', NULL, 49, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:34', '2024-08-29 08:10:34'),
(50, 'Sulenq Wazawsky', NULL, '$2y$12$i20XUFXaWUXJmKQaPyy1.OIM0GEC8sgYXtU4aBmK3/xOp829G0/T2', NULL, 50, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:35', '2024-08-31 00:27:45'),
(51, 'Mas Ndo', NULL, '$2y$12$qN3zuTogEhLf814FlKlDy.AIRO6viCiIjzfvD/LICKOH2fz0lfGwW', NULL, 51, NULL, 0, 2, '$2y$12$pJGX3s6MT0AECT3nmWPNW.ls3L5hyAsthx4dNEJbDr8nBBbI91hy2', '2024-08-31 02:54:20', '2024-08-29 08:10:35', '2024-08-31 02:44:20'),
(55, 'Haris Adiyatma Farhan', NULL, '$2y$12$D9KTxOWqIcfIq/Jrt.xBEOzcNn6Lnr/UJuHR1KysmVrDnk.PTmfZS', 4, 55, NULL, 0, 2, NULL, NULL, '2024-08-31 22:04:43', '2024-08-31 22:37:47'),
(56, 'Super Admin 2', NULL, '$2y$12$PSi7WRVFJ5esPkSb9zc0meiqZrw1Dutugd6bagzV2Vq/7Zn339YJm', NULL, NULL, NULL, 0, 2, NULL, NULL, '2024-08-29 08:10:20', '2024-09-01 20:05:31');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_kategori_activity_id_foreign` (`kategori_activity_id`),
  ADD KEY `activity_logs_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `berkas`
--
ALTER TABLE `berkas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `berkas_user_id_foreign` (`user_id`),
  ADD KEY `berkas_kategori_berkas_id_foreign` (`kategori_berkas_id`),
  ADD KEY `berkas_status_berkas_id_foreign` (`status_berkas_id`),
  ADD KEY `berkas_verifikator_1_foreign` (`verifikator_1`);

--
-- Indeks untuk tabel `cutis`
--
ALTER TABLE `cutis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cutis_user_id_foreign` (`user_id`),
  ADD KEY `cutis_tipe_cuti_id_foreign` (`tipe_cuti_id`),
  ADD KEY `cutis_status_cuti_id_foreign` (`status_cuti_id`),
  ADD KEY `cutis_verifikator_1_foreign` (`verifikator_1`),
  ADD KEY `cutis_verifikator_2_foreign` (`verifikator_2`);

--
-- Indeks untuk tabel `data_karyawans`
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
  ADD KEY `data_karyawans_pendidikan_terakhir_foreign` (`pendidikan_terakhir`),
  ADD KEY `data_karyawans_status_karyawan_id_foreign` (`status_karyawan_id`),
  ADD KEY `data_karyawans_kelompok_gaji_id_foreign` (`kelompok_gaji_id`),
  ADD KEY `data_karyawans_ptkp_id_foreign` (`ptkp_id`),
  ADD KEY `data_karyawans_verifikator_1_foreign` (`verifikator_1`);

--
-- Indeks untuk tabel `data_keluargas`
--
ALTER TABLE `data_keluargas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `data_keluargas_data_karyawan_id_foreign` (`data_karyawan_id`);

--
-- Indeks untuk tabel `detail_gajis`
--
ALTER TABLE `detail_gajis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detail_gajis_penggajian_id_foreign` (`penggajian_id`),
  ADD KEY `detail_gajis_kategori_gaji_id_foreign` (`kategori_gaji_id`);

--
-- Indeks untuk tabel `diklats`
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
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `hari_liburs`
--
ALTER TABLE `hari_liburs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `jabatans`
--
ALTER TABLE `jabatans`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `jadwals`
--
ALTER TABLE `jadwals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jadwals_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `jadwal_penggajians`
--
ALTER TABLE `jadwal_penggajians`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `jawabans`
--
ALTER TABLE `jawabans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jawabans_user_penilai_foreign` (`user_penilai`),
  ADD KEY `jawabans_pertanyaan_id_foreign` (`pertanyaan_id`);

--
-- Indeks untuk tabel `jenis_penilaians`
--
ALTER TABLE `jenis_penilaians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jenis_penilaians_status_karyawan_id_foreign` (`status_karyawan_id`),
  ADD KEY `jenis_penilaians_jabatan_penilai_foreign` (`jabatan_penilai`),
  ADD KEY `jenis_penilaians_jabatan_dinilai_foreign` (`jabatan_dinilai`);

--
-- Indeks untuk tabel `kategori_activity_logs`
--
ALTER TABLE `kategori_activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori_agamas`
--
ALTER TABLE `kategori_agamas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori_berkas`
--
ALTER TABLE `kategori_berkas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori_darahs`
--
ALTER TABLE `kategori_darahs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori_diklats`
--
ALTER TABLE `kategori_diklats`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori_gajis`
--
ALTER TABLE `kategori_gajis`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori_kompensasis`
--
ALTER TABLE `kategori_kompensasis`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori_notifikasis`
--
ALTER TABLE `kategori_notifikasis`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori_pendidikans`
--
ALTER TABLE `kategori_pendidikans`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori_potongans`
--
ALTER TABLE `kategori_potongans`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori_presensis`
--
ALTER TABLE `kategori_presensis`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori_ters`
--
ALTER TABLE `kategori_ters`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori_track_records`
--
ALTER TABLE `kategori_track_records`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori_transfer_karyawans`
--
ALTER TABLE `kategori_transfer_karyawans`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori_tukar_jadwals`
--
ALTER TABLE `kategori_tukar_jadwals`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kelompok_gajis`
--
ALTER TABLE `kelompok_gajis`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kompetensis`
--
ALTER TABLE `kompetensis`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `lemburs`
--
ALTER TABLE `lemburs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lemburs_user_id_foreign` (`user_id`),
  ADD KEY `lemburs_jadwal_id_foreign` (`jadwal_id`);

--
-- Indeks untuk tabel `lokasi_kantors`
--
ALTER TABLE `lokasi_kantors`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indeks untuk tabel `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indeks untuk tabel `non_shifts`
--
ALTER TABLE `non_shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `notifikasis`
--
ALTER TABLE `notifikasis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifikasis_kategori_notifikasi_id_foreign` (`kategori_notifikasi_id`),
  ADD KEY `notifikasis_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `pelaporans`
--
ALTER TABLE `pelaporans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pelaporans_pelapor_foreign` (`pelapor`),
  ADD KEY `pelaporans_pelaku_foreign` (`pelaku`),
  ADD KEY `pelaporans_upload_foto_foreign` (`upload_foto`);

--
-- Indeks untuk tabel `penggajians`
--
ALTER TABLE `penggajians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penggajians_riwayat_penggajian_id_foreign` (`riwayat_penggajian_id`),
  ADD KEY `penggajians_data_karyawan_id_foreign` (`data_karyawan_id`),
  ADD KEY `penggajians_status_gaji_id_foreign` (`status_gaji_id`);

--
-- Indeks untuk tabel `pengumumans`
--
ALTER TABLE `pengumumans`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengurang_gajis`
--
ALTER TABLE `pengurang_gajis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengurang_gajis_data_karyawan_id_foreign` (`data_karyawan_id`),
  ADD KEY `pengurang_gajis_premi_id_foreign` (`premi_id`);

--
-- Indeks untuk tabel `penilaians`
--
ALTER TABLE `penilaians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penilaians_user_dinilai_foreign` (`user_dinilai`),
  ADD KEY `penilaians_user_penilai_foreign` (`user_penilai`),
  ADD KEY `penilaians_jenis_penilaian_id_foreign` (`jenis_penilaian_id`);

--
-- Indeks untuk tabel `penyesuaian_gajis`
--
ALTER TABLE `penyesuaian_gajis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penyesuaian_gajis_penggajian_id_foreign` (`penggajian_id`),
  ADD KEY `penyesuaian_gajis_kategori_gaji_id_foreign` (`kategori_gaji_id`);

--
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indeks untuk tabel `pertanyaans`
--
ALTER TABLE `pertanyaans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pertanyaans_jenis_penilaian_id_foreign` (`jenis_penilaian_id`);

--
-- Indeks untuk tabel `perubahan_berkas`
--
ALTER TABLE `perubahan_berkas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `perubahan_berkas_riwayat_perubahan_id_foreign` (`riwayat_perubahan_id`),
  ADD KEY `perubahan_berkas_berkas_id_foreign` (`berkas_id`);

--
-- Indeks untuk tabel `perubahan_keluargas`
--
ALTER TABLE `perubahan_keluargas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `perubahan_keluargas_riwayat_perubahan_id_foreign` (`riwayat_perubahan_id`),
  ADD KEY `perubahan_keluargas_data_keluarga_id_foreign` (`data_keluarga_id`),
  ADD KEY `fk_pendidikan_terakhir` (`pendidikan_terakhir`);

--
-- Indeks untuk tabel `perubahan_personals`
--
ALTER TABLE `perubahan_personals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `perubahan_personals_riwayat_perubahan_id_foreign` (`riwayat_perubahan_id`),
  ADD KEY `fk_kategori_agama_id` (`kategori_agama_id`),
  ADD KEY `fk_kategori_darah_id` (`kategori_darah_id`),
  ADD KEY `fk_perubahan_personals_pendidikan` (`pendidikan_terakhir`);

--
-- Indeks untuk tabel `peserta_diklats`
--
ALTER TABLE `peserta_diklats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peserta_diklats_diklat_id_foreign` (`diklat_id`),
  ADD KEY `peserta_diklats_peserta_foreign` (`peserta`);

--
-- Indeks untuk tabel `premis`
--
ALTER TABLE `premis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `premis_kategori_potongan_id_foreign` (`kategori_potongan_id`);

--
-- Indeks untuk tabel `presensis`
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
-- Indeks untuk tabel `ptkps`
--
ALTER TABLE `ptkps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ptkps_kategori_ter_id_foreign` (`kategori_ter_id`);

--
-- Indeks untuk tabel `reward_bulan_lalus`
--
ALTER TABLE `reward_bulan_lalus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reward_bulan_lalus_data_karyawan_id_foreign` (`data_karyawan_id`);

--
-- Indeks untuk tabel `riwayat_izins`
--
ALTER TABLE `riwayat_izins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_izins_user_id_foreign` (`user_id`),
  ADD KEY `riwayat_izins_status_izin_id_foreign` (`status_izin_id`),
  ADD KEY `riwayat_izins_verifikator_1_foreign` (`verifikator_1`);

--
-- Indeks untuk tabel `riwayat_penggajians`
--
ALTER TABLE `riwayat_penggajians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_penggajians_status_gaji_id_foreign` (`status_gaji_id`);

--
-- Indeks untuk tabel `riwayat_perubahans`
--
ALTER TABLE `riwayat_perubahans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_perubahans_data_karyawan_id_foreign` (`data_karyawan_id`),
  ADD KEY `riwayat_perubahans_status_perubahan_id_foreign` (`status_perubahan_id`),
  ADD KEY `riwayat_perubahans_verifikator_1_foreign` (`verifikator_1`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indeks untuk tabel `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indeks untuk tabel `run_thrs`
--
ALTER TABLE `run_thrs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `run_thrs_data_karyawan_id_foreign` (`data_karyawan_id`);

--
-- Indeks untuk tabel `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `status_aktifs`
--
ALTER TABLE `status_aktifs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `status_berkas`
--
ALTER TABLE `status_berkas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `status_cutis`
--
ALTER TABLE `status_cutis`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `status_diklats`
--
ALTER TABLE `status_diklats`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `status_gajis`
--
ALTER TABLE `status_gajis`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `status_karyawans`
--
ALTER TABLE `status_karyawans`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `status_lemburs`
--
ALTER TABLE `status_lemburs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `status_perubahans`
--
ALTER TABLE `status_perubahans`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `status_presensis`
--
ALTER TABLE `status_presensis`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `status_riwayat_izins`
--
ALTER TABLE `status_riwayat_izins`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `status_tukar_jadwals`
--
ALTER TABLE `status_tukar_jadwals`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ters`
--
ALTER TABLE `ters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ters_kategori_ter_id_foreign` (`kategori_ter_id`);

--
-- Indeks untuk tabel `thrs`
--
ALTER TABLE `thrs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tipe_cutis`
--
ALTER TABLE `tipe_cutis`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `track_records`
--
ALTER TABLE `track_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `track_records_user_id_foreign` (`user_id`),
  ADD KEY `track_records_kategori_record_id_foreign` (`kategori_record_id`);

--
-- Indeks untuk tabel `transfer_karyawans`
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
-- Indeks untuk tabel `tukar_jadwals`
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
-- Indeks untuk tabel `unit_kerjas`
--
ALTER TABLE `unit_kerjas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_status_aktif_foreign` (`status_aktif`),
  ADD KEY `users_data_karyawan_id_foreign` (`data_karyawan_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `berkas`
--
ALTER TABLE `berkas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT untuk tabel `cutis`
--
ALTER TABLE `cutis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `data_karyawans`
--
ALTER TABLE `data_karyawans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT untuk tabel `data_keluargas`
--
ALTER TABLE `data_keluargas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT untuk tabel `detail_gajis`
--
ALTER TABLE `detail_gajis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=620;

--
-- AUTO_INCREMENT untuk tabel `diklats`
--
ALTER TABLE `diklats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `hari_liburs`
--
ALTER TABLE `hari_liburs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `jabatans`
--
ALTER TABLE `jabatans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `jadwals`
--
ALTER TABLE `jadwals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `jadwal_penggajians`
--
ALTER TABLE `jadwal_penggajians`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `jawabans`
--
ALTER TABLE `jawabans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jenis_penilaians`
--
ALTER TABLE `jenis_penilaians`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kategori_activity_logs`
--
ALTER TABLE `kategori_activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `kategori_agamas`
--
ALTER TABLE `kategori_agamas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `kategori_berkas`
--
ALTER TABLE `kategori_berkas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `kategori_darahs`
--
ALTER TABLE `kategori_darahs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `kategori_diklats`
--
ALTER TABLE `kategori_diklats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `kategori_gajis`
--
ALTER TABLE `kategori_gajis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kategori_kompensasis`
--
ALTER TABLE `kategori_kompensasis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `kategori_notifikasis`
--
ALTER TABLE `kategori_notifikasis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `kategori_pendidikans`
--
ALTER TABLE `kategori_pendidikans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `kategori_potongans`
--
ALTER TABLE `kategori_potongans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `kategori_presensis`
--
ALTER TABLE `kategori_presensis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `kategori_ters`
--
ALTER TABLE `kategori_ters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kategori_track_records`
--
ALTER TABLE `kategori_track_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kategori_transfer_karyawans`
--
ALTER TABLE `kategori_transfer_karyawans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `kategori_tukar_jadwals`
--
ALTER TABLE `kategori_tukar_jadwals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `kelompok_gajis`
--
ALTER TABLE `kelompok_gajis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `kompetensis`
--
ALTER TABLE `kompetensis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `lemburs`
--
ALTER TABLE `lemburs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `lokasi_kantors`
--
ALTER TABLE `lokasi_kantors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT untuk tabel `non_shifts`
--
ALTER TABLE `non_shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `notifikasis`
--
ALTER TABLE `notifikasis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT untuk tabel `pelaporans`
--
ALTER TABLE `pelaporans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `penggajians`
--
ALTER TABLE `penggajians`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT untuk tabel `pengumumans`
--
ALTER TABLE `pengumumans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `pengurang_gajis`
--
ALTER TABLE `pengurang_gajis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `penilaians`
--
ALTER TABLE `penilaians`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `penyesuaian_gajis`
--
ALTER TABLE `penyesuaian_gajis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT untuk tabel `pertanyaans`
--
ALTER TABLE `pertanyaans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `perubahan_berkas`
--
ALTER TABLE `perubahan_berkas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `perubahan_keluargas`
--
ALTER TABLE `perubahan_keluargas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `perubahan_personals`
--
ALTER TABLE `perubahan_personals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `peserta_diklats`
--
ALTER TABLE `peserta_diklats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `premis`
--
ALTER TABLE `premis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `presensis`
--
ALTER TABLE `presensis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `ptkps`
--
ALTER TABLE `ptkps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `reward_bulan_lalus`
--
ALTER TABLE `reward_bulan_lalus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `riwayat_izins`
--
ALTER TABLE `riwayat_izins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `riwayat_penggajians`
--
ALTER TABLE `riwayat_penggajians`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `riwayat_perubahans`
--
ALTER TABLE `riwayat_perubahans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `run_thrs`
--
ALTER TABLE `run_thrs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `status_aktifs`
--
ALTER TABLE `status_aktifs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `status_berkas`
--
ALTER TABLE `status_berkas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `status_cutis`
--
ALTER TABLE `status_cutis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `status_diklats`
--
ALTER TABLE `status_diklats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `status_gajis`
--
ALTER TABLE `status_gajis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `status_karyawans`
--
ALTER TABLE `status_karyawans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `status_lemburs`
--
ALTER TABLE `status_lemburs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `status_perubahans`
--
ALTER TABLE `status_perubahans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `status_presensis`
--
ALTER TABLE `status_presensis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `status_riwayat_izins`
--
ALTER TABLE `status_riwayat_izins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `status_tukar_jadwals`
--
ALTER TABLE `status_tukar_jadwals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `ters`
--
ALTER TABLE `ters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT untuk tabel `thrs`
--
ALTER TABLE `thrs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tipe_cutis`
--
ALTER TABLE `tipe_cutis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `track_records`
--
ALTER TABLE `track_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `transfer_karyawans`
--
ALTER TABLE `transfer_karyawans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `tukar_jadwals`
--
ALTER TABLE `tukar_jadwals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `unit_kerjas`
--
ALTER TABLE `unit_kerjas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_kategori_activity_id_foreign` FOREIGN KEY (`kategori_activity_id`) REFERENCES `kategori_activity_logs` (`id`),
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `berkas`
--
ALTER TABLE `berkas`
  ADD CONSTRAINT `berkas_kategori_berkas_id_foreign` FOREIGN KEY (`kategori_berkas_id`) REFERENCES `kategori_berkas` (`id`),
  ADD CONSTRAINT `berkas_status_berkas_id_foreign` FOREIGN KEY (`status_berkas_id`) REFERENCES `status_berkas` (`id`),
  ADD CONSTRAINT `berkas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `berkas_verifikator_1_foreign` FOREIGN KEY (`verifikator_1`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `cutis`
--
ALTER TABLE `cutis`
  ADD CONSTRAINT `cutis_status_cuti_id_foreign` FOREIGN KEY (`status_cuti_id`) REFERENCES `status_cutis` (`id`),
  ADD CONSTRAINT `cutis_tipe_cuti_id_foreign` FOREIGN KEY (`tipe_cuti_id`) REFERENCES `tipe_cutis` (`id`),
  ADD CONSTRAINT `cutis_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cutis_verifikator_1_foreign` FOREIGN KEY (`verifikator_1`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cutis_verifikator_2_foreign` FOREIGN KEY (`verifikator_2`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `data_karyawans`
--
ALTER TABLE `data_karyawans`
  ADD CONSTRAINT `data_karyawans_jabatan_id_foreign` FOREIGN KEY (`jabatan_id`) REFERENCES `jabatans` (`id`),
  ADD CONSTRAINT `data_karyawans_kategori_agama_id_foreign` FOREIGN KEY (`kategori_agama_id`) REFERENCES `kategori_agamas` (`id`),
  ADD CONSTRAINT `data_karyawans_kategori_darah_id_foreign` FOREIGN KEY (`kategori_darah_id`) REFERENCES `kategori_darahs` (`id`),
  ADD CONSTRAINT `data_karyawans_kelompok_gaji_id_foreign` FOREIGN KEY (`kelompok_gaji_id`) REFERENCES `kelompok_gajis` (`id`),
  ADD CONSTRAINT `data_karyawans_kompetensi_id_foreign` FOREIGN KEY (`kompetensi_id`) REFERENCES `kompetensis` (`id`),
  ADD CONSTRAINT `data_karyawans_pendidikan_terakhir_foreign` FOREIGN KEY (`pendidikan_terakhir`) REFERENCES `kategori_pendidikans` (`id`),
  ADD CONSTRAINT `data_karyawans_ptkp_id_foreign` FOREIGN KEY (`ptkp_id`) REFERENCES `ptkps` (`id`),
  ADD CONSTRAINT `data_karyawans_status_karyawan_id_foreign` FOREIGN KEY (`status_karyawan_id`) REFERENCES `status_karyawans` (`id`),
  ADD CONSTRAINT `data_karyawans_unit_kerja_id_foreign` FOREIGN KEY (`unit_kerja_id`) REFERENCES `unit_kerjas` (`id`),
  ADD CONSTRAINT `data_karyawans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `data_karyawans_verifikator_1_foreign` FOREIGN KEY (`verifikator_1`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `data_keluargas`
--
ALTER TABLE `data_keluargas`
  ADD CONSTRAINT `data_keluargas_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`);

--
-- Ketidakleluasaan untuk tabel `detail_gajis`
--
ALTER TABLE `detail_gajis`
  ADD CONSTRAINT `detail_gajis_kategori_gaji_id_foreign` FOREIGN KEY (`kategori_gaji_id`) REFERENCES `kategori_gajis` (`id`),
  ADD CONSTRAINT `detail_gajis_penggajian_id_foreign` FOREIGN KEY (`penggajian_id`) REFERENCES `penggajians` (`id`);

--
-- Ketidakleluasaan untuk tabel `diklats`
--
ALTER TABLE `diklats`
  ADD CONSTRAINT `diklats_dokumen_eksternal_foreign` FOREIGN KEY (`dokumen_eksternal`) REFERENCES `berkas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `diklats_gambar_foreign` FOREIGN KEY (`gambar`) REFERENCES `berkas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `diklats_kategori_diklat_id_foreign` FOREIGN KEY (`kategori_diklat_id`) REFERENCES `kategori_diklats` (`id`),
  ADD CONSTRAINT `diklats_status_diklat_id_foreign` FOREIGN KEY (`status_diklat_id`) REFERENCES `status_diklats` (`id`),
  ADD CONSTRAINT `diklats_verifikator_1_foreign` FOREIGN KEY (`verifikator_1`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `diklats_verifikator_2_foreign` FOREIGN KEY (`verifikator_2`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `jadwals`
--
ALTER TABLE `jadwals`
  ADD CONSTRAINT `jadwals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `jawabans`
--
ALTER TABLE `jawabans`
  ADD CONSTRAINT `jawabans_pertanyaan_id_foreign` FOREIGN KEY (`pertanyaan_id`) REFERENCES `pertanyaans` (`id`),
  ADD CONSTRAINT `jawabans_user_penilai_foreign` FOREIGN KEY (`user_penilai`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `jenis_penilaians`
--
ALTER TABLE `jenis_penilaians`
  ADD CONSTRAINT `jenis_penilaians_jabatan_dinilai_foreign` FOREIGN KEY (`jabatan_dinilai`) REFERENCES `jabatans` (`id`),
  ADD CONSTRAINT `jenis_penilaians_jabatan_penilai_foreign` FOREIGN KEY (`jabatan_penilai`) REFERENCES `jabatans` (`id`),
  ADD CONSTRAINT `jenis_penilaians_status_karyawan_id_foreign` FOREIGN KEY (`status_karyawan_id`) REFERENCES `status_karyawans` (`id`);

--
-- Ketidakleluasaan untuk tabel `lemburs`
--
ALTER TABLE `lemburs`
  ADD CONSTRAINT `lemburs_jadwal_id_foreign` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwals` (`id`),
  ADD CONSTRAINT `lemburs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifikasis`
--
ALTER TABLE `notifikasis`
  ADD CONSTRAINT `notifikasis_kategori_notifikasi_id_foreign` FOREIGN KEY (`kategori_notifikasi_id`) REFERENCES `kategori_notifikasis` (`id`),
  ADD CONSTRAINT `notifikasis_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `pelaporans`
--
ALTER TABLE `pelaporans`
  ADD CONSTRAINT `pelaporans_pelaku_foreign` FOREIGN KEY (`pelaku`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pelaporans_pelapor_foreign` FOREIGN KEY (`pelapor`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pelaporans_upload_foto_foreign` FOREIGN KEY (`upload_foto`) REFERENCES `berkas` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penggajians`
--
ALTER TABLE `penggajians`
  ADD CONSTRAINT `penggajians_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`),
  ADD CONSTRAINT `penggajians_riwayat_penggajian_id_foreign` FOREIGN KEY (`riwayat_penggajian_id`) REFERENCES `riwayat_penggajians` (`id`),
  ADD CONSTRAINT `penggajians_status_gaji_id_foreign` FOREIGN KEY (`status_gaji_id`) REFERENCES `status_gajis` (`id`);

--
-- Ketidakleluasaan untuk tabel `pengurang_gajis`
--
ALTER TABLE `pengurang_gajis`
  ADD CONSTRAINT `pengurang_gajis_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`),
  ADD CONSTRAINT `pengurang_gajis_premi_id_foreign` FOREIGN KEY (`premi_id`) REFERENCES `premis` (`id`);

--
-- Ketidakleluasaan untuk tabel `penilaians`
--
ALTER TABLE `penilaians`
  ADD CONSTRAINT `penilaians_jenis_penilaian_id_foreign` FOREIGN KEY (`jenis_penilaian_id`) REFERENCES `jenis_penilaians` (`id`),
  ADD CONSTRAINT `penilaians_user_dinilai_foreign` FOREIGN KEY (`user_dinilai`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `penilaians_user_penilai_foreign` FOREIGN KEY (`user_penilai`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `penyesuaian_gajis`
--
ALTER TABLE `penyesuaian_gajis`
  ADD CONSTRAINT `penyesuaian_gajis_kategori_gaji_id_foreign` FOREIGN KEY (`kategori_gaji_id`) REFERENCES `kategori_gajis` (`id`),
  ADD CONSTRAINT `penyesuaian_gajis_penggajian_id_foreign` FOREIGN KEY (`penggajian_id`) REFERENCES `penggajians` (`id`);

--
-- Ketidakleluasaan untuk tabel `pertanyaans`
--
ALTER TABLE `pertanyaans`
  ADD CONSTRAINT `pertanyaans_jenis_penilaian_id_foreign` FOREIGN KEY (`jenis_penilaian_id`) REFERENCES `jenis_penilaians` (`id`);

--
-- Ketidakleluasaan untuk tabel `perubahan_berkas`
--
ALTER TABLE `perubahan_berkas`
  ADD CONSTRAINT `perubahan_berkas_berkas_id_foreign` FOREIGN KEY (`berkas_id`) REFERENCES `berkas` (`id`),
  ADD CONSTRAINT `perubahan_berkas_riwayat_perubahan_id_foreign` FOREIGN KEY (`riwayat_perubahan_id`) REFERENCES `riwayat_perubahans` (`id`);

--
-- Ketidakleluasaan untuk tabel `perubahan_keluargas`
--
ALTER TABLE `perubahan_keluargas`
  ADD CONSTRAINT `fk_pendidikan_terakhir` FOREIGN KEY (`pendidikan_terakhir`) REFERENCES `kategori_pendidikans` (`id`),
  ADD CONSTRAINT `perubahan_keluargas_data_keluarga_id_foreign` FOREIGN KEY (`data_keluarga_id`) REFERENCES `data_keluargas` (`id`),
  ADD CONSTRAINT `perubahan_keluargas_riwayat_perubahan_id_foreign` FOREIGN KEY (`riwayat_perubahan_id`) REFERENCES `riwayat_perubahans` (`id`);

--
-- Ketidakleluasaan untuk tabel `perubahan_personals`
--
ALTER TABLE `perubahan_personals`
  ADD CONSTRAINT `fk_kategori_agama_id` FOREIGN KEY (`kategori_agama_id`) REFERENCES `kategori_agamas` (`id`),
  ADD CONSTRAINT `fk_kategori_darah_id` FOREIGN KEY (`kategori_darah_id`) REFERENCES `kategori_darahs` (`id`),
  ADD CONSTRAINT `fk_perubahan_personals_pendidikan` FOREIGN KEY (`pendidikan_terakhir`) REFERENCES `kategori_pendidikans` (`id`),
  ADD CONSTRAINT `perubahan_personals_riwayat_perubahan_id_foreign` FOREIGN KEY (`riwayat_perubahan_id`) REFERENCES `riwayat_perubahans` (`id`);

--
-- Ketidakleluasaan untuk tabel `peserta_diklats`
--
ALTER TABLE `peserta_diklats`
  ADD CONSTRAINT `peserta_diklats_diklat_id_foreign` FOREIGN KEY (`diklat_id`) REFERENCES `diklats` (`id`),
  ADD CONSTRAINT `peserta_diklats_peserta_foreign` FOREIGN KEY (`peserta`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `premis`
--
ALTER TABLE `premis`
  ADD CONSTRAINT `premis_kategori_potongan_id_foreign` FOREIGN KEY (`kategori_potongan_id`) REFERENCES `kategori_potongans` (`id`);

--
-- Ketidakleluasaan untuk tabel `presensis`
--
ALTER TABLE `presensis`
  ADD CONSTRAINT `presensis_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`),
  ADD CONSTRAINT `presensis_foto_keluar_foreign` FOREIGN KEY (`foto_keluar`) REFERENCES `berkas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `presensis_foto_masuk_foreign` FOREIGN KEY (`foto_masuk`) REFERENCES `berkas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `presensis_jadwal_id_foreign` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwals` (`id`),
  ADD CONSTRAINT `presensis_kategori_presensi_id_foreign` FOREIGN KEY (`kategori_presensi_id`) REFERENCES `kategori_presensis` (`id`),
  ADD CONSTRAINT `presensis_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `ptkps`
--
ALTER TABLE `ptkps`
  ADD CONSTRAINT `ptkps_kategori_ter_id_foreign` FOREIGN KEY (`kategori_ter_id`) REFERENCES `kategori_ters` (`id`);

--
-- Ketidakleluasaan untuk tabel `reward_bulan_lalus`
--
ALTER TABLE `reward_bulan_lalus`
  ADD CONSTRAINT `reward_bulan_lalus_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`);

--
-- Ketidakleluasaan untuk tabel `riwayat_izins`
--
ALTER TABLE `riwayat_izins`
  ADD CONSTRAINT `riwayat_izins_status_izin_id_foreign` FOREIGN KEY (`status_izin_id`) REFERENCES `status_riwayat_izins` (`id`),
  ADD CONSTRAINT `riwayat_izins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `riwayat_izins_verifikator_1_foreign` FOREIGN KEY (`verifikator_1`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `riwayat_penggajians`
--
ALTER TABLE `riwayat_penggajians`
  ADD CONSTRAINT `riwayat_penggajians_status_gaji_id_foreign` FOREIGN KEY (`status_gaji_id`) REFERENCES `status_gajis` (`id`);

--
-- Ketidakleluasaan untuk tabel `riwayat_perubahans`
--
ALTER TABLE `riwayat_perubahans`
  ADD CONSTRAINT `riwayat_perubahans_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`),
  ADD CONSTRAINT `riwayat_perubahans_status_perubahan_id_foreign` FOREIGN KEY (`status_perubahan_id`) REFERENCES `status_perubahans` (`id`),
  ADD CONSTRAINT `riwayat_perubahans_verifikator_1_foreign` FOREIGN KEY (`verifikator_1`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `run_thrs`
--
ALTER TABLE `run_thrs`
  ADD CONSTRAINT `run_thrs_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`);

--
-- Ketidakleluasaan untuk tabel `ters`
--
ALTER TABLE `ters`
  ADD CONSTRAINT `ters_kategori_ter_id_foreign` FOREIGN KEY (`kategori_ter_id`) REFERENCES `kategori_ters` (`id`);

--
-- Ketidakleluasaan untuk tabel `track_records`
--
ALTER TABLE `track_records`
  ADD CONSTRAINT `track_records_kategori_record_id_foreign` FOREIGN KEY (`kategori_record_id`) REFERENCES `kategori_track_records` (`id`),
  ADD CONSTRAINT `track_records_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `transfer_karyawans`
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
-- Ketidakleluasaan untuk tabel `tukar_jadwals`
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
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_data_karyawan_id_foreign` FOREIGN KEY (`data_karyawan_id`) REFERENCES `data_karyawans` (`id`),
  ADD CONSTRAINT `users_status_aktif_foreign` FOREIGN KEY (`status_aktif`) REFERENCES `status_aktifs` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
