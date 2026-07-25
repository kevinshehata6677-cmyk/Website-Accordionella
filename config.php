<?php
// config.php

// Google Apps Script API Webhook URL
define('GOOGLE_SCRIPT_URL', 'https://script.google.com/macros/s/AKfycbyyBjFkdfeXyhBUKUq_MtrlI0KVQAkY7LlAdUuQxnFMrISDwyxllPLVLF8QyDZ5_WhNyA/exec');

// Database Credentials
define('DB_HOST', 'sql306.infinityfree.com');
define('DB_USER', 'if0_42254420'); // Change to your production database username
define('DB_PASS', 'PzVgZi1rqLuyNZ1');     // Change to your production database password
define('DB_NAME', 'if0_42254420_accordionella');

// Admin Dashboard Credentials
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', '$2y$10$64oW1c8wW3aX4e5f6g7h8uI9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x');

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

        // Auto-create users table if it doesn't exist yet
        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) NOT NULL UNIQUE,
            `email` VARCHAR(255) NOT NULL,
            `password_hash` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(100) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Ensure missing columns are dynamically added if table existed from an older version
        $existingCols = [];
        $colStmt = $pdo->query("SHOW COLUMNS FROM `bookings`");
        while ($row = $colStmt->fetch(PDO::FETCH_ASSOC)) {
            $existingCols[strtolower($row['Field'])] = true;
        }

        $neededCols = [
            'user_id'         => "ADD COLUMN `user_id` INT NULL DEFAULT NULL",
            'client_name'     => "ADD COLUMN `client_name` VARCHAR(255) NOT NULL DEFAULT ''",
            'client_email'    => "ADD COLUMN `client_email` VARCHAR(255) DEFAULT ''",
            'client_phone'    => "ADD COLUMN `client_phone` VARCHAR(100) NOT NULL DEFAULT ''",
            'referral_source' => "ADD COLUMN `referral_source` VARCHAR(255) NOT NULL DEFAULT ''",
            'event_type'      => "ADD COLUMN `event_type` VARCHAR(255) NOT NULL DEFAULT ''",
            'booking_date'    => "ADD COLUMN `booking_date` VARCHAR(50) DEFAULT NULL",
            'booking_time'    => "ADD COLUMN `booking_time` VARCHAR(50) DEFAULT NULL",
            'event_location'  => "ADD COLUMN `event_location` TEXT DEFAULT NULL",
            'sound_system'    => "ADD COLUMN `sound_system` VARCHAR(10) DEFAULT 'no'",
            'language'        => "ADD COLUMN `language` VARCHAR(10) DEFAULT 'en'",
            'status'          => "ADD COLUMN `status` VARCHAR(50) DEFAULT 'Pending'",
            'confirmed_at'    => "ADD COLUMN `confirmed_at` DATETIME DEFAULT NULL",
            'created_at'      => "ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
        ];

        foreach ($neededCols as $col => $alterSql) {
            if (!isset($existingCols[$col])) {
                $pdo->exec("ALTER TABLE `bookings` " . $alterSql);
            }
        }

        // Relax NOT NULL constraints on date/time columns if table existed from an older version
        try {
            $pdo->exec("ALTER TABLE `bookings` MODIFY `booking_date` VARCHAR(50) NULL DEFAULT ''");
            $pdo->exec("ALTER TABLE `bookings` MODIFY `booking_time` VARCHAR(50) NULL DEFAULT ''");
        } catch (Exception $ex) {
            // Ignore if alter fails
        }

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

// Client authentication helpers
function getLoggedInUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!empty($_SESSION['user_id'])) {
        return [
            'id'    => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'] ?? ''
        ];
    }
    return null;
}

function requireUserLogin() {
    $user = getLoggedInUser();
    if (!$user) {
        if (ob_get_length()) ob_clean();
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
        }
        echo json_encode(["status" => "error", "message" => "Please log in to access your account."]);
        exit;
    }
    return $user;
}
?>