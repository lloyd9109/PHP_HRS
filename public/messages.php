<?php
require_once '../config/database.php';
require_once '../src/Auth.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./styles/messages.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>



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

</script>

