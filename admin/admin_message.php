<?php
session_start();
require_once '../config/database.php';

require_once 'admin.php';
include 'header.php';

$admin = new Admin($pdo);

if (!$admin->isLoggedIn()) {
    header('Location: admin_login.php');
    exit();
}

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
    <link rel="stylesheet" href="./styles/admin_message.css"> 
    <title>Admin Chat</title>
    <style>
    </style>
</head>
<body>

<div class="toggle-chat" onclick="toggleChat()">💬 Chat</div>

<div class="admin-chat-container" id="adminChatContainer">
    <div class="user-list" id="userList">
        <?php foreach ($messagesByUser as $userId => $data): 
            // Get the last message of the user
            $lastMessage = end($data['messages']); 
        ?>
            <button class="user-btn" onclick="showChat(<?= $userId ?>)">
                <div>
                    <strong><?= htmlspecialchars($data['user']) ?></strong>
                    <p class="last-message"><?= htmlspecialchars($lastMessage['message']) ?></p>
                </div>
                <span class="timestamp"><?= date('h:i A', strtotime($lastMessage['timestamp'])) ?></span>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<div class="chat-window" id="chatWindow">
    <!-- Close button to close the chat window -->
    <button class="close-btn" onclick="closeChat()">X</button>
    
    <div id="chat-content"></div>
    <div class="chat-input">
        <input type="text" id="adminMessage" placeholder="Type your reply..." />
        <button onclick="sendAdminMessage()">Send</button>
    </div>
</div>

<script>
    let currentUserId = null;

    // Toggle chat container visibility
    function toggleChat() {
        const chatContainer = document.getElementById('adminChatContainer');
        chatContainer.style.display = chatContainer.style.display === 'flex' ? 'none' : 'flex';
    }

    // Show chat content for a specific user
    function showChat(userId) {
        currentUserId = userId;
        document.getElementById('adminChatContainer').style.display = 'none';
        const chatWindow = document.getElementById('chatWindow');
        chatWindow.style.display = 'flex';

        fetch(`get_messages.php?user_id=${userId}`)
            .then(response => response.json())
            .then(messages => {
                const chatContent = document.getElementById('chat-content');
                chatContent.innerHTML = ''; // Clear current chat

                messages.forEach(msg => {
                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add('message', msg.is_admin ? 'admin-message' : 'user-message');
                    messageDiv.innerHTML = `<p>${msg.message}</p><span>${msg.timestamp}</span>`;
                    chatContent.appendChild(messageDiv);
                });

                chatContent.scrollTop = chatContent.scrollHeight;
            });
    }

    // Send a message to the user
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
                  if (data.success) {
                      const chatContent = document.getElementById('chat-content');
                      const messageDiv = document.createElement('div');
                      messageDiv.classList.add('message', 'admin-message');
                      messageDiv.innerHTML = `<p>${adminMessage}</p><span>${data.timestamp}</span>`;
                      chatContent.appendChild(messageDiv);
                      chatContent.scrollTop = chatContent.scrollHeight;
                      messageInput.value = ''; // Clear input
                  }
              });
        }
    }

    // Close the chat window
    function closeChat() {
        const chatWindow = document.getElementById('chatWindow');
        chatWindow.style.display = 'none';
        document.getElementById('adminChatContainer').style.display = 'flex'; // Optionally show the chat container again
    }
</script>

</body>
</html>
