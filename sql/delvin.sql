-- Delvin Diamond Tool Industries
-- Database: delvin
-- Full schema with upgrade-safe fixes

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

 /*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
 /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
 /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
 /*!40101 SET NAMES utf8mb4 */;

-- NOTE: Import this into your InfinityFree database via phpMyAdmin.
-- The database is pre-assigned by InfinityFree, so no CREATE DATABASE / USE needed.

-- =========================================================
-- TABLE: companydata
-- =========================================================

CREATE TABLE IF NOT EXISTS `companydata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companyname` varchar(255) NOT NULL,
  `gstno` varchar(20) NOT NULL,
  `gsttype` varchar(50) NOT NULL,
  `address` varchar(500) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `companydata` (`id`, `companyname`, `gstno`, `gsttype`, `address`) VALUES
(17, 'SUNRISE ENTERPRISES', '29ACMPK0591Q1ZS', 'igst', ''),
(18, 'SUNDARAM FASTENERS LTD', '33AAACS8779D1Z7', 'tngst', ''),
(19, 'SAIDEV ENTERPRISES', '33ADZFS2976B1ZN', 'tngst', ''),
(20, 'PALS ENTERPRISES', '33AAAFP3636M1Z7', 'tngst', ''),
(21, 'I.E.S AGENCIES', '33AAAFI5552K1ZF', 'tngst', ''),
(22, 'HONESTY HARDWARE MART', '33AJQPS2641L1ZR', 'tngst', ''),
(23, 'LAKSHMI CARD CLOTHING MFG', '33AAACL3521E1Z6', 'tngst', ''),
(24, 'SUPER FINE NEEDLES PVT LTD', '29AACCS6550L1ZW', 'igst', ''),
(25, 'MOGLI LABS INDIA PVT LTD', '33AAJCM7312H1ZL', 'tngst', ''),
(26, 'THE PROFESSIONAL COURIERS', '33AAKPK8983G2Z9', 'tngst', ''),
(27, 'NOVELTY DIAMOND TOOLS & PRODUCTS', '33ADWPD0161G1ZT', 'tngst', ''),
(28, 'WIN WIN DIAMOND PRODUCTS', '33AJBPR5326E1ZI', 'tngst', ''),
(29, 'J.D.DIAMOND INDUSTRIES', '24AXTPG5293K1ZV', '25p', ''),
(30, 'A.VADIVEL & CO', '33AADPV9874C1ZE', 'tngst', ''),
(31, 'THE PRECISION SCIENTIFIC CO', '33AJIPK3213G1ZO', 'tngst', ''),
(32, 'RENOLD CHAIN INDIA PVT LTP', '33AADCR9839E1Z8', 'tngst', ''),
(33, 'RANE AUTOMOTIVE INDIA PVT LTD', '05AAACR3147C1ZX', 'igst', ''),
(34, 'N.M.BELT CENTRE', '33AADFN1633R1Z3', 'tngst', ''),
(35, 'SREE GANESH AGENCIES', '33BJFPK8575D1Z5', 'tngst', ''),
(36, 'SRI RANGANATHAR VALVES PVT LTD', '33AALCS5492C1ZA', 'tngst', ''),
(37, 'M.S.V.ENTERPRISES', '33ALVPY7788D1Z3', 'tngst', ''),
(38, 'UNIVERSAL HARDWARE & TOOLS COR', '33AAAFU6771G1Z5', 'tngst', ''),
(40, 'PARAS DIAMOND CO(%25GST)', '24CMXPS4074B1ZP', '25p', ''),
(41, 'SARASWATHI AGENCIES 6%', '33AEEPR4761D2ZM', '6p', ''),
(42, 'M.P.M. MUTHU GENERAL STORE', '33AAAFM8415D1ZQ', 'igst', ''),
(44, 'ALIF TRADER', '33AADFA2185M1ZH', 'tngst', '');

-- =========================================================
-- TABLE: delvin (sales invoices)
-- =========================================================

CREATE TABLE IF NOT EXISTS `delvin` (
  `sno` int(11) NOT NULL AUTO_INCREMENT,
  `GSTNO` varchar(15) NOT NULL,
  `cname` varchar(40) NOT NULL,
  `bill` int(11) NOT NULL,
  `taxamt` decimal(11,1) NOT NULL,
  `cgst` decimal(11,1) NOT NULL DEFAULT 0,
  `sgst` decimal(11,1) NOT NULL DEFAULT 0,
  `Total` int(11) NOT NULL,
  `date` varchar(30) NOT NULL,
  `igst` decimal(11,1) NOT NULL DEFAULT 0,
  `challan_no` int(11) DEFAULT NULL,
  `challan_date` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`sno`),
  UNIQUE KEY `bill` (`bill`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================================================
-- TABLE: purchase
-- =========================================================

CREATE TABLE IF NOT EXISTS `purchase` (
  `sno` int(11) NOT NULL AUTO_INCREMENT,
  `GSTNO` varchar(15) NOT NULL,
  `cname` varchar(255) NOT NULL,
  `taxamt` decimal(11,1) NOT NULL,
  `cgst` decimal(11,1) NOT NULL DEFAULT 0,
  `sgst` decimal(11,1) NOT NULL DEFAULT 0,
  `igst` decimal(11,1) NOT NULL DEFAULT 0,
  `Total` int(11) NOT NULL,
  `date` varchar(25) NOT NULL,
  `bill` int(11) DEFAULT NULL,
  PRIMARY KEY (`sno`),
  UNIQUE KEY `bill` (`bill`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================================================
-- TABLE: tools
-- =========================================================

CREATE TABLE IF NOT EXISTS `tools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `toolname` varchar(255) NOT NULL,
  `rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================================================
-- TABLE: invoice_items (line items per invoice)
-- =========================================================

CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bill` int(11) NOT NULL,
  `invoice_type` varchar(20) NOT NULL DEFAULT 'invoice',
  `tool_name` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `bill_type` (`bill`, `invoice_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================================================
-- TABLE: users (login credentials)
-- =========================================================

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `users` (`id`, `username`, `password`) VALUES
(1, 'delvin', '1987');

COMMIT;

 /*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
 /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
 /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
