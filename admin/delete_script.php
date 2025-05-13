<?php
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        require_once '../config/database.php';

        // Prepare and execute the deletion
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->execute([$id]);

        // Check if the deletion was successful
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
?>
