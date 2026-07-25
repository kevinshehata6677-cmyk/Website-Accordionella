<?php
// process_booking.php
// Saves a new booking request as "Pending". No calendar event is created here —
// that only happens once the admin confirms the event by phone (see confirm_booking.php).

ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once 'config.php';

    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    if (!$data) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Empty or invalid data payload received."]);
        exit;
    }

    // Extract & sanitize values
    $clientName     = trim($data['name'] ?? '');
    $clientEmail    = trim($data['email'] ?? '');
    $clientPhone    = trim($data['phone'] ?? '');
    $referralSource = trim($data['referral_source'] ?? '');
    $eventType      = trim($data['event_type'] ?? '');
    $bookingDate    = !empty($data['date']) ? trim($data['date']) : null;
    $bookingTime    = !empty($data['time']) ? trim($data['time']) : null;
    $eventLocation  = trim($data['event_location'] ?? '');
    $soundSystem    = ($data['sound_system'] ?? 'no') === 'yes' ? 'yes' : 'no';
    $language       = $data['lang'] ?? 'en';

    // Only the 4 main fields are always required; the rest are optional
    if (empty($clientName) || empty($clientPhone) || empty($referralSource) || empty($eventType)) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Required booking fields are missing."]);
        exit;
    }

    $pdo = getDBConnection();

    $sql = "INSERT INTO bookings (
                client_name,
                client_email,
                client_phone,
                referral_source,
                event_type,
                booking_date,
                booking_time,
                event_location,
                sound_system,
                language,
                status
            ) VALUES (:name, :email, :phone, :referral, :event_type, :b_date, :b_time, :location, :sound, :lang, 'Pending')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'       => $clientName,
        ':email'      => $clientEmail,
        ':phone'      => $clientPhone,
        ':referral'   => $referralSource,
        ':event_type' => $eventType,
        ':b_date'     => $bookingDate,
        ':b_time'     => $bookingTime,
        ':location'   => $eventLocation,
        ':sound'      => $soundSystem,
        ':lang'       => $language
    ]);

    if (ob_get_length()) ob_clean();
    echo json_encode([
        "status"     => "success",
        "message"    => "Booking request saved. Pending admin confirmation.",
        "booking_id" => $pdo->lastInsertId()
    ]);
    exit;

} catch (PDOException $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(["status" => "error", "message" => "Database write error: " . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(["status" => "error", "message" => "General processing system error: " . $e->getMessage()]);
    exit;
}