<?php
require_once '../config/database.php';

// Update all unnotified bookings to notified
$sql = "UPDATE bookings SET notified = 1 WHERE notified = 0";
$stmt = $pdo->prepare($sql);
$stmt->execute();
?>
