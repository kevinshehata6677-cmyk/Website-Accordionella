<?php
// process_booking.php
// Saves a new booking request as "Pending". Optionally creates a client user account if requested.

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
    $clientEmail    = trim(strtolower($data['email'] ?? ''));
    $clientPhone    = trim($data['phone'] ?? '');
    $referralSource = trim($data['referral_source'] ?? '');
    $eventType      = trim($data['event_type'] ?? '');
    $bookingDate    = trim($data['date'] ?? '');
    $bookingTime    = trim($data['time'] ?? '');
    $eventLocation  = trim($data['event_location'] ?? '');
    $soundSystem    = ($data['sound_system'] ?? 'no') === 'yes' ? 'yes' : 'no';
    $language       = $data['lang'] ?? 'en';

    // Required fields check
    if (empty($clientName) || empty($clientPhone) || empty($referralSource) || empty($eventType)) {
        if (ob_get_length()) ob_clean();
        echo json_encode(["status" => "error", "message" => "Required booking fields are missing."]);
        exit;
    }

    $pdo = getDBConnection();
    $userId = null;

    // Check existing logged in user
    $currentUser = getLoggedInUser();
    if ($currentUser) {
        $userId = $currentUser['id'];
    } elseif (!empty($data['create_account'])) {
        // Create new account as requested during booking (using email + password)
        $accPassword = $data['account_password'] ?? '';

        if (empty($clientEmail) || empty($accPassword)) {
            if (ob_get_length()) ob_clean();
            echo json_encode(["status" => "error", "message" => "To create an account, email address and password are required."]);
            exit;
        }

        if (strlen($accPassword) < 6) {
            if (ob_get_length()) ob_clean();
            echo json_encode(["status" => "error", "message" => "Account password must be at least 6 characters long."]);
            exit;
        }

        // Check if account with this email already exists
        $chk = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = :e LIMIT 1");
        $chk->execute([':e' => $clientEmail]);
        $existing = $chk->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            $userId = $existing['id'];
        } else {
            $hash = password_hash($accPassword, PASSWORD_DEFAULT);
            $userStmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, phone) VALUES (:u, :e, :h, :p)");
            $userStmt->execute([
                ':u' => $clientEmail,
                ':e' => $clientEmail,
                ':h' => $hash,
                ':p' => $clientPhone
            ]);
            $userId = $pdo->lastInsertId();
        }

        // Auto-login session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id']    = $userId;
        $_SESSION['user_email'] = $clientEmail;
    }

    $sql = "INSERT INTO bookings (
                user_id,
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
            ) VALUES (:uid, :name, :email, :phone, :referral, :event_type, :b_date, :b_time, :location, :sound, :lang, 'Pending')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':uid'        => $userId,
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
        "booking_id" => $pdo->lastInsertId(),
        "user_created" => !empty($data['create_account']) && $userId ? true : false
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