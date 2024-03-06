-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 03, 2024 at 07:05 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `id21953952_delvin`
--

-- --------------------------------------------------------

--
-- Table structure for table `delvin`
--

CREATE TABLE `delvin` (
  `sno` int(11) NOT NULL,
  `GSTNO` varchar(15) NOT NULL,
  `cname` varchar(40) NOT NULL,
  `bill` int(11) NOT NULL,
  `taxamt` decimal(11,1) NOT NULL,
  `cgst` decimal(11,1) NOT NULL,
  `sgst` decimal(11,1) NOT NULL,
  `Total` int(11) NOT NULL,
  `date` varchar(30) NOT NULL,
  `igst` decimal(11,1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delvin`
--

INSERT INTO `delvin` (`sno`, `GSTNO`, `cname`, `bill`, `taxamt`, `cgst`, `sgst`, `Total`, `date`, `igst`) VALUES
(0, '33AABCV0736A1Z0', 'VENKATESA ENGG CONSULTANT PVT LTD', 132, 10350.0, 931.5, 931.5, 12213, '2024-01-03', 0.0),
(0, '33AJQPS2641L1ZR', 'HONESTY HARDWARE MART', 133, 13000.0, 1170.0, 1170.0, 15340, '2024-01-10', 0.0),
(0, '33AAACR3147C1ZY', 'RANE AUTOMOTIVE INDIA PVT LTD', 134, 19500.0, 1755.0, 1755.0, 23010, '2024-01-11', 0.0),
(0, '33AADCM9688C1ZA', 'MICRO SHARP NEEDLES PVT LTD', 135, 21000.0, 1890.0, 1890.0, 24780, '2024-01-19', 0.0),
(0, '33AGUPS0637D1ZB', 'SOUTHERN TOOLS & HARDWARE', 136, 5075.0, 456.8, 456.8, 5989, '2024-01-20', 0.0),
(0, '33AALCS5492C1ZA', 'SRI RANGANATHAR VALVES PVT LTD', 137, 4725.0, 425.3, 425.3, 5576, '2024-01-24', 0.0),
(0, '33BJFPK8575D1Z5', 'SREE GANESH AGENCIES', 138, 9675.0, 870.8, 870.8, 11417, '2024-01-30', 0.0),
(0, '33AAACT1279M1Z6', 'RANE ENGINE VALVES LTD', 139, 12000.0, 1080.0, 1080.0, 14160, '2024-01-31', 0.0);

-- --------------------------------------------------------

--
-- Table structure for table `purchase`
--

CREATE TABLE `purchase` (
  `sno` int(11) NOT NULL,
  `GSTNO` varchar(15) NOT NULL,
  `cname` varchar(255) NOT NULL,
  `taxamt` decimal(11,1) NOT NULL,
  `cgst` decimal(11,1) NOT NULL,
  `sgst` decimal(11,1) NOT NULL,
  `igst` decimal(11,1) NOT NULL,
  `Total` int(11) NOT NULL,
  `date` varchar(25) NOT NULL,
  `bill` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase`
--

INSERT INTO `purchase` (`sno`, `GSTNO`, `cname`, `taxamt`, `cgst`, `sgst`, `igst`, `Total`, `date`, `bill`) VALUES
(0, '33AAMFA9025H1Z1', 'ASHIKHA TOOLS', 326.0, 29.3, 29.3, 0.0, 385, '2024-01-23', 26925),
(0, '33AAKPK8983G2Z9', 'THE PROFESSIONAL COURIERS', 1110.0, 99.9, 99.9, 0.0, 1310, '2024-01-01', 47075),
(0, '33AADPV9874C1ZE', 'A.VADIVEL & CO', 2461.0, 221.5, 221.5, 0.0, 2904, '2024-01-23', 2898),
(0, '24AXTPG5293K1ZV', 'J.D.DIAMOND INDUSTRIED', 7731.9, 0.0, 0.0, 19.0, 7751, '2024-01-01', 732324),
(0, '24AXTPG5293K1ZV', 'J.D.DIAMOND INDUSTRIED', 14212.8, 0.0, 0.0, 36.0, 14249, '2024-01-12', 762324);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `delvin`
--
ALTER TABLE `delvin`
  ADD UNIQUE KEY `bill` (`bill`);

--
-- Indexes for table `purchase`
--
ALTER TABLE `purchase`
  ADD UNIQUE KEY `taxamt` (`taxamt`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
