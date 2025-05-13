<?php
require_once '../src/Auth.php';
require_once '../config/database.php';
require_once 'admin.php';

$admin = new Admin($pdo);

if (!$admin->isLoggedIn()) {
    header('Location: admin_login.php');
    exit();
}

// Fetch admin details
$adminDetails = $admin->getAdminDetails();
$adminFirstName = $adminDetails['firstname'] ?? '';
$adminLastName = $adminDetails['lastname'] ?? '';

// Fetch the 10 most recent bookings based on the booking ID (assuming 'id' is auto-incremented)
$sqlAll = "SELECT full_name, room_number, preferred_date, notified FROM bookings ORDER BY id DESC LIMIT 10";
$stmtAll = $pdo->prepare($sqlAll);
$stmtAll->execute();
$allBookings = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

// Fetch only unnotified bookings for the badge count
$sqlUnnotified = "SELECT COUNT(*) AS count FROM bookings WHERE notified = 0";
$stmtUnnotified = $pdo->prepare($sqlUnnotified);
$stmtUnnotified->execute();
$unnotifiedCount = $stmtUnnotified->fetch(PDO::FETCH_ASSOC)['count'];

// Fetch all users and their chat histories
$stmt = $pdo->prepare("
    SELECT c.id, c.message, c.is_admin, c.timestamp, u.id as user_id, u.first_name, u.last_name 
    FROM chat_messages c 
    JOIN users u ON c.user_id = u.id 
    ORDER BY u.id, c.timestamp ASC
");
$stmt->execute();
$chatData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group messages by user
$messagesByUser = [];
foreach ($chatData as $row) {
    $messagesByUser[$row['user_id']]['user'] = "{$row['first_name']} {$row['last_name']}";
    $messagesByUser[$row['user_id']]['messages'][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./styles/header.css"> 
    <title>Admin Dashboard</title>
</head>
<body>
<div class="navbar">
    <h1>Admin Panel</h1>
    <div class="hamburger" onclick="toggleSidebar()">
        <i class="fas fa-bars fa-2x"></i>
    </div>
    <div class="actions">
        <div class="notification-icon" id="notification-icon">
            <i class="fas fa-bell fa-2x"></i> 
            <?php if ($unnotifiedCount > 0): ?>
                <span class="notification-badge"><?php echo $unnotifiedCount; ?></span>
            <?php endif; ?>
        </div>

        <a class="chat-icon" onclick="toggleChat()">
            <i class="fas fa-comments fa-2x"></i> <!-- Chat icon -->
        </a>

        <div class="dropdown">
            <span class="admin-name"><?php echo $adminFirstName . ' ' . $adminLastName; ?> <i class="fas fa-caret-down"></i></span>
            <div class="dropdown-content">
                <a href="admin_logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="sidebar" id="sidebar">
    <h2>Navigation</h2>
    <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="manage_users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : '' ?>">
        <i class="fas fa-users"></i> Manage Users
    </a>
    <a href="manage_staff.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_staff.php' ? 'active' : '' ?>">
        <i class="fas fa-users-cog"></i> Manage Staff
    </a>
    <a href="manage_rooms.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_rooms.php' ? 'active' : '' ?>">
        <i class="fas fa-bed"></i> Manage Rooms
    </a>
    <a href="manage_bookings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_bookings.php' ? 'active' : '' ?>">
        <i class="fas fa-calendar-check"></i> Manage Bookings
    </a>
    <a href="manage_reservation.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_reservation.php' ? 'active' : '' ?>">
        <i class="fas fa-book"></i> Manage Reservations
    </a>
    <a href="booking_history.php" class="<?= basename($_SERVER['PHP_SELF']) == 'booking_history.php' ? 'active' : '' ?>">
        <i class="fas fa-history"></i> Booking History
    </a>
</div>



<div class="admin-chat-container" id="adminChatContainer">
    <div class="user-list" id="userList">
        <?php foreach ($messagesByUser as $userId => $data): 
            // Get the last message of the user
            $lastMessage = end($data['messages']); 
            
            // Limit the message length to 30 characters
            $displayMessage = strlen($lastMessage['message']) > 30 
                ? substr($lastMessage['message'], 0, 30) . '...' 
                : $lastMessage['message'];
        ?>
            <button class="user-btn" onclick="showChat(<?= $userId ?>)">
                <div class="user-info">
                    <strong class="user-name"><?= htmlspecialchars($data['user']) ?></strong>
                    <p class="last-message"><?= htmlspecialchars($displayMessage) ?></p>
                </div>
                <span class="timestamp"><?= date('h:i A', strtotime($lastMessage['timestamp'])) ?></span>
            </button>
        <?php endforeach; ?>
    </div>
</div>



<div class="chat-window" id="chatWindow">
<div class="chatbox-header">
            <h3>Chat with Us</h3>
            <button class="close-button" onclick="closeChat()">×</button>
        </div>
    <div class="chatbox-body" id="chat-content"></div>
    <div class="chat-input">
        <input type="text" id="adminMessage" class="chat-admin-input" placeholder="Type your reply..." />
        <div class="send-btn" onclick="sendAdminMessage()">Send</div>
    </div>
</div>

        <!-- Notification Dropdown -->
        <div class="notification-dropdown" id="notification-dropdown">
          <div class="dropdown-header">
            <h3>Notifications</h3>
          </div>
          <ul class="notification-list">
      <?php if (!empty($allBookings)) : ?>
        <?php foreach ($allBookings as $booking) : ?>
          <li>
            <img src="https://via.placeholder.com/40" alt="Profile Picture" />
            <div>
              <p>
                <strong><?php echo htmlspecialchars($booking['full_name']); ?></strong> 
                has reserved room <strong><?php echo htmlspecialchars($booking['room_number']); ?></strong> 
                on <strong><?php echo htmlspecialchars($booking['preferred_date']); ?></strong>.
                <?php if ($booking['notified'] == 0) : ?>
                  <span class="new-notification">(New)</span>
                <?php endif; ?>
              </p>
            </div>
          </li>
        <?php endforeach; ?>
      <?php else : ?>
        <li>
          <div>
            <p>No notifications available.</p>
          </div>
        </li>
      <?php endif; ?>
    </ul>


          <div class="dropdown-footer">
          </div>
        </div>
  </div>

</body>
</html>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}

let currentUserId = null;

// Declare the variable for chat state
let isChatOpen = false; // Default state is closed

function toggleChat() {
    const chatContainer = document.getElementById('adminChatContainer');
    const chatIcon = document.querySelector('.chat-icon'); // Get the chat icon

    // Check if the chat is open
    if (isChatOpen) {
        // Close the chat container
        chatContainer.style.display = 'none';
        isChatOpen = false;
    } else {
        // Open the chat container
        chatContainer.style.display = 'flex';
        const chatIconRect = chatIcon.getBoundingClientRect(); // Get the position of the chat icon

        // Position the chat container below the icon
        chatContainer.style.position = 'absolute';
        chatContainer.style.top = `${chatIconRect.bottom + window.scrollY + 10}px`; // Position 10px below the icon
        chatContainer.style.left = `${chatIconRect.left + window.scrollX - chatContainer.offsetWidth / 2 + chatIconRect.width / 2}px`; // Align horizontally with the icon center

        isChatOpen = true;
    }
}

// Close chat when clicking outside the chat container
document.addEventListener('click', function (event) {
    const chatContainer = document.getElementById('adminChatContainer');
    const chatIcon = document.querySelector('.chat-icon');

    // Check if the click was outside of the chat icon and the chat container
    if (isChatOpen && !chatContainer.contains(event.target) && !chatIcon.contains(event.target)) {
        chatContainer.style.display = 'none';
        isChatOpen = false;
    }
});


function showChat(userId) {
    currentUserId = userId;
    document.getElementById('adminChatContainer').style.display = 'none';
    const chatWindow = document.getElementById('chatWindow');
    chatWindow.style.display = 'flex';

    // Fetch and display the messages for the user
    fetchMessages(userId);
}

function fetchMessages(userId) {
    fetch(`get_messages.php?user_id=${userId}`)
        .then(response => response.json())
        .then(messages => {
            const chatContent = document.getElementById('chat-content');
            chatContent.innerHTML = ''; // Clear current chat

            messages.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message-container');

                const timestampDiv = document.createElement('div');
                timestampDiv.classList.add('timestamp');
                timestampDiv.innerText = new Date(msg.timestamp).toLocaleString(); // Format the timestamp

                const messageContentDiv = document.createElement('div');
                messageContentDiv.classList.add('message', msg.is_admin ? 'admin-message' : 'user-message');
                messageContentDiv.innerHTML = `<p>${msg.message}</p>`;

                messageDiv.appendChild(timestampDiv);
                messageDiv.appendChild(messageContentDiv);
                chatContent.appendChild(messageDiv);
            });

            chatContent.scrollTop = chatContent.scrollHeight; // Scroll to the bottom
        });
}


function sendAdminMessage() {
    const messageInput = document.getElementById('adminMessage');
    const adminMessage = messageInput.value;

    if (adminMessage.trim() && currentUserId) {
        fetch('send_admin_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `user_id=${currentUserId}&message=${encodeURIComponent(adminMessage)}`
        }).then(response => response.json())
          .then(data => {
              if (data.status === 'success') {
                  // After sending the message, fetch and display the updated chat
                  fetchMessages(currentUserId);

                  // Clear the input field
                  messageInput.value = '';
              }
          });
    }
}


// Close the chat window
function closeChat() {
    const chatWindow = document.getElementById('chatWindow');
    chatWindow.style.display = 'none';
}

document.addEventListener("DOMContentLoaded", () => {
    const notificationIcon = document.getElementById("notification-icon");
    const notificationDropdown = document.getElementById("notification-dropdown");
    const notificationBadge = document.querySelector(".notification-badge");

    // Toggle dropdown visibility
    notificationIcon.addEventListener("click", () => {
        notificationDropdown.style.display =
            notificationDropdown.style.display === "block" ? "none" : "block";

        // If dropdown is shown, reset the badge count and update notifications
        if (notificationDropdown.style.display === "block" && notificationBadge) {
            fetch('mark_notifications_notified.php', { method: 'POST' })
                .then((response) => {
                    if (response.ok) {
                        notificationBadge.textContent = ""; // Clear badge content
                        notificationBadge.style.display = "none"; // Hide the badge
                    }
                })
                .catch((error) => console.error("Error marking notifications as notified:", error));
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", (event) => {
        if (!notificationIcon.contains(event.target)) {
            notificationDropdown.style.display = "none";
        }
    });
});

</script>

<style>
/* Ensure inputs are well aligned and styled on focus */
.chat-input input[type="text"]:focus {
    outline: none;
    border-color: #00d25b;
    box-shadow: 0 0 5px rgba(0, 210, 91, 0.5);
    background-color: #2f2f3e;
}


.clear-btn {
  background: none;
  border: none;
  color: #1e88e5; /* Blue color for the button */
  font-size: 14px;
  cursor: pointer;
  text-decoration: underline;
}

.clear-btn:hover {
  text-decoration: none;
}

.notification-badge {
    position: absolute;
    top: 10px; /* Slightly above the bell icon */
    right: 300px; /* Slightly to the right of the bell icon */
    background-color: #f02849; /* Red for notifications */
    color: #ffffff;
    font-size: 14px;
    font-weight: bold;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 2px solid #121212; /* Matches background */
    font-size: 12px; /* Adjusts font size */
}

.notification-dropdown {
    display: none;
    position: absolute;
    top: 8%; /* Adjust this value for vertical alignment */
    left: 50%;
    transform: translateX(200px); /* Center the dropdown */
    width: 400px;
    background-color: #1f1f1f;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
    border-radius: 8px;
    overflow: hidden;
    z-index: 1000;
}

.dropdown-header{
    height:20px;
}

.notification-dropdown .dropdown-header {
    background-color: #292929;
    padding: 10px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #e0e0e0;
}

.notification-dropdown .tabs .tab {
    font-size: 14px;
    cursor: pointer;
    margin-left: 10px;
    color: #9e9e9e;
}

.notification-dropdown .tabs .tab.active {
    font-weight: bold;
    color: #ffffff;
}

.notification-list {
    list-style: none;
    padding: 0;
    margin: 0;
    height: 500px; /* Restrict the height */
    overflow-y: auto; /* Enable vertical scrolling */
    scrollbar-width: thin; /* Optional: Customizes scrollbar width for Firefox */
    scrollbar-color: #555 #1f1f1f; /* Optional: Customizes scrollbar colors */
}

.notification-list li {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #383838;
}

.notification-list li img {
    border-radius: 50%;
    width: 40px;
    height: 40px;
    margin-right: 10px;
    border: 2px solid #292929;
}

.notification-list li div {
    flex-grow: 1;
}

.notification-list li p {
    margin: 0;
}

.notification-list li .new-notification {
    color: #f02849;
    font-size: 12px;
}

.dropdown-footer {
    text-align: center;
    padding: 10px;
    background-color: #292929;
}

.dropdown-footer a {
    color: #1e88e5;
    text-decoration: none;
    font-size: 14px;
}

.dropdown-footer a:hover {
    text-decoration: underline;
}

/* Optional: Styling for the scrollbar (Webkit browsers like Chrome, Edge, Safari) */
.notification-list::-webkit-scrollbar {
    width: 8px;
}

.notification-list::-webkit-scrollbar-track {
    background: #1f1f1f;
}

.notification-list::-webkit-scrollbar-thumb {
    background-color: #555;
    border-radius: 4px;
    border: 2px solid #1f1f1f;
}
</style>