<?php
session_start();
include '../config/database.php'; // Adjust path as needed

if (!isset($_SESSION['staff_id'])) {
    header("Location: login_signup.php"); // Redirect to login if not logged in
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get booking ID and action from the POST request
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];

    // Prepare the SQL statement to update the status
    $sql = "UPDATE bookings SET status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    // Execute the update statement
    if ($stmt->execute([$action, $booking_id])) {
        // Redirect back to the dashboard after processing
        header("Location: staff_dashboard.php?msg=Booking $action successfully.");
        exit;
    } else {
        echo "<script>alert('Error updating booking.');</script>";
    }
} else {
    // If the request is not a POST, redirect back to the dashboard
    header("Location: staff_dashboard.php");
    exit;
}
?>
