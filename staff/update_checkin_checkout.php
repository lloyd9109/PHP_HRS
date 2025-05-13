<?php
include '../config/database.php';

// Assuming user is logged in and their user ID is stored in session
session_start();
$user_id = $_SESSION['user_id']; // Retrieve the user_id from session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = $_POST['room_number'];
    $status = $_POST['status'];

    // Fetch the reservation details from the 'reserved' table
    $fetchQuery = "SELECT * FROM reserved WHERE room_number = :room_number";
    $fetchStmt = $pdo->prepare($fetchQuery);
    $fetchStmt->bindParam(':room_number', $room_number);
    $fetchStmt->execute();
    $reservation = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        echo 'Error: Reservation not found';
        exit;
    }

    // Prepare SQL query to update the check-in status in 'reserved' table
    $query1 = "UPDATE reserved SET check_in_out = :status WHERE room_number = :room_number";
    $stmt1 = $pdo->prepare($query1);
    $stmt1->bindParam(':status', $status);
    $stmt1->bindParam(':room_number', $room_number);

    // Update the 'status' column in the 'bookings' table for cancellations
    if ($status == 'cancelled') {
        $updateBookingsQuery = "UPDATE bookings SET status = 'cancelled' WHERE room_number = :room_number";
        $updateBookingsStmt = $pdo->prepare($updateBookingsQuery);
        $updateBookingsStmt->bindParam(':room_number', $room_number);
        $updateBookingsStmt->execute();

        $query2 = "UPDATE rooms SET availability = 'available' WHERE room_number = :room_number";
        $stmt2 = $pdo->prepare($query2);
        $stmt2->bindParam(':room_number', $room_number);
        $stmt2->execute();
    } else {
        // Prepare SQL query to update the availability in the 'rooms' table
        $query2 = "UPDATE rooms SET availability = :availability WHERE room_number = :room_number";
        $availability = ($status == 'complete') ? 'available' : 'booked';
        $stmt2 = $pdo->prepare($query2);
        $stmt2->bindParam(':availability', $availability);
        $stmt2->bindParam(':room_number', $room_number);
    }

    // Prepare SQL query to insert into the 'booking_history' table
    if ($status == 'complete') {
        $historyQuery = "INSERT INTO booking_history (user_id, room_number, full_name, email, phone_number, preferred_date, preferred_time, guests, check_in_out, price, total_payment) 
                         VALUES (:user_id, :room_number, :full_name, :email, :phone_number, :preferred_date, :preferred_time, :guests, :check_in_out, :price, :total_payment)";
        $historyStmt = $pdo->prepare($historyQuery);
        $historyStmt->bindParam(':user_id', $user_id);
        $historyStmt->bindParam(':room_number', $reservation['room_number']);
        $historyStmt->bindParam(':full_name', $reservation['full_name']);
        $historyStmt->bindParam(':email', $reservation['email']);
        $historyStmt->bindParam(':phone_number', $reservation['phone_number']);
        $historyStmt->bindParam(':preferred_date', $reservation['preferred_date']);
        $historyStmt->bindParam(':preferred_time', $reservation['preferred_time']);
        $historyStmt->bindParam(':guests', $reservation['guests']);
        $historyStmt->bindParam(':check_in_out', $status);
        $historyStmt->bindParam(':price', $reservation['price']);
        $historyStmt->bindParam(':total_payment', $reservation['total_payment']);

        // Prepare SQL queries to delete from 'reserved' and 'bookings' tables
        $deleteReservedQuery = "DELETE FROM reserved WHERE room_number = :room_number";
        $deleteReservedStmt = $pdo->prepare($deleteReservedQuery);
        $deleteReservedStmt->bindParam(':room_number', $room_number);

        $deleteBookingsQuery = "DELETE FROM bookings WHERE room_number = :room_number";
        $deleteBookingsStmt = $pdo->prepare($deleteBookingsQuery);
        $deleteBookingsStmt->bindParam(':room_number', $room_number);
    }

    try {
        $pdo->beginTransaction();

        // Update reservation status
        $stmt1->execute();

        // Update room availability
        $stmt2->execute();

        // Insert into booking history and delete rows from 'reserved' and 'bookings'
        if ($status == 'complete') {
            $historyStmt->execute();
            $deleteReservedStmt->execute();
            $deleteBookingsStmt->execute();
        }

        $pdo->commit();
        echo 'Success';
    } catch (Exception $e) {
        $pdo->rollBack();
        echo 'Error: ' . $e->getMessage();
    }
}
?>
