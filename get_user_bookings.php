<?php
// get_user_bookings.php
ob_start();
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

$user = getLoggedInUser();

if (!$user) {
    if (ob_get_length()) ob_clean();
    echo json_encode(["status" => "unauthenticated", "bookings" => []]);
    exit;
}

try {
    $pdo = getDBConnection();
    // Match bookings either linked by user_id OR matching client_email
    $stmt = $pdo->prepare("SELECT id, client_name, client_email, client_phone, referral_source,
                                 event_type, booking_date, booking_time, event_location,
                                 sound_system, language, status, confirmed_at, created_at
                          FROM bookings
                          WHERE user_id = :uid OR (client_email != '' AND client_email = :email)
                          ORDER BY id DESC");
    $stmt->execute([
        ':uid'   => $user['id'],
        ':email' => $user['email']
    ]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (ob_get_length()) ob_clean();
    echo json_encode([
        "status"   => "success",
        "user"     => $user,
        "bookings" => $bookings
    ]);
    exit;

} catch (PDOException $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    exit;
}
