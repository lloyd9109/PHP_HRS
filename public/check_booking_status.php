<?php
// check_booking_status.php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['bookingId'])) {
    $bookingId = $data['bookingId'];

    // Query to check the booking status
    $stmt = $pdo->prepare("SELECT status FROM bookings WHERE id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        // Return the status of the booking
        echo json_encode(['status' => $booking['status']]);
    } else {
        echo json_encode(['status' => 'not_found']);
    }
}
?>
