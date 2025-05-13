<?php
session_start();
require_once '../config/database.php';

// Fetch notifications (example for cancelled and confirmed bookings)
$sql = "SELECT * FROM bookings WHERE status IN ('cancelled', 'confirmed') ORDER BY status_updated_at DESC";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="notification-container">
        <!-- Notification Bell Icon -->
        <div class="notification-bell" onclick="toggleNotifications()">
            <i class="fas fa-bell"></i>
            <span class="notification-count" id="notificationCount"><?php echo count($notifications); ?></span>
        </div>

        <!-- Notifications Dropdown -->
        <div id="notificationDropdown" class="notification-dropdown">
            <h4>Notifications</h4>
            <?php if (count($notifications) > 0): ?>
                <div class="notification-list">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item" data-timestamp="<?php echo htmlspecialchars($notification['status_updated_at']); ?>">
                            <div class="notification-content">
                                <p class="message">
                                    Your Booking <?php echo htmlspecialchars($notification['room_number']); ?> 
                                    has been 
                                    <span class="<?php echo htmlspecialchars($notification['status']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($notification['status'])); ?>
                                    </span>.
                                    <?php if ($notification['status'] == 'confirmed'): ?>
                                        You may now proceed to the hotel to check-in.
                                    <?php endif; ?>
                                </p>
                            </div>
                            <span class="timestamp"></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No new notifications.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Toggle notification dropdown
    function toggleNotifications() {
        var dropdown = document.getElementById('notificationDropdown');
        dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
        
        // Hide the notification count once clicked
        var notificationCount = document.querySelector('.notification-count');
        notificationCount.style.display = 'none'; // Hide the count after clicking bell

        // Store that the notifications have been viewed in localStorage
        localStorage.setItem('notificationsViewed', 'true');
    }

    // Check if notifications have been viewed previously (on page load)
    window.onload = function() {
        var notificationCount = document.getElementById('notificationCount');

        // If the user has already clicked the bell and viewed notifications, hide the count
        if (localStorage.getItem('notificationsViewed') === 'true') {
            notificationCount.style.display = 'none';
        } else {
            // Make sure the notification count is shown if not viewed
            notificationCount.style.display = 'block';
        }

        // Apply timeAgo to all notifications
        const timestamps = document.querySelectorAll('.timestamp');
        timestamps.forEach(function(timestampElement) {
            const notificationItem = timestampElement.closest('.notification-item');
            const notificationTime = notificationItem.getAttribute('data-timestamp');
            timestampElement.textContent = timeAgo(notificationTime);
        });
    };

    // Function to format time difference
    function timeAgo(timestamp) {
        const now = new Date();
        const notificationTime = new Date(timestamp); // Convert string timestamp to Date object

        const diffInSeconds = Math.floor((now - notificationTime) / 1000); // Time in seconds

        const minutes = Math.floor(diffInSeconds / 60);
        const hours = Math.floor(diffInSeconds / 3600);
        const days = Math.floor(diffInSeconds / 86400);

        if (days > 1) return days + ' days ago';
        if (days === 1) return '1 day ago';
        if (hours > 1) return hours + ' hours ago';
        if (hours === 1) return '1 hour ago';
        if (minutes > 1) return minutes + ' minutes ago';
        if (minutes === 1) return '1 minute ago';
        return 'Just now';
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        var dropdown = document.getElementById('notificationDropdown');
        var bellIcon = document.querySelector('.notification-bell');
        
        // If the clicked element is not the dropdown or the bell icon, close the dropdown
        if (!dropdown.contains(event.target) && !bellIcon.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });
</script>

</body>
</html>

<style>
/* General Styles */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f5f5f5;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.notification-container {
    position: relative;
    display: inline-block;
    margin-top: 10px;
}

/* Notification Bell Icon */
.notification-bell {
    font-size: 40px;
    cursor: pointer;
    position: relative;
    color: #333;
}

.notification-count {
    position: absolute;
    top: -5px;
    right: -8px;
    background-color: #ff3b30;
    color: white;
    border-radius: 50%;
    padding: 4px 8px;
    font-size: 14px;
}

/* Notification Dropdown */
.notification-dropdown {
    display: none;
    position: absolute;
    top: 50px;
    right: 0;
    width: 350px;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    padding: 10px;
    z-index: 1000;
    font-family: 'Arial', sans-serif;
    overflow-y: auto;
    max-height: 400px;
    border: 1px solid #ddd;
}

.notification-dropdown h4 {
    margin-top: 0;
    font-size: 18px;
    font-weight: bold;
    color: #333;
    border-bottom: 1px solid #ddd;
    padding-bottom: 8px;
    margin-bottom: 10px;
    text-align: center;
}

/* Notification Item Styling */
.notification-list {
    list-style-type: none;
    padding: 0;
    margin: 0;
}

.notification-item {
    display: flex;
    justify-content: flex-start;
    align-items: flex-start;
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
    color: #333;
    position: relative;
    transition: background-color 0.3s ease-in-out;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-item:hover {
    background-color: #f7f7f7;
    cursor: pointer;
}

.notification-content {
    flex: 1;
}

.notification-item .message {
    margin: 0;
    font-size: 14px;
    color: #333;
}

/* Status Styling */
.notification-item .confirmed {
    color: green; /* Confirmed bookings in green */
    font-weight: bold;
}

.notification-item .cancelled {
    color: red; /* Cancelled bookings in red */
    font-weight: bold;
}

/* Timestamp Styling */
.notification-item .timestamp {
    position: absolute;
    right: 12px;
    bottom: 4px; /* Increased bottom value to add space above the timestamp */
    font-size: 12px;
    color: #999;
    text-align: right;
}

/* No Notifications */
.notification-dropdown p {
    padding: 6px;
    font-size: 14px;
    color: #999;
}

/* Responsive Design */
@media (max-width: 768px) {
    .notification-container {
        margin-top: 20px;
    }

    .notification-dropdown {
        width: 250px;
    }

    .notification-bell {
        font-size: 35px;
    }

    .notification-count {
        padding: 4px 6px;
        font-size: 12px;
    }

    .notification-item .timestamp {
        font-size: 10px;
    }
}
</style>
