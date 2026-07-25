<?php
// user_register.php
ob_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once 'config.php';

    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    $username = trim($data['username'] ?? '');
    $email    = trim(strtolower($data['email'] ?? ''));
    $password = $data['password'] ?? '';
    $phone    = trim($data['phone'] ?? '');

    if (empty($username) || empty($email) || empty($password)) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Username, email, and password are required."]);
        exit;
    }

    if (strlen($username) < 3) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Username must be at least 3 characters long."]);
        exit;
    }

    if (strlen($password) < 6) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters long."]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Please enter a valid email address."]);
        exit;
    }

    $pdo = getDBConnection();

    // Check if username or email already exists
    $chk = $pdo->prepare("SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1");
    $chk->execute([':u' => $username, ':e' => $email]);
    if ($chk->fetch()) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "An account with that username or email already exists."]);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, phone) VALUES (:u, :e, :h, :p)");
    $stmt->execute([
        ':u' => $username,
        ':e' => $email,
        ':h' => $hash,
        ':p' => $phone
    ]);

    $userId = $pdo->lastInsertId();

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['user_id']       = $userId;
    $_SESSION['user_username'] = $username;
    $_SESSION['user_email']    = $email;

    if (ob_get_length()) ob_clean();
    echo json_encode([
        "status" => "success",
        "message" => "Account created successfully.",
        "user" => [
            "id"       => $userId,
            "username" => $username,
            "email"    => $email
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
