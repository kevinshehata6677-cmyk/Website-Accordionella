<?php
// get_bookings.php
ob_start();
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

requireAdminLogin();

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT id, client_name, client_email, client_phone, referral_source,
                                 event_type, booking_date, booking_time, event_location,
                                 sound_system, language, status, confirmed_at, created_at
                          FROM bookings
                          ORDER BY id DESC");
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (ob_get_length()) ob_clean();
    echo json_encode(["status" => "success", "bookings" => $bookings]);
} catch (PDOException $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(["status" => "error", "message" => "Database reading failure: " . $e->getMessage()]);
}
