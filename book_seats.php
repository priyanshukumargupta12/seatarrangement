// book_seats.php
<?php
require_once 'basedata.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input data
    if (!$data || !isset($data['name']) || !isset($data['email']) || !isset($data['seats']) || !is_array($data['seats'])) {
        throw new Exception('Invalid input data');
    }

    // Validate name
    if (!preg_match('/^[A-Za-z\s]+$/', $data['name'])) {
        throw new Exception('Invalid name format');
    }

    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate seat numbers
    foreach ($data['seats'] as $seat) {
        if (!is_numeric($seat) || $seat < 1 || $seat > 40) {
            throw new Exception('Invalid seat number');
        }
    }

    $pdo->beginTransaction();
    
    // Check if seats are already booked
    $stmt = $pdo->prepare("SELECT seat_number FROM bookings WHERE seat_number = ? AND booking_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    foreach ($data['seats'] as $seatNumber) {
        $stmt->execute([$seatNumber]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("Seat $seatNumber is already booked.");
        }
    }
    
    // Insert booking
    $stmt = $pdo->prepare("INSERT INTO bookings (name, email, seat_number, booking_date) VALUES (?, ?, ?, NOW())");
    foreach ($data['seats'] as $seatNumber) {
        $stmt->execute([
            htmlspecialchars($data['name']),
            $data['email'],
            $seatNumber
        ]);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Booking error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}