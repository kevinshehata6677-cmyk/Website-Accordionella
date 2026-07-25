-- Accordionella MySQL Database Schema

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'client',
  `phone` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Default Admin Account (Password: Accordionella@2026)
INSERT IGNORE INTO `users` (`username`, `email`, `password_hash`, `role`)
VALUES ('admin', 'admin@accordionella.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
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
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
