<?php
session_start();
include '../config/database.php'; // Adjust path as needed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];

    if ($action === 'approved') {
        $new_status = 'Approved';
    } elseif ($action === 'denied') {
        $new_status = 'Denied';
    } else {
        exit('Invalid action');
    }

    $stmt = $pdo->prepare("UPDATE bookings SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $new_status);
    $stmt->bindParam(':id', $booking_id);
    $stmt->execute();

    // Return the new status as a response
    echo $new_status;
    exit();
}
?>
