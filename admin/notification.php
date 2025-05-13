<?php
session_start();
require_once '../config/database.php';
require_once 'admin.php';

// Use output buffering to prevent header issues
ob_start();


// Fetch notifications from the database (for demo purposes, we will simulate them)
$sql = "SELECT * FROM bookings ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="styles/notifications.css">
</head>
<body>
    <div class="notification-bell">
        <i class="fas fa-bell"></i>
        <span class="notification-count"><?php echo count($notifications); ?></span>
    </div>

    <div class="notifications-container">
        <h2>Recent Notifications</h2>
        <ul>
            <?php foreach ($notifications as $notification): ?>
                <li>
                    <?php echo htmlspecialchars($notification['message']); ?> - 
                    <small><?php echo htmlspecialchars($notification['created_at']); ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>

<style>
    .notification-bell {
    position: relative;
    font-size: 2rem;
    cursor: pointer;
}

.notification-count {
    position: absolute;
    top: 0;
    right: 0;
    background-color: red;
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 50%;
    font-size: 1rem;
}

.notifications-container {
    margin-top: 20px;
    padding: 20px;
    background-color: #f4f4f4;
    border-radius: 5px;
}

.notifications-container h2 {
    font-size: 1.5rem;
}

.notifications-container ul {
    list-style: none;
    padding: 0;
}

.notifications-container li {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

.notifications-container li:last-child {
    border-bottom: none;
}

</style>