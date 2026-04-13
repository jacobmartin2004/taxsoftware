-- Migration script for existing databases
-- Run this ONCE via phpMyAdmin to update your existing DB

-- =========================================================
-- 1. Fix companydata: drop state and district columns
-- =========================================================
ALTER TABLE `companydata` DROP COLUMN IF EXISTS `state`;
ALTER TABLE `companydata` DROP COLUMN IF EXISTS `district`;

-- =========================================================
-- 2. Add delivery challan fields to delvin (sales) table
-- =========================================================
ALTER TABLE `delvin` ADD COLUMN IF NOT EXISTS `challan_no` int(11) DEFAULT NULL;
ALTER TABLE `delvin` ADD COLUMN IF NOT EXISTS `challan_date` varchar(30) DEFAULT NULL;

-- =========================================================
-- 3. Fix purchase table primary key
--    If your purchase table used taxamt as primary key,
--    this will fix it to use sno (auto-increment)
-- =========================================================

-- Check if sno column exists; if not, recreate the table properly
-- Option A: If table has wrong primary key, run these:
-- (Uncomment the lines below if your purchase table has taxamt as PK)

-- ALTER TABLE `purchase` DROP PRIMARY KEY;
-- ALTER TABLE `purchase` ADD COLUMN `sno` int(11) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`sno`);

-- Option B: If you want to recreate the purchase table cleanly
-- (WARNING: This will DELETE all existing purchase data!)
-- Uncomment below only if you want a fresh purchase table:

/*
DROP TABLE IF EXISTS `purchase`;
CREATE TABLE `purchase` (
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
*/

-- =========================================================
-- Safe fix: If purchase table exists but has wrong PK structure,
-- this adds sno if missing and fixes the primary key
-- =========================================================
-- Run these one by one in phpMyAdmin if needed:
-- 1. Check current structure: DESCRIBE purchase;
-- 2. If sno doesn't exist:
--    ALTER TABLE `purchase` ADD COLUMN `sno` int(11) NOT NULL AUTO_INCREMENT FIRST, DROP PRIMARY KEY, ADD PRIMARY KEY (`sno`);
-- 3. If bill doesn't have unique key:
--    ALTER TABLE `purchase` ADD UNIQUE KEY `bill` (`bill`);
