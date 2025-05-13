<?php
// process_reservation.php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $reservation_id = $_POST['reservation_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $sql = "UPDATE reservations SET status = 'approved' WHERE id = ?";
    } elseif ($action === 'deny') {
        $sql = "UPDATE reservations SET status = 'denied' WHERE id = ?";
    }

    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$reservation_id])) {
        echo "Reservation {$action}d successfully.";
    } else {
        echo "Error updating reservation.";
    }
}
?>
