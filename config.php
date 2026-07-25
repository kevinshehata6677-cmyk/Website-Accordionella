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
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]);
        exit;
    }
}

// Call this at the top of any admin-only PHP file to require a logged-in session
function requireAdminLogin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['admin_logged_in'])) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Not authenticated. Please log in."]);
        exit;
    }
}
?>