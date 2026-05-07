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
(44, 'ALIF TRADER', '33AADFA2185M1ZH', 'tngst', ''),
(45, 'MRS INDUSTRIES', '33ATRPR2098D3ZB', 'tngst', ''),
(46, 'AMPS TOOLS', '33BEJPS1968F1Z9', 'tngst', ''),
(47, 'RANE AUTOMOTIVE INDIA PVT LTD (VIRALIMALAI)', '33AAACR3147C1ZY', 'tngst', ''),
(48, 'VENKATESA ENGG CONSULTANT PVT LTD', '33AABCV0736A1Z0', 'tngst', ''),
(49, 'T.M.A KUPPUSAMY CHETTIYAR & SONS', '33AABFT0332P1Z9', 'tngst', ''),
(50, 'JET ENTERPRISES', '29ACRPC1229L1ZC', 'igst', ''),
(51, 'INDIA MART INTERMESH LTD', '09AAACI5853L2Z5', 'igst', ''),
(52, 'southern tools & hardwares', '33agups0637d1zb', 'tngst', ''),
(53, 'MICRO SHARP NEEDLES PVT LTD', '33AADCM9688C1ZA', 'tngst', ''),
(54, 'THIRUMALA ELECTRODES', '33AADFT6100F1ZQ', 'tngst', ''),
(55, 'BANSAL INDUSTRIAL TOOLS', '06AENPB8134R1ZZ', 'igst', ''),
(56, 'BALAJI TOOLS', '33AAVPR3966K1ZU', 'tngst', ''),
(58, 'THREE SIXTY INDUSTRIES', '33AZEPN7144H1ZD', 'tngst', ''),
(59, 'VISION TOOLS', '27CRAPS4852F1ZM', 'igst', ''),
(60, 'CRYSTAL HARDWARE & TOOLS', '33BAHPA5884N1ZD', 'tngst', ''),
(61, 'ASHIKA TOOLS', '33AAMFA9025H1ZI', 'tngst', ''),
(62, 'M.P.M MUTHU GENERAL STORES ', '33AAAFM8415D1ZQ', '6p', ''),
(63, 'THE METAL POWDER COMPANY LIMITED', '33AAACT4262E1ZQ', 'tngst', ''),
(64, 'Sree valves', '33FPOPS3099B1ZJ', 'tngst', ''),
(66, 'INDUSTRIAL EQUIPMENT CO', '33ACWPA7669K1ZY', 'tngst', ''),
(67, 'M.Q.ENTERPRISES', '33AAMFM3618C1ZL', 'tngst', ''),
(68, 'VRJ ENTERPRISES', '29AGLPS4768A1ZZ', 'igst', ''),
(69, 'SANDHAYA TRADERS', '24AFBPD5546C1Z2', 'igst', ''),
(70, 'EXCEL IMPEX', '32ACUPT1143K1ZA', 'igst', ''),
(71, 'TECHNO SUPERABRASIVES', '33GFPPS9592F1ZK', 'tngst', ''),
(73, 'AVM MEDICAL & SURGICALS', '33ACRPV8244N1ZN', '6p', ''),
(74, 'SCHUTZ CARBON ELECTRODES PVT LTD', '24AADCS0463M1ZB', 'igst', ''),
(75, 'SERVAL PAPER PVT LTD', '33ABFCS6978J1ZQ', 'tngst', ''),
(76, 'HICAL TECHNOLOGIES PVT LTD', '29AACCH7296L1ZU', 'igst', ''),
(77, 'UNIZEDCHEM & POLIMERS', '08AVBPK9282E1ZF', 'igst', ''),
(78, 'UNIVERSAL STEEL & TOOL CO', '33AAAFU1429P1Z1', 'tngst', ''),
(79, 'JEYALAKSHMI TOOLS', '33AAFPA7619G1Z2', 'tngst', ''),
(81, 'TIGHTWELL FASTNERS (K)', '06AAAFT4735D1ZH', 'igst', ''),
(82, 'ORDNANCE FACTORY', '33AAVCA6457D1ZH', 'tngst', ''),
(83, 'MAHINDRA ENGINEERING TOOLS', '33ENKPS1386G1ZO', 'tngst', ''),
(84, 'MIRACLE CNC APPLICATION ', '33AAOFM6248C1ZB', 'tngst', ''),
(86, 'ROOTS PRECISION PRODUCTS', '33AADCR6315D1ZT', 'tngst', ''),
(87, 'HARDWARE & TOOLS SUPPLIERS', '33AACFH4003N1ZO', 'tngst', ''),
(89, 'TUBA ENGINEERS', '33AAAFT7559D1Z8', 'tngst', ''),
(90, 'M.M.S SETHURAMAN CHETTIYAR & SONS', '33aaefm8459k1zv', 'tngst', ''),
(91, 'TAMIL NADU NEWSPRINT AND PAPERS LTD', '33AAACT2935J1ZF', 'tngst', ''),
(92, 'KANNAN TOOLS & HARDWARES', '33BXSPR0563D1ZD', 'tngst', ''),
(93, 'REGAL CARBIDE DIES PVT LTD', '07AAFCR9337K1ZV', 'igst', ''),
(94, 'MODERN DIAMOND TOOLS', '33AASFM1167E1ZB', 'tngst', ''),
(95, 'BHARATH SURGICALS (6% GST)', '33ATQPM7171G1ZZ', '6p', ''),
(96, 'SUNVIK STEELS PVT LTD STORE', '29AAHCS6286N1ZE', 'igst', ''),
(97, 'WEZMANN TOOLS PVT LTD', '27AAACW9213G1ZB', 'igst', ''),
(98, 'JINDAL ENTERPRISES', '03BTHPJ3755L1Z1', 'igst', ''),
(99, 'PARAS DIAMOND CO(%18GST)', '24CMXPS4074B1ZP', 'igst', ''),
(100, 'DNA PRECISION WORKS', '27AALFD6571A1ZI', 'igst', ''),
(101, 'jidoka automations', '06coxpr2019m1z8', 'igst', ''),
(102, 'swathi tools & hardwares', '33aalpd6001b1zl', 'tngst', ''),
(103, 'ohm shivo enterprises', '33cgupd6132l1z5', 'tngst', ''),
(104, 'n-tec industrial solutions', '32dfmpk1025r1z7', 'igst', ''),
(105, 'impact calibration and testing solutions pvt.ltd', '29aahcl4055p1zx', 'igst', ''),
(106, 'INDIAN INSTITUTE OF INFORMATION TECHNOLOGY', '37AABAI0429D1Z5', 'igst', ''),
(107, 'A B ENTERPRISES', '27ACHFA6308C1ZW', 'igst', ''),
(108, 'ACE GRIND TECH', '33ACEFA8974F1ZA', 'tngst', ''),
(109, 'KIRTHIKA TECH ENGINEERING', '33BINPS5648L1ZI', 'tngst', ''),
(110, 'MABS ASSOCIATES', '33AAGFM0715J1ZK', 'tngst', ''),
(111, 'INDAR ELECTRICALS', '33AAFPA7663N1ZJ', 'tngst', ''),
(112, 'QCID TECHNOLOGIES PVT LTD', '29AAACQ5702A1ZV', 'igst', ''),
(113, 'VAIBHAV CARTONS PVT LTD', '33AADCV2061J1ZH', '6p', ''),
(114, 'BHEL', '33AAACB4146P2ZL', 'tngst', ''),
(115, 'KOWSHIKA TOOLS & TRADERS', '34AAJFK9819Q1ZG', 'igst', ''),
(116, 'POPULAR ENGINEERING STORES', '33AACFP2922Q1Z2', 'tngst', ''),
(117, 'S.J.S TRADERS', '33AAVJF1606L1Z4', 'tngst', ''),
(118, 'SMS GEARS', '33DKQPP2137G1Z1', 'tngst', ''),
(119, 'ROLEX ENGINEERING COMPANY', '33ABBFR4310E1ZU', 'tngst', ''),
(120, 'SARVESH MULTI PLAST INDIA PVT LTD', '33AANCS0352D1ZP', 'tngst', ''),
(121, 'PRIME TRADING CO', '33AIXPS2221G1Z5', 'tngst', ''),
(122, 'AUTOMECH TOOLS', '33AADFA2206M1ZV', 'tngst', ''),
(123, 'SREE EASWAR ENGINEERS', '33AABPE6818R1ZG', 'tngst', ''),
(125, 'SIMHA SPRINGS PVT.LTD', '27AAICS5912J1Z2', 'igst', ''),
(126, 'GOOD LUCK AGENCIES', '33AABFG4197P1ZZ', 'tngst', ''),
(127, 'THAKER ENGG CO PVT.LTD', '24AAACT4535E1ZP', 'igst', ''),
(128, 'BHARAT REFRACTORIES ENGINEERS', '20AAKFB7681C1ZQ', 'igst', ''),
(129, 'RAMAKRISHNA COMPONENTS MFG PVT LTD', '33AACCR9612E1ZM', 'tngst', ''),
(130, 'R.D ENGINEERING WORKS', '27ACSPY7235R1Z7', 'igst', ''),
(131, 'INTECH GRINDING WORKS', '29AAXPV2583J1ZK', 'igst', ''),
(132, 'SADHI TECH INDI ENTERPRISES', '24ERRPP2867J1Z0', 'igst', ''),
(133, 'PERFECT INDUSTRIES SUPLIERS', '33ABSPN2825P1Z1', 'tngst', ''),
(134, 'VINAYAHA TOOLS & HARDWARE', '33AIDPJ9913E1ZH', 'tngst', ''),
(135, 'TECHXEARTHSPACE PVT LTD', '29AAKCT2224L1Z0', 'igst', ''),
(137, 'ELITE INDUSTRIES', '29ADJPE8663M1ZU', 'igst', ''),
(138, 'DYNAMIC TRANSMISSION LTD', '06AAACD4358H1ZS', 'igst', ''),
(139, 'ZEN TECH FASTENERS PVT LTD', '33AACCZ8888N1ZB', 'tngst', ''),
(140, 'ANNAI ENTERPRISES', '33AHRPH5216M1Z3', 'tngst', '');

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
