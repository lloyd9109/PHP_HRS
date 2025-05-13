<?php
require_once '../config/database.php';

// Check if the staff ID is passed
if (isset($_POST['staff_id'])) {
    $staff_id = $_POST['staff_id'];

    // Prepare SQL query to delete staff from the database
    $query = "DELETE FROM staff WHERE id = :staff_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
}
?>
