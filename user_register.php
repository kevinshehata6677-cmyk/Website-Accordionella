<?php
// user_register.php
// Creates a client user account with email and password (no username required)
ob_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once 'config.php';

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
        echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters long."]);
        exit;
    }

    $pdo = getDBConnection();

    // Check if email already exists
    $chk = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = :e LIMIT 1");
    $chk->execute([':e' => $email]);
    if ($chk->fetch()) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "An account with this email address already exists. Please log in."]);
        exit;
    }

    $username = $email; // Use email as default username internal value
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (:u, :e, :h)");
    $stmt->execute([
        ':u' => $username,
        ':e' => $email,
        ':h' => $hash
    ]);

    $userId = $pdo->lastInsertId();

    // Automatically link any past/pending reservations submitted under this email
    $linkStmt = $pdo->prepare("UPDATE bookings SET user_id = :uid WHERE LOWER(client_email) = :e AND (user_id IS NULL OR user_id = 0)");
    $linkStmt->execute([':uid' => $userId, ':e' => $email]);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['user_id']    = $userId;
    $_SESSION['user_email'] = $email;

    if (ob_get_length()) ob_clean();
    echo json_encode([
        "status" => "success",
        "message" => "Account created successfully.",
        "user" => [
            "id"    => $userId,
            "email" => $email
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
