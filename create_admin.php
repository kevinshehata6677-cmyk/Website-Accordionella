<?php
// create_admin.php
// Endpoint for an authenticated admin to register a new admin account in the database.
ob_start();
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

requireAdminLogin();

try {
    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    $email    = trim(strtolower($data['email'] ?? ''));
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Email and password are required."]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Please enter a valid email address."]);
        exit;
    }

    if (strlen($password) < 6) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Admin password must be at least 6 characters long."]);
        exit;
    }

    $pdo = getDBConnection();

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id, role FROM users WHERE LOWER(email) = :e OR LOWER(username) = :e LIMIT 1");
    $stmt->execute([':e' => $email]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    $hash = password_hash($password, PASSWORD_DEFAULT);

    if ($existing) {
        // Upgrade existing account to admin and update password
        $upd = $pdo->prepare("UPDATE users SET password_hash = :h, role = 'admin' WHERE id = :id");
        $upd->execute([':h' => $hash, ':id' => $existing['id']]);
    } else {
        // Insert new admin user
        $username = $email;
        $ins = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (:u, :e, :h, 'admin')");
        $ins->execute([':u' => $username, ':e' => $email, ':h' => $hash]);
    }

    if (ob_get_length()) ob_clean();
    echo json_encode([
        "status"  => "success",
        "message" => "New admin account registered successfully for " . $email
    ]);
    exit;

} catch (PDOException $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(["status" => "error", "message" => "System error: " . $e->getMessage()]);
    exit;
}
