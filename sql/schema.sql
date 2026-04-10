-- Add new columns to companydata table
ALTER TABLE `companydata` ADD COLUMN IF NOT EXISTS `address` VARCHAR(500) DEFAULT '' AFTER `gsttype`;
ALTER TABLE `companydata` ADD COLUMN IF NOT EXISTS `state` VARCHAR(100) DEFAULT '' AFTER `address`;
ALTER TABLE `companydata` ADD COLUMN IF NOT EXISTS `district` VARCHAR(100) DEFAULT '' AFTER `state`;

-- Tools table
CREATE TABLE IF NOT EXISTS `tools` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `toolname` VARCHAR(255) NOT NULL,
    `rate` DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;