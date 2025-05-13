<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['booking_id'])) {
    $bookingId = $_POST['booking_id'];

    try {
        // Update the status of the booking to 'cancelled' in the database
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND status = 'pending'");
        $stmt->execute([$bookingId]);

        if ($stmt->rowCount() > 0) {
            // Return a success response
            echo json_encode(['success' => true]);
        } else {
            // Return an error response if booking couldn't be cancelled
            echo json_encode(['success' => false]);
        }
    } catch (PDOException $e) {
        // Return an error response in case of an exception
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
