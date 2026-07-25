<?php
// user_login.php
ob_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once 'config.php';

    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    $login    = trim($data['login'] ?? ''); // username or email
    $password = $data['password'] ?? '';

    if (empty($login) || empty($password)) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Please enter your username/email and password."]);
        exit;
    }

    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE username = :l OR email = :l LIMIT 1");
    $stmt->execute([':l' => $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Invalid credentials. Check your username/email and password."]);
        exit;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);
    $_SESSION['user_id']       = $user['id'];
    $_SESSION['user_username'] = $user['username'];
    $_SESSION['user_email']    = $user['email'];

    if (ob_get_length()) ob_clean();
    echo json_encode([
        "status" => "success",
        "message" => "Logged in successfully.",
        "user" => [
            "id"       => $user['id'],
            "username" => $user['username'],
            "email"    => $user['email']
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
