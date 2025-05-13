<?php
include '../config/database.php';

if (isset($_GET['email'])) {
    $email = trim($_GET['email']);
    $sql = "SELECT 1 FROM staff WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['exists' => $exists ? true : false]);
}
?>
