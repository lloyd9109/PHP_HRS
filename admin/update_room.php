<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);
if ($data) {
    $roomNumber = $data['room_number'];
    $availability = $data['availability'];

    // Prepare the SQL query to update availability
    $sql = "UPDATE rooms SET availability = :availability WHERE room_number = :room_number";
    $stmt = $pdo->prepare($sql);

    // Bind parameters and execute
    $stmt->bindParam(':availability', $availability);
    $stmt->bindParam(':room_number', $roomNumber);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
