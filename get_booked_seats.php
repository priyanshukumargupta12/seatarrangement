// get_booked_seats.php
<?php
require_once 'basedata.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT seat_number FROM bookings WHERE booking_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stmt->execute();
    $bookedSeats = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($bookedSeats);
} catch(PDOException $e) {
    error_log("Error fetching booked seats: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching booked seats']);
}