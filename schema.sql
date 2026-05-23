CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `properties` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `county` VARCHAR(255) DEFAULT NULL,
    `country` VARCHAR(255) DEFAULT NULL,
    `town` VARCHAR(255) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `displayable_address` VARCHAR(500) DEFAULT NULL,
    `image_url` VARCHAR(500) DEFAULT NULL,
    `thumbnail_url` VARCHAR(500) DEFAULT NULL,
    `latitude` DECIMAL(10, 7) DEFAULT NULL,
    `longitude` DECIMAL(10, 7) DEFAULT NULL,
    `num_bedrooms` INT DEFAULT NULL,
    `num_bathrooms` INT DEFAULT NULL,
    `price` DECIMAL(12, 2) DEFAULT NULL,
    `property_type` VARCHAR(255) DEFAULT NULL,
    `property_type_id` INT DEFAULT NULL,
    `property_description` VARCHAR(255) DEFAULT NULL,
    `for_sale` TINYINT(1) DEFAULT 1,
    `source` ENUM('admin', 'api') NOT NULL DEFAULT 'admin',
    `api_uuid` VARCHAR(36) DEFAULT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO `admin_users` (`username`, `password`) VALUES ('admin', '$2a$12$EGMjTO.D8bKOOA7jl8zsiOL6cSn8U2SBRhFdMZv8jYEit7F8LZa3.');
