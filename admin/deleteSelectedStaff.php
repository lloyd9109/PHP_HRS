<?php
require_once '../config/database.php';

// Check if the staff IDs are passed
if (isset($_POST['staff_ids'])) {
    $staff_ids = json_decode($_POST['staff_ids']); // Decode the array of staff IDs

    // Prepare SQL query to delete staff from the database
    $query = "DELETE FROM staff WHERE id IN (" . implode(',', array_map('intval', $staff_ids)) . ")";
    $stmt = $pdo->prepare($query);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
}
?>
