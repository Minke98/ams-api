-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 18 Des 2025 pada 15.04
-- Versi server: 10.6.24-MariaDB-cll-lve-log
-- Versi PHP: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `artimuco_monitoring_palembang`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `mr_activity_logs`
--

DROP TABLE IF EXISTS `mr_activity_logs`;
CREATE TABLE `mr_activity_logs` (
  `id` varchar(100) NOT NULL,
  `activity` varchar(225) DEFAULT NULL,
  `table_name` varchar(150) DEFAULT NULL,
  `user_id` varchar(15) NOT NULL,
  `create_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `mr_alat`
--

DROP TABLE IF EXISTS `mr_alat`;
CREATE TABLE `mr_alat` (
  `id` varchar(20) NOT NULL,
  `ruangan_id` varchar(20) NOT NULL,
  `nama_alat` varchar(200) DEFAULT NULL,
  `merek_model` varchar(225) DEFAULT NULL,
  `kuantitas` int(11) DEFAULT NULL,
  `tahun_pengadaan` year(4) DEFAULT NULL,
  `status_alat` tinyint(1) DEFAULT NULL COMMENT '1=Fungsi, 2=Tidak Fungsi, 3=Maintenance, 4=Tidak Digunakan',
  `kalibrasi_alat` tinyint(1) DEFAULT NULL COMMENT '1=Perlu Kalibrasi, 0=Tidak Perlu Kalibrasi',
  `kondisi` tinyint(1) DEFAULT NULL COMMENT '1=baru, 2=bekas, 3=baik, 4=rusak',
  `sparepart_tersedia` tinyint(1) DEFAULT NULL COMMENT '1=Ya 0=Tidak',
  `sparepart_list` text DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(225) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mr_alat`
--

INSERT INTO `mr_alat` (`id`, `ruangan_id`, `nama_alat`, `merek_model`, `kuantitas`, `tahun_pengadaan`, `status_alat`, `kalibrasi_alat`, `kondisi`, `sparepart_tersedia`, `sparepart_list`, `deskripsi`, `foto`, `created_at`, `updated_at`) VALUES
('A001', 'R001', 'Computer Server', 'HP Proliant ML110 Gen10', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A002', 'R001', 'Computer Trainee', 'HP Pro Tower 280 G9 PCI', 10, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A003', 'R001', 'Computer trainee ATC', 'Intel(R) Core(TM) i5-10400 CPU @2,90GHz (12 CPUs), ~2,9GHz', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A004', 'R001', 'Hp 150 wired mouse and keyboard', 'HP', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A005', 'R001', 'Hp 125 wired keyboard', 'HP', 10, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A006', 'R001', 'Mouse Keyboard MK129 wired optical', 'LOGITECH', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A007', 'R001', 'wired optical mouse', 'HP', 10, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A008', 'R001', 'Monitor hp M24fwa', 'HP', 11, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A009', 'R001', 'Monitor Samsung 27”', 'SAMSUNG', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A010', 'R001', 'Headset Logitech G335', 'LOGITECH G335 3.5mm jack input', 10, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A011', 'R001', 'Switch HP 1420 24 PORT', '24 port switch HP1420 series 220 VAC tegangan input', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A012', 'R001', 'Screen projector', 'Tegangan 220VAC 50Hz', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A013', 'R001', 'Projector view sonic PS501X', 'sonic PS501X', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A014', 'R001', 'Epson EcoTank M15140', 'EcoTank M15140', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A015', 'R001', 'Panel Box ADB', 'ADB Safegate', 1, '2023', 1, 1, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A016', 'R001', 'Panel Box MDB Simulator', '3 phase', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A017', 'R001', 'Rack Indorack', 'Rack Type 32U', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A018', 'R001', 'Meja Instuctor', 'Meja kayu instruktur', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A019', 'R001', 'Meja trainee', 'Meja kayu trainee', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A020', 'R001', 'Lantai raised floor', 'Ruangan CBT dan AFL', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A021', 'R001', 'Kursi Instruktur', NULL, 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A022', 'R001', 'Kursi debriefing', NULL, 15, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A023', 'R001', 'Meja resepsionis', NULL, 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A024', 'R001', 'Kursi resepsionis', NULL, 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A025', 'R001', 'Lampu threshold merah hijau', 'ADB SAFEGATE', 2, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A026', 'R001', 'Lampu runway putih putih', 'ADB SAFEGATE', 2, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A027', 'R001', 'Lampu runway putih kuning', 'ADB SAFEGATE', 3, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A028', 'R001', 'Lampu PAPI', 'ADB SAFEGATE', 2, '2023', 1, 1, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A029', 'R001', 'Lampu taxiway center line Inset hijau kuning', 'ADB SAFEGATE, INSET', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A030', 'R001', 'Lampu taxiway center line kuning', 'ADB SAFEGATE, INSET', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A031', 'R001', 'Lampu runway center line hijau', 'ADB SAFEGATE, INSET', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A032', 'R001', 'Lampu runway center line hijau hijau', 'ADB SAFEGATE, INSET', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A033', 'R001', 'Lampu runway center line merah putih', 'ADB SAFEGATE, INSET', 2, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A034', 'R001', 'Lampu threshold hijau hijau merah', 'ADB SAFEGATE, INSET', 2, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A035', 'R001', 'Lampu approach putih putih putih', 'ADB SAFEGATE, INSET', 2, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A036', 'R001', 'Lampu SQFL inside', 'ADB SAFEGATE, INSET', 1, '2023', 1, 1, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A037', 'R001', 'Lampu taxiway inside', 'ADB SAFEGATE, INSET', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A038', 'R001', 'Lampu SQFL', 'ADB SAFEGATE, ELEVATED', 1, '2023', 1, 1, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A039', 'R001', 'Lampu Approach', 'ADB SAFEGATE, ELEVATED', 2, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A040', 'R001', 'Lampu taxiway', 'ADB SAFEGATE, ELEVATED', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A041', 'R001', 'Rotating Beacon', 'ADB SAFEGATE', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A042', 'R001', 'CCR', 'Tipe : ID0C11B001000021 IDM8000-1-ESW-PB/4/230 Input : 230VAC/21A 50/60Hz 1-PHASE Output : 606V 4,0 kW/KVA at 6,6A SN : WCDN 2321 50885 / 15', 1, '2023', 1, 1, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A043', 'R001', 'Taxiway guidance signs', 'EP00017-100-02 SN : 2324061201352', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A044', 'R001', 'Runway guidance signs', 'EP00017-100-02 SN : 2324061201245', 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A045', 'R001', 'Windsock', NULL, 1, '2023', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A046', 'R001', 'Theodolite', 'RUIDE', 2, '2023', 1, 1, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A047', 'R001', 'Kompas', 'SUNTO', 2, '2023', 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A048', 'R001', 'Tripod theodolite', 'RUIDE', 2, '2023', 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A049', 'R001', 'Telescopic levelling staff', NULL, 1, '2023', 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:36:25', '2025-12-01 02:36:25'),
('A050', 'R013', 'Piknometer', NULL, 6, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A051', 'R013', 'Kerucut Terpancung', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A052', 'R013', 'Tongkat penumbuk Kecil', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A053', 'R013', 'Thermometer', NULL, 6, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A054', 'R013', 'Desikator', NULL, 3, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A055', 'R013', 'Keranjang Kawat', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A056', 'R013', 'Mesin Los Angeles', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A057', 'R013', 'Concrete Test', NULL, 1, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A058', 'R013', 'Cetakan Silinder', NULL, 5, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A059', 'R013', 'Cetakan Kubus', NULL, 12, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A060', 'R013', 'Cetakan Balok', NULL, 4, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A061', 'R013', 'Hydraulic Concrete beam', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A062', 'R013', 'Hammer Test', NULL, 4, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A063', 'R013', 'Concrete Mixer', NULL, 2, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A064', 'R013', 'Neraca Timbangan', NULL, 2, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A065', 'R013', 'Sieve Shaker', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A066', 'R013', 'Cent 0 Gram Balance', NULL, 2, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A067', 'R013', 'Alat Perojok Besar', NULL, 2, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A068', 'R014', 'Marshall Compression Machine', NULL, 2, NULL, 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A069', 'R014', 'Waterbath', NULL, 3, NULL, 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A070', 'R014', 'Automatic Asphalt Compactor', NULL, 2, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A071', 'R014', 'Core Drilling Test', NULL, 2, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A072', 'R014', 'Penetration Test (Penetrometer Aspal)', NULL, 3, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A073', 'R014', 'Saybolt / Saybat Viscometer', NULL, 2, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A074', 'R014', 'Benkelman Beam', NULL, 2, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A075', 'R014', 'Oven Laboratorium', NULL, 1, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A076', 'R014', 'Thin Film Oven Test (TFOT)', NULL, 2, NULL, 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A077', 'R014', 'Centrifuge Extractor', NULL, 2, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A078', 'R014', 'Ductility Test', NULL, 1, NULL, 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('A079', 'R015', 'Sondir', NULL, 1, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:44:24', '2025-12-01 02:44:24'),
('A080', 'R015', 'Oven', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:44:24', '2025-12-01 02:44:24'),
('A081', 'R015', 'Cawan', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:44:24', '2025-12-01 02:44:24'),
('A082', 'R015', 'Desikator', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:44:24', '2025-12-01 02:44:24'),
('A083', 'R015', 'Piknometer', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:44:24', '2025-12-01 02:44:24'),
('A084', 'R015', 'Termometer', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:44:24', '2025-12-01 02:44:24'),
('A085', 'R015', 'Pemadatan', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:44:24', '2025-12-01 02:44:24'),
('A086', 'R015', 'CBR', NULL, 1, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:44:24', '2025-12-01 02:44:24'),
('A087', 'R015', 'Sand Cone', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:44:24', '2025-12-01 02:44:24'),
('A088', 'R015', 'Hand Bor', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:44:24', '2025-12-01 02:44:24'),
('A089', 'R015', 'Palu', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:44:24', '2025-12-01 02:44:24'),
('A090', 'R019', 'Sirene', '-', 1, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:45:39', '2025-12-01 02:45:39'),
('A091', 'R019', 'Crash Bell', '-', 1, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:45:39', '2025-12-01 02:45:39'),
('A092', 'R019', 'Layout Bandara Southview', '-', 1, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:45:39', '2025-12-01 02:45:39'),
('A093', 'R019', 'Radio HT', '-', 10, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:45:39', '2025-12-01 02:45:39'),
('A094', 'R019', 'Radio RIG', '-', 1, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:45:39', '2025-12-01 02:45:39'),
('A095', 'R019', 'Interkom', '-', 2, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:45:39', '2025-12-01 02:45:39'),
('A096', 'R019', 'Papan Tulis', '-', 1, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:45:39', '2025-12-01 02:45:39'),
('A097', 'R019', 'Jam Digital', '-', 1, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:45:39', '2025-12-01 02:45:39'),
('A098', 'R019', 'Miniatur Pesawat', '-', 7, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:45:39', '2025-12-01 02:45:39'),
('A099', 'R019', 'Gunlight', '-', 1, NULL, 2, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:45:39', '2025-12-01 02:45:39'),
('A100', 'R019', 'Monitor Desk Controller', '-', 2, NULL, 2, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:45:39', '2025-12-01 02:45:39'),
('A101', 'R019', 'Monitor Adjacent Unit', '-', 1, NULL, 2, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:45:39', '2025-12-01 02:45:39'),
('A102', 'R022', 'FOAM TENDER', 'MERCEDESBENZ ACTROS 8158', 1, '2012', 1, 1, NULL, 1, NULL, 'Perlu perbaikan pompa', NULL, '2025-12-01 02:47:34', '2025-12-01 02:47:34'),
('A103', 'R022', 'RIV', 'FORD', 1, '2017', 1, 0, NULL, 1, NULL, 'Perbaikan indikator bahan bakar', NULL, '2025-12-01 02:47:34', '2025-12-01 02:47:34'),
('A104', 'R022', 'AMBULANCE', 'VW', 1, '2017', 1, 0, NULL, 1, NULL, 'Perbaikan kelistrikan', NULL, '2025-12-01 02:47:34', '2025-12-01 02:47:34'),
('A105', 'R009', 'Fuselage', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A106', 'R009', 'Wings', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A107', 'R009', 'Exit Door', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A108', 'R009', 'Emergency exit', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A109', 'R009', 'Cockpit Interior', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A110', 'R009', 'Cabin Interior', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A111', 'R009', 'lamp/Light', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A112', 'R009', 'Cabin Light', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A113', 'R009', 'Overhead warning signs', NULL, NULL, NULL, 2, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A114', 'R009', 'Reading Light', NULL, NULL, NULL, 2, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A115', 'R009', 'Lavatory Light', NULL, NULL, NULL, 2, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A116', 'R009', 'Exit light', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A117', 'R009', 'Window light', NULL, NULL, NULL, 2, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A118', 'R009', 'Pasanger Seat', NULL, NULL, NULL, 2, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A119', 'R009', 'Audio System', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A120', 'R009', 'Air Conditioner (AC)', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:48:47', '2025-12-01 02:48:47'),
('A121', 'R010', 'Compressor', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:49:57', '2025-12-01 02:49:57'),
('A122', 'R010', 'Helicopter mockup', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:49:57', '2025-12-01 02:49:57'),
('A123', 'R010', 'Kolam latih', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:49:57', '2025-12-01 02:49:57'),
('A124', 'R010', 'Pneumatic crane', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:49:57', '2025-12-01 02:49:57'),
('A125', 'R006', 'Door trainer B-727 NG', NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:50:59', '2025-12-01 02:50:59'),
('A126', 'R006', 'Door trainer ATR 72', NULL, NULL, NULL, 2, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:50:59', '2025-12-01 02:50:59'),
('A127', 'R006', 'Door trainer Airbus', NULL, NULL, NULL, 2, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:50:59', '2025-12-01 02:50:59'),
('A128', 'R006', 'Belt', NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:50:59', '2025-12-01 02:50:59'),
('A129', 'R006', 'Oxygen mask', NULL, NULL, NULL, 1, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:50:59', '2025-12-01 02:50:59'),
('A130', 'R006', 'Life jacket', NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:50:59', '2025-12-01 02:50:59'),
('A131', 'R006', 'Medical kit', NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:50:59', '2025-12-01 02:50:59'),
('A132', 'R006', 'Protective breathing equipment', NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:50:59', '2025-12-01 02:50:59'),
('A133', 'R006', 'Emergency safety card', NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-12-01 02:50:59', '2025-12-01 02:50:59'),
('A134', 'R017', 'Basic electronic trainer', NULL, 1, '2018', 1, 0, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A135', 'R017', 'Digital trainer', NULL, 2, '2018', 1, 0, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A136', 'R017', 'Transformers trainer', NULL, 1, '2018', 1, 0, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A137', 'R017', 'Basic electricity trainer', NULL, 1, '2018', 1, 0, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A138', 'R017', 'Electrical machine trainer', NULL, 1, '2018', 1, 0, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A139', 'R017', 'Single phase ac machine', NULL, 1, '2018', 1, 0, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A140', 'R017', 'Single phase asynchronous motor', NULL, 1, '2018', 1, 0, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A141', 'R017', '3 phase induction motor', NULL, 1, '2018', 1, 0, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A142', 'R017', 'Laboratory breadboard', NULL, 2, '2018', 1, 0, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A143', 'R017', 'Console', NULL, 2, '2018', 1, 0, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A144', 'R017', 'Generator set 325 KVA', 'CATERPILAR', 2, '2014', 1, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A145', 'R017', 'Kubikel, Trafo, LVMDP dan Panel SDP', 'LOKAL', 1, '2014', 1, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A146', 'R017', 'Power Quality Analyzer', 'HIOKI', 1, '2025', 1, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A147', 'R017', 'Earth Tester', 'HIOKI', 1, '2025', 1, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A148', 'R017', 'Insulation Tester', 'HIOKI', 1, '2025', 1, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A149', 'R017', 'Clamp Power Meter', 'HIOKI', 1, '2025', 1, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A150', 'R017', 'Clamp Leackage Meter', 'HIOKI', 1, '2025', 1, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18'),
('A151', 'R017', 'Thermo Gun', 'HIOKI', 1, '2025', 1, 1, NULL, 0, NULL, NULL, NULL, '2025-12-01 02:52:18', '2025-12-01 02:52:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mr_api_users`
--

DROP TABLE IF EXISTS `mr_api_users`;
CREATE TABLE `mr_api_users` (
  `email` varchar(255) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `hit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mr_api_users`
--

INSERT INTO `mr_api_users` (`email`, `api_key`, `hit`) VALUES
('nasnurhuda11@gmail.com', 'm91FZ7qkA3pLs82VNx4rDb0WgTYeH5Cu', 7101);

-- --------------------------------------------------------

--
-- Struktur dari tabel `mr_laporan_kerusakan`
--

DROP TABLE IF EXISTS `mr_laporan_kerusakan`;
CREATE TABLE `mr_laporan_kerusakan` (
  `id` varchar(20) NOT NULL,
  `alat_id` varchar(50) DEFAULT NULL,
  `software_id` varchar(50) DEFAULT NULL,
  `sdm_id` varchar(20) NOT NULL,
  `tanggal_laporan` date NOT NULL,
  `deskripsi_kerusakan` text NOT NULL,
  `prioritas` tinyint(4) DEFAULT NULL COMMENT '1=low, 2=medium, 3=high, 4=critical',
  `status` tinyint(4) DEFAULT 1 COMMENT '1=Open, 2=on progress, 3=close, 4=cancelled',
  `foto` varchar(225) DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mr_laporan_kerusakan`
--

INSERT INTO `mr_laporan_kerusakan` (`id`, `alat_id`, `software_id`, `sdm_id`, `tanggal_laporan`, `deskripsi_kerusakan`, `prioritas`, `status`, `foto`, `tanggal_selesai`, `created_at`) VALUES
('LPR0001', 'A001', '', 'SDM001', '2025-01-05', 'LCD tidak menyala saat dinyalakan.', 3, 1, NULL, NULL, '2025-11-14 07:15:14'),
('LPR0002', 'A002', '', 'SDM003', '2025-01-10', 'Kabel power putus, tidak bisa dihubungkan ke sumber listrik.', 2, 2, NULL, NULL, '2025-11-14 07:15:14'),
('LPR0003', 'A003', '', 'SDM002', '2025-01-12', 'Software crash ketika digunakan untuk praktikum.', 1, 3, NULL, '2025-01-13', '2025-11-14 07:15:14'),
('LPR0004', 'A004', '', 'SDM004', '2025-01-15', 'Kipas pendingin tidak berfungsi, alat cepat panas.', 2, 1, NULL, NULL, '2025-11-14 07:15:14'),
('LPR0005', 'A005', '', 'SDM001', '2025-01-20', 'Tombol kontrol rusak tidak merespon.', 4, 2, NULL, NULL, '2025-11-14 07:15:14'),
('LPR0006', 'A006', '', 'SDM002', '2025-01-22', 'Kerusakan mekanik pada komponen motor.', 3, 3, NULL, '2025-12-15', '2025-11-14 07:15:14'),
('LPR0007', 'A007', '', 'SDM003', '2025-01-25', 'Alat mengeluarkan bau gosong saat digunakan.', 4, 2, NULL, NULL, '2025-11-14 07:15:14'),
('LPR0008', 'A008', '', 'SDM004', '2025-01-27', 'Sensor tidak membaca input dengan benar.', 2, 3, NULL, '2025-01-28', '2025-11-14 07:15:14'),
('LPR0009', 'A010', '', 'SDM002', '2025-01-30', 'Baterai internal soak tidak bisa charging.', 3, 4, NULL, NULL, '2025-11-14 07:15:14'),
('LPR0010', 'A012', '', 'SDM001', '2025-02-01', 'Alat mati total setelah digunakan intensif.', 4, 3, NULL, '2025-12-12', '2025-11-14 07:15:14'),
('LPR0011', 'A009', NULL, 'SDM140', '2025-12-15', 'coba', 2, 4, NULL, NULL, '2025-12-15 03:37:55'),
('LPR0012', 'A003', NULL, 'SDM140', '2025-12-15', 'test', 1, 4, NULL, NULL, '2025-12-15 03:56:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mr_maintenance`
--

DROP TABLE IF EXISTS `mr_maintenance`;
CREATE TABLE `mr_maintenance` (
  `id` varchar(20) NOT NULL,
  `alat_id` varchar(50) DEFAULT NULL,
  `software_id` varchar(50) DEFAULT NULL,
  `tanggal_mulai_maintenance` date DEFAULT NULL,
  `tanggal_selesai_maintenance` date DEFAULT NULL,
  `jenis_maintenance` tinyint(1) DEFAULT NULL COMMENT '1=Open, 2=on progress, 3=close',
  `teknisi` varchar(100) DEFAULT NULL,
  `biaya` decimal(15,2) DEFAULT NULL,
  `judul_maintenance` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `next_maintenance` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mr_maintenance`
--

INSERT INTO `mr_maintenance` (`id`, `alat_id`, `software_id`, `tanggal_mulai_maintenance`, `tanggal_selesai_maintenance`, `jenis_maintenance`, `teknisi`, `biaya`, `judul_maintenance`, `deskripsi`, `next_maintenance`, `created_at`) VALUES
('MT0001', 'A001', '', '2025-01-05', '2025-01-06', 3, 'Andi', 150000.00, 'LCD', 'Perbaikan LCD dan pengecekan kabel internal.', '2025-03-05', '2025-11-14 07:16:10'),
('MT0002', 'A002', '', '2025-01-10', NULL, 2, 'Budi', 75000.00, 'Penggantian Kabel', 'Penggantian kabel power masih dalam proses.', '2025-03-10', '2025-11-14 07:16:10'),
('MT0003', 'A003', '', '2025-01-12', '2025-01-13', 3, 'Teknisi Candra', 50000.00, 'Maintenance Sim ALS Software', 'Update software dan pengecekan sistem.', '2025-04-12', '2025-11-14 07:16:10'),
('MT0004', 'A004', '', '2025-01-15', NULL, 1, 'Teknisi Doni', 120000.00, 'Maintenance Sim ALS Kipas', 'Kipas pendingin sedang diperiksa.', '2025-03-15', '2025-11-14 07:16:10'),
('MT0005', 'A005', '', '2025-01-20', '2025-12-28', 3, 'Teknisi Eka', 95000.00, 'Ganti Tombol Kontrol', 'Tombol kontrol sedang diganti.', '2025-04-20', '2025-11-14 07:16:10'),
('MT0006', 'A006', '', '2025-01-22', '2025-01-25', 3, 'Teknisi Fajar', 200000.00, 'Perbaikan Komposen Motor', 'Perbaikan komponen motor dan pelumasan.', '2025-05-22', '2025-11-14 07:16:10'),
('MT0007', 'A007', '', '2025-01-25', '2025-12-15', 3, 'Teknisi Gilang', 175000.00, 'Investigasi', 'Investigasi penyebab bau gosong.', '2025-03-25', '2025-11-14 07:16:10'),
('MT0008', 'A008', '', '2025-01-27', '2025-01-28', 3, 'Teknisi Hendra', 65000.00, 'Kalibrasi Sensor', 'Kalibrasi sensor dan penggantian modul kecil.', '2025-04-27', '2025-11-14 07:16:10'),
('MT0009', 'A009', '', '2025-01-30', NULL, 1, 'Teknisi Irfan', 80000.00, 'Pemeriksaan Modul', 'Pemeriksaan modul baterai internal.', '2025-03-30', '2025-11-14 07:16:10'),
('MT0010', 'A010', '', '2025-02-01', '2025-12-10', 3, 'Teknisi Joko', 250000.00, 'Kerusakan Alat ALS', 'Diagnosa awal kerusakan total pada perangkat.', '2025-05-01', '2025-11-14 07:16:10'),
('MT0012', NULL, 'SW004', '2025-12-12', '2025-12-12', 3, 'jsjs', NULL, 'hejd', 'bshsh', NULL, '2025-12-12 02:15:10'),
('MT0013', NULL, 'SW004', '2025-12-12', '2025-12-12', 3, 'as', 200000.00, 'sjjdjd', 'hshsjjsjsjss', NULL, '2025-12-12 02:16:00'),
('MT0014', NULL, 'SW004', '2025-12-14', '2025-12-12', 3, 'a', 200000.00, 'test', 'yshsbbejeje', NULL, '2025-12-12 03:29:02'),
('MT0016', NULL, 'SW004', '2025-12-12', '2025-12-26', 1, NULL, NULL, 'iyak', 'iyakk', NULL, '2025-12-12 04:43:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mr_notifikasi`
--

DROP TABLE IF EXISTS `mr_notifikasi`;
CREATE TABLE `mr_notifikasi` (
  `id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `pesan` text NOT NULL,
  `jenis_notifikasi` enum('reminder','alert','info') DEFAULT 'info',
  `modul_terkait` varchar(50) DEFAULT NULL,
  `id_terkait` varchar(20) DEFAULT NULL,
  `status_baca` tinyint(1) DEFAULT 0,
  `tanggal_kirim` timestamp NULL DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mr_notifikasi`
--

INSERT INTO `mr_notifikasi` (`id`, `user_id`, `judul`, `pesan`, `jenis_notifikasi`, `modul_terkait`, `id_terkait`, `status_baca`, `tanggal_kirim`, `expiry_date`, `created_at`) VALUES
('NTF001', 'USR004', 'Sertifikat Mengajar Akan Kadaluarsa', 'Sertifikat “Basic Instruction Training” akan kadaluarsa dalam 10 hari.', 'reminder', 'sertifikasi', 'SRTF001', 0, '2025-01-05 02:00:00', '2025-01-15', '2025-11-14 07:21:44'),
('NTF002', 'USR003', 'Kerusakan Alat Teridentifikasi', 'Alat “Oscilloscope Tektronix” mengalami kerusakan level sedang. Mohon segera diperiksa.', 'alert', 'alat', 'ALT002', 0, '2025-01-07 03:30:00', NULL, '2025-11-14 07:21:44'),
('NTF003', 'USR003', 'Jadwal Maintenance Hari Ini', 'Maintenance berkala pada alat “Simulator Engine A320” dijadwalkan hari ini.', 'reminder', 'maintenance', 'MNT001', 0, '2025-01-07 23:00:00', '2025-01-08', '2025-11-14 07:21:44'),
('NTF004', 'USR002', 'Update Lisensi Software', 'Lisensi software “AutoCAD 2024” telah diperpanjang selama 1 tahun.', 'info', 'software', 'SWF004', 1, '2025-01-06 07:10:00', NULL, '2025-11-14 07:21:44'),
('NTF005', 'USR001', 'Sertifikat Sudah Expired', 'Sertifikat “Aviation Safety Management” milik SDM ID SDM005 telah kadaluarsa.', 'alert', 'sertifikasi', 'SRTF005', 0, '2025-01-03 09:45:00', '2025-01-01', '2025-11-14 07:21:44'),
('NTF006', 'USR004', 'Alat Baru Ditambahkan', 'Alat baru “Multimeter Fluke 87V” telah ditambahkan ke Laboratorium Listrik.', 'info', 'alat', 'ALT010', 1, '2025-01-09 04:15:00', NULL, '2025-11-14 07:21:44'),
('NTF007', 'USR003', 'Lisensi Software Hampir Habis', 'Lisensi “MATLAB Academic License” akan kadaluarsa dalam 5 hari.', 'reminder', 'software', 'SWF007', 0, '2025-01-10 01:20:00', '2025-01-15', '2025-11-14 07:21:44'),
('NTF008', 'USR001', 'Percobaan Login Mencurigakan', 'Terdapat percobaan login gagal sebanyak 5 kali pada akun Anda.', 'alert', 'sistem', NULL, 0, '2025-01-11 00:40:00', NULL, '2025-11-14 07:21:44'),
('NTF009', 'USR002', 'Data Ruangan Telah Diperbarui', 'Data ruangan “LAB Navigasi 1” telah diperbarui oleh Laboran.', 'info', 'ruangan', 'R001', 1, '2025-01-08 06:55:00', NULL, '2025-11-14 07:21:44'),
('NTF010', 'USR003', 'Reminder Maintenance Berikutnya', 'Maintenance berikutnya untuk alat “GPS Trainer Unit” dijadwalkan pada 2025-02-10.', 'reminder', 'maintenance', 'MNT004', 0, '2025-01-12 02:25:00', '2025-02-10', '2025-11-14 07:21:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mr_penggunaan_ruangan`
--

DROP TABLE IF EXISTS `mr_penggunaan_ruangan`;
CREATE TABLE `mr_penggunaan_ruangan` (
  `id` varchar(20) NOT NULL,
  `ruangan_id` varchar(20) NOT NULL,
  `alat_id` varchar(50) DEFAULT NULL,
  `software_id` varchar(50) DEFAULT NULL,
  `sdm_id` varchar(20) NOT NULL,
  `tanggal_mulai` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tanggal_selesai` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `kegiatan` varchar(200) DEFAULT NULL,
  `deskripsi` text NOT NULL,
  `foto` varchar(225) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1 COMMENT '1=digunakan, 0=tidak digunakan',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mr_penggunaan_ruangan`
--

INSERT INTO `mr_penggunaan_ruangan` (`id`, `ruangan_id`, `alat_id`, `software_id`, `sdm_id`, `tanggal_mulai`, `tanggal_selesai`, `kegiatan`, `deskripsi`, `foto`, `status`, `created_at`, `updated_at`) VALUES
('P001', 'R001', 'A001', '', 'SDM001', '2025-12-02 07:17:27', '2025-01-10 03:00:00', 'Pelatihan ALS Dasar', '', 'als_training.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P002', 'R002', 'A003', '', 'SDM002', '2025-12-02 07:17:27', '2025-01-11 04:00:00', 'Praktikum Komputer Dasar', '', 'komputer_praktikum.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P003', 'R003', 'A005', '', 'SDM003', '2025-12-02 07:17:27', '2025-01-12 03:00:00', 'Latihan Bahasa Inggris', '', 'bahasa_inggris.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P004', 'R004', 'A007', '', 'SDM004', '2025-12-02 07:17:27', '2025-01-12 08:00:00', 'Simulasi FOO', '', 'foo_simulation.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P005', 'R005', 'A009', '', 'SDM005', '2025-12-02 07:17:27', '2025-01-13 05:00:00', 'Pelatihan X-Ray', '', 'xray_training.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P006', 'R006', 'A011', '', 'SDM006', '2025-12-02 07:17:27', '2025-01-13 08:00:00', 'Latihan Door Trainer', '', 'door_trainer.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P007', 'R007', 'A013', '', 'SDM001', '2025-12-02 07:17:27', '2025-01-14 04:00:00', 'Tes CBT AVSEC', '', 'cbt_avsec.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P008', 'R008', 'A015', '', 'SDM002', '2025-12-02 07:17:27', '2025-01-14 08:00:00', 'Simulasi Smoke Chamber', '', 'smoke_chamber.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P009', 'R009', 'A017', '', 'SDM003', '2025-12-02 07:17:27', '2025-01-15 04:00:00', 'Latihan Evakuasi Fix Wing', '', 'fixwing_training.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P010', 'R010', 'A019', '', 'SDM004', '2025-12-02 07:17:27', '2025-01-15 09:00:00', 'Simulasi HUET', '', 'huet.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P011', 'R011', 'A021', '', 'SDM005', '2025-12-02 07:17:27', '2025-01-16 05:00:00', 'Simulasi FTDS', '', 'ftds_sim.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P012', 'R012', 'A023', '', 'SDM006', '2025-12-02 07:17:27', '2025-01-16 08:00:00', 'Praktikum FTDS Komputer', '', 'pc_ftds.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P013', 'R013', 'A025', '', 'SDM001', '2025-12-02 07:17:27', '2025-01-17 04:00:00', 'Uji Kuat Tekan Beton', '', 'uji_beton.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P014', 'R014', 'A027', '', 'SDM002', '2025-12-02 07:17:27', '2025-01-17 08:00:00', 'Uji Material Aspal', '', 'uji_aspal.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P015', 'R015', 'A029', '', 'SDM003', '2025-12-02 07:17:27', '2025-01-18 03:00:00', 'Praktikum Uji Tanah', '', 'uji_tanah.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P016', 'R016', 'A031', '', 'SDM004', '2025-12-02 07:17:27', '2025-01-18 08:00:00', 'Tes CBT ALS', '', 'cbt_als.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P017', 'R017', 'A033', '', 'SDM005', '2025-12-02 07:17:27', '2025-01-19 04:00:00', 'Praktikum Elektronika', '', 'elektronika_praktikum.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P018', 'R018', 'A035', '', 'SDM006', '2025-12-02 07:17:27', '2025-01-19 09:00:00', 'Pengukuran Cuaca', '', 'weather_measurement.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P019', 'R019', 'A037', '', 'SDM001', '2025-12-02 07:17:27', '2025-01-20 05:00:00', 'Simulasi ATC', '', 'atc_sim.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P020', 'R020', 'A039', '', 'SDM002', '2025-12-02 07:17:27', '2025-01-20 08:00:00', 'Simulasi Fire Trainer', '', 'fire_trainer.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P021', 'R021', 'A041', '', 'SDM003', '2025-12-02 07:17:27', '2025-01-21 03:00:00', 'Pelatihan Hydrant', '', 'hydrant_training.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P022', 'R022', 'A043', '', 'SDM004', '2025-12-02 07:17:27', '2025-01-21 08:00:00', 'Simulasi Kendaraan PKP-PK', '', 'pkppk_vehicle.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P023', 'R023', 'A045', '', 'SDM005', '2025-12-02 07:17:27', '2025-01-22 04:00:00', 'Simulasi ARFF', '', 'arff_trainer.jpg', 0, '2025-11-14 07:13:55', '2025-11-14 07:13:55'),
('P024', 'R016', NULL, 'SW010', 'SDM130', '2025-12-09 07:35:44', '2025-12-09 05:56:00', 'Praktikum', 'coba', NULL, 0, '2025-12-09 04:57:15', NULL),
('P025', 'R016', NULL, 'SW010', '', '2025-12-16 03:55:58', '2025-12-15 05:16:00', 'Praktikum', 'coba', NULL, 0, '2025-12-15 02:16:33', NULL),
('P026', 'R014', 'A074', NULL, '', '2025-12-16 03:55:58', '2025-12-15 07:29:00', 'Praktikum', 'tesy', NULL, 0, '2025-12-15 02:31:14', NULL),
('P027', 'R014', 'A070', NULL, '', '2025-12-16 03:55:58', '2025-12-15 07:29:00', 'Praktikum', 'tesy', NULL, 0, '2025-12-15 02:31:14', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `mr_prodi`
--

DROP TABLE IF EXISTS `mr_prodi`;
CREATE TABLE `mr_prodi` (
  `id` varchar(20) NOT NULL,
  `kode_prodi` varchar(50) DEFAULT NULL,
  `nama_prodi` varchar(225) DEFAULT NULL,
  `jenjang` tinyint(4) DEFAULT NULL COMMENT '1=D3, 2=D4, 3=S1, 4=S2, 5=S3',
  `kepala_prodi` varchar(150) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Aktif, 0=Nonaktif',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mr_prodi`
--

INSERT INTO `mr_prodi` (`id`, `kode_prodi`, `nama_prodi`, `jenjang`, `kepala_prodi`, `foto`, `status`, `created_at`, `updated_at`) VALUES
('1', 'TRBU', 'Teknologi Rekayasa Bandar Udara', NULL, NULL, NULL, 1, '2025-11-18 03:55:03', '2025-12-01 03:44:36'),
('2', 'MBU', 'Manajemen Bandar Udara', NULL, NULL, NULL, 1, '2025-11-18 03:55:03', '2025-12-01 03:44:45'),
('3', 'PPKP', 'Penyelamatan dan Pemadam  Kebakaran Penerbangan', NULL, NULL, NULL, 1, '2025-11-18 03:55:35', '2025-12-01 03:44:55'),
('4', 'Pelatihan', 'Program Diklat', NULL, NULL, NULL, 1, '2025-11-18 03:55:35', '2025-12-08 07:03:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mr_reminder_schedule`
--

DROP TABLE IF EXISTS `mr_reminder_schedule`;
CREATE TABLE `mr_reminder_schedule` (
  `id` varchar(20) NOT NULL,
  `modul_terkait` tinyint(1) DEFAULT NULL COMMENT '1=sertifikasi, 2=maintenance, 3=kalibrasi, 4=lisensi',
  `id_terkait` varchar(20) NOT NULL,
  `jenis_reminder` varchar(20) DEFAULT NULL COMMENT '''30 hari'',''15 hari'',''7 hari'',''1 hari'',''expired''',
  `tanggal_reminder` date NOT NULL,
  `status` tinyint(1) DEFAULT 0 COMMENT '''pending'',''sent'',''cancelled''',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mr_reminder_schedule`
--

INSERT INTO `mr_reminder_schedule` (`id`, `modul_terkait`, `id_terkait`, `jenis_reminder`, `tanggal_reminder`, `status`, `created_at`) VALUES
('REM001', 1, 'SERT001', '30 hari', '2025-12-01', 0, '2025-11-14 07:23:46'),
('REM002', 1, 'SERT001', '15 hari', '2025-12-15', 0, '2025-11-14 07:23:46'),
('REM003', 1, 'SERT002', '7 hari', '2025-11-20', 1, '2025-11-14 07:23:46'),
('REM004', 1, 'SERT002', 'expired', '2025-10-01', 1, '2025-11-14 07:23:46'),
('REM005', 2, 'MT0001', '30 hari', '2025-11-30', 0, '2025-11-14 07:23:46'),
('REM006', 2, 'MT0002', '1 hari', '2025-11-10', 1, '2025-11-14 07:23:46'),
('REM007', 2, 'MT0003', '7 hari', '2025-11-23', 0, '2025-11-14 07:23:46'),
('REM008', 3, 'MT0004', '30 hari', '2025-12-05', 0, '2025-11-14 07:23:46'),
('REM009', 3, 'MT0005', '7 hari', '2025-11-15', 1, '2025-11-14 07:23:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mr_ruangan`
--

DROP TABLE IF EXISTS `mr_ruangan`;
CREATE TABLE `mr_ruangan` (
  `id` varchar(20) NOT NULL,
  `prodi_id` varchar(20) NOT NULL,
  `kode_ruangan` varchar(20) DEFAULT NULL,
  `nama_ruangan` varchar(150) DEFAULT NULL,
  `kapasitas` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(225) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mr_ruangan`
--

INSERT INTO `mr_ruangan` (`id`, `prodi_id`, `kode_ruangan`, `nama_ruangan`, `kapasitas`, `deskripsi`, `foto`, `created_at`, `updated_at`) VALUES
('R001', '1', 'ALS-01', 'Simulator ALS', 30, 'Ruangan simulator untuk latihan prosedur ALS.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:47'),
('R002', '1', 'KOM-01', 'Lab. Kompoter', 25, 'Laboratorium komputer untuk kegiatan praktik mahasiswa.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:47'),
('R003', '2', 'BHS-01', 'Lab. Bahasa', 38, 'Laboratorium bahasa untuk pelatihan komunikasi penerbangan.', NULL, '2025-11-18 04:46:40', '2025-12-12 03:27:30'),
('R004', '2', 'FOO-01', 'Lab. FOO', 35, 'Lab khusus praktik Flight Operation Officer.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:15:47'),
('R005', '2', 'XRAY-01', 'Lab. X-Ray', 20, 'Lab untuk pelatihan X-Ray dan keamanan penerbangan.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:15:47'),
('R006', '4', 'DRT-01', 'Lab. Door Trainer', 30, 'Ruangan latihan pembukaan pintu darurat pesawat.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:47'),
('R007', '2', 'CBT-01', 'Lab. CBT AVSEC', 25, 'Ruangan CBT untuk pelatihan keamanan penerbangan.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:15:09'),
('R008', '3', 'SMK-01', 'Lab. Smoke Chamber', 20, 'Area pelatihan smoke chamber untuk pemadam kebakaran.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:47'),
('R009', '3', 'FW-01', 'Fix Wing', 50, 'Area latihan teknik pemadaman berbasis fixed wing.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:47'),
('R010', '3', 'HUET-01', 'Kolam Latihan & HUET', 60, 'Kolam latihan penggunaan HUET dan water rescue.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:47'),
('R011', '3', 'FTDS-01', 'FTDS', 30, 'Flight Training Device Simulator.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:47'),
('R012', '3', 'FTDS-02', 'Komputer FTDS', 20, 'Lab komputer pendukung simulator FTDS.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:47'),
('R013', '1', 'BTN-01', 'Lab. Beton', 25, 'Laboratorium beton untuk program TRBU.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:47'),
('R014', '1', 'ASP-01', 'Lab. Aspal', 25, 'Laboratorium aspal untuk keperluan uji material.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:47'),
('R015', '1', 'TNH-01', 'Lab. Tanah', 25, 'Laboratorium tanah untuk pengujian geoteknik.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:47'),
('R016', '1', 'CBT-ALS-01', 'CBT ALS', 20, 'Ruangan CBT untuk pelatihan ALS.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:47'),
('R017', '1', 'EL-01', 'Lab. Elektro', 30, 'Laboratorium elektro untuk praktik kelistrikan bandara.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:47'),
('R018', '1', 'GWS-01', 'GWS TRBU', 35, 'Ground Warning System untuk program TRBU.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:47'),
('R019', '4', 'ATC-01', 'Lab. ATC', 25, 'Laboratorium simulasi Air Traffic Control.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:47'),
('R020', '3', 'BFT-01', 'Building Fire Trainer', 40, 'Fasilitas pelatihan pemadaman gedung.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:48'),
('R021', '3', 'WSP-01', 'Workshop PKP-PK', 30, 'Workshop peralatan pemadam PKP-PK.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:48'),
('R022', '3', 'KND-01', 'Kendaraan PKP-PK', 20, 'Area penyimpanan dan perawatan kendaraan PKP-PK.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:48'),
('R023', '3', 'ARFF-01', 'ARFF Trainer', 50, 'Trainer ARFF untuk pelatihan pemadam kebakaran bandara.', NULL, '2025-11-18 04:46:40', '2025-12-08 04:20:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mr_sdm`
--

DROP TABLE IF EXISTS `mr_sdm`;
CREATE TABLE `mr_sdm` (
  `id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `prodi_id` varchar(20) DEFAULT NULL,
  `jenis_kelamin` char(2) DEFAULT NULL COMMENT '0=Pria, 1=Wanita',
  `tanggal_lahir` date DEFAULT NULL,
  `pendidikan_terakhir` varchar(50) DEFAULT NULL,
  `bidang_studi` text DEFAULT NULL,
  `klasifikasi` tinyint(1) DEFAULT NULL COMMENT '0=asisten ahli, 1-lektor, 2=lektor kepala, 3=guru besar, 4=pranata ruang',
  `kategori_pengajar` tinyint(1) DEFAULT NULL COMMENT '0=basic, 1=junior, 2=senior',
  `status` tinyint(1) DEFAULT 1 COMMENT '1=active'',2=inactive''',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mr_sdm`
--

INSERT INTO `mr_sdm` (`id`, `user_id`, `prodi_id`, `jenis_kelamin`, `tanggal_lahir`, `pendidikan_terakhir`, `bidang_studi`, `klasifikasi`, `kategori_pengajar`, `status`, `created_at`, `updated_at`) VALUES
('SDM001', 'USR001', '3', '0', '1978-10-25', 'S3', NULL, 1, 2, 1, '2025-11-26 12:29:59', '2025-12-01 04:00:39'),
('SDM002', 'USR002', '3', '0', '1968-10-11', 'S2', NULL, 1, 2, 1, '2025-11-26 12:29:59', '2025-12-01 04:00:42'),
('SDM003', 'USR003', '3', '0', '1989-01-21', 'S2', NULL, 1, 2, 1, '2025-11-26 12:29:59', '2025-12-01 04:00:46'),
('SDM004', 'USR004', '3', '1', '1987-05-25', 'S3', NULL, 2, 0, 1, '2025-11-26 12:29:59', '2025-12-01 04:00:49'),
('SDM005', 'USR005', '3', '1', '1983-07-19', 'S3', NULL, 1, NULL, 1, '2025-11-26 12:29:59', '2025-12-01 04:00:52'),
('SDM006', 'USR006', '3', '1', '1986-07-03', 'S2', NULL, 0, 0, 1, '2025-11-26 12:29:59', '2025-12-01 04:00:56'),
('SDM007', 'USR007', '2', '0', '1976-06-12', 'S2', '1. Kebandarudaraan  \r\n2. Aerodrome, heliport and waterbase  \r\n3. Aerodrome reporting Procedure  \r\n4. Marshalling  \r\n5. Radiotelephony  \r\n6. ATS  \r\n7. Operasi Bandar Udara', 1, 0, 1, '2025-11-26 13:01:11', '2025-12-01 04:07:33'),
('SDM008', 'USR008', '2', '0', '1970-02-03', 'S3', 'Ramp and Aircraft Handling', 1, NULL, 1, '2025-11-26 13:01:11', '2025-12-01 04:07:38'),
('SDM009', 'USR009', '2', '0', '1980-03-05', 'S3', 'Basic Cargo', 1, NULL, 1, '2025-11-26 13:01:11', '2025-12-01 04:07:41'),
('SDM010', 'USR010', '2', '0', '1972-09-08', 'S2', 'Manajemen Logistik', 0, NULL, 1, '2025-11-26 13:01:11', '2025-12-01 04:07:46'),
('SDM011', 'USR011', '2', '1', '1983-02-07', 'S2', '1. Bahasa Indonesia  \r\n2. Manajemen SDM  \r\n3. Pancasila  \r\n4. Kewarganegaraan', 1, NULL, 1, '2025-11-26 13:01:11', '2025-12-01 04:07:52'),
('SDM012', 'USR012', '2', '0', '1984-05-13', 'S2', '1. Bahasa Inggris Penerbangan  \r\n2. IELP', 1, NULL, 1, '2025-11-26 13:01:11', '2025-12-01 04:07:57'),
('SDM013', 'USR013', '2', '0', '1988-03-08', 'S2', '1. Statistika  \r\n2. Metode Penelitian', 1, NULL, 1, '2025-11-26 13:01:11', '2025-12-01 04:08:15'),
('SDM014', 'USR014', '2', '1', '1978-11-18', 'S2', '1. Pancasila  \r\n2. Kewarganegaraan', 0, NULL, 1, '2025-11-26 13:01:11', '2025-12-01 04:08:34'),
('SDM015', 'USR015', '2', '1', '1990-12-10', 'S2', '1. Radiotelephony  \r\n2. ATS  \r\n3. Rules and Regulation', 1, NULL, 1, '2025-11-26 13:01:11', '2025-12-01 04:08:40'),
('SDM016', 'USR016', '1', '0', '1972-02-17', 'S3 FISIKA', 'Rekayasa Pengembangan Bandar Udara, Rangkaian Listrik, Desain Sistem Catu daya dan Jaringan Listrik Bandara, Teknik Pemeliharaan Prasarana Bandara, Teknik Pengukuran', 1, 2, 1, '2025-11-27 12:08:26', '2025-12-01 04:13:48'),
('SDM017', 'USR017', '1', '0', '1960-11-27', 'S3 TEKNOLOGI PENDIDIKAN,PASCASARJANA', 'Sistem Mekanikal Bandara, Teknologi Mekanik, Perawatan Alat-Alat Besar, Water and Pump System, Hidrolik Pneumatic', 1, NULL, 1, '2025-11-27 12:08:26', '2025-12-01 04:13:53'),
('SDM018', 'USR018', '1', '0', '1975-06-21', 'S2 MANAJEMEN PENDIDIKAN', 'Analisa Sistem Tenaga Listrik, Genset dan ACOS, Sistem Alat Bantu Pendaratan Visual dan CCR, Teknik Pendingin dan Tata Udara, Mesin Listrik, Teknik Listrik, Generator Set dan ACOS, Desain Sistem Alat Bantu Pendaratan Visual, Airport Automation, UPS dan Sollarcell', 1, NULL, 1, '2025-11-27 12:08:26', '2025-12-01 04:13:56'),
('SDM019', 'USR019', '1', '0', '1986-10-08', 'S2 SISTEM DAN TEKNIK TRANSPORTASI', 'Konstruksi Perkerasan, Struktur Beton, Rekayasa Pengembangan Bandar Udara, Rekayasa Mekanika Tanah dan Teknik Pondasi, Program Analisa Struktur, Rekayasa Pengembangan Bandara, Struktur Baja, Drainase dan Pengendalian Banjir', 1, NULL, 1, '2025-11-27 12:08:26', '2025-12-01 04:14:00'),
('SDM020', 'USR020', '1', '1', '1983-12-13', 'S2 AVIATION SAFETY MANAGEMENT', 'Manajemen Pemeliharaan, Bahasa Indonesia, Dasar Teknologi Informasi, SMK3L, Teknologi Informasi, Metode Penelitian, Mekatronika, Menggambar Teknik, Undang-Undang Penerbangan dan Regulasi Internaional', 1, NULL, 1, '2025-11-27 12:08:26', '2025-12-01 04:14:06'),
('SDM021', 'USR021', '1', '1', '1983-07-25', 'S2 ILMU ADMINISTRASI', 'Analisa Sistem Tenaga Listrik, Pancasila, Kendali Mutu dan Jaminan', 1, NULL, 1, '2025-11-27 12:08:26', '2025-12-01 04:14:10'),
('SDM022', 'USR022', '1', '0', '1981-03-06', 'S2 ILMU ADMINISTRASI', 'Sistem Mekanikal Bandara, Teknik Pendingin dan Tata Udara, Aerodrome, Undang-undang Penerbangan dan Regulasi Internasional, Metode Elemen Hingga, Perawatan GSE dan Peralatan Bandara, Thermodinamika', 0, NULL, 1, '2025-11-27 12:08:26', '2025-12-01 04:14:15'),
('SDM023', 'USR023', '2', '0', '1970-02-03', 'S3', 'MBU, Pelatihan', NULL, 0, 1, '2025-11-27 19:29:30', '2025-12-01 04:48:32'),
('SDM024', 'USR023', '4', '0', '1970-02-03', 'S3', 'MBU, Pelatihan', NULL, 0, 1, '2025-11-27 19:29:30', '2025-12-01 04:47:19'),
('SDM025', 'USR024', '1', '0', '1980-05-31', 'S2', 'TRBU', NULL, 1, 1, '2025-11-27 19:30:53', '2025-12-01 04:49:24'),
('SDM026', 'USR025', '1', '0', '1982-06-19', 'S2', 'TRBU, MBU, Pelatihan, PPKP', NULL, NULL, 1, '2025-11-27 19:31:38', '2025-12-01 04:49:50'),
('SDM027', 'USR025', '2', '0', '1982-06-19', 'S2', 'TRBU, MBU, Pelatihan, PPKP', NULL, NULL, 1, '2025-11-27 19:31:38', '2025-12-01 04:49:55'),
('SDM028', 'USR025', '3', '0', '1982-06-19', 'S2', 'TRBU, MBU, Pelatihan, PPKP', NULL, NULL, 1, '2025-11-27 19:31:38', '2025-12-01 04:49:59'),
('SDM029', 'USR025', '4', '0', '1982-06-19', 'S2', 'TRBU, MBU, Pelatihan, PPKP', NULL, NULL, 1, '2025-11-27 19:31:38', '2025-12-01 04:50:01'),
('SDM030', 'USR026', '2', '0', '1972-09-08', 'S2', 'MBU', NULL, NULL, 1, '2025-11-27 19:32:48', '2025-12-01 04:50:13'),
('SDM031', 'USR027', '2', '0', '1960-09-01', 'S2', 'MBU', NULL, NULL, 1, '2025-11-27 19:32:48', '2025-12-01 04:50:16'),
('SDM032', 'USR028', '1', '0', '1960-11-27', 'S-3', NULL, NULL, NULL, 1, '2025-11-27 19:39:02', '2025-12-02 04:48:34'),
('SDM033', 'USR028', '4', '0', '1960-11-27', 'S-3', NULL, NULL, NULL, 1, '2025-11-27 19:39:02', '2025-12-02 04:48:34'),
('SDM034', 'USR029', '2', '0', '1980-03-05', 'S-3', NULL, NULL, NULL, 1, '2025-11-27 19:39:02', '2025-12-02 04:48:34'),
('SDM035', 'USR030', '3', '0', '1978-10-25', 'S-3', NULL, NULL, NULL, 1, '2025-11-27 19:39:02', '2025-12-02 04:48:34'),
('SDM036', 'USR030', '4', '0', '1978-10-25', 'S-3', NULL, NULL, NULL, 1, '2025-11-27 19:39:02', '2025-12-02 04:48:34'),
('SDM037', 'USR031', '3', '0', '1968-10-11', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:39:02', '2025-12-02 04:48:34'),
('SDM038', 'USR031', '4', '0', '1968-10-11', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:39:02', '2025-12-02 04:48:34'),
('SDM039', 'USR032', '1', '0', '1975-06-21', 'S-2', 'Instructor Development Program | ICAO TIC Part 2 | PEKERTI/AA | TOT PKP-PK | Fire Investigation | PLC | Airport Operation | SMS | ARFF', NULL, NULL, 1, '2025-11-27 19:39:02', '2025-12-02 04:48:34'),
('SDM040', 'USR032', '3', '0', '1975-06-21', 'S-2', 'Instructor Development Program | ICAO TIC Part 2 | PEKERTI/AA | TOT PKP-PK | Fire Investigation | PLC | Airport Operation | SMS | ARFF', NULL, NULL, 1, '2025-11-27 19:39:02', '2025-12-02 04:48:34'),
('SDM041', 'USR032', '4', '0', '1975-06-21', 'S-2', 'Instructor Development Program | ICAO TIC Part 2 | PEKERTI/AA | TOT PKP-PK | Fire Investigation | PLC | Airport Operation | SMS | ARFF', NULL, NULL, 1, '2025-11-27 19:39:02', '2025-12-02 04:48:34'),
('SDM042', 'USR033', '1', '0', '1982-11-07', 'S-2', 'Multimoda Transport | PEKERTI | Arduino | Human Factor', NULL, NULL, 1, '2025-11-27 19:39:02', '2025-12-02 04:48:34'),
('SDM043', 'USR033', '4', '0', '1982-11-07', 'S-2', 'Multimoda Transport | PEKERTI | Arduino | Human Factor', NULL, NULL, 1, '2025-11-27 19:39:02', '2025-12-02 04:48:34'),
('SDM044', 'USR034', '3', '1', '1978-11-18', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:43:00', '2025-12-02 04:48:34'),
('SDM045', 'USR034', '4', '1', '1978-11-18', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:43:00', '2025-12-02 04:48:34'),
('SDM046', 'USR035', '1', '0', '1981-03-06', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:43:00', '2025-12-02 04:48:34'),
('SDM047', 'USR035', '4', '0', '1981-03-06', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:43:00', '2025-12-02 04:48:34'),
('SDM048', 'USR036', '2', '1', '1971-06-23', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:43:00', '2025-12-02 04:48:34'),
('SDM049', 'USR037', '3', '0', '1973-04-30', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:43:00', '2025-12-02 04:48:34'),
('SDM050', 'USR037', '4', '0', '1973-04-30', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:43:00', '2025-12-02 04:48:34'),
('SDM051', 'USR038', '1', '0', '1972-02-17', 'S-3', NULL, NULL, NULL, 1, '2025-11-27 19:43:00', '2025-12-02 04:48:34'),
('SDM052', 'USR038', '4', '0', '1972-02-17', 'S-3', NULL, NULL, NULL, 1, '2025-11-27 19:43:00', '2025-12-02 04:48:34'),
('SDM053', 'USR039', '4', '0', '1981-10-02', 'S-3', NULL, NULL, NULL, 1, '2025-11-27 19:44:28', '2025-12-02 04:48:34'),
('SDM054', 'USR039', '2', '0', '1981-10-02', 'S-3', NULL, NULL, NULL, 1, '2025-11-27 19:44:28', '2025-12-02 04:48:34'),
('SDM055', 'USR040', '3', '1', '1987-05-25', 'S-3', NULL, NULL, NULL, 1, '2025-11-27 19:44:28', '2025-12-02 04:48:34'),
('SDM056', 'USR041', '4', '1', '1981-02-26', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 19:44:28', '2025-12-02 04:48:34'),
('SDM057', 'USR041', '3', '1', '1981-02-26', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 19:44:28', '2025-12-02 04:48:34'),
('SDM058', 'USR042', '4', '0', '1985-07-14', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 19:44:28', '2025-12-02 04:48:34'),
('SDM059', 'USR042', '3', '0', '1985-07-14', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 19:44:28', '2025-12-02 04:48:34'),
('SDM060', 'USR042', '2', '0', '1985-07-14', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 19:44:28', '2025-12-02 04:48:34'),
('SDM061', 'USR043', '3', '1', '1983-07-19', 'S-3', NULL, NULL, NULL, 1, '2025-11-27 19:44:28', '2025-12-02 04:48:34'),
('SDM062', 'USR043', '4', '1', '1983-07-19', 'S-3', NULL, NULL, NULL, 1, '2025-11-27 19:44:28', '2025-12-02 04:48:34'),
('SDM063', 'USR044', '1', '0', '1986-10-08', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:44:28', '2025-12-02 04:48:34'),
('SDM064', 'USR044', '4', '0', '1986-10-08', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:44:28', '2025-12-02 04:48:34'),
('SDM065', 'USR045', '2', '1', '1983-02-07', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:44:28', '2025-12-02 04:48:34'),
('SDM066', 'USR045', '4', '1', '1983-02-07', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:44:28', '2025-12-02 04:48:34'),
('SDM067', 'USR046', '2', '1', '1983-07-25', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:44:28', '2025-12-02 04:48:34'),
('SDM068', 'USR047', '1', '1', '1984-08-20', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM069', 'USR047', '4', '1', '1984-08-20', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM070', 'USR048', '1', '1', '1983-12-13', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM071', 'USR048', '4', '1', '1983-12-13', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM072', 'USR049', '3', '0', '1989-01-21', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM073', 'USR049', '4', '0', '1989-01-21', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM074', 'USR050', '1', '0', '1981-10-05', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM075', 'USR050', '4', '0', '1981-10-05', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM076', 'USR051', '4', '0', '1993-05-22', NULL, NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM077', 'USR051', '3', '0', '1993-05-22', NULL, NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM078', 'USR052', '2', '0', '1984-05-13', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM079', 'USR052', '4', '0', '1984-05-13', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM080', 'USR053', '4', '0', '1987-02-05', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM081', 'USR054', '3', '0', '1984-11-17', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM082', 'USR054', '4', '0', '1984-11-17', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM083', 'USR055', '2', '0', '1984-06-29', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM084', 'USR055', '4', '0', '1984-06-29', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM085', 'USR056', '1', '1', '1985-09-18', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM086', 'USR056', '4', '1', '1985-09-18', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM087', 'USR057', '2', '0', '1989-07-02', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM088', 'USR057', '4', '0', '1989-07-02', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM089', 'USR058', '2', '0', '1988-04-08', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM090', 'USR058', '4', '0', '1988-04-08', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM091', 'USR059', '3', '0', '1988-03-08', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM092', 'USR059', '1', '0', '1988-03-08', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM093', 'USR059', '4', '0', '1988-03-08', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:45:15', '2025-12-02 04:48:34'),
('SDM094', 'USR060', '3', '1', '1986-07-03', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM095', 'USR060', '4', '1', '1986-07-03', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM096', 'USR061', '3', '0', '1987-06-10', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM097', 'USR061', '4', '0', '1987-06-10', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM098', 'USR062', '4', '0', '1985-04-13', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM099', 'USR063', '1', '0', '1983-11-29', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM100', 'USR063', '4', '0', '1983-11-29', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM101', 'USR064', '4', '0', '2001-04-16', 'D-IV', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM102', 'USR065', '4', '0', '2000-01-10', 'D-IV', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM103', 'USR066', '4', '0', '2000-05-25', 'D-IV', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM104', 'USR067', '4', '1', '1999-04-16', 'D-IV', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM105', 'USR068', '4', '0', '2001-12-07', 'D-IV', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM106', 'USR069', '4', '1', '2001-02-15', 'D-IV', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM107', 'USR070', '3', '0', '1991-03-12', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM108', 'USR070', '4', '0', '1991-03-12', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM109', 'USR071', '3', '0', '1996-06-20', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM110', 'USR071', '4', '0', '1996-06-20', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM111', 'USR072', '4', '1', '1999-12-19', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM112', 'USR072', '1', '1', '1999-12-19', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM113', 'USR073', '2', '1', '2000-05-17', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM114', 'USR073', '4', '1', '2000-05-17', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 19:56:01', '2025-12-02 04:48:34'),
('SDM115', 'USR074', '1', '1', '2000-07-04', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-12 08:29:28'),
('SDM116', 'USR074', '4', '1', '2000-07-04', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-02 04:48:34'),
('SDM117', 'USR075', '2', '1', '2000-12-12', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-02 04:48:34'),
('SDM118', 'USR075', '4', '1', '2000-12-12', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-02 04:48:34'),
('SDM119', 'USR076', '1', '1', '1997-11-17', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-02 04:48:34'),
('SDM120', 'USR076', '4', '1', '1997-11-17', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-02 04:48:34'),
('SDM121', 'USR077', '2', '0', '1999-05-04', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-02 04:48:34'),
('SDM122', 'USR077', '4', '0', '1999-05-04', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-02 04:48:34'),
('SDM123', 'USR078', '3', '0', '2001-03-29', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-02 04:48:34'),
('SDM124', 'USR078', '4', '0', '2001-03-29', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-02 04:48:34'),
('SDM125', 'USR079', '3', '0', '2001-02-28', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-02 04:48:34'),
('SDM126', 'USR079', '4', '0', '2001-02-28', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-02 04:48:34'),
('SDM127', 'USR080', '4', '1', '2001-04-13', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-02 04:48:34'),
('SDM128', 'USR081', '4', '1', '2003-01-07', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-02 04:48:34'),
('SDM129', 'USR082', '4', '0', '1984-04-02', 'S-2', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-02 04:48:34'),
('SDM130', 'USR083', '4', '0', '1996-11-22', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 20:04:47', '2025-12-02 04:48:34'),
('SDM135', 'USR086', '4', '1', '1998-12-10', 'D-IV', NULL, NULL, NULL, 1, '2025-11-27 20:06:50', '2025-12-02 04:48:34'),
('SDM136', 'USR087', '4', '0', '1995-04-18', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 20:06:50', '2025-12-02 04:48:34'),
('SDM137', 'USR088', '4', '0', '1999-10-15', 'D-III', NULL, NULL, NULL, 1, '2025-11-27 20:06:50', '2025-12-02 04:48:34'),
('SDM138', 'USR089', '4', '0', '1992-03-19', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 20:06:50', '2025-12-02 04:48:34'),
('SDM139', 'USR090', '4', '1', '1983-12-31', 'SMA', NULL, NULL, NULL, 1, '2025-11-27 20:06:50', '2025-12-12 08:28:27'),
('SDM140', 'USR153', '4', '0', '1992-07-03', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 20:06:50', '2025-12-15 03:19:09'),
('SDM141', 'USR155', '4', '0', '1992-07-03', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 20:06:50', '2025-12-15 03:19:09'),
('SDM142', 'USR155', '3', '0', '1992-07-03', 'S-1', NULL, NULL, NULL, 1, '2025-11-27 20:06:50', '2025-12-16 08:07:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mr_sertifikasi`
--

DROP TABLE IF EXISTS `mr_sertifikasi`;
CREATE TABLE `mr_sertifikasi` (
  `id` varchar(20) NOT NULL,
  `sdm_id` varchar(20) NOT NULL,
  `nama_sertifikat` varchar(200) NOT NULL,
  `institusi` varchar(100) DEFAULT NULL,
  `no_sertifikat` varchar(100) DEFAULT NULL,
  `tanggal_terbit` date DEFAULT NULL,
  `tanggal_expiry` date DEFAULT NULL,
  `file_sertifikat` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL COMMENT '1=active,2=expired,3=warning',
  `reminder_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mr_sertifikasi`
--

INSERT INTO `mr_sertifikasi` (`id`, `sdm_id`, `nama_sertifikat`, `institusi`, `no_sertifikat`, `tanggal_terbit`, `tanggal_expiry`, `file_sertifikat`, `status`, `reminder_sent`, `created_at`) VALUES
('SRT001', 'SDM001', 'Training of Trainer PKP-PK', 'Lembaga PKP-PK', 'SRT001-2023-001', '2023-01-01', '2026-01-10', 'SRT001.pdf', 1, 0, '2025-12-04 01:45:31'),
('SRT002', 'SDM001', 'Advanced Firefighting', 'Lembaga Fire Safety', 'SRT002-2023-002', '2023-02-01', '2026-02-01', 'SRT002.pdf', 1, 0, '2025-12-04 01:45:31'),
('SRT003', 'SDM001', 'Sertifikat Instruktur Penerbangan', 'Lembaga Penerbangan', 'SRT003-2023-003', '2023-03-01', '2026-03-01', 'SRT003.pdf', 1, 0, '2025-12-04 01:45:31'),
('SRT004', 'SDM002', 'Basic PKP-PK', 'Lembaga PKP-PK', 'SRT004-2023-004', '2023-04-01', '2026-04-01', 'SRT004.pdf', 1, 0, '2025-12-04 01:46:53'),
('SRT005', 'SDM002', 'Junior PKP-PK', 'Lembaga PKP-PK', 'SRT005-2023-005', '2023-05-01', '2026-05-01', 'SRT005.pdf', 1, 0, '2025-12-04 01:46:53'),
('SRT006', 'SDM002', 'Senior PKP-PK', 'Lembaga PKP-PK', 'SRT006-2023-006', '2023-06-01', '2026-06-01', 'SRT006.pdf', 1, 0, '2025-12-04 01:46:53'),
('SRT007', 'SDM002', 'Advanced Fire Fighting', 'Lembaga Fire Safety', 'SRT007-2023-007', '2023-07-01', '2026-07-01', 'SRT007.pdf', 1, 0, '2025-12-04 01:46:53'),
('SRT008', 'SDM003', 'Advanced Fire Fighting', 'Lembaga Fire Safety', 'SRT008-2023-008', '2023-08-01', '2026-08-01', 'SRT008.pdf', 1, 0, '2025-12-04 01:47:28'),
('SRT009', 'SDM003', 'Basic PKP-PK', 'Lembaga PKP-PK', 'SRT009-2023-009', '2023-09-01', '2026-09-01', 'SRT009.pdf', 1, 0, '2025-12-04 01:47:28'),
('SRT010', 'SDM003', 'Junior PKP-PK', 'Lembaga PKP-PK', 'SRT010-2023-010', '2023-10-01', '2026-10-01', 'SRT010.pdf', 1, 0, '2025-12-04 01:47:28'),
('SRT011', 'SDM003', 'Senior PKP-PK', 'Lembaga PKP-PK', 'SRT011-2023-011', '2023-11-01', '2026-11-01', 'SRT011.pdf', 1, 0, '2025-12-04 01:47:28'),
('SRT012', 'SDM003', 'Sertifikat Instruktur Penerbangan', 'Lembaga Penerbangan', 'SRT012-2023-012', '2023-12-01', '2026-12-01', 'SRT012.pdf', 1, 0, '2025-12-04 01:47:28'),
('SRT013', 'SDM003', 'PEKERTI', 'Lembaga PEKERTI', 'SRT013-2024-013', '2024-01-01', '2027-01-01', 'SRT013.pdf', 1, 0, '2025-12-04 01:47:28'),
('SRT014', 'SDM003', 'Applied Approach', 'Lembaga Applied Approach', 'SRT014-2024-014', '2024-02-01', '2027-02-01', 'SRT014.pdf', 1, 0, '2025-12-04 01:47:28'),
('SRT015', 'SDM003', 'Certified Proffesional Risk Management (CPRM)', 'Lembaga Risk Management', 'SRT015-2024-015', '2024-03-01', '2027-03-01', 'SRT015.pdf', 1, 0, '2025-12-04 01:47:28'),
('SRT016', 'SDM003', 'Aviation Safety Fundamental - IATA', 'IATA', 'SRT016-2024-016', '2024-04-01', '2027-04-01', 'SRT016.pdf', 1, 0, '2025-12-04 01:47:28'),
('SRT017', 'SDM003', 'Human Factors and Safety Management Fundamentals - IATA', 'IATA', 'SRT017-2024-017', '2024-05-01', '2027-05-01', 'SRT017.pdf', 1, 0, '2025-12-04 01:47:28'),
('SRT018', 'SDM003', 'Safety Management - ICAO', 'ICAO', 'SRT018-2024-018', '2024-06-01', '2027-06-01', 'SRT018.pdf', 1, 0, '2025-12-04 01:47:28'),
('SRT019', 'SDM003', 'Sertifikasi Dosen', 'Lembaga Pendidikan', 'SRT019-2024-019', '2024-07-01', '2027-07-01', 'SRT019.pdf', 1, 0, '2025-12-04 01:47:28'),
('SRT020', 'SDM003', 'Training Instructor Course ICAO', 'ICAO', 'SRT020-2024-020', '2024-08-01', '2027-08-01', 'SRT020.pdf', 1, 0, '2025-12-04 01:47:28'),
('SRT021', 'SDM004', 'Diklat General Instructor', 'Lembaga Diklat Umum', 'SRT021-2024-021', '2024-09-01', '2027-09-01', 'SRT021.pdf', 1, 0, '2025-12-04 01:48:01'),
('SRT022', 'SDM004', 'Diklat Keudaraan', 'Lembaga Diklat Keudaraan', 'SRT022-2024-022', '2024-10-01', '2027-10-01', 'SRT022.pdf', 1, 0, '2025-12-04 01:48:01'),
('SRT023', 'SDM004', 'Basic PKP-PK', 'Lembaga PKP-PK', 'SRT023-2024-023', '2024-11-01', '2027-11-01', 'SRT023.pdf', 1, 0, '2025-12-04 01:48:01'),
('SRT024', 'SDM004', 'Safety Manajemen System (SMS IATA)', 'IATA', 'SRT024-2024-024', '2024-12-01', '2027-12-01', 'SRT024.pdf', 1, 0, '2025-12-04 01:48:01'),
('SRT025', 'SDM004', 'Human Factor IATA', 'IATA', 'SRT025-2025-025', '2025-01-01', '2028-01-01', 'SRT025.pdf', 1, 0, '2025-12-04 01:48:01'),
('SRT026', 'SDM004', 'Training Of Trainer 609', 'Lembaga TOT 609', 'SRT026-2025-026', '2025-02-01', '2028-02-01', 'SRT026.pdf', 1, 0, '2025-12-04 01:48:01'),
('SRT027', 'SDM004', 'Training Of Examination 312', 'Lembaga TOT 312', 'SRT027-2025-027', '2025-03-01', '2028-03-01', 'SRT027.pdf', 1, 0, '2025-12-04 01:48:01'),
('SRT028', 'SDM004', 'Diklat Pekerti', 'Lembaga Pekerti', 'SRT028-2025-028', '2025-04-01', '2028-04-01', 'SRT028.pdf', 1, 0, '2025-12-04 01:48:01'),
('SRT029', 'SDM004', 'Diklat Applied Approach', 'Lembaga Applied Approach', 'SRT029-2025-029', '2025-05-01', '2028-05-01', 'SRT029.pdf', 1, 0, '2025-12-04 01:48:01'),
('SRT030', 'SDM004', 'Basic Safety Training', 'Lembaga Safety Training', 'SRT030-2025-030', '2025-06-01', '2028-06-01', 'SRT030.pdf', 1, 0, '2025-12-04 01:48:01'),
('SRT031', 'SDM004', 'Assesor Kompetensi BNSP', 'BNSP', 'SRT031-2025-031', '2025-07-01', '2028-07-01', 'SRT031.pdf', 1, 0, '2025-12-04 01:48:01'),
('SRT032', 'SDM004', 'Sertifikasi Dosen', 'Lembaga Pendidikan', 'SRT032-2025-032', '2025-08-01', '2028-08-01', 'SRT032.pdf', 1, 0, '2025-12-04 01:48:01'),
('SRT033', 'SDM005', 'Human Factors', 'Lembaga Human Factors', 'SRT033-2025-033', '2025-09-01', '2028-09-01', 'SRT033.pdf', 1, 0, '2025-12-04 01:48:39'),
('SRT034', 'SDM005', 'Safety Management System', 'Lembaga SMS', 'SRT034-2025-034', '2025-10-01', '2028-10-01', 'SRT034.pdf', 1, 0, '2025-12-04 01:48:39'),
('SRT035', 'SDM005', 'Pekerti', 'Lembaga Pekerti', 'SRT035-2025-035', '2025-11-01', '2028-11-01', 'SRT035.pdf', 1, 0, '2025-12-04 01:48:39'),
('SRT036', 'SDM006', 'General Instructor Course', 'Lembaga Instructor', 'SRT036-2025-036', '2025-12-01', '2028-12-01', 'SRT036.pdf', 1, 0, '2025-12-04 01:48:49'),
('SRT037', 'SDM006', 'PEKERTI', 'Lembaga PEKERTI', 'SRT037-2026-037', '2026-01-01', '2029-01-01', 'SRT037.pdf', 1, 0, '2025-12-04 01:48:49'),
('SRT038', 'SDM006', 'Pelatihan SMS', 'Lembaga SMS', 'SRT038-2026-038', '2026-02-01', '2029-02-01', 'SRT038.pdf', 1, 0, '2025-12-04 01:48:49'),
('SRT039', 'SDM006', 'Pelatihan Human Factor', 'Lembaga Human Factor', 'SRT039-2026-039', '2026-03-01', '2029-03-01', 'SRT039.pdf', 1, 0, '2025-12-04 01:48:49'),
('SRT040', 'SDM006', 'Pelatihan DG', 'Lembaga DG', 'SRT040-2026-040', '2026-04-01', '2029-04-01', 'SRT040.pdf', 1, 0, '2025-12-04 01:48:49'),
('SRT041', 'SDM007', 'Air Traffic Controller', 'ATC', 'SRT041-2026-041', '2026-05-01', '2029-05-01', 'SRT041.pdf', 1, 0, '2025-12-04 01:50:00'),
('SRT042', 'SDM007', 'Kawasan Bandar Udara', 'Bandara', 'SRT042-2026-042', '2026-06-01', '2029-06-01', 'SRT042.pdf', 1, 0, '2025-12-04 01:50:00'),
('SRT043', 'SDM007', 'SMS', 'SMS', 'SRT043-2026-043', '2026-07-01', '2029-07-01', 'SRT043.pdf', 1, 0, '2025-12-04 01:50:00'),
('SRT044', 'SDM007', 'Ramp Safety Awareness', 'Ramp Safety', 'SRT044-2026-044', '2026-08-01', '2029-08-01', 'SRT044.pdf', 1, 0, '2025-12-04 01:50:00'),
('SRT045', 'SDM007', 'DG', 'DG', 'SRT045-2026-045', '2026-09-01', '2029-09-01', 'SRT045.pdf', 1, 0, '2025-12-04 01:50:00'),
('SRT046', 'SDM007', 'Aerodrome Inspector', 'Aerodrome Inspector', 'SRT046-2026-046', '2026-10-01', '2029-10-01', 'SRT046.pdf', 1, 0, '2025-12-04 01:50:00'),
('SRT047', 'SDM007', 'Human Factor', 'Human Factor', 'SRT047-2026-047', '2026-11-01', '2029-11-01', 'SRT047.pdf', 1, 0, '2025-12-04 01:50:00'),
('SRT048', 'SDM007', 'Aerodrome Certification', 'Aerodrome Certification', 'SRT048-2026-048', '2026-12-01', '2029-12-01', 'SRT048.pdf', 1, 0, '2025-12-04 01:50:00'),
('SRT049', 'SDM008', 'Commercial Pilot License', 'Pilot License', 'SRT049-2027-049', '2027-01-01', '2030-01-01', 'SRT049.pdf', 1, 0, '2025-12-04 01:50:25'),
('SRT050', 'SDM008', 'Company Aviation Safety Officer', 'Aviation Safety Officer', 'SRT050-2027-050', '2027-02-01', '2030-02-01', 'SRT050.pdf', 1, 0, '2025-12-04 01:50:25'),
('SRT051', 'SDM008', 'ATPL Course', 'ATPL Course', 'SRT051-2027-051', '2027-03-01', '2030-03-01', 'SRT051.pdf', 1, 0, '2025-12-04 01:50:25'),
('SRT052', 'SDM008', 'Aircraft Type Rating Boeing 737', 'Aircraft Type Rating', 'SRT052-2027-052', '2027-04-01', '2030-04-01', 'SRT052.pdf', 1, 0, '2025-12-04 01:50:25'),
('SRT053', 'SDM010', 'Perencanaan Transportasi', 'Transportasi', 'SRT053-2027-053', '2027-05-01', '2030-05-01', 'SRT053.pdf', 1, 0, '2025-12-04 01:51:44'),
('SRT054', 'SDM010', 'Basic General Soft Skill Training', 'Soft Skill Training', 'SRT054-2027-054', '2027-06-01', '2030-06-01', 'SRT054.pdf', 1, 0, '2025-12-04 01:51:44'),
('SRT055', 'SDM010', 'Auditor Kepelabuhanan', 'Auditor Kepelabuhanan', 'SRT055-2027-055', '2027-07-01', '2030-07-01', 'SRT055.pdf', 1, 0, '2025-12-04 01:51:44'),
('SRT056', 'SDM010', 'Management Of Training', 'Management Training', 'SRT056-2027-056', '2027-08-01', '2030-08-01', 'SRT056.pdf', 1, 0, '2025-12-04 01:51:44'),
('SRT057', 'SDM010', 'Human Factor', 'Human Factor', 'SRT057-2027-057', '2027-09-01', '2030-09-01', 'SRT057.pdf', 1, 0, '2025-12-04 01:51:44'),
('SRT058', 'SDM011', 'Keselamatan ASD', 'Keselamatan ASD', 'SRT058-2027-058', '2027-10-01', '2030-10-01', 'SRT058.pdf', 1, 0, '2025-12-04 01:54:25'),
('SRT059', 'SDM011', 'Training of Trainers', 'Training of Trainers', 'SRT059-2027-059', '2027-11-01', '2030-11-01', 'SRT059.pdf', 1, 0, '2025-12-04 01:54:25'),
('SRT060', 'SDM011', 'Diklat Keudaraan', 'Diklat Keudaraan', 'SRT060-2027-060', '2027-12-01', '2030-12-01', 'SRT060.pdf', 1, 0, '2025-12-04 01:54:25'),
('SRT061', 'SDM011', 'Flight Instructor', 'Flight Instructor', 'SRT061-2028-061', '2028-01-01', '2031-01-01', 'SRT061.pdf', 1, 0, '2025-12-04 01:54:25'),
('SRT062', 'SDM012', 'Enhancing TVET Personnel Professional Skills and Instructional Competencies', 'TVET', 'SRT062-2028-062', '2028-02-01', '2031-02-01', 'SRT062.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT063', 'SDM012', 'Trainair Plus Training Instructor Course', 'Trainair Plus', 'SRT063-2028-063', '2028-03-01', '2031-03-01', 'SRT063.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT064', 'SDM012', 'Diklat Bahasa Jerman', 'Bahasa Jerman', 'SRT064-2028-064', '2028-04-01', '2031-04-01', 'SRT064.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT065', 'SDM012', 'English for Special Purposes', 'English', 'SRT065-2028-065', '2028-05-01', '2031-05-01', 'SRT065.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT066', 'SDM012', 'Bahasa Prancis', 'Bahasa Prancis', 'SRT066-2028-066', '2028-06-01', '2031-06-01', 'SRT066.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT067', 'SDM012', 'Bahasa Inggris', 'Bahasa Inggris', 'SRT067-2028-067', '2028-07-01', '2031-07-01', 'SRT067.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT068', 'SDM012', 'Penerjemah FILBA', 'Penerjemah FILBA', 'SRT068-2028-068', '2028-08-01', '2031-08-01', 'SRT068.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT069', 'SDM012', 'Effective Learning Management', 'Learning Management', 'SRT069-2028-069', '2028-09-01', '2031-09-01', 'SRT069.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT070', 'SDM013', 'SPMI', 'SPMI', 'SRT070-2028-070', '2028-10-01', '2031-10-01', 'SRT070.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT071', 'SDM013', 'General Instructor Course', 'General Instructor', 'SRT071-2028-071', '2028-11-01', '2031-11-01', 'SRT071.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT072', 'SDM013', 'Human Factor', 'Human Factor', 'SRT072-2028-072', '2028-12-01', '2031-12-01', 'SRT072.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT073', 'SDM014', 'Human Factor', 'Human Factor', 'SRT073-2029-073', '2029-01-01', '2032-01-01', 'SRT073.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT074', 'SDM014', 'Safety Management System', 'Safety Management', 'SRT074-2029-074', '2029-02-01', '2032-02-01', 'SRT074.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT075', 'SDM014', 'Pelatihan Peningkatan Keterampilan Dasar Teknik Instruksional', 'Teknik Instruksional', 'SRT075-2029-075', '2029-03-01', '2032-03-01', 'SRT075.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT076', 'SDM015', 'Primary ATC', 'Primary ATC', 'SRT076-2029-076', '2029-04-01', '2032-04-01', 'SRT076.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT077', 'SDM015', 'Flight Plan in Air Traffic Flow Management', 'Air Traffic Flow', 'SRT077-2029-077', '2029-05-01', '2032-05-01', 'SRT077.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT078', 'SDM015', 'ATC Logbook, Bridging to the Development of Air Traffic Service Information', 'ATC Logbook', 'SRT078-2029-078', '2029-06-01', '2032-06-01', 'SRT078.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT079', 'SDM015', 'Aerodrome Control', 'Aerodrome Control', 'SRT079-2029-079', '2029-07-01', '2032-07-01', 'SRT079.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT080', 'SDM015', 'ICAO Language Proficiency', 'ICAO', 'SRT080-2029-080', '2029-08-01', '2032-08-01', 'SRT080.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT081', 'SDM015', 'Safety Risk and Lead Auditor', 'Safety Risk', 'SRT081-2029-081', '2029-09-01', '2032-09-01', 'SRT081.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT082', 'SDM015', 'TOT Radar', 'TOT Radar', 'SRT082-2029-082', '2029-10-01', '2032-10-01', 'SRT082.pdf', 1, 0, '2025-12-04 01:54:26'),
('SRT083', 'SDM016', 'Rating Transmission and Distribution', 'Transmission & Distribution', 'SRT083-2029-083', '2029-11-01', '2032-11-01', 'SRT083.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT084', 'SDM016', 'Safety Management System', 'Safety Management', 'SRT084-2029-084', '2029-12-01', '2032-12-01', 'SRT084.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT085', 'SDM016', 'Aeronautical Ground Lighting (AGL)', 'AGL', 'SRT085-2030-085', '2030-01-01', '2033-01-01', 'SRT085.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT086', 'SDM016', 'Basic Avionics', 'Avionics', 'SRT086-2030-086', '2030-02-01', '2033-02-01', 'SRT086.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT087', 'SDM016', 'Instrument Landing System', 'ILS', 'SRT087-2030-087', '2030-03-01', '2033-03-01', 'SRT087.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT088', 'SDM016', 'Motor 3 Phase dan Solar Central Unit', 'Motor & Solar Central', 'SRT088-2030-088', '2030-04-01', '2033-04-01', 'SRT088.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT089', 'SDM017', 'Safety Management System', 'Safety Management', 'SRT089-2030-089', '2030-05-01', '2033-05-01', 'SRT089.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT090', 'SDM018', 'Training Instructors Course (TIC)', 'Training Instructors', 'SRT090-2030-090', '2030-06-01', '2033-06-01', 'SRT090.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT091', 'SDM018', 'Airfield Lighting Standards & Principles', 'Airfield Lighting', 'SRT091-2030-091', '2030-07-01', '2033-07-01', 'SRT091.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT092', 'SDM018', 'Programmable Logic Control', 'PLC', 'SRT092-2030-092', '2030-08-01', '2033-08-01', 'SRT092.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT093', 'SDM018', 'Aerodrome Inspection Course', 'Aerodrome Inspection', 'SRT093-2030-093', '2030-09-01', '2033-09-01', 'SRT093.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT094', 'SDM018', 'Sustainable Airport Security, Airside and Operation System Improvement', 'Airport Security', 'SRT094-2030-094', '2030-10-01', '2033-10-01', 'SRT094.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT095', 'SDM019', 'Bangunan dan Landasan', 'Bangunan & Landasan', 'SRT095-2030-095', '2030-11-01', '2033-11-01', 'SRT095.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT096', 'SDM019', 'Jasa Konstruksi', 'Jasa Konstruksi', 'SRT096-2030-096', '2030-12-01', '2033-12-01', 'SRT096.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT097', 'SDM019', 'Ahli Sistem Manajemen Mutu - Madya', 'Sistem Mutu Madya', 'SRT097-2031-097', '2031-01-01', '2034-01-01', 'SRT097.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT098', 'SDM019', 'Ahli Teknik Landasan Terbang - Madya', 'Teknik Landasan Madya', 'SRT098-2031-098', '2031-02-01', '2034-02-01', 'SRT098.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT099', 'SDM020', 'Keselamatan dan Kesehatan Kerja Ketenagalistrikan', 'Keselamatan Ketenagalistrikan', 'SRT099-2031-099', '2031-03-01', '2034-03-01', 'SRT099.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT100', 'SDM020', 'Human Factors and Safety Management Fundamentals', 'Human Factors', 'SRT100-2031-100', '2031-04-01', '2034-04-01', 'SRT100.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT101', 'SDM020', 'Safety Management', 'Safety Management', 'SRT101-2031-101', '2031-05-01', '2034-05-01', 'SRT101.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT102', 'SDM020', 'Certified Professional Risk Management', 'Risk Management', 'SRT102-2031-102', '2031-06-01', '2034-06-01', 'SRT102.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT103', 'SDM021', 'Transmission and Distribution System', 'Transmission & Distribution', 'SRT103-2031-103', '2031-07-01', '2034-07-01', 'SRT103.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT104', 'SDM021', 'General Instructor Course', 'General Instructor', 'SRT104-2031-104', '2031-08-01', '2034-08-01', 'SRT104.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT105', 'SDM021', 'Fire Alarm System', 'Fire Alarm', 'SRT105-2031-105', '2031-09-01', '2034-09-01', 'SRT105.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT106', 'SDM021', 'Electrical Power System Analysis Using ETAP', 'ETAP Analysis', 'SRT106-2031-106', '2031-10-01', '2034-10-01', 'SRT106.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT107', 'SDM022', 'Rating Transmission and Distribution', 'Transmission & Distribution', 'SRT107-2031-107', '2031-11-01', '2034-11-01', 'SRT107.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT108', 'SDM022', 'Programmable Logic Control', 'PLC', 'SRT108-2031-108', '2031-12-01', '2034-12-01', 'SRT108.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT109', 'SDM022', 'Rating Genset dan ACOS', 'Rating Genset & ACOS', 'SRT109-2032-109', '2032-01-01', '2035-01-01', 'SRT109.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT110', 'SDM022', 'Airfield Lighting System', 'Airfield Lighting', 'SRT110-2032-110', '2032-02-01', '2035-02-01', 'SRT110.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT111', 'SDM022', 'Bangunan dan Landasan', 'Bangunan & Landasan', 'SRT111-2032-111', '2032-03-01', '2035-03-01', 'SRT111.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT112', 'SDM022', 'Air Conditioning System (ACS)', 'Air Conditioning', 'SRT112-2032-112', '2032-04-01', '2035-04-01', 'SRT112.pdf', 1, 0, '2025-12-04 01:58:44'),
('SRT113', 'SDM035', 'Sertifikat Dosen', NULL, '1122334455', '2025-12-15', '2026-03-31', 'cert_693f9698d057b.pdf', 1, 0, '2025-12-15 05:03:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mr_software`
--

DROP TABLE IF EXISTS `mr_software`;
CREATE TABLE `mr_software` (
  `id` varchar(20) NOT NULL,
  `ruangan_id` varchar(20) NOT NULL,
  `nama_software` varchar(100) NOT NULL,
  `jenis_software` varchar(50) DEFAULT NULL,
  `versi_tahun` varchar(20) DEFAULT NULL,
  `status_lisensi` tinyint(1) DEFAULT NULL COMMENT '1=active, 0=nonactive',
  `jenis_lisensi` tinyint(1) DEFAULT NULL COMMENT '1=year, 2=subcription, 3=seat',
  `tanggal_aktif_lisensi` date DEFAULT NULL,
  `tanggal_habis_lisensi` date DEFAULT NULL,
  `jumlah_lisensi` int(11) DEFAULT 1,
  `lokasi_penggunaan` varchar(200) DEFAULT NULL,
  `status_penggunaan` tinyint(1) DEFAULT NULL COMMENT '1=active, 0=nonactive',
  `keterangan_tambahan` text DEFAULT NULL,
  `foto` varchar(225) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mr_software`
--

INSERT INTO `mr_software` (`id`, `ruangan_id`, `nama_software`, `jenis_software`, `versi_tahun`, `status_lisensi`, `jenis_lisensi`, `tanggal_aktif_lisensi`, `tanggal_habis_lisensi`, `jumlah_lisensi`, `lokasi_penggunaan`, `status_penggunaan`, `keterangan_tambahan`, `foto`, `created_at`, `updated_at`) VALUES
('SW001', 'R001', 'ALS Simulator Suite', 'Training Simulator', '2023', 1, 2, '2024-01-01', '2025-01-01', 10, 'Simulator ALS', 1, 'Digunakan untuk pelatihan ALS', NULL, '2025-11-14 07:19:20', '2025-11-14 07:19:20'),
('SW002', 'R002', 'Microsoft Office 365', 'Productivity', '2024', 1, 2, '2023-10-01', '2024-10-01', 30, 'Lab Komputer', 1, 'Lisensi digunakan oleh seluruh PC lab', NULL, '2025-11-14 07:19:20', '2025-11-14 07:19:20'),
('SW003', 'R002', 'AutoCAD', 'Desain Teknik', '2022', 1, 3, '2024-03-01', '2025-03-01', 15, 'Lab Komputer', 1, 'Digunakan untuk perkuliahan TRBU', NULL, '2025-11-14 07:19:20', '2025-11-14 07:19:20'),
('SW004', 'R003', 'Sanako Language Lab', 'Language Learning', '2025', 1, 2, '2025-12-12', '2025-12-28', 25, 'Lab Bahasa', 1, NULL, NULL, '2025-11-14 07:19:20', '2025-12-12 04:40:34'),
('SW005', 'R005', 'X-Ray Simulation Pro', 'Security Screening', '2024', 1, 1, '2024-02-01', '2025-02-01', 5, 'Lab X-Ray', 1, 'Simulasi pemeriksaan X-Ray bandara', NULL, '2025-11-14 07:19:20', '2025-11-14 07:19:20'),
('SW006', 'R008', 'Fire Safety VR', 'Simulator', '2023', 1, 2, '2023-09-01', '2024-09-01', 3, 'Smoke Chamber', 1, 'VR training untuk evakuasi asap', NULL, '2025-11-14 07:19:20', '2025-11-14 07:19:20'),
('SW007', 'R011', 'FTDS Trainer Software', 'Flight Training', '2024', 1, 3, '2024-01-01', '2026-01-01', 8, 'FTDS Room', 1, 'Software pengendali FTDS', NULL, '2025-11-14 07:19:20', '2025-11-14 07:19:20'),
('SW008', 'R013', 'Concrete Analyzer', 'Engineering', '2022', 1, 1, '2024-01-10', '2025-01-10', 5, 'Lab Beton', 1, 'Analisis material beton', NULL, '2025-11-14 07:19:20', '2025-11-14 07:19:20'),
('SW009', 'R019', 'ATC Simulator Pro', 'Air Traffic Control', '2023', 1, 2, '2023-11-01', '2024-11-01', 12, 'Lab ATC', 1, 'Digunakan untuk latihan prosedur ATC', NULL, '2025-11-14 07:19:20', '2025-11-14 07:19:20'),
('SW010', 'R016', 'CBT Manager', 'Computer Based Training', '2025', 1, 2, '0000-00-00', '2025-12-31', 40, 'CBT ALS', 1, NULL, NULL, '2025-11-14 07:19:20', '2025-12-12 04:01:09');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mr_users`
--

DROP TABLE IF EXISTS `mr_users`;
CREATE TABLE `mr_users` (
  `id` varchar(20) NOT NULL,
  `nip` varchar(50) NOT NULL,
  `full_name` varchar(225) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(225) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` tinyint(4) DEFAULT NULL COMMENT '0=admin, 1=direktur, 2=kepala jurusan, 3=laboran, 4=dosen',
  `is_claim` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=aktif, 0=non aktif',
  `device_id` varchar(255) DEFAULT NULL,
  `player_id` varchar(255) DEFAULT NULL,
  `foto` varchar(225) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mr_users`
--

INSERT INTO `mr_users` (`id`, `nip`, `full_name`, `username`, `email`, `password`, `role`, `is_claim`, `device_id`, `player_id`, `foto`, `last_login`, `created_at`, `updated_at`) VALUES
('USR001', '197810252000031001', 'Dr. Anton Abdullah, S.T., M.M.', 'anton', 'anton@gmail.com', '$2y$10$hTWAdhvVJuaSJ4GwWC30OugZcx.xnTciu53m8/9xWz7GPXDXvGt/6', 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:01:10', '2025-12-16 08:21:37'),
('USR002', '196810111991121001', 'Sutiyo, S.Sos., M.Si.', '196810111991121001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:01:10', '2025-12-15 08:38:46'),
('USR003', '198901212009121002', 'Wildan Nugraha, S.E., MS.ASM.', '198901212009121002', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:01:10', '2025-12-15 08:38:46'),
('USR004', '198705252009122005', 'Dr. Yeti Komalasari, S.Si.T., M.Adm.SDA.', '198705252009122005', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:01:10', '2025-12-15 08:38:46'),
('USR005', '198307192009122001', 'Dr. Fitri Masito, S.Pd., MS.ASM.', '198307192009122001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:01:10', '2025-12-15 08:38:46'),
('USR006', '198607032022032002', 'Thursina Andayani, M.Sc.', 'dos', NULL, '$2y$10$cI/QKXPl8A8Z3xwW9ZhJuuXcj6n4VyGGnz4pjKNp3y8r2kKsyCQAC', 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:01:10', '2025-12-15 08:38:46'),
('USR007', '197606121998031001', 'Dwi Candra Yuniar, S.H., S.ST., M.Si.', '197606121998031001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:07:24', '2025-12-15 08:38:46'),
('USR008', '197002031995031001', 'Dr. Capt. Ahmad Hariri, S.T., S.Si.T., M.Si.', '197002031995031001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:07:24', '2025-12-15 08:38:46'),
('USR009', '198003052005021001', 'Dr. Bambang Setiawan, S.Kom., M.T.', '198003052005021001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:07:24', '2025-12-15 08:38:46'),
('USR010', '197209081998031002', 'Mohammad Syukri Pesilette, S.T., M.M.', '197209081998031002', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:07:24', '2025-12-15 08:38:46'),
('USR011', '198302072007122002', 'Herlina Febiyanti, S.T., M.M.', '198302072007122002', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:07:24', '2025-12-15 08:38:46'),
('USR012', '198405132019021001', 'Iwansyah Putra, S.S., M.Pd.', '198405132019021001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:07:24', '2025-12-15 08:38:46'),
('USR013', '198803082020121006', 'Minulya Eska Nugraha, M.Pd.', '198803082020121006', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:07:24', '2025-12-15 08:38:46'),
('USR014', '197811182005022001', 'Zusnita Hermala, S.Kom., M.Si.', '197811182005022001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:07:24', '2025-12-15 08:38:46'),
('USR015', '199012102010122001', 'Inda Tri Pasa, S.S.T., M.M.', '199012102010122001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:07:24', '2025-12-15 08:38:46'),
('USR016', '197202171995011001', 'Dr. SUNARDI, S.T., M.Pd., M.T', '197202171995011001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:13:20', '2025-12-15 08:38:46'),
('USR017', '196011271980021001', 'Dr. Ir. SETIYO, M.M.', '196011271980021001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:13:20', '2025-12-15 08:38:46'),
('USR018', '197506211998031002', 'Ir. ASEP MUHAMAD SOLEH, S.Si.T., S.T., M.Pd', '197506211998031002', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:13:20', '2025-12-15 08:38:46'),
('USR019', '198610082009121004', 'Ir. VIKTOR SURYAN, S.T. M.Sc.', '198610082009121004', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:13:20', '2025-12-15 08:38:46'),
('USR020', '198312132010122003', 'Ir. DIRESTU AMALIA, S.T. MS., ASM.', '198312132010122003', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:13:20', '2025-12-15 08:38:46'),
('USR021', '198307252008122001', 'YAYUK SUPRIHARTINI, S.Si.T., M.A', '198307252008122001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-11-30 21:13:20', '2025-12-15 08:38:46'),
('USR022', '198103062002121001', 'Ir. M. INDRA MARTADINATA, S.SiT., M.Si.', 'Yusufkoe123', 'indrakoe@poltekbangplg.ac.id', '$2y$10$/PnYalb11UHnJ6Bk2I7jzOdCH/WUBtEPgEZbqWPNKEf30CYA7pY6y', 4, 1, 'UP1A.231005.007_d51c2726-2f09-459a-822a-3820117ed246', '7ac84244-5154-49d4-aba6-2c567736f00d', NULL, NULL, '2025-11-30 21:13:20', '2025-12-16 08:22:33'),
('USR023', '197002031995031001', 'Dr. Capt. Ahmad Hariri, S.T., S.Si.T., M.Si.', '197002031995031001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR024', '198005312005021002', 'Supriyadi, S.Si.T., M.Sc.', '198005312005021002', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR025', '198206192005021001', 'Yani Yudha Wirawan, S.Si.T., M.T.', '198206192005021001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR026', '197209081998031002', 'Mohammad Syukri Pesilette, S.T., M.M.', '197209081998031002', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR027', '196009011981031001', 'Ir. Bambang Wijaya Putra, M.M.', '196009011981031001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR028', '196011271980021001', 'Dr. Ir. Setiyo, M.M.', '196011271980021001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR029', '198003052005021001', 'Dr. Bambang Setiawan, S.Kom., M.T.', '198003052005021001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR030', '197810252000031001', 'Anton Abdullah, S.T., M.M.', '197810252000031001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR031', '196810111991121001', 'Sutiyo, S.Sos., M.Si.', '196810111991121001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR032', '197506211998031002', 'Asep Muhamad Soleh, S.Si.T., S.T., M.Pd.', '197506211998031002', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR033', '197606121998031001', 'Dwi Candra Yuniar, S.H., S.ST., M.Si.', '197606121998031001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR034', '198211072005021001', 'Wahyudi Saputra, S.Si.T., M.T.', '198211072005021001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR035', '197811182005022001', 'Zusnita Hermala, S.Kom., M.Si.', '197811182005022001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR036', '198103062002121001', 'M. Indra Martadinata, S.ST., M.Si.', '198103062002121001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR037', '197106231991121003', 'Rita Zahara, S.Sos., M.Si.', '197106231991121003', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR038', '197304302006041001', 'Noor Sulistiyono, S.SiT., M.M., M.Mar E.', '197304302006041001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR039', '197202171995011001', 'Dr. Sunardi, S.T., M.Pd., M.T.', '197202171995011001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR040', '198110022009041001', 'Dr. Iswadi Idris, S.Pd., M.Pd.', '198110022009041001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR041', '19870525200912005', 'Dr. Yeti Komalasari, S.Si.T., M.Adm.SDA.', '19870525200912005', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR042', '198102262010121001', 'dr. Yessy Budiarti', '198102262010121001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR043', '19850714200912007', 'Yulius Bhanu Wijaya, S.Pd.', '19850714200912007', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR044', '19830719200912001', 'Fitri Masito, S.Pd., MS.ASM.', '19830719200912001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR045', '198610082009121004', 'Viktor Suryan, S.T., M.Sc.', '198610082009121004', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR046', '198302072007122002', 'Herlina Febiyanti, S.T., M.M.', '198302072007122002', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR047', '198307252008122001', 'Yayuk Suprihartini, S.Si.T., M.A.', '198307252008122001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR048', '198408202013012001', 'Ria Maya Sari, S.T.', '198408202013012001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR049', '198312132010122003', 'Direstu Amalia, S.T., MS.ASM.', '198312132010122003', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR050', '198901212009121002', 'Wildan Nugraha, S.E., MS.ASM.', '198901212009121002', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR051', '198110052009121003', 'Johny Emiyani, S.Si.T., M.Si.', 'Johny', 'johny@poltekbangplg.ac.id', '$2y$10$mfuAEHmsNu8/JC0F4qOf/eXm7TdA6JCZO3uW3KUKhe8WU0jckstou', 4, 1, 'BP2A.250605.031.A3_af22d253-c262-4f81-9670-10e6c3ddac12', '5d78adf0-a7b2-4f75-a69c-c36eee5966ab', NULL, NULL, '2025-12-01 04:37:57', '2025-12-16 03:55:57'),
('USR052', '199305222019021002', 'dr. Wira Dharma Utama', '199305222019021002', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR053', '198405132019021001', 'Iwansyah Putra, S.S., M.Pd.', '198405132019021001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR054', '198702052008121001', 'Ahmad Siradjuddin, S.H.', '198702052008121001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR055', '198411172010121002', 'Fetra Novriandi AS, S.S.T.', '198411172010121002', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR056', '198406292008121002', 'Muh. Syahrul Munir, S.E., M.M.', '198406292008121002', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR057', '198509182010121001', 'Virma Septiani, S.T., M.Si.', '198509182010121001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR058', '198907022010121004', 'Muhammad Erawan Destyana, S.E.', '198907022010121004', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR059', '198804082010121003', 'Arif Priandono, S.Sos.', '198804082010121003', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR060', '198803082020121006', 'Minulya Eska Nugraha, M.Pd.', '198803082020121006', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:37:57', '2025-12-15 08:38:46'),
('USR061', '200001102022101002', 'Reghuver Refan Mubarak, S.Tr.T', '200001102022101002', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR062', '200103292022101002', 'Zidhane Aliffaputra Nuranto', '200103292022101002', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR063', '200007042021122001', 'Amalia Resky Hasniati', 'lab', NULL, '$2y$10$f47Ye1zE95fDr4dmX4vKrevnKHjgnw3gkkZTBF3r0rz4fI0FFkfSe', 3, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR064', '200005252022101001', 'Raihan Muhammad Farid, S.Tr.T', '200005252022101001', NULL, NULL, 3, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR065', '199711172022032017', 'Mayang Enggar Kusumastuti', '199711172022032017', NULL, NULL, 3, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR066', '199912192021122002', 'Ni Putu Heni Handayani', '199912192021122002', NULL, NULL, 3, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR067', '200012122021122001', 'Shabrina Ramadhani', '200012122021122001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR068', '199804082022032018', 'Nabilah Alisdiyanti', '199804082022032018', NULL, NULL, 3, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR069', '200005172021122001', 'Eka Puspitasari Hutauruk', '200005172021122001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR070', '200104132023102001', 'Dwi Putri', '200104132023102001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR071', '199611222023211007', 'M. Nabil Putra Esa Yani', '199611222023211007', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR072', '199103122020121006', 'Gilang Eka Prandana', '199103122020121006', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR073', '199904162022102002', 'Hana Fatiha', '199904162022102002', NULL, NULL, 3, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR074', '200102152024122001', 'Siti Salbiah Ristumanda', '200102152024122001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR075', '199402152023211013', 'Wibi Ramadhan', '199402152023211013', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR076', '199905042022031006', 'Bintang Arif Manullang', 'lab1', NULL, '$2y$10$.afrv60GHqjkOpXrtHTZ..7OcjvRBwdXQ/M/F9NYIEmX3y7N4AgdO', 3, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR077', '200112072024121002', 'Ahmad Furqon', '200112072024121002', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR078', '200003142022102001', 'Ingelda Gabrella', '200003142022102001', NULL, NULL, 3, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR079', '200104162022101001', 'Muhammad Hanif Zaidan', '200104162022101001', NULL, NULL, 3, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR080', '199705152023212028', 'Yudita Nirmala Kartikasari', '199705152023212028', NULL, NULL, 3, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR081', '199905112023211001', 'Andi Muh Khairum DR', '199905112023211001', NULL, NULL, 3, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR082', '200301072023102001', 'Vania Nadhifa Azzahra', '200301072023102001', NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, '2025-12-01 04:40:28', '2025-12-15 08:38:46'),
('USR083', '200102282022101002', 'Ade Bisma Ferdiansyah, A.Md', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR084', '200112072024121002', 'Ahmad Furqon, S.Tr.T.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR085', '198702052008121001', 'Ahmad Siradjuddin, S.H.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR086', '200007042021122001', 'Amalia Resky Hasniati, A.Md.T.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR087', '199905112023211001', 'Andi Muh Khairum dr, A.Md.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR088', '197810252000031001', 'Anton Abdullah, S.T., M.M.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR089', '198804082010121003', 'Arif Priandono, S.Sos.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR090', '197506211998031002', 'Asep Muhamad Soleh, S.Si.T., S.T., M.Pd.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR091', '199812102025211015', 'Astri Puspita Kesuma, S.Tr.T.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR092', '199905042022031006', 'Bintang Arif Manullang, A.Md.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR093', '199504182025211022', 'Denni Apriansyah, S.I.Kom', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR094', '198312312025211033', 'Dini Desti Maulani', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR095', '198312132010121003', 'Direstu Amalia, S.T., MS.ASM.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR096', '198003052005021001', 'Dr. Bambang Setiawan, S.Kom., M.T.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR097', '197002031995031001', 'Dr. Capt. Ahmad Hariri, S.T., S.Si.T., M.Si.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR098', '196011271980021001', 'Dr. Ir. Setiyo, M.M.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR099', '198110022009041001', 'Dr. Iswadi Idris, S.Pd., M.Pd.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR100', '197202171995011001', 'Dr. Sunardi, S.T., M.Pd., M.T.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR101', '199305222019021002', 'dr. Wira Dharma Utama', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR102', '198102262010121001', 'dr. Yessy Budiarti', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR103', '198705252009121005', 'Dr. Yeti Komalasari, S.Si.T., M.Adm.SDA.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR104', '198311292006041004', 'Dwi Cahyono', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR105', '197606121998031001', 'Dwi Candra Yuniar, S.H., S.ST., M.Si.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR106', '200104132023102001', 'Dwi Putri, A.Md.Tra.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR107', '200005172021122001', 'Eka Puspitasari Hutahuruk, A.Md.Tra.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR108', '199910152025211009', 'Fahrudin Al Rasyid', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR109', '198706102010121003', 'Fandhy Gunawan, S.AP., M.A.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR110', '198411172010121002', 'Fetra Novriandi AS, S.S.T.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR111', '198307192009121001', 'Fitri Masito, S.Pd., MS.ASM.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR112', '199103122020121006', 'Gilang Eka Prandana, A.Md.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR113', '199904162022102002', 'Hana Fatiha, S.Tr.T.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR114', '198302072007121002', 'Herlina Febiyanti, S.T., M.M.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR115', '196009011981031001', 'Ir. Bambang Wijaya Putra, M.M.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR116', '198405132019021001', 'Iwansyah Putra, S.S., M.Pd.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR117', '198110052009121003', 'Johny Emiyani, S.Si.T., M.Si.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR118', '199203192025211013', 'Kiswanto, S.Pd.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR119', '198103062002121001', 'M. Indra Martadinata, S.ST., M.Si.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR120', '199611222023211007', 'M. Nabil Putra Esa Yani, S.Kom', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR121', '199501062023211019', 'M. Wahid Alqorni, S.Kom.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR122', '199711172022031017', 'Mayang Enggar Kusumastuti, A.Md.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR123', '198803082020121006', 'Minulya Eska Nugraha, M.Pd.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR124', '197209081998031002', 'Mohammad Syukri Pesilette, S.T.,M.M.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR125', '198406292008121002', 'Muh. Syahrul Munir, S.E., M.M.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR126', '198907022010121004', 'Muhammad Erawan Destyana, S.E.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR127', '200104162022101001', 'Muhammad Hanif Zaidan, S.Tr.T.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR128', '199912192021122002', 'Ni Putu Heni Handayani, A.Md.T.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR129', '197304302006041001', 'Noor Sulistiyono, S.SiT., M.M., M.Mar E.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR130', '198404022023211003', 'Ns. Sasono Mardiono, S.Kep, M.Kes.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR131', '199606202021121002', 'Putu Eggi Wiliana Wijaya, A.Md.Tra.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR132', '200005252022101001', 'Raihan Muhammad Farid, S.Tr.T.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR133', '200001102022101002', 'Reghuver Refan Mubarak, S.Tr.T.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR134', '198408202013012001', 'Ria Maya Sari, S.T.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR135', '197106231991121003', 'Rita Zahara, S.Sos., M.Si.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR136', '200012122021121001', 'Shabrina Ramadhani, A.Md.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR137', '200102152024121001', 'Siti Salbiah Ristumanda, S.Tr.T.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR138', '198005312005021002', 'Supriyadi, S.Si.T., M.Sc.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR139', '196810111991121001', 'Sutiyo, S.Sos., M.Si.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR140', '198504132006041001', 'Syaiful Anwar', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR141', '198607032022031002', 'Thursina Andayani, M.Sc.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR142', '199207032025211024', 'Try Sakti Wahyudi', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR143', '200301072023102001', 'Vania Nadhifa Azzahra, A.Md.Tra.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR144', '198610082009121004', 'Viktor Suryan, S.T., M.Sc.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR145', '198509182010121001', 'Virma Septiani, S.T., M.Si.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR146', '198211072005021001', 'Wahyudi Saputra, S.Si.T., M.T.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR147', '198901212009121002', 'Wildan Nugraha, S.E., MS.ASM.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR148', '198206192005021001', 'Yani Yudha Wirawan, S.Si.T., M.T.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR149', '198307252008121001', 'Yayuk Suprihartini, S.Si.T., M.A.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR150', '198507142009121007', 'Yulius Bhanu Wijaya, S.Pd.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR151', '200103292022101002', 'Zidhane Aliffaputra Nuranto, A.Md', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR152', '197811182005021001', 'Zusnita Hermala, S.Kom., M.Si.', NULL, NULL, NULL, 4, 0, NULL, NULL, NULL, NULL, NULL, NULL),
('USR153', '00000001', 'Super Admin', 'adm', NULL, '$2y$10$R.PBpB4fivCi0VxUfgBlg.8Y.q2Wa5pmFN27T/sFBOGdvShWdFeLq', 0, 0, NULL, NULL, NULL, NULL, '2025-12-01 08:28:13', '2025-12-15 08:38:46'),
('USR154', '197002031995031001', 'Dr. Capt. AHMAD HARIRI, S.T., S.Si.T., M.Si.', NULL, NULL, '$2y$10$geq45Iru8SyXo/YaQHo17e4GeMI66bc8Zdiw1D.tf7MkFARx8iFmy', 1, 0, NULL, NULL, NULL, NULL, '2025-12-08 08:34:50', '2025-12-15 08:20:07'),
('USR155', '001122334455', 'Asep', 'asep', 'test@gmail.com', '$2y$10$UG4Sg1BDOkqi6ppKDPuz0.C0KElKzbxBR0IOPz2ESCK73jhi30zC6', 0, 1, 'AP3A.240905.015.A2_d42b1c2a-e69c-4012-9e90-a53bb0cbf4cf', '40da6654-00b1-4f89-8f81-4011ddf960ee', NULL, NULL, '2025-12-15 03:10:31', '2025-12-16 08:19:21'),
('USR156', '0011223344', 'AA', NULL, NULL, NULL, 3, 0, NULL, NULL, NULL, NULL, '2025-12-15 03:58:52', '2025-12-15 08:22:24'),
('USR157', '0918171626', 'AAA', NULL, NULL, NULL, 2, 0, NULL, NULL, NULL, NULL, '2025-12-15 04:00:48', '2025-12-15 08:22:10');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `mr_activity_logs`
--
ALTER TABLE `mr_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `mr_alat`
--
ALTER TABLE `mr_alat`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `mr_laporan_kerusakan`
--
ALTER TABLE `mr_laporan_kerusakan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `mr_maintenance`
--
ALTER TABLE `mr_maintenance`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `mr_notifikasi`
--
ALTER TABLE `mr_notifikasi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `mr_penggunaan_ruangan`
--
ALTER TABLE `mr_penggunaan_ruangan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `mr_prodi`
--
ALTER TABLE `mr_prodi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `mr_reminder_schedule`
--
ALTER TABLE `mr_reminder_schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `mr_ruangan`
--
ALTER TABLE `mr_ruangan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_ruangan` (`kode_ruangan`);

--
-- Indeks untuk tabel `mr_sdm`
--
ALTER TABLE `mr_sdm`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `mr_sertifikasi`
--
ALTER TABLE `mr_sertifikasi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `mr_software`
--
ALTER TABLE `mr_software`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `mr_users`
--
ALTER TABLE `mr_users`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
