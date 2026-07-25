<?php
// user_login.php
// Authenticates a client or admin user with email and password from database
ob_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once 'config.php';

    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    $email     = trim(strtolower($data['email'] ?? $data['login'] ?? ''));
    $password  = $data['password'] ?? '';
    $loginMode = $data['mode'] ?? 'client'; // 'client' or 'admin'

    if (empty($email) || empty($password)) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Please enter your email and password."]);
        exit;
    }

    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT id, username, email, password_hash, role FROM users WHERE LOWER(email) = :e OR LOWER(username) = :e LIMIT 1");
    $stmt->execute([':e' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Invalid email or password. Please check your credentials."]);
        exit;
    }

    if ($loginMode === 'admin' && $user['role'] !== 'admin') {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Access denied. This account does not have admin privileges."]);
        exit;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_email'] = $user['email'];

    if ($user['role'] === 'admin') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id']        = $user['id'];
        $_SESSION['admin_username']  = $user['username'] ?: $user['email'];
    }

    if (ob_get_length()) ob_clean();
    echo json_encode([
        "status"   => "success",
        "message"  => "Logged in successfully.",
        "redirect" => ($loginMode === 'admin' || $user['role'] === 'admin') ? 'admin.php' : null,
        "user" => [
            "id"    => $user['id'],
            "email" => $user['email'],
            "role"  => $user['role']
        ]
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
