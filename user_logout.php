<?php
// user_logout.php
ob_start();
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['user_id']);
unset($_SESSION['user_username']);
unset($_SESSION['user_email']);

if (ob_get_length()) ob_clean();
echo json_encode(["status" => "success", "message" => "Logged out successfully."]);
exit;
