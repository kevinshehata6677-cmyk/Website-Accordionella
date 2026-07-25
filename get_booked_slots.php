<?php
// get_booked_slots.php
ob_start();
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once 'config.php';

    $db = getDBConnection();

    // Fetch future/current bookings that are still active (Pending or Confirmed)
    // so the calendar can't be double-booked while an admin call is still pending.
    $stmt = $db->query("SELECT booking_date, booking_time FROM bookings
                         WHERE (booking_date IS NOT NULL AND booking_date != '' AND booking_date >= CURDATE()) AND status != 'Cancelled'");
    $bookedSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (ob_get_length()) ob_clean();
    echo json_encode([
        "status" => "success",
        "booked" => $bookedSlots
    ]);

} catch (PDOException $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode([
        "status" => "error",
        "message" => "Database reading failure: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode([
        "status" => "error",
        "message" => "System processing failure: " . $e->getMessage()
    ]);
}