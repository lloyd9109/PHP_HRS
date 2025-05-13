<?php
require_once '../config/database.php';

// Fetch the message
$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$message) {
        die("Message not found.");
    }
}

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply = htmlspecialchars($_POST['reply']);
    $stmt = $pdo->prepare("UPDATE messages SET reply = :reply, replied_at = NOW() WHERE id = :id");
    $stmt->execute([':reply' => $reply, ':id' => $id]);

    echo "<script>alert('Reply sent successfully!'); window.location.href='admin_messages.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Message</title>
    <link rel="stylesheet" href="./styles/admin.css">
</head>
<body>
    <h2>Reply to Message</h2>
    <p><strong>Name:</strong> <?= htmlspecialchars($message['name']); ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($message['email']); ?></p>
    <p><strong>Subject:</strong> <?= htmlspecialchars($message['subject']); ?></p>
    <p><strong>Message:</strong> <?= htmlspecialchars($message['message']); ?></p>
    
    <form action="" method="POST">
        <textarea name="reply" rows="5" placeholder="Write your reply here..." required><?= htmlspecialchars($message['reply'] ?? ''); ?></textarea>
        <button type="submit">Send Reply</button>
    </form>
</body>
</html>
