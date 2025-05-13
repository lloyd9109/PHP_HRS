<?php
include 'header.php';
require_once '../config/database.php';
require_once '../src/Auth.php';

$current_page = basename($_SERVER['PHP_SELF'], ".php");

// Handle form submission via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = htmlspecialchars($_POST['message']);
    $user_email = $_SESSION['user_email'] ?? null;

    if ($user_email) {
        try {
            $sql = "INSERT INTO messages (email, message) VALUES (:email, :message)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $user_email, ':message' => $message]);
            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
}

// Fetch messages and replies for the user if they are logged in
$user_email = $_SESSION['user_email'] ?? null;
$messages_with_replies = [];

if ($user_email) {
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE email = :email ORDER BY created_at DESC");
    $stmt->execute([':email' => $user_email]);
    $messages_with_replies = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Hotel Reservation</title>
    <link rel="stylesheet" href="./styles/contact.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<!-- Chat Box Section -->
<div class="chat-container">
    <h2>Contact Support</h2>
    <div class="chat-box">
        <div id="chat-messages">
            <?php foreach (array_reverse($messages_with_replies) as $message): ?>
                <div class="user-message">
                    <p><strong>You:</strong> <?= htmlspecialchars($message['message']); ?></p>
                </div>
                <?php if ($message['reply']): ?>
                    <div class="admin-reply">
                        <p><strong>Admin:</strong> <?= htmlspecialchars($message['reply']); ?></p>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <form id="chat-form">
        <textarea id="chat-input" rows="3" placeholder="Type your message..." required></textarea>
        <button type="submit"><i class="fas fa-paper-plane"></i> Send</button>
    </form>
</div>

<footer>
    <?php include 'footer.php'; ?>
</footer>

<script>
$(document).ready(function() {
    // Scroll chat to the bottom
    const chatMessages = $("#chat-messages");
    chatMessages.scrollTop(chatMessages[0].scrollHeight);

    // Handle chat form submission via AJAX
    $("#chat-form").on("submit", function(e) {
        e.preventDefault();  // Prevent normal form submission

        const message = $("#chat-input").val().trim();
        if (message === "") return;

        $.ajax({
            url: "contact.php",  // Same page for handling AJAX request
            method: "POST",
            data: { message: message },  // Send the message data
            success: function(response) {
                const result = JSON.parse(response);
                if (result.status === "success") {
                    // Clear input field
                    $("#chat-input").val('');

                    // Append the new message to the chat box
                    const userMessageHtml = `<div class="user-message"><p><strong>You:</strong> ${message}</p></div>`;
                    chatMessages.append(userMessageHtml);

                    // Scroll chat to the bottom
                    chatMessages.scrollTop(chatMessages[0].scrollHeight);

                    // Dynamically reload the messages (without full page reload)
                    $.ajax({
                        url: 'contact.php',  // Same page to fetch updated messages
                        method: 'GET',
                        success: function(updatedMessages) {
                            $('#chat-messages').html(updatedMessages);
                        }
                    });
                } else {
                    alert("Error: " + result.message);
                }
            },
            error: function() {
                alert("An error occurred while sending your message.");
            }
        });
    });
});
</script>

<style>
    .chat-container {
        max-width: 600px;
        margin: 20px auto;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        background-color: #fff;
    }
    .chat-box {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #ccc;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
        background-color: #f9f9f9;
    }
    #chat-messages {
        display: flex;
        flex-direction: column;
    }
    .user-message, .admin-reply {
        margin-bottom: 10px;
    }
    .user-message {
        text-align: left;
    }
    .admin-reply {
        text-align: right;
        background-color: #e6f7ff;
        padding: 5px;
        border-radius: 5px;
    }
    #chat-form {
        display: flex;
        gap: 10px;
    }
    #chat-input {
        flex-grow: 1;
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 5px;
        resize: none;
    }
    #chat-form button {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
    }
    #chat-form button:hover {
        background-color: #0056b3;
    }
</style>

</body>
</html>
