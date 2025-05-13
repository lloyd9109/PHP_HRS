<?php
session_start();
require_once '../config/database.php';
require_once '../src/Auth.php';
$current_page = basename($_SERVER['PHP_SELF'], ".php");

// Display success message if set
if (isset($_SESSION['successMessage'])) {
    echo '<div class="notification success">' . htmlspecialchars($_SESSION['successMessage']) . '</div>';
    unset($_SESSION['successMessage']); // Clear message after displaying
}

if (Auth::isLoggedIn()) {
    $userId = $_SESSION['user_id']; // Get the logged-in user's ID
    
    // Fetch notifications (example for cancelled and confirmed bookings specific to the user)
    $sql = "SELECT * FROM bookings WHERE user_id = :user_id AND status IN ('cancelled', 'confirmed') ORDER BY status_updated_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch user details including profile_image
    $stmt = $pdo->prepare('SELECT first_name, last_name, email, cellphone, profile_image FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $notifications = [];
    $user = null; // Ensure $user is null if not logged in
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Reservation</title>
    <link rel="stylesheet" href="./styles/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<div id="notification" class="notification" style="display: none;"></div>

<header class="header" id="header">
    <div class="header-container">

    <img src="./styles/LOGO.png" alt="Hotel Logo" class="logo">
        <!-- Hamburger Menu Icon (Mobile) -->
        <div class="hamburger" id="hamburgerMenu">
            <i class="fas fa-bars"></i>
        </div>

        <!-- Header Title on the Left -->
        <h1 class="header-title">HOTEL HIVE</h1>

        <!-- Navigation Links in the Center -->
        <nav class="nav-links" id="navLinks">
            <a href="index.php" class="<?php echo ($current_page == 'index') ? 'active' : ''; ?>">Home</a>
            <a href="rooms.php" class="<?php echo ($current_page == 'rooms') ? 'active' : ''; ?>">Rooms</a>
            <a href="bookings.php" class="<?php echo ($current_page == 'bookings' || $current_page == 'booking_history') ? 'active' : ''; ?>">Bookings</a>
            <a href="facilities.php" class="<?php echo ($current_page == 'facilities') ? 'active' : ''; ?>">Facilities</a>
        </nav>

        <div class="auth-buttons" id="authButtons">
        <?php if (Auth::isLoggedIn()): ?>
            <div class="notification-bell" onclick="toggleNotifications()">
                <i class="fas fa-bell"></i>
             
                <span class="notification-count" id="notificationCount"><?php echo count($notifications); ?></span>
            </div>

            <div class="user-info">
                <a href="profile.php">
                    <?php if (is_array($user) && !empty($user['profile_image'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="profile-image">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                </a>
                <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
            </div>


            <div class="dropdown">
                <span class="settings-icon" id="settingsIcon"><i class="fas fa-cog"></i></span>
                <div class="dropdown-menu" id="settingsMenu">
                    <div class="dropdown-header">Settings</div>
                    <a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php" id="accountLoginBtn" class="auth-button">
                <i class="fas fa-user"></i> Sign in
            </a>
        <?php endif; ?>
    </div>

    <!-- Notifications Dropdown -->
    <div class="notification-dropdown"id="notificationDropdown" >
        <div class="dropdown-header">
            <h4>Notifications</h4>
        </div>
            <?php if (count($notifications) > 0): ?>
                <div class="notification-list">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item" data-timestamp="<?php echo htmlspecialchars($notification['status_updated_at']); ?>">
                            <div class="notification-content">
                                <p class="message">
                                    Your Booking <?php echo htmlspecialchars($notification['room_number']); ?> 
                                    has been 
                                    <span class="<?php echo htmlspecialchars($notification['status']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($notification['status'])); ?>.
                                    </span>
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
            <div class="dropdown-footer">
                <button id="clear-notifications" class="clear-btn">Clear all Notifications</button>
            </div>
        </div>
    </div>

</header>

<!-- Floating "Don't have an Account? Signup" Container -->
<?php if (!Auth::isLoggedIn()): ?>
    <div class="floating-signup">
        <p>Don't have an Account? <a href="signup.php" class="signup-btn">Sign Up</a></p>
    </div>
<?php endif; ?>



<?php if (Auth::isLoggedIn()): ?>
    <!-- Only show chatbox if the user is logged in -->
    <div class="chatbox-container">
        <div class="chatbox-header">
            <h3>Chat with Us</h3>
            <button class="close-btn" onclick="toggleChatBox()">×</button>
        </div>
        <div class="chatbox-body">
            <div class="message bot-message">
                <p></p>
            </div>
            <div class="message user-message">
                <p></p>
            </div>
        </div>
        <div class="chatbox-footer">
            <input type="text" placeholder="Type your message..." id="userMessage" />
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>
    <!-- Open Chatbox Button (always visible) -->
    <button class="open-chatbox-btn" onclick="toggleChatBox()">💬</button>
<?php endif; ?>

</body>
</html>

<script>
function toggleChatBox() {
    const chatbox = document.querySelector('.chatbox-container');
    const openButton = document.querySelector('.open-chatbox-btn');
    
    if (chatbox.style.display === 'none' || chatbox.style.display === '') {
        chatbox.style.display = 'flex';
        openButton.style.display = 'none';
        
        // Trigger the transition animation
        setTimeout(() => {
            chatbox.classList.add('show');
        }, 10); // Slight delay to allow for CSS transition to apply

        // Load chat history from the server
        loadChatHistory();
    } else {
        chatbox.classList.remove('show'); // Trigger the fade out
        setTimeout(() => {
            chatbox.style.display = 'none';
            openButton.style.display = 'block';
        }, 300); // Match this delay with the transition duration
    }
}

function loadChatHistory() {
    fetch('get_messages.php')
        .then(response => response.json())
        .then(messages => {
            const chatboxBody = document.querySelector('.chatbox-body');
            chatboxBody.innerHTML = ''; // Clear existing messages
            messages.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message');
                messageDiv.classList.add(msg.is_admin ? 'bot-message' : 'user-message');
                messageDiv.innerHTML = `<p>${msg.message}</p>`;
                chatboxBody.appendChild(messageDiv);
            });

            // Scroll to the bottom of the chatbox
            chatboxBody.scrollTop = chatboxBody.scrollHeight;
        });
}

function sendMessage() {
    const messageInput = document.getElementById('userMessage');
    const userMessage = messageInput.value;

    if (userMessage.trim()) {
        const chatboxBody = document.querySelector('.chatbox-body');

        // Display the user's message immediately
        const userMessageDiv = document.createElement('div');
        userMessageDiv.classList.add('message', 'user-message');
        userMessageDiv.innerHTML = `<p>${userMessage}</p>`;
        chatboxBody.appendChild(userMessageDiv);

        // Clear input field
        messageInput.value = '';

        // Scroll to the bottom
        chatboxBody.scrollTop = chatboxBody.scrollHeight;

        // Send the user's message to the server
        fetch('send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `message=${encodeURIComponent(userMessage)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Optionally, simulate admin's response or trigger it in the backend
                setTimeout(() => {
                    const botMessageDiv = document.createElement('div');
                    botMessageDiv.classList.add('message', 'bot-message');
                    botMessageDiv.innerHTML = `<p>Thank you for your message! We will get back to you shortly.</p>`;
                    chatboxBody.appendChild(botMessageDiv);
                    chatboxBody.scrollTop = chatboxBody.scrollHeight;
                }, 1000);
            }
        });
    }
}

// Hamburger menu toggle
document.getElementById("hamburgerMenu").addEventListener("click", function() {
    document.getElementById("navLinks").classList.toggle("active");
});

// Toggle dropdown menu
document.getElementById("settingsIcon").addEventListener("click", function() {
    const menu = document.getElementById("settingsMenu");
    menu.style.display = menu.style.display === "block" ? "none" : "block";
});

        // Toggle notification dropdown
        function toggleNotifications() {
            var dropdown = document.getElementById('notificationDropdown');
            dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
            
            // Reset the notification count and hide it
            var notificationCount = document.querySelector('.notification-count');
            notificationCount.style.display = 'none'; // Hide the count

            // Store that the notifications have been viewed in localStorage
            localStorage.setItem('notificationsViewed', 'true');
        }

        // Check if notifications have been viewed previously (page reload)
        window.onload = function() {
            var notificationCount = document.getElementById('notificationCount');

            // If the user has already clicked the bell and viewed notifications, hide the count
            if (localStorage.getItem('notificationsViewed') === 'true') {
                notificationCount.style.display = 'none';
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



<style>
/* Logo styles */
.header .logo {
    width: 45px;  /* Adjust the size as needed */
    height: 45px;  /* Ensure the height matches the width for a perfect circle */
    position: absolute;  /* Position the logo outside the normal flow */
    top: 50%;  /* Center the logo vertically */
    left: 100px;  /* Adjust the left margin as needed */
    transform: translateY(-50%) scale(1.8);  /* Perfectly center the logo vertically */
    border-radius: 50%;  /* This creates the circular effect */
    object-fit: cover;  /* Ensures the image doesn't stretch/distort */
}
.notification-bell {
    font-size: 1.5rem; /* Adjust size */
    margin-right: 10px; /* Add spacing between bell icon and user-circle */
    cursor: pointer;
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
    top: 80%; /* Adjust this value for vertical alignment */
    left: 50%;
    transform: translateX(200px); /* Center the dropdown */
    width: 350px;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    overflow: hidden;
    border: 1px solid  #0f969c;
    
}

.notification-dropdown p{
    text-align: center;
    color: black;
}
.notification-dropdown .dropdown-header {
    background-color:  #05161a;
    padding: 10px 15px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #e0e0e0;
    height: 20px;
}


/* Notification Item Styling */
.notification-list {
    list-style: none;
    padding: 0;
    margin: 0;
    height: 400px; 
    overflow-y: auto; 
    scrollbar-width: thin;
    scrollbar-color: #555 #1f1f1f;
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

.dropdown-footer {
    text-align: center;
    padding: 10px;
    background-color:   #05161a;
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
/* Responsive Design */
@media (max-width: 768px) {
    .notification-container {
        margin-top: 20px;
    }

    .notification-dropdown {
        width: 250px;
    }

    .notification-bell {
        font-size: 20px;
    }

    .notification-count {
        padding: 4px 6px;
        font-size: 12px;
    }

    .notification-item .timestamp {
        font-size: 10px;
    }
}

.profile-image {
    width: 40px;       /* Set the width of the image */
    height: 40px;      /* Set the height of the image */
    border-radius: 50%; /* Makes the image circular */
    object-fit: cover; /* Ensures the image covers the circular area without distortion */
    border: 2px solid #ddd; /* Optional: adds a border around the image */
}
/* Media Query for Mobile Devices */
@media (max-width: 768px) {
    /* Adjust the logo size and position on mobile */
    .header .logo {
        width: 35px;
        height: 35px;
        top: 50%;
        left: 130px;  /* Adjust this value based on your design */
        transform: translateY(-50%) scale(1.2);  /* Smaller scale for mobile */
    }

    .header-title {
        font-size: 1.2em;
        margin-left:15px;
    }


    /* Display the hamburger menu on mobile */
    .hamburger {
        display: block;  /* Show hamburger icon */
        position: absolute;
        top: 50%;
        right: 480px;  /* Adjust this value based on your design */
        transform: translateY(-50%);
    }

    /* Optional: Adjust font size for mobile */
    .hamburger i {
        font-size: 2em;
    }
}

/* Media Query for Larger Screens */
@media (min-width: 769px) {
    .hamburger {
        display: none; /* Hide the hamburger menu on larger screens */
    }
}
</style>
