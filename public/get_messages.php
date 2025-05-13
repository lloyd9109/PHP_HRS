<?php
session_start();
require_once '../config/database.php';

$userId = isset($_GET['user_id']) ? $_GET['user_id'] : $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM chat_messages WHERE user_id = ? ORDER BY timestamp ASC");
$stmt->execute([$userId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);
?>
