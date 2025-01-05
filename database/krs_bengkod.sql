-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 05 Jan 2025 pada 07.43
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `krs_bengkod`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `inputmhs`
--

CREATE TABLE `inputmhs` (
  `id` int(11) NOT NULL,
  `namaMhs` varchar(255) DEFAULT NULL,
  `nim` varchar(15) DEFAULT NULL,
  `ipk` float DEFAULT NULL,
  `sks` int(1) DEFAULT NULL,
  `matakuliah` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `inputmhs`
--

INSERT INTO `inputmhs` (`id`, `namaMhs`, `nim`, `ipk`, `sks`, `matakuliah`) VALUES
(16, 'Ajie Kusumadhany', 'A11.2021.13379', 4, 24, 'Basis Data'),
(18, 'Fahri', 'A11.2021.13380', 3, 24, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jwl_matakuliah`
--

CREATE TABLE `jwl_matakuliah` (
  `id` int(11) NOT NULL,
  `matakuliah` varchar(250) DEFAULT NULL,
  `sks` int(1) DEFAULT NULL,
  `kelp` varchar(10) DEFAULT NULL,
  `ruangan` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jwl_matakuliah`
--

INSERT INTO `jwl_matakuliah` (`id`, `matakuliah`, `sks`, `kelp`, `ruangan`) VALUES
(1, 'Algoritma dan Pemrograman', 3, 'A11.4201', 'H1.1'),
(2, 'Struktur Data', 4, 'A11.4202', 'H1.2'),
(3, 'Basis Data', 3, 'A11.4203', 'H2.1'),
(4, 'Jaringan Komputer', 2, 'A11.4204', 'H2.2'),
(5, 'Sistem Operasi', 3, 'A11.4205', 'H3.1'),
(6, 'Rekayasa Perangkat Lunak', 4, 'A11.4206', 'H3.2'),
(7, 'Pengembangan Web', 2, 'A11.4207', 'H4.1'),
(8, 'Kecerdasan Buatan', 3, 'A11.4208', 'H4.2'),
(9, 'Keamanan Jaringan', 2, 'A11.4209', 'H5.1'),
(10, 'Mobile Programming', 4, 'A11.4210', 'H5.2');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jwl_mhs`
--

CREATE TABLE `jwl_mhs` (
  `id` int(11) NOT NULL,
  `mhs_id` int(11) DEFAULT NULL,
  `matakuliah` varchar(255) DEFAULT NULL,
  `sks` int(11) DEFAULT NULL,
  `kelp` varchar(50) DEFAULT NULL,
  `ruangan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jwl_mhs`
--

INSERT INTO `jwl_mhs` (`id`, `mhs_id`, `matakuliah`, `sks`, `kelp`, `ruangan`) VALUES
(52, 16, 'Basis Data', 3, 'A11.4203', 'H2.1');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `inputmhs`
--
ALTER TABLE `inputmhs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `jwl_matakuliah`
--
ALTER TABLE `jwl_matakuliah`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `jwl_mhs`
--
ALTER TABLE `jwl_mhs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mhs_id` (`mhs_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `inputmhs`
--
ALTER TABLE `inputmhs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `jwl_matakuliah`
--
ALTER TABLE `jwl_matakuliah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `jwl_mhs`
--
ALTER TABLE `jwl_mhs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `jwl_mhs`
--
ALTER TABLE `jwl_mhs`
  ADD CONSTRAINT `jwl_mhs_ibfk_1` FOREIGN KEY (`mhs_id`) REFERENCES `inputmhs` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
