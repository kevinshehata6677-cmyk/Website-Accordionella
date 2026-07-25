<?php
// config.php

// Google Apps Script API Webhook URL
define('GOOGLE_SCRIPT_URL', 'https://script.google.com/macros/s/AKfycbzbs-IgIkkjLykMFMOPE_J6w_SQwxO3ehtsBK9Ln_ZhgG5GiJymC7-52j93SK6uBYVPlg/exec');

// Database Credentials
define('DB_HOST', 'sql306.infinityfree.com');
define('DB_USER', 'if0_42254420'); // Change to your production database username
define('DB_PASS', 'PzVgZi1rqLuyNZ1');     // Change to your production database password
define('DB_NAME', 'if0_42254420_accordionella');

// Admin Dashboard Credentials
// IMPORTANT: change ADMIN_USERNAME below, and generate a new password hash with:
//   php -r "echo password_hash('YourNewPassword', PASSWORD_DEFAULT);"
// then paste the result into ADMIN_PASSWORD_HASH. The default password below is:
//   Accordionella@2026
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', '$2b$10$tib3fOA65kHDs0PAabFY/ugQlUaEgHOxTdvbMsVP1r0Hd/2z7lR52');

// Secure Database Connection Helper
function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]);

        // Auto-create bookings table if it doesn't exist yet
        $pdo->exec("CREATE TABLE IF NOT EXISTS `bookings` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        return $pdo;
    } catch (PDOException $e) {
        if (ob_get_length()) ob_clean();
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(["status" => "error", "message" => "Database connection/schema error: " . $e->getMessage()]);
        exit;
    }
}

// Call this at the top of any admin-only PHP file to require a logged-in session
function requireAdminLogin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['admin_logged_in'])) {
        if (ob_get_length()) ob_clean();
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
        }
        echo json_encode(["status" => "error", "message" => "Not authenticated. Please log in."]);
        exit;
    }
}
?>