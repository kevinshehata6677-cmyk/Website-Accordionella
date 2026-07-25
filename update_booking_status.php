<?php
// update_booking_status.php
// action=confirm  -> marks Confirmed, asks Apps Script to create the calendar event
//                     (starting 2 hours before the client's chosen time) and email the client.
// action=cancel   -> marks Cancelled. No calendar event, no Apps Script call.

ob_start();
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

requireAdminLogin();

try {
    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    $bookingId = (int)($data['booking_id'] ?? 0);
    $action    = $data['action'] ?? '';

    if (!$bookingId || !in_array($action, ['confirm', 'cancel'], true)) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Missing or invalid booking_id/action."]);
        exit;
    }

    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = :id");
    $stmt->execute([':id' => $bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Booking not found."]);
        exit;
    }

    if ($action === 'cancel') {
        $upd = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = :id");
        $upd->execute([':id' => $bookingId]);
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "success", "message" => "Booking cancelled."]);
        exit;
    }

    // action === 'confirm'
    $upd = $pdo->prepare("UPDATE bookings SET status = 'Confirmed', confirmed_at = NOW() WHERE id = :id");
    $upd->execute([':id' => $bookingId]);

    // Ask the Apps Script to create the calendar event (starting 2 hours before the
    // client's chosen start time, for band setup) and send the final confirmation email.
    $payload = [
        'action'          => 'confirm_booking',
        'name'            => $booking['client_name'],
        'email'           => $booking['client_email'],
        'phone'           => $booking['client_phone'],
        'referral_source' => $booking['referral_source'],
        'event_type'      => $booking['event_type'],
        'date'            => $booking['booking_date'],
        'time'            => substr($booking['booking_time'] ?? '', 0, 5),
        'event_location'  => $booking['event_location'],
        'sound_system'    => $booking['sound_system'],
        'lang'            => $booking['language'] ?: 'en'
    ];

    $ch = curl_init(GOOGLE_SCRIPT_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/plain;charset=utf-8']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $appsScriptResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if (ob_get_length()) ob_clean();
    echo json_encode([
        "status"  => "success",
        "message" => "Booking confirmed. Calendar event requested.",
        "apps_script_response" => $appsScriptResponse,
        "apps_script_error"    => $curlError ?: null
    ]);

} catch (PDOException $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(["status" => "error", "message" => "System error: " . $e->getMessage()]);
}
