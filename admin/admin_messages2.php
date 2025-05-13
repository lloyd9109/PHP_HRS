<?php
require_once '../config/database.php';

// Fetch messages
$sql = "SELECT * FROM messages ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Messages</title>
    <link rel="stylesheet" href="./styles/admin.css">
</head>
<body>
    <h2>Messages</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Reply</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $message): ?>
            <tr>
                <td><?= htmlspecialchars($message['name']); ?></td>
                <td><?= htmlspecialchars($message['email']); ?></td>
                <td><?= htmlspecialchars($message['subject']); ?></td>
                <td><?= htmlspecialchars($message['message']); ?></td>
                <td><?= htmlspecialchars($message['reply'] ?? 'No reply yet'); ?></td>
                <td>
                    <a href="reply_message.php?id=<?= $message['id']; ?>">Reply</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

<style>
table {
    width: 100%;
    border-collapse: collapse;
}

thead th {
    background-color: #333;
    color: white;
    padding: 10px;
}

tbody td {
    padding: 10px;
    border-bottom: 1px solid #ccc;
}

a {
    color: #f1c40f;
    text-decoration: none;
}

</style>