-- Accordionella MySQL Database Schema
-- Use this file to import the `bookings` table in InfinityFree phpMyAdmin if required.

CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_name` VARCHAR(255) NOT NULL,
  `client_email` VARCHAR(255) DEFAULT '',
  `client_phone` VARCHAR(100) NOT NULL,
  `referral_source` VARCHAR(255) NOT NULL,
  `event_type` VARCHAR(255) NOT NULL,
  `booking_date` VARCHAR(50) DEFAULT NULL,
  `booking_time` VARCHAR(50) DEFAULT NULL,
  `event_location` TEXT DEFAULT NULL,
  `sound_system` VARCHAR(10) DEFAULT 'no',
  `language` VARCHAR(10) DEFAULT 'en',
  `status` VARCHAR(50) DEFAULT 'Pending',
  `confirmed_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
